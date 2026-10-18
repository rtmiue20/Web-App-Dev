<?php
require_once __DIR__ . '/../core/Database.php';

class Post {
    private $id;
    private $userId;
    private $categoryId;
    private $title;
    private $content;
    private $location;
    private $createdAt;
    private $username;
    private $categoryName;

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

    public function getAll(?int $categoryId = null): array {
        $sql = "SELECT p.*, u.username, c.name AS category_name,
                (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes,
                (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN categories c ON p.category_id = c.id";
        $params = [];

        if ($categoryId !== null) {
            $sql .= " WHERE p.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        $sql .= " ORDER BY p.created_at DESC";

        try {
            return Database::query($sql, $params);
        } catch (PDOException $e) {
            error_log('Error getting posts: ' . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        $sql = "SELECT p.*, u.username, c.name AS category_name,
                (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        try {
            $posts = Database::query($sql, ['id' => $id]);
            return $posts[0] ?? false;
        } catch (PDOException $e) {
            error_log('Error getting post by ID: ' . $e->getMessage());
            return false;
        }
    }

    public function create(int $userId, int $categoryId, string $title, string $content, ?string $location = null, ?string $imageUrl = null): int {
        $db = Database::getConnection();
        $sql = "INSERT INTO posts (user_id, category_id, title, content, location, created_at) 
                VALUES (:user_id, :category_id, :title, :content, :location, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':category_id' => $categoryId,
            ':title' => $title,
            ':content' => $content,
            ':location' => $location
        ]);
        $postId = (int)$db->lastInsertId();

        if ($imageUrl && $postId > 0) {
            $sqlImg = "INSERT INTO post_images (post_id, image_url) VALUES (:post_id, :image_url)";
            $stmtImg = $db->prepare($sqlImg);
            $stmtImg->execute([':post_id' => $postId, ':image_url' => $imageUrl]);
        }

        return $postId;
    }

    public function count(): int {
        try {
            $res = Database::query("SELECT COUNT(*) AS total FROM posts");
            return (int)($res[0]['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function toggleLike(int $postId, int $userId): int {
        try {
            $results = Database::call('sp_toggle_like', [$postId, $userId]);
            return (int) ($results[0]['likes'] ?? 0);
        } catch (PDOException $e) {
            // Fallback if stored routine is not yet created
            $check = Database::query("SELECT 1 FROM post_likes WHERE post_id = :p AND user_id = :u", [
                'p' => $postId, 'u' => $userId
            ]);
            if (!empty($check)) {
                Database::execute("DELETE FROM post_likes WHERE post_id = :p AND user_id = :u", [
                    'p' => $postId, 'u' => $userId
                ]);
            } else {
                Database::execute("INSERT INTO post_likes (post_id, user_id) VALUES (:p, :u)", [
                    'p' => $postId, 'u' => $userId
                ]);
            }
            $countRes = Database::query("SELECT COUNT(*) as total FROM post_likes WHERE post_id = :p", ['p' => $postId]);
            return (int)($countRes[0]['total'] ?? 0);
        }
    }
}