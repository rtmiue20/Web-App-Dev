<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Comment.php';

/**
 * CommentApi - API quản lý bình luận bằng AJAX (FR07 - Vương - Tuần 4)
 * - list():   GET  /api/posts/{id}/comments?page=2  (Tải thêm bình luận)
 * - store():  POST /api/posts/{id}/comments         (Thêm bình luận mới)
 * - delete(): POST /api/comments/{id}/delete        (Xóa bình luận của chính mình)
 * Trả về chuẩn JSON: {"success": bool, "data": mixed, "message": string}
 */
class CommentApi extends Controller
{
    private const PAGE_SIZE = 5;

    private Post $posts;
    private Comment $comments;

    public function __construct()
    {
        $this->posts = new Post();
        $this->comments = new Comment();
    }

    /**
     * GET /api/posts/{id}/comments?page=2
     */
    public function list($id): void
    {
        Response::requireMethod('GET');

        $postId = (int)$id;
        $post = $this->posts->getById($postId);
        if (!$post) {
            Response::fail('Bài viết không tồn tại.', 404);
        }

        $page = 1;
        if (isset($_GET['page'])) {
            $page = (int)$_GET['page'];
            if ($page < 1) {
                $page = 1;
            }
        }

        $offset = ($page - 1) * self::PAGE_SIZE;

        // Lấy PAGE_SIZE + 1 dòng để kiểm tra xem còn bình luận trang tiếp theo không
        $rows = $this->comments->getByPostIdPaged($postId, self::PAGE_SIZE + 1, $offset);

        $hasMore = false;
        if (count($rows) > self::PAGE_SIZE) {
            $hasMore = true;
            $rows = array_slice($rows, 0, self::PAGE_SIZE);
        }

        $currentUserId = $this->currentUserId();
        $isAdmin = $this->isCurrentUserAdmin();
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id' => (int)$row['id'],
                'username' => (string)$row['username'],
                'content' => (string)$row['content'],
                'created_at' => date('d/m/Y H:i', strtotime((string)$row['created_at'])),
                'can_delete' => ($currentUserId > 0 && ($currentUserId === (int)$row['user_id'] || $isAdmin))
            ];
        }

        Response::ok([
            'comments' => $items,
            'page' => $page,
            'has_more' => $hasMore
        ]);
    }

    /**
     * POST /api/posts/{id}/comments
     * Thêm bình luận mới bằng AJAX kèm kiểm tra CSRF token
     */
    public function store($id): void
    {
        Response::requireMethod('POST');

        if (!Session::has('user')) {
            Response::fail('Vui lòng đăng nhập để gửi bình luận.', 401);
        }

        $payload = $this->readRequestPayload();
        $csrfToken = $payload['_csrf'] ?? ($_POST['_csrf'] ?? null);
        if (!Csrf::verify($csrfToken)) {
            Response::fail('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.', 419);
        }

        $postId = (int)$id;
        $post = $this->posts->getById($postId);
        if (!$post) {
            Response::fail('Bài viết không tồn tại.', 404);
        }

        $content = trim((string)($payload['content'] ?? ($_POST['content'] ?? '')));
        if (mb_strlen($content) < 2) {
            Response::fail('Nội dung bình luận phải từ 2 ký tự trở lên.', 400);
        }
        if (mb_strlen($content) > 1000) {
            Response::fail('Nội dung bình luận không được vượt quá 1000 ký tự.', 400);
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);
        $username = (string)($user['username'] ?? 'Thành viên');

        $commentId = $this->comments->create($postId, $userId, $content);
        if ($commentId <= 0) {
            Response::fail('Không thể lưu bình luận vào cơ sở dữ liệu.', 500);
        }

        $totalComments = $this->comments->countByPost($postId);

        Response::ok([
            'id' => $commentId,
            'post_id' => $postId,
            'username' => $username,
            'content' => $content,
            'created_at' => date('d/m/Y H:i'),
            'can_delete' => true,
            'total_comments' => $totalComments
        ], 'Đã gửi bình luận thành công.', 201);
    }

    /**
     * POST /api/comments/{id}/delete
     * Chỉ tác giả bình luận (hoặc admin) mới được xóa
     */
    public function delete($id): void
    {
        Response::requireMethod('POST');

        if (!Session::has('user')) {
            Response::fail('Vui lòng đăng nhập để thực hiện.', 401);
        }

        $payload = $this->readRequestPayload();
        $csrfToken = $payload['_csrf'] ?? ($_POST['_csrf'] ?? null);
        if (!Csrf::verify($csrfToken)) {
            Response::fail('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.', 419);
        }

        $commentId = (int)$id;
        $comment = $this->comments->findById($commentId);
        if ($comment === null) {
            Response::fail('Bình luận không tồn tại.', 404);
        }

        $currentUserId = $this->currentUserId();
        $isAdmin = $this->isCurrentUserAdmin();
        if ($currentUserId <= 0 || ($currentUserId !== (int)$comment['user_id'] && !$isAdmin)) {
            Response::fail('Bạn chỉ có thể xóa bình luận của chính mình.', 403);
        }

        $deleted = $this->comments->delete($commentId, $currentUserId, $isAdmin);
        if (!$deleted) {
            Response::fail('Không thể xóa bình luận. Vui lòng thử lại.', 500);
        }

        $postId = (int)($comment['post_id'] ?? 0);
        $totalComments = $postId > 0 ? $this->comments->countByPost($postId) : 0;

        Response::ok([
            'id' => $commentId,
            'post_id' => $postId,
            'total_comments' => $totalComments
        ], 'Đã xóa bình luận.');
    }

    /**
     * Đọc dữ liệu từ JSON body hoặc $_POST
     */
    private function readRequestPayload(): array
    {
        $rawInput = file_get_contents('php://input');
        if (is_string($rawInput) && $rawInput !== '') {
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $_POST;
    }

    private function currentUserId(): int
    {
        $user = Session::get('user');
        if (!is_array($user)) {
            return 0;
        }
        return (int)($user['id'] ?? 0);
    }

    private function isCurrentUserAdmin(): bool
    {
        $user = Session::get('user');
        if (!is_array($user)) {
            return false;
        }
        return (($user['role_name'] ?? '') === 'admin') || ((int)($user['role_id'] ?? 0) === 2);
    }
}
