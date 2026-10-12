<?php
require_once __DIR__ . '/../core/Model.php';

class Post extends Model {
    /**
     * Lấy danh sách tất cả bài viết
     */
    public function getAll() {
        $sql = "SELECT p.*, u.username, c.name AS category_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết bài viết theo ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, u.username, c.name AS category_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thích / Bỏ thích bài viết (Gọi Stored Procedure)
     */
    public function toggleLike($postId, $userId) {
        $stmt = $this->db->prepare("CALL sp_toggle_like(:post_id, :user_id)");
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Đóng cursor sau khi gọi Stored Procedure trong PDO
        
        if (isset($result['likes'])) {
            return $result['likes'];
        }
        return 0;
    }
}