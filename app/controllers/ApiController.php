<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../services/WeatherService.php';
require_once __DIR__ . '/../services/VietnamAddressService.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Csrf.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Controller ApiController - Xử lý các endpoint AJAX (JSON) & Tích hợp Web Service ngoài
 * Phục vụ FR06 (Thời tiết Open-Meteo, Bản đồ, Địa chỉ hành chính), FR07 (Bình luận AJAX), FR08 (Thích bài AJAX)
 */
class ApiController extends Controller
{
    private WeatherService $weatherService;
    private VietnamAddressService $addressService;
    private Comment $comments;
    private Post $posts;

    public function __construct()
    {
        $this->weatherService = new WeatherService();
        $this->addressService = new VietnamAddressService();
        $this->comments = new Comment();
        $this->posts = new Post();
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
     * Lấy danh sách bình luận của bài viết qua AJAX (FR07)
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

        $rows = $this->comments->getByPostId($postId);
        $formatted = [];
        foreach ($rows as $row) {
            $cmUserId = (int)($row['user_id'] ?? 0);
            $formatted[] = [
                'id'         => (int)($row['id'] ?? 0),
                'post_id'    => (int)($row['post_id'] ?? $postId),
                'user_id'    => $cmUserId,
                'parent_id'  => !empty($row['parent_id']) ? (int)$row['parent_id'] : null,
                'username'   => htmlspecialchars((string)($row['username'] ?? 'Thành viên'), ENT_QUOTES, 'UTF-8'),
                'role_name'  => htmlspecialchars((string)($row['role_name'] ?? 'member'), ENT_QUOTES, 'UTF-8'),
                'content'    => htmlspecialchars((string)($row['content'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'created_at' => !empty($row['created_at']) ? date('d/m/Y H:i', strtotime((string)$row['created_at'])) : date('d/m/Y H:i'),
                'can_delete' => ($canModerate || ($currentUserId > 0 && $currentUserId === $cmUserId))
            ];
        }

        Response::json([
            'success' => true,
            'data'    => [
                'comments' => $formatted,
                'tree'     => $this->comments->getTreeByPostId($postId),
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
     * GET /api/posts
     * Danh sách hoặc gợi ý tìm kiếm bài viết dạng JSON
     */
    public function posts(): void
    {
        $keyword = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;

        if ($keyword !== '' && isset($_GET['suggest'])) {
            $suggestions = $this->posts->suggest($keyword);
            Response::json([
                'success' => true,
                'data'    => $suggestions
            ]);
            return;
        }

        if ($keyword !== '') {
            $posts = $this->posts->search($keyword, $categoryId);
        } else {
            $posts = $this->posts->getAll($categoryId);
        }

        Response::json([
            'success' => true,
            'data'    => $posts
        ]);
    }
}

