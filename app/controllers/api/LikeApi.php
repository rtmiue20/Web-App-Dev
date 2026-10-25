<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Like.php';

/**
 * LikeApi - API thích / bỏ thích bài viết bằng AJAX (FR08 - Vương - Tuần 4)
 * Trả về chuẩn JSON: {"success": bool, "data": {"likes": int, "count": int, "liked": bool}, "message": string}
 */
class LikeApi extends Controller
{
    private Post $posts;
    private Like $likes;

    public function __construct()
    {
        $this->posts = new Post();
        $this->likes = new Like();
    }

    /**
     * POST /api/posts/{id}/like
     */
    public function toggle($id): void
    {
        Response::requireMethod('POST');

        if (!Session::has('user')) {
            Response::fail('Vui lòng đăng nhập để thích bài viết.', 401);
        }

        if (!$this->verifyCsrfToken()) {
            Response::fail('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.', 419);
        }

        $postId = (int)$id;
        $post = $this->posts->getById($postId);
        if (!$post) {
            Response::fail('Bài viết không tồn tại.', 404);
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);

        $likeCount = $this->likes->toggle($postId, $userId);
        $isLiked = $this->likes->isLiked($postId, $userId);

        Response::ok([
            'likes' => $likeCount,
            'count' => $likeCount,
            'liked' => $isLiked
        ], $isLiked ? 'Đã thích bài viết.' : 'Đã bỏ thích bài viết.');
    }
}
