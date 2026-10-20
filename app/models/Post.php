<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Post - Quản lý dữ liệu bài viết (Tuần 2 - Phúc)
 * Kế thừa BaseModel / Model theo kiến trúc MVC & OOP
 * Ưu tiên gọi Stored Procedures (sp_post_list, sp_post_get, sp_post_create)
 */
class Post extends Model {
    private ?int $id = null;
    private ?int $userId = null;
    private ?int $categoryId = null;
    private ?string $title = null;
    private ?string $content = null;
    private ?string $location = null;
    private ?string $createdAt = null;
    private ?string $username = null;
    private ?string $categoryName = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): void { $this->userId = $userId; }

    public function getCategoryId(): ?int { return $this->categoryId; }
    public function setCategoryId(?int $categoryId): void { $this->categoryId = $categoryId; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): void { $this->title = $title; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): void { $this->content = $content; }

    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $location): void { $this->location = $location; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(?string $username): void { $this->username = $username; }

    public function getCategoryName(): ?string { return $this->categoryName; }
    public function setCategoryName(?string $categoryName): void { $this->categoryName = $categoryName; }

    /**
     * Lấy danh sách bài viết (gọi routine sp_post_list)
     * @param int|null $categoryId Lọc theo chuyên mục hoặc null nếu lấy tất cả
     * @return array Danh sách bài viết kèm thông tin tác giả, chuyên mục, like, comment
     */
    public function getAll(?int $categoryId = null): array {
        try {
            // Ưu tiên gọi stored procedure sp_post_list
            return Database::call('sp_post_list', [$categoryId]);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_list fallback query: ' . $e->getMessage());

            // Fallback truy vấn trực tiếp nếu routine chưa được tạo
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
            } catch (PDOException $ex) {
                error_log('Error getting posts: ' . $ex->getMessage());
                return [];
            }
        }
    }

    /**
     * Lấy chi tiết bài viết theo ID (gọi routine sp_post_get)
     * @param int $id ID bài viết
     * @return array|false Mảng dữ liệu bài viết hoặc false nếu không tìm thấy
     */
    public function getById(int $id): array|false {
        try {
            // Ưu tiên gọi stored procedure sp_post_get
            $posts = Database::call('sp_post_get', [$id]);
            return $posts[0] ?? false;
        } catch (PDOException $e) {
            error_log('Notice: sp_post_get fallback query: ' . $e->getMessage());

            // Fallback truy vấn trực tiếp
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
            } catch (PDOException $ex) {
                error_log('Error getting post by ID: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Tạo bài viết mới (gọi routine sp_post_create)
     * @param int $userId ID tác giả
     * @param int $categoryId ID chuyên mục
     * @param string $title Tiêu đề
     * @param string $content Nội dung
     * @param string|null $location Địa điểm
     * @param string|null $imageUrl Đường dẫn ảnh bìa
     * @return int ID bài viết vừa tạo (0 nếu thất bại)
     */
    public function create(int $userId, int $categoryId, string $title, string $content, ?string $location = null, ?string $imageUrl = null): int {
        $postId = 0;

        try {
            // Ưu tiên gọi stored procedure sp_post_create
            $result = Database::call('sp_post_create', [$userId, $categoryId, $title, $content, $location]);
            $postId = (int)($result[0]['post_id'] ?? 0);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_create fallback query: ' . $e->getMessage());

            // Fallback truy vấn trực tiếp
            try {
                $sql = "INSERT INTO posts (user_id, category_id, title, content, location, created_at) 
                        VALUES (:user_id, :category_id, :title, :content, :location, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':category_id' => $categoryId,
                    ':title' => $title,
                    ':content' => $content,
                    ':location' => $location
                ]);
                $postId = (int)$this->db->lastInsertId();
            } catch (PDOException $ex) {
                error_log('Error creating post: ' . $ex->getMessage());
                return 0;
            }
        }

        // Lưu ảnh bìa nếu có
        if ($imageUrl && $postId > 0) {
            try {
                $sqlImg = "INSERT INTO post_images (post_id, image_url) VALUES (:post_id, :image_url)";
                $stmtImg = $this->db->prepare($sqlImg);
                $stmtImg->execute([':post_id' => $postId, ':image_url' => $imageUrl]);
            } catch (PDOException $ex) {
                error_log('Error saving post image: ' . $ex->getMessage());
            }
        }

        return $postId;
    }

    /**
     * Đếm tổng số bài viết trong hệ thống
     */
    public function count(): int {
        try {
            $res = Database::query("SELECT COUNT(*) AS total FROM posts");
            return (int)($res[0]['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('Error counting posts: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Thích hoặc bỏ thích bài viết (gọi routine sp_toggle_like)
     */
    public function toggleLike(int $postId, int $userId): int {
        try {
            $results = Database::call('sp_toggle_like', [$postId, $userId]);
            return (int) ($results[0]['likes'] ?? 0);
        } catch (PDOException $e) {
            // Fallback nếu routine sp_toggle_like chưa có
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