<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Comment.php';
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

    public function __construct() {
        $this->posts = new Post();
        $this->categories = new Category();
        $this->comments = new Comment();
    }

    /**
     * Danh sách bài viết
     */
    public function index(): void {
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $postsList = $this->posts->getAll($categoryId);
        $categoriesList = $this->categories->getAll();

        $this->render('posts/index', [
            'title' => 'Bài viết dã ngoại',
            'posts' => $postsList,
            'categories' => $categoriesList,
            'selectedCategoryId' => $categoryId
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
        $this->render('posts/create', [
            'title' => 'Viết bài chia sẻ',
            'categories' => $categoriesList,
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

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $categoriesList = $this->categories->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
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

        $imageUrl = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $imageUrl = Upload::image($_FILES['image'], 'posts');
            } catch (Exception $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $categoriesList = $this->categories->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 1);

        $postId = $this->posts->create(
            $userId, 
            $categoryId, 
            $title, 
            $content, 
            $location ?: null, 
            $imageUrl, 
            $lat, 
            $lng
        );

        if ($postId > 0) {
            Session::flash('success', 'Đăng bài viết thành công!');
            $this->redirect('/posts/' . $postId);
        } else {
            $categoriesList = $this->categories->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
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

        $commentsList = $this->comments->getByPostId($id);
        $canManage = $this->canManagePost($post);

        $this->render('posts/show', [
            'title' => $post['title'],
            'post' => $post,
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
        $this->render('posts/edit', [
            'title' => 'Chỉnh sửa bài viết',
            'post' => $post,
            'categories' => $categoriesList,
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

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $categoriesList = $this->categories->getAll();
            $this->render('posts/edit', [
                'title' => 'Chỉnh sửa bài viết',
                'post' => $post,
                'categories' => $categoriesList,
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

        $imageUrl = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $imageUrl = Upload::image($_FILES['image'], 'posts');
            } catch (Exception $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $categoriesList = $this->categories->getAll();
            $this->render('posts/edit', [
                'title' => 'Chỉnh sửa bài viết',
                'post' => $post,
                'categories' => $categoriesList,
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
            $imageUrl,
            $lat,
            $lng
        );

        if ($updated) {
            Session::flash('success', 'Cập nhật bài viết thành công!');
            $this->redirect('/posts/' . $id);
        } else {
            $categoriesList = $this->categories->getAll();
            $this->render('posts/edit', [
                'title' => 'Chỉnh sửa bài viết',
                'post' => $post,
                'categories' => $categoriesList,
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
