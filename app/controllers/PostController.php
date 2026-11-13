<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/PlaceReview.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../helpers/Upload.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../services/PostService.php';

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
    private PostService $postService;

    public function __construct() {
        $this->posts = new Post();
        $this->categories = new Category();
        $this->comments = new Comment();
        $this->products = new Product();
        $this->postService = new PostService();
    }

    /**
     * Danh sách bài viết theo chuyên mục, tìm kiếm nâng cao (Fulltext & Bộ lọc) và phân trang (Tuần 4 - Phúc)
     */
    public function index(): void {
        $feedData = $this->postService->getPublicFeed($_GET, 6);
        $feedData['title'] = 'Bài viết dã ngoại';

        $this->render('posts/index', $feedData);
    }

    /**
     * Giao diện tạo bài viết mới
     */
    public function create(): void {
        if (!Auth::isMember()) {
            Session::flash('error', Auth::denyGuestMessage('viết bài chia sẻ'));
            $this->redirect(Auth::check() ? '/posts' : '/login');
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
        if (!Auth::isMember()) {
            Session::flash('error', Auth::denyGuestMessage('viết bài chia sẻ'));
            $this->redirect(Auth::check() ? '/posts' : '/login');
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
        $cloudUrl = trim((string)($_POST['cloud_image_url'] ?? ''));
        if ($cloudUrl !== '') {
            $splitUrls = preg_split('/[\r\n,]+/', $cloudUrl);
            foreach ($splitUrls as $cUrl) {
                $cUrl = trim($cUrl);
                if ($cUrl !== '' && filter_var($cUrl, FILTER_VALIDATE_URL)) {
                    $imageUrls[] = $cUrl;
                }
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
        $canPublishDirectly = Auth::canPublishDirectly($user);

        // Khi member post bài, trạng thái chuyển về 'pending' (Chờ duyệt).
        // Khi Moderator hoặc Admin đăng thì được 'published' trực tiếp.
        $status = $canPublishDirectly ? 'published' : 'pending';

        $regionInput = trim((string)($_POST['region'] ?? ''));
        $customRegion = trim((string)($_POST['custom_region'] ?? ''));
        if ($regionInput === 'Quốc tế khác') {
            $regionInput = $customRegion !== '' ? $customRegion : 'Quốc tế';
        }

        $tripData = [
            'route'          => trim((string)($_POST['route'] ?? '')),
            'trip_duration'  => trim((string)($_POST['trip_duration'] ?? '')),
            'estimated_cost' => trim((string)($_POST['estimated_cost'] ?? '')),
            'gear_needed'    => trim((string)($_POST['gear_needed'] ?? '')),
            'difficulty'     => trim((string)($_POST['difficulty'] ?? 'medium')),
            'region'         => $regionInput,
            'activity_type'  => trim((string)($_POST['activity_type'] ?? '')),
        ];

        $postId = $this->posts->create(
            $userId, 
            $categoryId, 
            $title, 
            $content, 
            $location ?: null, 
            $imageUrls, 
            $lat, 
            $lng,
            $status,
            $tripData
        );

        if ($postId > 0) {
            // Xử lý các dụng cụ tự nhập (custom tools) kèm link mua (Shopee, Lazada, ...), giá, ảnh
            $customToolIds = $this->processCustomTools($categoryId > 0 ? $categoryId : null);
            if (!empty($customToolIds)) {
                foreach ($customToolIds as $cId) {
                    $selectedProductIds[] = $cId;
                }
            }
            $selectedProductIds = array_values(array_unique($selectedProductIds));

            // Lưu liên kết dụng cụ của chuyến đi vào bảng post_products
            if (!empty($selectedProductIds)) {
                $this->products->syncPostProducts($postId, $selectedProductIds);
                if ($status === 'published') {
                    $idListStr = implode(',', array_map('intval', $selectedProductIds));
                    if ($idListStr !== '') {
                        try {
                            Database::execute("UPDATE products SET is_shared = 1 WHERE id IN ({$idListStr})");
                        } catch (Throwable $e) {
                        }
                    }
                }
            }

            if (class_exists('Notification')) {
                $shortTitle = mb_substr($title, 0, 35);
                $authorName = (string)($user['username'] ?? 'Thành viên');
                Notification::send(
                    $userId,
                    $userId,
                    'create_post',
                    $postId,
                    $status === 'pending'
                        ? "Bài viết '{$shortTitle}' đã được gửi và đang chờ duyệt."
                        : "Bạn đã đăng bài viết mới: '{$shortTitle}'."
                );
                if ($status === 'pending') {
                    Notification::notifyAdmins(
                        $userId,
                        'admin_pending_post',
                        $postId,
                        "{$authorName} vừa gửi bài viết mới chờ duyệt: '{$shortTitle}'."
                    );
                } else {
                    Notification::notifyFollowersOnNewPost($userId, $authorName, $categoryId, $postId, $title);
                }
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

        // Tăng số mắt xem (views_count) khi bài viết đã xuất bản (tránh spam F5 trong cùng phiên làm việc)
        if ($postStatus === 'published') {
            $viewedPosts = (array)Session::get('viewed_posts', []);
            if (!in_array($id, $viewedPosts, true)) {
                $this->posts->incrementViews($id);
                $viewedPosts[] = $id;
                Session::set('viewed_posts', $viewedPosts);
                $post['views_count'] = ((int)($post['views_count'] ?? 0)) + 1;
            }
        }

        $currentUserId = Auth::check() ? (int)Auth::id() : 0;
        $commentsTree = $this->comments->getTreeByPostId($id, $currentUserId);
        $totalComments = $this->comments->countByPost($id);
        $tools = $this->products->getByPostId($id);
        $userVote = Auth::check() ? $this->posts->getUserVote($id, Auth::id()) : 0;

        $bookmarkModel = new Bookmark();
        $isBookmarked = $bookmarkModel->isBookmarked($currentUserId, $id);

        $authorId = (int)($post['user_id'] ?? 0);
        $followModel = new Follow();
        $isFollowingAuthor = $followModel->isFollowingUser($currentUserId, $authorId);
        $authorFollowersCount = $followModel->getFollowersCount($authorId);
        $authorGamification = Gamification::calculate($authorId);
        $placeReviews = new PlaceReview();

        // Tối ưu hóa Web Service Thời tiết Open-Meteo: lấy dữ liệu từ cache hoặc cURL Multi để SSR
        // Giúp giao diện bài viết hiển thị thời tiết NGAY TỨC THÌ (0ms), không bị kẹt spinner loading
        $initialWeather = null;
        if (!empty($post['lat']) && !empty($post['lng'])) {
            try {
                $weatherService = new WeatherService();
                $initialWeather = $weatherService->getForecast((float)$post['lat'], (float)$post['lng']);
            } catch (Throwable $wEx) {
                $initialWeather = null;
            }
        }

        $this->render('posts/show', [
            'title'                => $post['title'],
            'post'                 => $post,
            'initialWeather'       => $initialWeather,
            'placeReviews'         => $placeReviews->getByPost($id),
            'placeRating' => $placeReviews->getSummary($id),
            'tools'                => $tools,
            'comments'             => $commentsTree,
            'totalComments'        => $totalComments,
            'canManage'            => $canManage,
            'userVote'             => $userVote,
            'isBookmarked'         => $isBookmarked,
            'isFollowingAuthor'    => $isFollowingAuthor,
            'authorFollowersCount' => $authorFollowersCount,
            'authorGamification'   => $authorGamification,
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
            'tools' => $tools,
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
        $cloudUrl = trim((string)($_POST['cloud_image_url'] ?? ''));
        if ($cloudUrl !== '') {
            $splitUrls = preg_split('/[\r\n,]+/', $cloudUrl);
            foreach ($splitUrls as $cUrl) {
                $cUrl = trim($cUrl);
                if ($cUrl !== '' && filter_var($cUrl, FILTER_VALIDATE_URL)) {
                    $imageUrls[] = $cUrl;
                }
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

        $regionInput = trim((string)($_POST['region'] ?? ''));
        $customRegion = trim((string)($_POST['custom_region'] ?? ''));
        if ($regionInput === 'Quốc tế khác') {
            $regionInput = $customRegion !== '' ? $customRegion : 'Quốc tế';
        }

        $tripData = [
            'route'          => trim((string)($_POST['route'] ?? '')),
            'trip_duration'  => trim((string)($_POST['trip_duration'] ?? '')),
            'estimated_cost' => trim((string)($_POST['estimated_cost'] ?? '')),
            'gear_needed'    => trim((string)($_POST['gear_needed'] ?? '')),
            'difficulty'     => trim((string)($_POST['difficulty'] ?? 'medium')),
            'region'         => $regionInput,
            'activity_type'  => trim((string)($_POST['activity_type'] ?? '')),
        ];

        $updated = $this->posts->update(
            $id,
            $categoryId,
            $title,
            $content,
            $location ?: null,
            $imageUrls,
            $lat,
            $lng,
            null,
            $tripData
        );

        if ($updated) {
            // Xóa các ảnh được đánh dấu gỡ bỏ trong post_images (nếu có)
            if (!empty($_POST['delete_image_ids']) && is_array($_POST['delete_image_ids'])) {
                $this->posts->deleteImages($id, $_POST['delete_image_ids']);
            }

            // Cập nhật thông tin các dụng cụ hiện có (nếu tác giả sửa đổi trực tiếp)
            $this->processUpdateExistingTools();

            // Xử lý các dụng cụ tự nhập mới (custom tools) kèm link mua, giá, ảnh
            $customToolIds = $this->processCustomTools($categoryId > 0 ? $categoryId : null);
            if (!empty($customToolIds)) {
                foreach ($customToolIds as $cId) {
                    $selectedProductIds[] = $cId;
                }
            }
            $selectedProductIds = array_values(array_unique($selectedProductIds));

            // Cập nhật danh sách dụng cụ của bài viết trong post_products
            $this->products->syncPostProducts($id, $selectedProductIds);
            if (($post['status'] ?? '') === 'published') {
                $idListStr = implode(',', array_map('intval', $selectedProductIds));
                if ($idListStr !== '') {
                    try {
                        Database::execute("UPDATE products SET is_shared = 1 WHERE id IN ({$idListStr})");
                    } catch (Throwable $e) {
                    }
                }
            }

            if (class_exists('Notification')) {
                $currentUserId = (int)Auth::id();
                $postAuthorId = (int)($post['user_id'] ?? 0);
                $shortTitle = mb_substr($title, 0, 38);
                if ($currentUserId > 0) {
                    Notification::send(
                        $currentUserId,
                        $currentUserId,
                        'update_post',
                        $id,
                        "Bạn đã cập nhật bài viết: '{$shortTitle}'."
                    );
                }
                if ($postAuthorId > 0 && $postAuthorId !== $currentUserId) {
                    Notification::send(
                        $postAuthorId,
                        $currentUserId > 0 ? $currentUserId : $postAuthorId,
                        'system',
                        $id,
                        "Ban quản trị đã cập nhật nội dung bài viết '{$shortTitle}' của bạn."
                    );
                }
            }

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
            if (class_exists('Notification')) {
                $currentUserId = (int)Auth::id();
                $postAuthorId = (int)($post['user_id'] ?? 0);
                $shortTitle = mb_substr((string)($post['title'] ?? 'bài viết'), 0, 38);
                if ($currentUserId > 0) {
                    Notification::send(
                        $currentUserId,
                        $currentUserId,
                        'delete_post',
                        0,
                        "Bạn đã xóa bài viết: '{$shortTitle}'."
                    );
                }
                if ($postAuthorId > 0 && $postAuthorId !== $currentUserId) {
                    Notification::send(
                        $postAuthorId,
                        $currentUserId > 0 ? $currentUserId : $postAuthorId,
                        'post_rejected',
                        0,
                        "Ban quản trị đã xóa bài viết '{$shortTitle}' của bạn."
                    );
                }
            }
            $redirectTarget = (!empty($_POST['redirect']) && $_POST['redirect'] === 'profile')
                ? '/profile?tab=posts'
                : '/posts';
            Session::flash('success', 'Đã xóa bài viết thành công!');
            $this->redirect($redirectTarget);
        } else {
            Session::flash('error', 'Không thể xóa bài viết. Vui lòng thử lại.');
            $this->redirect('/posts/' . $id);
        }
    }

    /**
     * Upvote (+1) / Downvote (-1) bài viết qua AJAX
     */
    public function like(int $id): void {
        if (!Auth::isMember()) {
            Response::json(['success' => false, 'message' => Auth::denyGuestMessage('bình chọn (Upvote / Downvote) bài viết')], Auth::denyGuestStatusCode());
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $csrfToken = (string)($json['_csrf'] ?? $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!Csrf::verify($csrfToken)) {
            Response::json(['success' => false, 'message' => 'CSRF token không hợp lệ. Vui lòng tải lại trang.'], 403);
            return;
        }

        $voteRaw = $json['vote'] ?? $_POST['vote'] ?? 1;
        $voteType = ((int)$voteRaw < 0) ? -1 : 1;

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);
        $voteData = $this->posts->vote($id, $userId, $voteType);

        if (class_exists('Notification') && (int)($voteData['user_vote'] ?? 0) !== 0) {
            $post = $this->posts->getById($id);
            if ($post) {
                $shortTitle = mb_substr((string)($post['title'] ?? 'bài viết'), 0, 35);
                $postAuthorId = (int)($post['user_id'] ?? 0);
                $isUpvote = (int)($voteData['user_vote'] ?? 0) === 1;

                Notification::send(
                    $userId,
                    $userId,
                    'like_post',
                    $id,
                    $isUpvote
                        ? "Bạn đã thích (Upvote) bài viết: '{$shortTitle}'."
                        : "Bạn đã đánh giá (Downvote) bài viết: '{$shortTitle}'."
                );

                if ($postAuthorId > 0 && $postAuthorId !== $userId) {
                    Notification::send(
                        $postAuthorId,
                        $userId,
                        'like_post',
                        $id,
                        ($user['username'] ?? 'Một thành viên') . ($isUpvote ? " đã thích bài viết '{$shortTitle}' của bạn." : " đã bình chọn bài viết '{$shortTitle}' của bạn.")
                    );
                }
            }
        }

        Response::json([
            'success' => true,
            'data'    => $voteData
        ]);
    }

    /**
     * Xử lý các dụng cụ tự nhập (custom tools) từ form đăng bài / sửa bài
     * Lưu vào CSDL bảng products với link mua (Shopee, Lazada...), giá, hình ảnh và mô tả
     * @param int|null $categoryId Chuyên mục của bài viết
     * @return array Danh sách ID các sản phẩm vừa tạo
     */
    private function processCustomTools(?int $categoryId): array
    {
        $createdIds = [];
        $names = $_POST['custom_tool_name'] ?? [];
        if (!is_array($names)) {
            return $createdIds;
        }

        $prices = $_POST['custom_tool_price'] ?? [];
        $links = $_POST['custom_tool_link'] ?? [];
        $descs = $_POST['custom_tool_desc'] ?? [];
        $imageUrls = $_POST['custom_tool_image_url'] ?? [];
        $sharedList = $_POST['custom_tool_is_shared'] ?? [];

        $count = count($names);
        for ($i = 0; $i < $count; $i++) {
            $name = trim(trim((string)($names[$i] ?? '')), " \t\n\r\0\x0B,;");
            if ($name === '') {
                continue;
            }

            $priceRaw = (string)($prices[$i] ?? '0');
            $price = (float)preg_replace('/[^\d.]/', '', $priceRaw);

            $link = trim((string)($links[$i] ?? ''));
            if ($link !== '' && !preg_match('#^https?://#i', $link)) {
                $link = 'https://' . $link;
            }

            $desc = trim((string)($descs[$i] ?? ''));
            $imgUrl = trim((string)($imageUrls[$i] ?? ''));
            $isShared = (!isset($sharedList[$i]) || (string)$sharedList[$i] === '1' || $sharedList[$i] === 1) ? 1 : 0;

            // Kiểm tra upload file ảnh từ máy
            if (!empty($_FILES['custom_tool_file']['name'][$i]) && ($_FILES['custom_tool_file']['error'][$i] ?? null) === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name'     => $_FILES['custom_tool_file']['name'][$i],
                    'type'     => $_FILES['custom_tool_file']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['custom_tool_file']['tmp_name'][$i],
                    'error'    => $_FILES['custom_tool_file']['error'][$i],
                    'size'     => $_FILES['custom_tool_file']['size'][$i] ?? 0,
                ];
                try {
                    $savedPath = Upload::image($singleFile, 'products');
                    if (!empty($savedPath)) {
                        $imgUrl = $savedPath;
                    }
                } catch (Exception $e) {
                    error_log('Upload custom tool image failed: ' . $e->getMessage());
                }
            }

            if ($imgUrl === '') {
                $imgUrl = 'assets/img/tent-forest.png';
            }

            $currentUserId = Auth::id();
            $newId = $this->products->create(
                $categoryId > 0 ? $categoryId : null,
                $name,
                $price,
                $imgUrl,
                $desc ?: 'Dụng cụ được chia sẻ bởi người đăng bài',
                $link !== '' ? $link : null,
                $isShared,
                $currentUserId
            );

            if ($newId > 0) {
                $createdIds[] = $newId;
            }
        }

        return $createdIds;
    }

    /**
     * Cập nhật thông tin các dụng cụ đã gắn với bài viết nếu tác giả sửa đổi trực tiếp
     */
    private function processUpdateExistingTools(): void
    {
        $ids = $_POST['existing_tool_id'] ?? [];
        if (!is_array($ids)) {
            return;
        }

        $names = $_POST['existing_tool_name'] ?? [];
        $prices = $_POST['existing_tool_price'] ?? [];
        $links = $_POST['existing_tool_link'] ?? [];
        $descs = $_POST['existing_tool_desc'] ?? [];
        $imageUrls = $_POST['existing_tool_image_url'] ?? [];
        $sharedList = $_POST['existing_tool_is_shared'] ?? [];

        $count = count($ids);
        for ($i = 0; $i < $count; $i++) {
            $pId = (int)($ids[$i] ?? 0);
            if ($pId <= 0) {
                continue;
            }

            $name = trim(trim((string)($names[$i] ?? '')), " \t\n\r\0\x0B,;");
            if ($name === '') {
                continue;
            }

            $priceRaw = (string)($prices[$i] ?? '0');
            $price = (float)preg_replace('/[^\d.]/', '', $priceRaw);

            $link = trim((string)($links[$i] ?? ''));
            if ($link !== '' && !preg_match('#^https?://#i', $link)) {
                $link = 'https://' . $link;
            }

            $desc = trim((string)($descs[$i] ?? ''));
            $imgUrl = trim((string)($imageUrls[$i] ?? ''));
            $isShared = (!isset($sharedList[$i]) || (string)$sharedList[$i] === '1' || $sharedList[$i] === 1) ? 1 : 0;

            if (!empty($_FILES['existing_tool_file']['name'][$i]) && ($_FILES['existing_tool_file']['error'][$i] ?? null) === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name'     => $_FILES['existing_tool_file']['name'][$i],
                    'type'     => $_FILES['existing_tool_file']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['existing_tool_file']['tmp_name'][$i],
                    'error'    => $_FILES['existing_tool_file']['error'][$i],
                    'size'     => $_FILES['existing_tool_file']['size'][$i] ?? 0,
                ];
                try {
                    $savedPath = Upload::image($singleFile, 'products');
                    if (!empty($savedPath)) {
                        $imgUrl = $savedPath;
                    }
                } catch (Exception $e) {
                    error_log('Upload existing tool image failed: ' . $e->getMessage());
                }
            }

            $current = $this->products->findById($pId);
            $catId = $current['category_id'] ?? null;
            if ($imgUrl === '' && !empty($current['image_url'])) {
                $imgUrl = $current['image_url'];
            }

            $this->products->update(
                $pId,
                $catId,
                $name,
                $price,
                $imgUrl !== '' ? $imgUrl : null,
                $desc,
                $link !== '' ? $link : null,
                $isShared
            );
        }
    }

    /**
     * Kiểm tra quyền quản lý bài viết (Tác giả bài viết, Moderator hoặc Admin)
     */
    private function canManagePost(array $post): bool {
        return Auth::canManagePost($post);
    }
}
