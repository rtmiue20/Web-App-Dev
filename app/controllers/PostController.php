<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../helpers/Upload.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Controller PostController - Quản lý chức năng bài viết (Tuần 2 & Tuần 3 - Phúc)
 * Xử lý: Danh sách, Chi tiết, Đăng bài, Sửa bài, Xóa bài, Like bài viết
 * Phân quyền: Chỉ tác giả hoặc quản trị viên (Admin) mới có quyền Sửa/Xóa
 */
class PostController extends Controller {
    private Post $posts;
    private Category $categories;
    private Comment $comments;
    private Product $products;

    public function __construct() {
        $this->posts = new Post();
        $this->categories = new Category();
        $this->comments = new Comment();
        $this->products = new Product();
    }

    /**
     * Danh sách bài viết theo chuyên mục và phân trang (Tuần 4 - FR03 - Phúc)
     */
    public function index(): void {
        $categoryId = null;
        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $categoryId = (int)$_GET['category'];
            if ($categoryId <= 0) {
                $categoryId = null;
            }
        }

        $page = 1;
        if (isset($_GET['page'])) {
            $page = (int)$_GET['page'];
            if ($page < 1) {
                $page = 1;
            }
        }

        $limit = 6; // 6 bài viết mỗi trang cho người dùng

        $totalPosts = $this->posts->countFiltered($categoryId, 'published');
        $totalPages = 1;
        if ($totalPosts > 0) {
            $totalPages = (int)ceil($totalPosts / $limit);
        }

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $postsList = $this->posts->getPaginated($categoryId, $page, $limit, 'published');
        $categoriesList = $this->categories->getAll();

        $this->render('posts/index', [
            'title' => 'Bài viết dã ngoại',
            'posts' => $postsList,
            'categories' => $categoriesList,
            'selectedCategoryId' => $categoryId,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPosts' => $totalPosts,
            'limit' => $limit
        ]);
    }

    /**
     * Giao diện tạo bài viết mới
     */
    public function create(): void {
        if (!Session::has('user')) {
            Session::flash('error', 'Vui lòng đăng nhập để viết bài chia sẻ.');
            $this->redirect('/login');
            return;
        }

        $categoriesList = $this->categories->getAll();
        $allProducts = $this->products->getAll();
        $this->render('posts/create', [
            'title' => 'Viết bài chia sẻ',
            'categories' => $categoriesList,
            'products' => $allProducts,
            'selectedProductIds' => [],
            'errors' => [],
            'old' => []
        ]);
    }

    /**
     * Xử lý lưu bài viết mới vào CSDL
     */
    public function store(): void {
        if (!Session::has('user')) {
            Session::flash('error', 'Vui lòng đăng nhập để viết bài.');
            $this->redirect('/login');
            return;
        }

        // Lấy danh sách dụng cụ đã chọn (nếu có, không dùng lambda)
        $selectedProductIds = [];
        if (!empty($_POST['product_ids']) && is_array($_POST['product_ids'])) {
            foreach ($_POST['product_ids'] as $pId) {
                $cleanId = (int)$pId;
                if ($cleanId > 0) {
                    $selectedProductIds[] = $cleanId;
                }
            }
        }

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $categoriesList = $this->categories->getAll();
            $allProducts = $this->products->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
                'products' => $allProducts,
                'selectedProductIds' => $selectedProductIds,
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => $_POST
            ]);
            return;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $location = trim((string)($_POST['location'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));
        $lat = (isset($_POST['lat']) && $_POST['lat'] !== '') ? (float)$_POST['lat'] : null;
        $lng = (isset($_POST['lng']) && $_POST['lng'] !== '') ? (float)$_POST['lng'] : null;

        $errors = [];
        if (mb_strlen($title) < 5) {
            $errors['title'] = 'Tiêu đề bài viết phải từ 5 ký tự trở lên.';
        }
        if ($categoryId <= 0) {
            $errors['category_id'] = 'Vui lòng chọn một chuyên mục.';
        }
        if (mb_strlen($content) < 10) {
            $errors['content'] = 'Nội dung bài viết phải từ 10 ký tự trở lên.';
        }
        if ($lat !== null && ($lat < -90 || $lat > 90)) {
            $errors['lat'] = 'Vĩ độ (Lat) phải nằm trong khoảng -90 đến 90.';
        }
        if ($lng !== null && ($lng < -180 || $lng > 180)) {
            $errors['lng'] = 'Kinh độ (Lng) phải nằm trong khoảng -180 đến 180.';
        }

        $imageUrls = [];
        if (!empty($_FILES['images']['name'])) {
            try {
                $imageUrls = Upload::multipleImages($_FILES['images'], 'posts');
            } catch (Exception $e) {
                $errors['images'] = $e->getMessage();
            }
        }
        if (!empty($_FILES['image']['name'])) {
            try {
                $singleImg = Upload::image($_FILES['image'], 'posts');
                if (!empty($singleImg)) {
                    $imageUrls[] = $singleImg;
                }
            } catch (Exception $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $categoriesList = $this->categories->getAll();
            $allProducts = $this->products->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
                'products' => $allProducts,
                'selectedProductIds' => $selectedProductIds,
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 1);
        $roleName = $user['role_name'] ?? '';
        $roleId = (int)($user['role_id'] ?? 0);
        $isAdmin = ($roleName === 'admin' || $roleId === 2);

        // Khi member post bài, trạng thái chuyển về 'pending' (Chờ duyệt). Admin đăng thì được 'published' trực tiếp.
        $status = $isAdmin ? 'published' : 'pending';

        $postId = $this->posts->create(
            $userId, 
            $categoryId, 
            $title, 
            $content, 
            $location ?: null, 
            $imageUrls, 
            $lat, 
            $lng,
            $status
        );

        if ($postId > 0) {
            // Lưu liên kết dụng cụ của chuyến đi vào bảng post_products
            if (!empty($selectedProductIds)) {
                $this->products->syncPostProducts($postId, $selectedProductIds);
            }

            if ($status === 'pending') {
                Session::flash('success', 'Đăng bài viết thành công! Bài viết của bạn đang ở trạng thái Chờ duyệt (Pending) và sẽ được hiển thị công khai sau khi Quản trị viên phê duyệt.');
            } else {
                Session::flash('success', 'Đăng bài viết thành công!');
            }
            $this->redirect('/posts/' . $postId);
        } else {
            $categoriesList = $this->categories->getAll();
            $allProducts = $this->products->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
                'products' => $allProducts,
                'selectedProductIds' => $selectedProductIds,
                'errors' => ['form' => 'Không thể lưu bài viết. Vui lòng thử lại.'],
                'old' => $_POST
            ]);
        }
    }

    /**
     * Xem chi tiết bài viết
     */
    public function show(int $id): void {
        $post = $this->posts->getById($id);
        if (!$post) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy bài viết']);
            return;
        }

        $canManage = $this->canManagePost($post);
        $postStatus = $post['status'] ?? 'published';

        // Nếu bài viết chưa duyệt (pending), bị từ chối (rejected), hoặc bị ẩn (hidden)
        // Chỉ tác giả bài viết hoặc Admin mới có quyền xem trước
        if ($postStatus !== 'published' && !$canManage) {
            http_response_code(404);
            $this->render('errors/404', [
                'title' => 'Bài viết chưa được xuất bản',
                'message' => 'Bài viết này đang chờ duyệt hoặc tạm thời không khả dụng.'
            ]);
            return;
        }

        $commentsList = $this->comments->getByPostId($id);
        $canManage = $this->canManagePost($post);
        $tools = $this->products->getByPostId($id);

        $this->render('posts/show', [
            'title' => $post['title'],
            'post' => $post,
            'tools' => $tools,
            'comments' => $commentsList,
            'canManage' => $canManage
        ]);
    }

    /**
     * Giao diện chỉnh sửa bài viết (Tuần 3 - Phúc)
     * Chỉ tác giả hoặc Admin mới được phép truy cập
     */
    public function edit(int $id): void {
        if (!Session::has('user')) {
            Session::flash('error', 'Vui lòng đăng nhập để chỉnh sửa bài viết.');
            $this->redirect('/login');
            return;
        }

        $post = $this->posts->getById($id);
        if (!$post) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy bài viết']);
            return;
        }

        if (!$this->canManagePost($post)) {
            http_response_code(403);
            $this->render('errors/403', ['title' => 'Bạn không có quyền chỉnh sửa bài viết này']);
            return;
        }

        $categoriesList = $this->categories->getAll();
        $allProducts = $this->products->getAll();
        $tools = $this->products->getByPostId($id);

        $selectedProductIds = [];
        foreach ($tools as $tool) {
            $selectedProductIds[] = (int)($tool['id'] ?? 0);
        }

        $this->render('posts/edit', [
            'title' => 'Chỉnh sửa bài viết',
            'post' => $post,
            'categories' => $categoriesList,
            'products' => $allProducts,
            'selectedProductIds' => $selectedProductIds,
            'errors' => [],
            'old' => $post
        ]);
    }

    /**
     * Xử lý cập nhật bài viết (Tuần 3 - Phúc)
     */
    public function update(int $id): void {
        if (!Session::has('user')) {
            Session::flash('error', 'Vui lòng đăng nhập để thực hiện.');
            $this->redirect('/login');
            return;
        }

        $post = $this->posts->getById($id);
        if (!$post) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy bài viết']);
            return;
        }

        if (!$this->canManagePost($post)) {
            http_response_code(403);
            $this->render('errors/403', ['title' => 'Bạn không có quyền chỉnh sửa bài viết này']);
            return;
        }

        // Lấy danh sách dụng cụ đã chọn từ form (không dùng lambda)
        $selectedProductIds = [];
        if (!empty($_POST['product_ids']) && is_array($_POST['product_ids'])) {
            foreach ($_POST['product_ids'] as $pId) {
                $cleanId = (int)$pId;
                if ($cleanId > 0) {
                    $selectedProductIds[] = $cleanId;
                }
            }
        }

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $categoriesList = $this->categories->getAll();
            $allProducts = $this->products->getAll();
            $this->render('posts/edit', [
                'title' => 'Chỉnh sửa bài viết',
                'post' => $post,
                'categories' => $categoriesList,
                'products' => $allProducts,
                'selectedProductIds' => $selectedProductIds,
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => $_POST
            ]);
            return;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $location = trim((string)($_POST['location'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));
        $lat = (isset($_POST['lat']) && $_POST['lat'] !== '') ? (float)$_POST['lat'] : null;
        $lng = (isset($_POST['lng']) && $_POST['lng'] !== '') ? (float)$_POST['lng'] : null;

        $errors = [];
        if (mb_strlen($title) < 5) {
            $errors['title'] = 'Tiêu đề bài viết phải từ 5 ký tự trở lên.';
        }
        if ($categoryId <= 0) {
            $errors['category_id'] = 'Vui lòng chọn một chuyên mục.';
        }
        if (mb_strlen($content) < 10) {
            $errors['content'] = 'Nội dung bài viết phải từ 10 ký tự trở lên.';
        }
        if ($lat !== null && ($lat < -90 || $lat > 90)) {
            $errors['lat'] = 'Vĩ độ (Lat) phải nằm trong khoảng -90 đến 90.';
        }
        if ($lng !== null && ($lng < -180 || $lng > 180)) {
            $errors['lng'] = 'Kinh độ (Lng) phải nằm trong khoảng -180 đến 180.';
        }

        $imageUrls = [];
        if (!empty($_FILES['images']['name'])) {
            try {
                $imageUrls = Upload::multipleImages($_FILES['images'], 'posts');
            } catch (Exception $e) {
                $errors['images'] = $e->getMessage();
            }
        }
        if (!empty($_FILES['image']['name'])) {
            try {
                $singleImg = Upload::image($_FILES['image'], 'posts');
                if (!empty($singleImg)) {
                    $imageUrls[] = $singleImg;
                }
            } catch (Exception $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $categoriesList = $this->categories->getAll();
            $allProducts = $this->products->getAll();
            $this->render('posts/edit', [
                'title' => 'Chỉnh sửa bài viết',
                'post' => $post,
                'categories' => $categoriesList,
                'products' => $allProducts,
                'selectedProductIds' => $selectedProductIds,
                'errors' => $errors,
                'old' => array_merge($post, $_POST)
            ]);
            return;
        }

        $updated = $this->posts->update(
            $id,
            $categoryId,
            $title,
            $content,
            $location ?: null,
            $imageUrls,
            $lat,
            $lng
        );

        if ($updated) {
            // Cập nhật danh sách dụng cụ của bài viết trong post_products
            $this->products->syncPostProducts($id, $selectedProductIds);

            Session::flash('success', 'Cập nhật bài viết thành công!');
            $this->redirect('/posts/' . $id);
        } else {
            $categoriesList = $this->categories->getAll();
            $allProducts = $this->products->getAll();
            $this->render('posts/edit', [
                'title' => 'Chỉnh sửa bài viết',
                'post' => $post,
                'categories' => $categoriesList,
                'products' => $allProducts,
                'selectedProductIds' => $selectedProductIds,
                'errors' => ['form' => 'Không thể lưu thay đổi. Vui lòng thử lại.'],
                'old' => $_POST
            ]);
        }
    }


    /**
     * Xử lý xóa bài viết (Tuần 3 - Phúc)
     * Chỉ tác giả bài viết hoặc Admin mới được xóa
     */
    public function delete(int $id): void {
        if (!Session::has('user')) {
            Session::flash('error', 'Vui lòng đăng nhập để thực hiện.');
            $this->redirect('/login');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/posts/' . $id);
            return;
        }

        $post = $this->posts->getById($id);
        if (!$post) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy bài viết']);
            return;
        }

        if (!$this->canManagePost($post)) {
            http_response_code(403);
            $this->render('errors/403', ['title' => 'Bạn không có quyền xóa bài viết này']);
            return;
        }

        $deleted = $this->posts->delete($id);
        if ($deleted) {
            Session::flash('success', 'Đã xóa bài viết thành công!');
            $this->redirect('/posts');
        } else {
            Session::flash('error', 'Không thể xóa bài viết. Vui lòng thử lại.');
            $this->redirect('/posts/' . $id);
        }
    }

    /**
     * Thích bài viết qua AJAX
     */
    public function like(int $id): void {
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::has('user')) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thích bài viết.']);
            exit;
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);
        $newLikes = $this->posts->toggleLike($id, $userId);

        echo json_encode([
            'success' => true,
            'data' => [
                'likes' => $newLikes,
                'liked' => true
            ]
        ]);
        exit;
    }

    /**
     * Kiểm tra quyền quản lý bài viết (Chỉ tác giả hoặc admin)
     */
    private function canManagePost(array $post): bool {
        $user = Session::get('user');
        if (!is_array($user)) {
            return false;
        }
        $userId = (int)($user['id'] ?? 0);
        $roleName = $user['role_name'] ?? '';
        $roleId = (int)($user['role_id'] ?? 0);
        $isAdmin = ($roleName === 'admin' || $roleId === 2);
        $isAuthor = ($userId > 0 && $userId === (int)($post['user_id'] ?? 0));
        return $isAdmin || $isAuthor;
    }
}
