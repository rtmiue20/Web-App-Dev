<?php
require_once __DIR__ . '/../core/Model.php';

class Comment extends Model {
    /**
     * Lấy danh sách bình luận theo mã bài viết
     */
    public function getByPostId($postId) {
        $sql = "SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = :post_id 
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm bình luận mới vào cơ sở dữ liệu
     */
    public function create($postId, $userId, $content) {
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at) 
                VALUES (:post_id, :user_id, :content, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId,
            ':content' => $content
        ]);
    }
}