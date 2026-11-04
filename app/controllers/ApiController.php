<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/WeatherService.php';
require_once __DIR__ . '/../services/VietnamAddressService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Controller ApiController - Xử lý các endpoint AJAX (JSON) & Tích hợp Web Service ngoài
 * Phục vụ:
 * 1. FR08: Like/vote bài viết & Thích bình luận không reload trang
 * 2. FR07: Bình luận realtime đa cấp (polling/fetch định kỳ), xóa bình luận
 * 3. FR04: Tìm kiếm gợi ý (autocomplete) khi gõ
 * 4. FR03: Phân trang / "tải thêm" bài viết (Infinite Scroll)
 * 5. FR01: Kiểm tra trùng username/email ngay khi đăng ký
 * 6. FR06: Thời tiết Open-Meteo, Bản đồ, Địa chỉ hành chính Việt Nam
 */
class ApiController extends Controller
{
    private WeatherService $weatherService;
    private VietnamAddressService $addressService;
    private Comment $comments;
    private Post $posts;
    private User $users;

    public function __construct()
    {
        $this->weatherService = new WeatherService();
        $this->addressService = new VietnamAddressService();
        $this->comments = new Comment();
        $this->posts = new Post();
        $this->users = new User();
    }

    /**
     * GET /api/weather?lat=...&lng=...
     * Gọi Web Service Open-Meteo lấy thời tiết hiện tại và dự báo 4 ngày
     */
    public function weather(): void
    {
        $lat = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : 21.2833;
        $lng = isset($_GET['lng']) && is_numeric($_GET['lng']) ? (float)$_GET['lng'] : 104.3167;

        $forecast = $this->weatherService->getForecast($lat, $lng);
        Response::json([
            'success' => true,
            'data'    => $forecast
        ]);
    }

    /**
     * GET /api/provinces
     * Gọi Web Service lấy danh sách Tỉnh/Thành phố & Quận/Huyện Việt Nam kèm tọa độ
     */
    public function provinces(): void
    {
        $provinces = $this->addressService->getProvinces();
        $provinceCode = isset($_GET['code']) ? trim((string)$_GET['code']) : '';

        if ($provinceCode !== '') {
            foreach ($provinces as $prov) {
                if ((string)$prov['code'] === $provinceCode) {
                    Response::json([
                        'success' => true,
                        'data'    => $prov
                    ]);
                    return;
                }
            }
            Response::json(['success' => false, 'message' => 'Không tìm thấy tỉnh/thành phố.'], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data'    => $provinces
        ]);
    }

    /**
     * GET /api/comments?post_id=...
     * Lấy danh sách bình luận của bài viết qua AJAX (FR07 - Hỗ trợ Realtime Polling)
     */
    public function listComments(): void
    {
        $postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
        if ($postId <= 0) {
            Response::json(['success' => false, 'message' => 'Thiếu ID bài viết.'], 400);
            return;
        }

        $user = Session::get('user');
        $currentUserId = (int)($user['id'] ?? 0);
        $canModerate = Auth::isModerator($user);

        $rows = $this->comments->getByPostId($postId, $currentUserId);
        $formatted = [];
        foreach ($rows as $row) {
            $cmUserId = (int)($row['user_id'] ?? 0);
            $formatted[] = [
                'id'          => (int)($row['id'] ?? 0),
                'post_id'     => (int)($row['post_id'] ?? $postId),
                'user_id'     => $cmUserId,
                'parent_id'   => !empty($row['parent_id']) ? (int)$row['parent_id'] : null,
                'username'    => htmlspecialchars((string)($row['username'] ?? 'Thành viên'), ENT_QUOTES, 'UTF-8'),
                'role_name'   => htmlspecialchars((string)($row['role_name'] ?? 'member'), ENT_QUOTES, 'UTF-8'),
                'content'     => htmlspecialchars((string)($row['content'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'created_at'  => !empty($row['created_at']) ? date('d/m/Y H:i', strtotime((string)$row['created_at'])) : date('d/m/Y H:i'),
                'can_delete'  => ($canModerate || ($currentUserId > 0 && $currentUserId === $cmUserId)),
                'likes_count' => (int)($row['likes_count'] ?? 0),
                'user_liked'  => !empty($row['user_liked'])
            ];
        }

        Response::json([
            'success' => true,
            'data'    => [
                'comments' => $formatted,
                'tree'     => $this->comments->getTreeByPostId($postId, $currentUserId),
                'total'    => count($formatted)
            ]
        ]);
    }

    /**
     * POST /api/comments (hoặc /api/comments.php)
     * Thêm bình luận mới hoặc trả lời bình luận lồng nhau nhiều cấp bằng AJAX (FR07)
     */
    public function createComment(): void
    {
        if (!Auth::isMember()) {
            Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập tài khoản Thành viên để gửi bình luận.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $postId = (int)($json['post_id'] ?? $_POST['post_id'] ?? 0);
        $parentIdRaw = $json['parent_id'] ?? $_POST['parent_id'] ?? null;
        $parentId = ($parentIdRaw !== null && $parentIdRaw !== '' && (int)$parentIdRaw > 0) ? (int)$parentIdRaw : null;
        $content = trim((string)($json['content'] ?? $_POST['content'] ?? ''));
        $csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::verify(is_string($csrf) ? $csrf : null)) {
            Response::json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'], 419);
            return;
        }

        if ($postId <= 0 || mb_strlen($content) < 2) {
            Response::json(['success' => false, 'message' => 'Nội dung bình luận phải từ 2 ký tự trở lên.'], 400);
            return;
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);
        $roleName = Auth::role($user);

        $parentUsername = null;
        if ($parentId !== null && $parentId > 0) {
            $parentRow = $this->comments->findById($parentId);
            if ($parentRow && (int)($parentRow['post_id'] ?? 0) === $postId) {
                $parentUsername = (string)($parentRow['username'] ?? '');
            } else {
                $parentId = null;
            }
        }

        $commentId = $this->comments->create($postId, $userId, $content, $parentId);
        if ($commentId > 0) {
            $totalComments = $this->comments->countByPost($postId);

            // Gửi thông báo đến chủ bài viết hoặc người được trả lời
            $post = $this->posts->getById($postId);
            $postTitleShort = $post ? mb_strimwidth($post['title'], 0, 35, '...') : 'bài viết';
            if ($parentId && $parentUsername) {
                $parentRow = $this->comments->findById($parentId);
                if ($parentRow && (int)($parentRow['user_id'] ?? 0) !== $userId) {
                    Notification::send(
                        (int)$parentRow['user_id'],
                        $userId,
                        'reply_comment',
                        $postId,
                        ($user['username'] ?? 'Một thành viên') . ' đã trả lời bình luận của bạn trong bài "' . $postTitleShort . '"'
                    );
                }
            } elseif ($post && (int)$post['user_id'] !== $userId) {
                Notification::send(
                    (int)$post['user_id'],
                    $userId,
                    'comment_post',
                    $postId,
                    ($user['username'] ?? 'Một thành viên') . ' đã bình luận về bài viết "' . $postTitleShort . '" của bạn.'
                );
            }

            Response::json([
                'success' => true,
                'message' => $parentId ? 'Đã gửi câu trả lời thành công!' : 'Đã gửi bình luận thành công!',
                'data'    => [
                    'id'              => $commentId,
                    'post_id'         => $postId,
                    'user_id'         => $userId,
                    'parent_id'       => $parentId,
                    'parent_username' => $parentUsername !== null ? htmlspecialchars($parentUsername, ENT_QUOTES, 'UTF-8') : null,
                    'content'         => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
                    'username'        => htmlspecialchars((string)($user['username'] ?? 'Bạn'), ENT_QUOTES, 'UTF-8'),
                    'role_name'       => htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'),
                    'created_at'      => date('d/m/Y H:i'),
                    'can_delete'      => true,
                    'total_comments'  => $totalComments
                ]
            ]);
            return;
        }

        Response::json(['success' => false, 'message' => 'Không thể lưu bình luận vào cơ sở dữ liệu.'], 500);
    }

    /**
     * POST /api/comments/delete
     * Xóa bình luận của chính mình hoặc Admin qua AJAX (FR07)
     */
    public function deleteComment(): void
    {
        if (!Session::has('user')) {
            Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $commentId = (int)($json['comment_id'] ?? $_POST['comment_id'] ?? 0);
        $postId = (int)($json['post_id'] ?? $_POST['post_id'] ?? 0);
        $csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::verify(is_string($csrf) ? $csrf : null)) {
            Response::json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'], 419);
            return;
        }

        if ($commentId <= 0) {
            Response::json(['success' => false, 'message' => 'Bình luận không hợp lệ.'], 400);
            return;
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);
        $canModerate = Auth::isModerator($user);

        $deleted = $this->comments->delete($commentId, $userId, $canModerate);
        if ($deleted) {
            $totalComments = $postId > 0 ? $this->comments->countByPost($postId) : 0;
            Response::json([
                'success' => true,
                'message' => 'Đã xóa bình luận.',
                'data'    => [
                    'comment_id'     => $commentId,
                    'total_comments' => $totalComments
                ]
            ]);
            return;
        }

        Response::json(['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này hoặc bình luận không tồn tại.'], 403);
    }

    /**
     * POST /api/comments/like (hoặc /api/comments.php?action=like)
     * Thích hoặc Bỏ thích bình luận bằng AJAX không reload trang (FR07, FR08)
     */
    public function likeComment(): void
    {
        if (!Auth::isMember()) {
            Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập để thích bình luận.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $commentId = (int)($json['comment_id'] ?? $_POST['comment_id'] ?? 0);
        $csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::verify(is_string($csrf) ? $csrf : null)) {
            Response::json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'], 419);
            return;
        }

        if ($commentId <= 0) {
            Response::json(['success' => false, 'message' => 'Bình luận không hợp lệ.'], 400);
            return;
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);

        $result = $this->comments->toggleLike($commentId, $userId);

        if ($result['liked']) {
            $cm = $this->comments->getById($commentId);
            if ($cm && (int)($cm['user_id'] ?? 0) !== $userId) {
                Notification::send(
                    (int)$cm['user_id'],
                    $userId,
                    'like_comment',
                    (int)($cm['post_id'] ?? 0),
                    ($user['username'] ?? 'Một thành viên') . ' đã thích bình luận của bạn.'
                );
            }
        }

        Response::json([
            'success' => true,
            'data'    => [
                'comment_id'  => $commentId,
                'liked'       => $result['liked'],
                'likes_count' => $result['likes_count']
            ]
        ]);
    }

    /**
     * GET /api/check-user (hoặc /api/check-user.php)
     * Kiểm tra trùng username hoặc email ngay khi đăng ký bằng AJAX (FR01)
     */
    public function checkUserAvailability(): void
    {
        $field = trim((string)($_GET['field'] ?? ''));
        $username = trim((string)($_GET['username'] ?? ''));
        $email = trim((string)($_GET['email'] ?? ''));

        // 1. Kiểm tra username
        if ($username !== '' || $field === 'username') {
            $val = $username !== '' ? $username : trim((string)($_GET['value'] ?? ''));
            if (mb_strlen($val) < 3) {
                Response::json([
                    'success'   => true,
                    'available' => false,
                    'field'     => 'username',
                    'message'   => 'Tên người dùng phải có ít nhất 3 ký tự.'
                ]);
                return;
            }
            if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $val)) {
                Response::json([
                    'success'   => true,
                    'available' => false,
                    'field'     => 'username',
                    'message'   => 'Tên chỉ chứa chữ cái, số, gạch dưới (_) hoặc gạch ngang (-).'
                ]);
                return;
            }

            $exists = $this->users->existsByUsername($val);
            Response::json([
                'success'   => true,
                'available' => !$exists,
                'field'     => 'username',
                'message'   => $exists ? 'Tên người dùng này đã được sử dụng.' : 'Tên người dùng hợp lệ và có thể đăng ký.'
            ]);
            return;
        }

        // 2. Kiểm tra email
        if ($email !== '' || $field === 'email') {
            $val = $email !== '' ? $email : trim((string)($_GET['value'] ?? ''));
            if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                Response::json([
                    'success'   => true,
                    'available' => false,
                    'field'     => 'email',
                    'message'   => 'Định dạng email chưa hợp lệ.'
                ]);
                return;
            }

            $exists = $this->users->existsByEmail($val);
            Response::json([
                'success'   => true,
                'available' => !$exists,
                'field'     => 'email',
                'message'   => $exists ? 'Địa chỉ email này đã được đăng ký tài khoản.' : 'Địa chỉ email hợp lệ và có thể sử dụng.'
            ]);
            return;
        }

        Response::json(['success' => false, 'message' => 'Thiếu thông tin username hoặc email cần kiểm tra.'], 400);
    }

    /**
     * GET /api/posts
     * Danh sách bài viết dạng JSON, hỗ trợ Tìm kiếm gợi ý (Autocomplete) và Phân trang AJAX (Infinite Scroll)
     */
    public function posts(): void
    {
        $keyword = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
        $sort = Post::normalizeSort($_GET['sort'] ?? 'hot');

        // Gợi ý tìm kiếm nhanh (Autocomplete khi gõ - FR04)
        if ($keyword !== '' && isset($_GET['suggest'])) {
            $suggestions = $this->posts->suggest($keyword);
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            $formatted = [];
            foreach ($suggestions as $s) {
                $formatted[] = [
                    'id'       => (int)($s['id'] ?? 0),
                    'title'    => (string)($s['title'] ?? ''),
                    'location' => (string)($s['location'] ?? ''),
                    'url'      => $baseUrl . '/posts/' . (int)($s['id'] ?? 0)
                ];
            }
            Response::json([
                'success' => true,
                'data'    => $formatted
            ]);
            return;
        }

        // Phân trang / "Tải thêm" (Infinite Scroll - FR03)
        $page = isset($_GET['page']) ? (int)$_GET['page'] : null;
        $limit = isset($_GET['limit']) ? max(1, min(24, (int)$_GET['limit'])) : 6;

        if ($page !== null && $page >= 1) {
            $totalPosts = $this->posts->countFiltered($categoryId, 'published');
            $totalPages = $totalPosts > 0 ? (int)ceil($totalPosts / $limit) : 1;
            $postsList = $this->posts->getPaginated($categoryId, $page, $limit, 'published', $sort);

            Response::json([
                'success' => true,
                'data'    => [
                    'posts'        => $postsList,
                    'current_page' => $page,
                    'total_pages'  => $totalPages,
                    'total_posts'  => $totalPosts,
                    'has_more'     => ($page < $totalPages)
                ]
            ]);
            return;
        }

        if ($keyword !== '') {
            $posts = $this->posts->search($keyword, $categoryId, $sort);
        } else {
            $posts = $this->posts->getAll($categoryId, $sort);
        }

        Response::json([
            'success' => true,
            'data'    => $posts
        ]);
    }

    /**
     * POST /api/likes.php hoặc /api/votes
     * Xử lý Upvote (+1) / Downvote (-1) bài viết qua AJAX
     */
    public function votePost(): void
    {
        if (!Auth::isMember()) {
            Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập tài khoản Thành viên để bình chọn (Upvote / Downvote) bài viết.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $postId = (int)($json['post_id'] ?? $_POST['post_id'] ?? $_GET['post_id'] ?? 0);
        if ($postId <= 0) {
            Response::json(['success' => false, 'message' => 'Bài viết không hợp lệ.'], 400);
            return;
        }

        $voteRaw = $json['vote'] ?? $_POST['vote'] ?? 1;
        $voteType = ((int)$voteRaw < 0) ? -1 : 1;

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);
        $voteData = $this->posts->vote($postId, $userId, $voteType);

        if ($voteData['user_vote'] === 1) {
            $post = $this->posts->getById($postId);
            if ($post && (int)($post['user_id'] ?? 0) !== $userId) {
                Notification::send(
                    (int)$post['user_id'],
                    $userId,
                    'like_post',
                    $postId,
                    ($user['username'] ?? 'Một thành viên') . ' đã thích bài viết "' . mb_strimwidth($post['title'], 0, 35, '...') . '" của bạn.'
                );
            }
        }

        Response::json([
            'success' => true,
            'data'    => $voteData
        ]);
    }

    /**
     * POST /api/posts/{id}/bookmark hoặc /api/bookmarks
     * Bật / tắt lưu bài viết (Bookmark) qua AJAX
     */
    public function toggleBookmark(): void
    {
        if (!Auth::isMember()) {
            Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập để lưu bài viết này.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $postId = (int)($json['post_id'] ?? $_POST['post_id'] ?? $_GET['post_id'] ?? 0);
        $csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::verify(is_string($csrf) ? $csrf : null)) {
            Response::json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'], 419);
            return;
        }

        if ($postId <= 0) {
            Response::json(['success' => false, 'message' => 'Bài viết không hợp lệ.'], 400);
            return;
        }

        $userId = (int)Auth::id();
        $bookmarkModel = new Bookmark();
        $res = $bookmarkModel->toggle($userId, $postId);

        Response::json([
            'success' => true,
            'message' => $res['bookmarked'] ? 'Đã lưu bài viết vào bộ sưu tập.' : 'Đã bỏ lưu bài viết.',
            'data'    => [
                'post_id'    => $postId,
                'bookmarked' => $res['bookmarked'],
                'total'      => $res['total']
            ]
        ]);
    }

    /**
     * POST /api/users/{id}/follow hoặc /api/follows/user
     * Theo dõi hoặc Bỏ theo dõi người dùng
     */
    public function toggleUserFollow(): void
    {
        if (!Auth::isMember()) {
            Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập để theo dõi thành viên này.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $followingId = (int)($json['user_id'] ?? $_POST['user_id'] ?? $_GET['user_id'] ?? 0);
        $csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::verify(is_string($csrf) ? $csrf : null)) {
            Response::json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'], 419);
            return;
        }

        $followerId = (int)Auth::id();
        if ($followingId <= 0 || $followingId === $followerId) {
            Response::json(['success' => false, 'message' => 'Không thể tự theo dõi chính mình.'], 400);
            return;
        }

        $followModel = new Follow();
        $res = $followModel->toggleUserFollow($followerId, $followingId);

        if ($res['following']) {
            $user = Session::get('user');
            Notification::send(
                $followingId,
                $followerId,
                'follow_user',
                $followerId,
                ($user['username'] ?? 'Một thành viên') . ' đã bắt đầu theo dõi bạn!'
            );
        }

        Response::json([
            'success' => true,
            'message' => $res['following'] ? 'Đã theo dõi thành viên.' : 'Đã hủy theo dõi.',
            'data'    => [
                'user_id'         => $followingId,
                'following'       => $res['following'],
                'followers_count' => $res['followers_count']
            ]
        ]);
    }

    /**
     * POST /api/categories/{id}/follow hoặc /api/follows/category
     * Theo dõi hoặc Bỏ theo dõi chuyên mục / chủ đề
     */
    public function toggleCategoryFollow(): void
    {
        if (!Auth::isMember()) {
            Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập để theo dõi chuyên mục.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $categoryId = (int)($json['category_id'] ?? $_POST['category_id'] ?? $_GET['category_id'] ?? 0);
        $csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::verify(is_string($csrf) ? $csrf : null)) {
            Response::json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'], 419);
            return;
        }

        if ($categoryId <= 0) {
            Response::json(['success' => false, 'message' => 'Chuyên mục không hợp lệ.'], 400);
            return;
        }

        $userId = (int)Auth::id();
        $followModel = new Follow();
        $res = $followModel->toggleCategoryFollow($userId, $categoryId);

        Response::json([
            'success' => true,
            'message' => $res['following'] ? 'Đã theo dõi chuyên mục này.' : 'Đã hủy theo dõi chuyên mục.',
            'data'    => [
                'category_id' => $categoryId,
                'following'   => $res['following']
            ]
        ]);
    }

    /**
     * GET /api/notifications
     * Lấy danh sách thông báo và số lượng chưa đọc của user hiện tại
     */
    public function notifications(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'message' => 'Chưa đăng nhập.', 'data' => ['notifications' => [], 'unread_count' => 0]]);
            return;
        }

        $userId = (int)Auth::id();
        $notifModel = new Notification();
        $list = $notifModel->getByUserId($userId, 15);
        $unreadCount = $notifModel->getUnreadCount($userId);

        Response::json([
            'success' => true,
            'data'    => [
                'notifications' => $list,
                'unread_count'  => $unreadCount
            ]
        ]);
    }

    /**
     * POST /api/notifications/read
     * Đánh dấu đã đọc một hoặc toàn bộ thông báo
     */
    public function readNotification(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $userId = (int)Auth::id();
        $id = isset($json['id']) ? (int)$json['id'] : 0;
        $notifModel = new Notification();

        if ($id > 0) {
            $notifModel->markAsRead($id, $userId);
        } else {
            $notifModel->markAllAsRead($userId);
        }

        $unreadCount = $notifModel->getUnreadCount($userId);
        Response::json([
            'success' => true,
            'data'    => ['unread_count' => $unreadCount]
        ]);
    }

    /**
     * POST /api/reports
     * Báo cáo vi phạm (report) cho bài viết, bình luận hoặc người dùng
     */
    public function report(): void
    {
        if (!Auth::check()) {
            Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi báo cáo vi phạm.'], 401);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
        if (!is_array($json)) {
            $json = [];
        }

        $targetType = trim((string)($json['target_type'] ?? $_POST['target_type'] ?? 'post'));
        $targetId = (int)($json['target_id'] ?? $_POST['target_id'] ?? 0);
        $reason = trim((string)($json['reason'] ?? $_POST['reason'] ?? ''));
        $details = trim((string)($json['details'] ?? $_POST['details'] ?? ''));
        $csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::verify(is_string($csrf) ? $csrf : null)) {
            Response::json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'], 419);
            return;
        }

        if (!in_array($targetType, ['post', 'comment', 'user'], true) || $targetId <= 0) {
            Response::json(['success' => false, 'message' => 'Đối tượng báo cáo không hợp lệ.'], 400);
            return;
        }

        if ($reason === '') {
            Response::json(['success' => false, 'message' => 'Vui lòng chọn lý do báo cáo vi phạm.'], 400);
            return;
        }

        $reporterId = (int)Auth::id();
        $reportModel = new Report();
        $reportId = $reportModel->create($reporterId, $targetType, $targetId, $reason, $details);

        if ($reportId > 0) {
            Response::json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã gửi báo cáo. Ban điều hành (Moderator) sẽ xem xét và xử lý sớm nhất.'
            ]);
            return;
        }

        Response::json(['success' => false, 'message' => 'Không thể gửi báo cáo. Vui lòng thử lại sau.'], 500);
    }
}

