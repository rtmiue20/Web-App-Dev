<?php
require_once __DIR__ . '/../core/Database.php';

class Post {
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
    public function getAll(?int $categoryId = null): array {
        $sql = "SELECT p.*, u.username, c.name AS category_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN categories c ON p.category_id = c.id";
        $params = [];

        if ($categoryId !== null) {
            $sql .= " WHERE p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        $sql .= " ORDER BY p.created_at DESC";
        return Database::query($sql, $params);
    }

    public function getById(int $id): array|false {
        $sql = "SELECT p.*, u.username, c.name AS category_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        return Database::query($sql, [':id' => $id])[0] ?? false;
    }

    public function create(int $userId, int $categoryId, string $title, string $content, ?string $location): int {
        return Database::execute(
            "INSERT INTO posts (user_id, category_id, title, content, location)
             VALUES (:user_id, :category_id, :title, :content, :location)",
            [
                ':user_id' => $userId,
                ':category_id' => $categoryId,
                ':title' => $title,
                ':content' => $content,
                ':location' => $location,
            ]
        );
    }

    public function userExists(int $userId): bool {
        return Database::query(
            'SELECT id FROM users WHERE id = :id',
            [':id' => $userId]
        ) !== [];
    }

    public function toggleLike($postId, $userId) {
        $stmt = Database::getConnection()->prepare("CALL sp_toggle_like(:post_id, :user_id)");
        $stmt->execute([':post_id' => $postId, ':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        if (isset($result['likes'])) {
            return $result['likes'];
        }
        return 0;
    }
}