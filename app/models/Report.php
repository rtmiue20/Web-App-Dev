<?php

class Report extends Model
{
    /**
     * Tạo báo cáo vi phạm mới
     */
    public function create(
        int $reporterId,
        string $targetType,
        int $targetId,
        string $reason,
        ?string $details = null
    ): int {
        if ($reporterId <= 0 || $targetId <= 0) {
            return 0;
        }

        try {
            // Tránh báo cáo trùng lặp từ cùng 1 người dùng khi báo cáo trước đó còn pending
            $checkSql = "SELECT id FROM reports 
                         WHERE reporter_id = :rid AND target_type = :tt AND target_id = :tid AND status = 'pending' 
                         LIMIT 1";
            $existing = Database::query($checkSql, [
                'rid' => $reporterId,
                'tt'  => $targetType,
                'tid' => $targetId,
            ]);

            if (!empty($existing)) {
                return (int)$existing[0]['id'];
            }

            $sql = "INSERT INTO reports (reporter_id, target_type, target_id, reason, details, status, created_at)
                    VALUES (:rid, :tt, :tid, :reason, :details, 'pending', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':rid'     => $reporterId,
                ':tt'      => $targetType,
                ':tid'     => $targetId,
                ':reason'  => $reason,
                ':details' => $details,
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error creating report: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy danh sách báo cáo (cho Moderator & Admin)
     */
    public function getAll(string $status = 'pending', int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        try {
            $sql = "SELECT r.*, 
                           u.username AS reporter_username,
                           res_u.username AS resolved_by_username
                    FROM reports r
                    JOIN users u ON u.id = r.reporter_id
                    LEFT JOIN users res_u ON res_u.id = r.resolved_by";

            $params = [];
            if ($status !== 'all') {
                $sql .= " WHERE r.status = :status";
                $params['status'] = $status;
            }

            $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue(':' . $k, $v);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Gắn thêm ngữ cảnh mục tiêu bị báo cáo (Tiêu đề bài viết, nội dung bình luận...)
            foreach ($reports as &$item) {
                $item['target_info'] = $this->getTargetPreview($item['target_type'], (int)$item['target_id']);
            }
            unset($item);

            return $reports;
        } catch (PDOException $e) {
            error_log('Error getting reports: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy bản xem trước đối tượng bị báo cáo (Post title, comment content...)
     */
    public function getTargetPreview(string $type, int $id): ?array
    {
        try {
            if ($type === 'post') {
                $rows = Database::query(
                    "SELECT id, title, status, user_id FROM posts WHERE id = :id",
                    ['id' => $id]
                );
                return $rows[0] ?? null;
            } elseif ($type === 'comment') {
                $rows = Database::query(
                    "SELECT c.id, c.content, c.post_id, c.user_id, u.username 
                     FROM comments c 
                     JOIN users u ON u.id = c.user_id 
                     WHERE c.id = :id",
                    ['id' => $id]
                );
                return $rows[0] ?? null;
            } elseif ($type === 'user') {
                $rows = Database::query(
                    "SELECT id, username, email FROM users WHERE id = :id",
                    ['id' => $id]
                );
                return $rows[0] ?? null;
            }
        } catch (PDOException $e) {
            return null;
        }
        return null;
    }

    /**
     * Đếm số lượng báo cáo đang chờ xử lý
     */
    public function countPending(): int
    {
        try {
            $rows = Database::query("SELECT COUNT(*) AS total FROM reports WHERE status = 'pending'");
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Cập nhật trạng thái báo cáo (resolved, dismissed)
     */
    public function updateStatus(int $id, string $status, int $resolvedBy): bool
    {
        try {
            $sql = "UPDATE reports 
                    SET status = :status, resolved_by = :rb, updated_at = NOW() 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':status' => $status,
                ':rb'     => $resolvedBy,
                ':id'     => $id,
            ]);
        } catch (PDOException $e) {
            error_log('Error updating report status: ' . $e->getMessage());
            return false;
        }
    }
}
