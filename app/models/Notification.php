<?php

class Notification extends Model
{
    /**
     * Tạo thông báo mới cho người dùng
     */
    public static function send(
        int $userId,
        int $actorId,
        string $type,
        int $targetId,
        string $content
    ): int {
        // ID người dùng không hợp lệ
        if ($userId <= 0 || $actorId <= 0) {
            return 0;
        }

        // Các thao tác trực tiếp của user mà hệ thống cần thông báo phản hồi (nhật ký hành động)
        $selfAllowedTypes = [
            'create_post',
            'create_trip',
            'join_trip',
            'leave_trip',
            'create_checklist',
            'bookmark_post',
            'review_place',
            'update_profile',
            'system',
            'activity'
        ];

        // Với các tương tác xã hội (tự like, tự bình luận bài của mình), không gửi để tránh spam
        if ($userId === $actorId && !in_array($type, $selfAllowedTypes, true)) {
            return 0;
        }

        try {
            // Tránh spam cùng 1 loại thông báo trong thời gian ngắn (ví dụ like/unlike liên tục)
            $checkSql = "SELECT id FROM notifications 
                         WHERE user_id = :uid AND actor_id = :aid AND type = :t AND target_id = :tid 
                         AND created_at >= NOW() - INTERVAL 5 MINUTE 
                         LIMIT 1";
            $existing = Database::query($checkSql, [
                'uid' => $userId,
                'aid' => $actorId,
                't'   => $type,
                'tid' => $targetId,
            ]);

            if (!empty($existing)) {
                // Cập nhật lại thời gian và đánh dấu chưa đọc
                $upSql = "UPDATE notifications SET is_read = 0, created_at = NOW(), content = :content WHERE id = :id";
                Database::query($upSql, ['content' => $content, 'id' => (int)$existing[0]['id']]);
                return (int)$existing[0]['id'];
            }

            $insSql = "INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
                       VALUES (:uid, :aid, :t, :tid, :content, 0, NOW())";
            $db = Database::getConnection();
            $stmt = $db->prepare($insSql);
            $stmt->execute([
                ':uid'     => $userId,
                ':aid'     => $actorId,
                ':t'       => $type,
                ':tid'     => $targetId,
                ':content' => $content,
            ]);
            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error sending notification: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy danh sách thông báo của 1 user
     */
    public function getByUserId(int $userId, int $limit = 20): array
    {
        if ($userId <= 0) {
            return [];
        }
        try {
            $sql = "SELECT n.*, u.username AS actor_username, u.avatar_path AS actor_avatar
                    FROM notifications n
                    JOIN users u ON u.id = n.actor_id
                    WHERE n.user_id = :uid
                    ORDER BY n.created_at DESC
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting notifications: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm số thông báo chưa đọc
     */
    public function getUnreadCount(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }
        try {
            $rows = Database::query(
                "SELECT COUNT(*) AS total FROM notifications WHERE user_id = :uid AND is_read = 0",
                ['uid' => $userId]
            );
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(int $id, int $userId): bool
    {
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid";
            Database::query($sql, ['id' => $id, 'uid' => $userId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Đánh dấu tất cả thông báo của user là đã đọc
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = :uid";
            Database::query($sql, ['uid' => $userId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
