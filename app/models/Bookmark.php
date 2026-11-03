<?php

class Bookmark extends Model
{
    /**
     * Bật / tắt lưu bài viết (Bookmark)
     * @return array ['bookmarked' => bool, 'total' => int]
     */
    public function toggle(int $userId, int $postId): array
    {
        try {
            $checkSql = "SELECT 1 FROM post_bookmarks WHERE user_id = :uid AND post_id = :pid";
            $exists = Database::query($checkSql, ['uid' => $userId, 'pid' => $postId]);

            if (!empty($exists)) {
                $delSql = "DELETE FROM post_bookmarks WHERE user_id = :uid AND post_id = :pid";
                Database::query($delSql, ['uid' => $userId, 'pid' => $postId]);
                $bookmarked = false;
            } else {
                $insSql = "INSERT INTO post_bookmarks (user_id, post_id, created_at) VALUES (:uid, :pid, NOW())";
                Database::query($insSql, ['uid' => $userId, 'pid' => $postId]);
                $bookmarked = true;
            }

            $countSql = "SELECT COUNT(*) AS total FROM post_bookmarks WHERE post_id = :pid";
            $res = Database::query($countSql, ['pid' => $postId]);
            $total = (int)($res[0]['total'] ?? 0);

            return [
                'bookmarked' => $bookmarked,
                'total'      => $total,
            ];
        } catch (PDOException $e) {
            error_log('Error toggling bookmark: ' . $e->getMessage());
            return ['bookmarked' => false, 'total' => 0];
        }
    }

    /**
     * Kiểm tra user đã lưu bài viết chưa
     */
    public function isBookmarked(int $userId, int $postId): bool
    {
        if ($userId <= 0 || $postId <= 0) {
            return false;
        }
        try {
            $rows = Database::query(
                "SELECT 1 FROM post_bookmarks WHERE user_id = :uid AND post_id = :pid LIMIT 1",
                ['uid' => $userId, 'pid' => $postId]
            );
            return !empty($rows);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Lấy danh sách bài viết đã lưu của 1 user
     */
    public function getByUserId(int $userId, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }
        try {
            $sql = "SELECT p.*, u.username, c.name AS category_name,
                           (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                           COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS vote_score,
                           (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count,
                           b.created_at AS bookmarked_at
                    FROM post_bookmarks b
                    JOIN posts p ON p.id = b.post_id
                    JOIN users u ON u.id = p.user_id
                    JOIN categories c ON c.id = p.category_id
                    WHERE b.user_id = :uid AND p.status = 'published'
                    ORDER BY b.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting bookmarked posts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số bài viết user đã lưu
     */
    public function countByUserId(int $userId): int
    {
        try {
            $rows = Database::query(
                "SELECT COUNT(*) AS total 
                 FROM post_bookmarks b 
                 JOIN posts p ON p.id = b.post_id 
                 WHERE b.user_id = :uid AND p.status = 'published'",
                ['uid' => $userId]
            );
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }
}
