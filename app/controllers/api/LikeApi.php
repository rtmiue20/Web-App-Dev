<?php
require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../ApiController.php';

/**
 * LikeApi - RESTful API Controller xử lý Thích / Bình chọn (Upvote/Downvote) bài viết & bình luận (FR08).
 *
 * Endpoints:
 *  - POST /api/posts/{id}/like
 *  - POST /api/posts/{id}/vote
 *  - POST /api/comments/{id}/like
 */
class LikeApi extends BaseApiController
{
    private ApiController $api;

    public function __construct(?ApiController $api = null)
    {
        $this->api = $api ?? new ApiController();
    }

    /**
     * POST /api/posts/{id}/like hoặc POST /api/posts/{id}/vote
     */
    public function togglePostLike(?int $id = null): void
    {
        if ($id !== null && $id > 0) {
            $_POST['post_id'] = $id;
        }
        $this->api->votePost($id);
    }

    /**
     * POST /api/comments/{id}/like
     */
    public function toggleCommentLike(?int $id = null): void
    {
        if ($id !== null && $id > 0) {
            $_POST['comment_id'] = $id;
        }
        $this->api->likeComment($id);
    }
}

