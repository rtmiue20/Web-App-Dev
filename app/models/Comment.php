<?php
require_once __DIR__ . '/../core/Model.php';

class Comment extends Model {
    // 1. Attributes (Thuộc tính đại diện cho bảng comments trong CSDL)
    private $id;
    private $postId;
    private $userId;
    private $content;
    private $createdAt;
    private $username; // Thuộc tính mở rộng khi JOIN với bảng users

    // 2. Getters & Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getPostId() { return $this->postId; }
    public function setPostId($postId) { $this->postId = $postId; }

    public function getUserId() { return $this->userId; }
    public function setUserId($userId) { $this->userId = $userId; }

    public function getContent() { return $this->content; }
    public function setContent($content) { $this->content = $content; }

    public function getCreatedAt() { return $this->createdAt; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }

    public function getUsername() { return $this->username; }
    public function setUsername($username) { $this->username = $username; }

    // 3. Functions riêng (Custom Functions tương tác CSDL)
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