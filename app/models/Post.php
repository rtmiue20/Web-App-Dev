<?php
require_once __DIR__ . '/../core/Model.php';

class Post extends Model {
    // 1. Attributes (Thuộc tính đại diện cho bảng posts)
    private $id;
    private $userId;
    private $categoryId;
    private $title;
    private $content;
    private $location;
    private $createdAt;
    private $username;
    private $categoryName;

    // 2. Getters & Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getUserId() { return $this->userId; }
    public function setUserId($userId) { $this->userId = $userId; }

    public function getCategoryId() { return $this->categoryId; }
    public function setCategoryId($categoryId) { $this->categoryId = $categoryId; }

    public function getTitle() { return $this->title; }
    public function setTitle($title) { $this->title = $title; }

    public function getContent() { return $this->content; }
    public function setContent($content) { $this->content = $content; }

    public function getLocation() { return $this->location; }
    public function setLocation($location) { $this->location = $location; }

    public function getCreatedAt() { return $this->createdAt; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }

    public function getUsername() { return $this->username; }
    public function setUsername($username) { $this->username = $username; }

    public function getCategoryName() { return $this->categoryName; }
    public function setCategoryName($categoryName) { $this->categoryName = $categoryName; }

    // 3. Functions riêng
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

    public function toggleLike($postId, $userId) {
        $stmt = $this->db->prepare("CALL sp_toggle_like(:post_id, :user_id)");
        $stmt->execute([
            ':post_id' => $postId,
            ':user_id' => $userId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        if (isset($result['likes'])) {
            return $result['likes'];
        }
        return 0;
    }
}