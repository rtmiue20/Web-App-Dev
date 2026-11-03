<?php

class Follow extends Model
{
    /**
     * Bật / tắt theo dõi người dùng khác
     * @return array ['following' => bool, 'followers_count' => int]
     */
    public function toggleUserFollow(int $followerId, int $followingId): array
    {
        if ($followerId <= 0 || $followingId <= 0 || $followerId === $followingId) {
            return ['following' => false, 'followers_count' => 0];
        }

        try {
            $checkSql = "SELECT 1 FROM user_follows WHERE follower_id = :fid AND following_id = :tid";
            $exists = Database::query($checkSql, ['fid' => $followerId, 'tid' => $followingId]);

            if (!empty($exists)) {
                $delSql = "DELETE FROM user_follows WHERE follower_id = :fid AND following_id = :tid";
                Database::query($delSql, ['fid' => $followerId, 'tid' => $followingId]);
                $following = false;
            } else {
                $insSql = "INSERT INTO user_follows (follower_id, following_id, created_at) VALUES (:fid, :tid, NOW())";
                Database::query($insSql, ['fid' => $followerId, 'tid' => $followingId]);
                $following = true;
            }

            $count = $this->getFollowersCount($followingId);

            return [
                'following'       => $following,
                'followers_count' => $count,
            ];
        } catch (PDOException $e) {
            error_log('Error toggling user follow: ' . $e->getMessage());
            return ['following' => false, 'followers_count' => 0];
        }
    }

    /**
     * Kiểm tra followerId có đang theo dõi followingId không
     */
    public function isFollowingUser(int $followerId, int $followingId): bool
    {
        if ($followerId <= 0 || $followingId <= 0) {
            return false;
        }
        try {
            $rows = Database::query(
                "SELECT 1 FROM user_follows WHERE follower_id = :fid AND following_id = :tid LIMIT 1",
                ['fid' => $followerId, 'tid' => $followingId]
            );
            return !empty($rows);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Đếm số người đang theo dõi user (Followers)
     */
    public function getFollowersCount(int $userId): int
    {
        try {
            $rows = Database::query(
                "SELECT COUNT(*) AS total FROM user_follows WHERE following_id = :uid",
                ['uid' => $userId]
            );
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Đếm số người mà user đang theo dõi (Following)
     */
    public function getFollowingCount(int $userId): int
    {
        try {
            $rows = Database::query(
                "SELECT COUNT(*) AS total FROM user_follows WHERE follower_id = :uid",
                ['uid' => $userId]
            );
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Lấy danh sách người mà user đang theo dõi
     */
    public function getFollowingUsers(int $userId, int $limit = 20): array
    {
        try {
            return Database::query(
                "SELECT u.id, u.username, u.avatar_path, u.bio, uf.created_at AS followed_at
                 FROM user_follows uf
                 JOIN users u ON u.id = uf.following_id
                 WHERE uf.follower_id = :uid
                 ORDER BY uf.created_at DESC
                 LIMIT :limit",
                ['uid' => $userId, 'limit' => $limit]
            );
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Bật / tắt theo dõi chuyên mục / chủ đề
     * @return array ['following' => bool]
     */
    public function toggleCategoryFollow(int $userId, int $categoryId): array
    {
        if ($userId <= 0 || $categoryId <= 0) {
            return ['following' => false];
        }

        try {
            $checkSql = "SELECT 1 FROM category_follows WHERE user_id = :uid AND category_id = :cid";
            $exists = Database::query($checkSql, ['uid' => $userId, 'cid' => $categoryId]);

            if (!empty($exists)) {
                $delSql = "DELETE FROM category_follows WHERE user_id = :uid AND category_id = :cid";
                Database::query($delSql, ['uid' => $userId, 'cid' => $categoryId]);
                $following = false;
            } else {
                $insSql = "INSERT INTO category_follows (user_id, category_id, created_at) VALUES (:uid, :cid, NOW())";
                Database::query($insSql, ['uid' => $userId, 'cid' => $categoryId]);
                $following = true;
            }

            return ['following' => $following];
        } catch (PDOException $e) {
            error_log('Error toggling category follow: ' . $e->getMessage());
            return ['following' => false];
        }
    }

    /**
     * Kiểm tra user có theo dõi chuyên mục không
     */
    public function isFollowingCategory(int $userId, int $categoryId): bool
    {
        if ($userId <= 0 || $categoryId <= 0) {
            return false;
        }
        try {
            $rows = Database::query(
                "SELECT 1 FROM category_follows WHERE user_id = :uid AND category_id = :cid LIMIT 1",
                ['uid' => $userId, 'cid' => $categoryId]
            );
            return !empty($rows);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Lấy danh sách chuyên mục user đang theo dõi
     */
    public function getFollowingCategories(int $userId): array
    {
        try {
            return Database::query(
                "SELECT c.id, c.name, c.description, cf.created_at AS followed_at
                 FROM category_follows cf
                 JOIN categories c ON c.id = cf.category_id
                 WHERE cf.user_id = :uid
                 ORDER BY cf.created_at DESC",
                ['uid' => $userId]
            );
        } catch (PDOException $e) {
            return [];
        }
    }
}
