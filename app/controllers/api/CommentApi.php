<?php
require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../ApiController.php';

/**
 * CommentApi - RESTful API Controller quản lý tài nguyên Bình luận bài viết (FR07).
 *
 * Endpoints:
 *  - GET    /api/posts/{id}/comments : Danh sách bình luận (phẳng & cây đa cấp)
 *  - POST   /api/posts/{id}/comments : Thêm mới bình luận hoặc trả lời bình luận (HTTP 201)
 *  - DELETE /api/comments/{id}       : Xóa bình luận (chính chủ hoặc Moderator/Admin)
 */
class CommentApi extends BaseApiController
{
    private ApiController $api;

    public function __construct(?ApiController $api = null)
    {
        $this->api = $api ?? new ApiController();
    }

    /**
     * GET /api/posts/{id}/comments
     */
    public function index(?int $postId = null): void
    {
        if ($postId !== null && $postId > 0) {
            $_GET['post_id'] = $postId;
        }
        $this->api->listComments($postId);
    }

    /**
     * POST /api/posts/{id}/comments
     */
    public function store(?int $postId = null): void
    {
        if ($postId !== null && $postId > 0) {
            $_POST['post_id'] = $postId;
        }
        $this->api->createComment($postId);
    }

    /**
     * DELETE /api/comments/{id} (hoặc POST /api/comments/{id}/delete)
     */
    public function destroy(?int $commentId = null): void
    {
        if ($commentId !== null && $commentId > 0) {
            $_POST['comment_id'] = $commentId;
        }
        $this->api->deleteComment($commentId);
    }
}

