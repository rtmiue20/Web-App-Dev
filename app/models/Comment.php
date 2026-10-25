<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Comment - Quản lý bình luận bài viết (Vương - Tuần 2 & 4)
 * Gọi các Stored Procedures: sp_comment_list, sp_comment_create, sp_comment_delete, sp_comment_count_by_post
 */
class Comment extends Model {
    private ?int $id = null;
    private ?int $postId = null;
    private ?int $userId = null;
    private ?string $content = null;
    private ?string $createdAt = null;
    private ?string $username = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getPostId(): ?int { return $this->postId; }
    public function setPostId(?int $postId): void { $this->postId = $postId; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): void { $this->userId = $userId; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): void { $this->content = $content; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(?string $username): void { $this->username = $username; }

    /**
     * Lấy danh sách bình luận theo ID bài viết (gọi routine sp_comment_list)
     */
    public function getByPostId(int $postId): array {
        try {
            return Database::call('sp_comment_list', [$postId]);
        } catch (PDOException $e) {
            error_log('Notice: sp_comment_list fallback query: ' . $e->getMessage());
            try {
                $sql = "SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username 
                        FROM comments c 
                        JOIN users u ON c.user_id = u.id 
                        WHERE c.post_id = :post_id 
                        ORDER BY c.created_at DESC";
                return Database::query($sql, ['post_id' => $postId]);
            } catch (PDOException $ex) {
                error_log('Error getting comments: ' . $ex->getMessage());
                return [];
            }
        }
    }

    /**
     * Thêm bình luận mới vào bài viết (gọi routine sp_comment_create)
     */
    public function create(int $postId, int $userId, string $content): int {
        try {
            $rows = Database::call('sp_comment_create', [$postId, $userId, $content]);
            $commentId = (int)($rows[0]['comment_id'] ?? 0);
            if ($commentId > 0) {
                $this->id = $commentId;
            }
            return $commentId;
        } catch (PDOException $e) {
            error_log('Notice: sp_comment_create fallback query: ' . $e->getMessage());
            try {
                $sql = "INSERT INTO comments (post_id, user_id, content, created_at) 
                        VALUES (:post_id, :user_id, :content, NOW())";
                $stmt = $this->db->prepare($sql);
                $ok = $stmt->execute([
                    ':post_id' => $postId,
                    ':user_id' => $userId,
                    ':content' => $content
                ]);
                return $ok ? (int)$this->db->lastInsertId() : 0;
            } catch (PDOException $ex) {
                error_log('Error creating comment: ' . $ex->getMessage());
                return 0;
            }
        }
    }

    /**
     * Xóa bình luận (gọi routine sp_comment_delete)
     */
    public function delete(int $id, int $userId, bool $isAdmin = false): bool {
        try {
            Database::call('sp_comment_delete', [$id, $userId, $isAdmin ? 1 : 0]);
            return true;
        } catch (PDOException $e) {
            error_log('Notice: sp_comment_delete fallback query: ' . $e->getMessage());
            try {
                if ($isAdmin) {
                    $sql = "DELETE FROM comments WHERE id = :id";
                    return Database::execute($sql, ['id' => $id]) > 0;
                }
                $sql = "DELETE FROM comments WHERE id = :id AND user_id = :user_id";
                return Database::execute($sql, ['id' => $id, 'user_id' => $userId]) > 0;
            } catch (PDOException $ex) {
                return false;
            }
        }
    }

    /**
     * Đếm số bình luận của bài viết (gọi routine sp_comment_count_by_post)
     */
    public function countByPost(int $postId): int {
        try {
            $rows = Database::call('sp_comment_count_by_post', [$postId]);
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            try {
                $res = Database::query("SELECT COUNT(*) AS total FROM comments WHERE post_id = :post_id", ['post_id' => $postId]);
                return (int)($res[0]['total'] ?? 0);
            } catch (PDOException $ex) {
                return 0;
            }
        }
    }
}
