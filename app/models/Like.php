<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Like - Quản lý lượt thích bài viết (Vương - Tuần 2 & Tuần 4: FR08)
 * Gọi các Stored Procedures: sp_post_toggle_like, sp_post_is_liked, sp_post_like_count
 */
class Like extends Model
{
    private ?int $postId = null;
    private ?int $userId = null;

    public function getPostId(): ?int { return $this->postId; }
    public function setPostId(?int $postId): void { $this->postId = $postId; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): void { $this->userId = $userId; }

    /**
     * Thích hoặc bỏ thích bài viết (gọi routine sp_post_toggle_like)
     * @return int Tổng số lượt thích mới của bài viết
     */
    public function toggle(int $postId, int $userId): int
    {
        $this->postId = $postId;
        $this->userId = $userId;

        try {
            $rows = Database::call('sp_post_toggle_like', [$postId, $userId]);
            return (int)($rows[0]['likes'] ?? 0);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_toggle_like fallback query: ' . $e->getMessage());

            try {
                $check = Database::query(
                    'SELECT 1 FROM post_likes WHERE post_id = :p AND user_id = :u',
                    ['p' => $postId, 'u' => $userId]
                );

                if (!empty($check)) {
                    Database::execute(
                        'DELETE FROM post_likes WHERE post_id = :p AND user_id = :u',
                        ['p' => $postId, 'u' => $userId]
                    );
                } else {
                    Database::execute(
                        'INSERT INTO post_likes (post_id, user_id) VALUES (:p, :u)',
                        ['p' => $postId, 'u' => $userId]
                    );
                }

                return $this->countByPost($postId);
            } catch (PDOException $ex) {
                error_log('Error toggling like: ' . $ex->getMessage());
                return 0;
            }
        }
    }

    /**
     * Kiểm tra người dùng đã thích bài viết chưa (gọi routine sp_post_is_liked)
     */
    public function isLiked(int $postId, int $userId): bool
    {
        try {
            $rows = Database::call('sp_post_is_liked', [$postId, $userId]);
            return !empty($rows) && (bool)($rows[0]['is_liked'] ?? false);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_is_liked fallback query: ' . $e->getMessage());

            try {
                $rows = Database::query(
                    'SELECT 1 FROM post_likes WHERE post_id = :p AND user_id = :u',
                    ['p' => $postId, 'u' => $userId]
                );
                return !empty($rows);
            } catch (PDOException $ex) {
                error_log('Error checking like: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Đếm số lượt thích của bài viết (gọi routine sp_post_like_count)
     */
    public function countByPost(int $postId): int
    {
        try {
            $rows = Database::call('sp_post_like_count', [$postId]);
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_like_count fallback query: ' . $e->getMessage());

            try {
                $rows = Database::query(
                    'SELECT COUNT(*) AS total FROM post_likes WHERE post_id = :p',
                    ['p' => $postId]
                );
                return (int)($rows[0]['total'] ?? 0);
            } catch (PDOException $ex) {
                error_log('Error counting likes: ' . $ex->getMessage());
                return 0;
            }
        }
    }
}
