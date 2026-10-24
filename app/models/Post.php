<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Post - Quản lý dữ liệu bài viết (Tuần 2 & Tuần 3 - Phúc)
 * Kế thừa BaseModel / Model theo kiến trúc MVC & OOP
 * Gọi Stored Procedures (sp_post_list, sp_post_get, sp_post_create, sp_post_update, sp_post_delete)
 */
class Post extends Model {
    private ?int $id = null;
    private ?int $userId = null;
    private ?int $categoryId = null;
    private ?string $title = null;
    private ?string $content = null;
    private ?string $location = null;
    private ?float $lat = null;
    private ?float $lng = null;
    private ?string $status = 'published';
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

    public function getLat(): ?float { return $this->lat; }
    public function setLat(?float $lat): void { $this->lat = $lat; }

    public function getLng(): ?float { return $this->lng; }
    public function setLng(?float $lng): void { $this->lng = $lng; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(?string $username): void { $this->username = $username; }

    public function getCategoryName(): ?string { return $this->categoryName; }
    public function setCategoryName(?string $categoryName): void { $this->categoryName = $categoryName; }

    /**
     * Lấy danh sách bài viết (gọi routine sp_post_list)
     * @param int|null $categoryId Lọc theo chuyên mục hoặc null nếu lấy tất cả
     * @return array Danh sách bài viết
     */
    public function getAll(?int $categoryId = null): array {
        try {
            return Database::call('sp_post_list', [$categoryId]);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_list fallback query: ' . $e->getMessage());

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
     * Lấy danh sách bài viết theo chuyên mục và phân trang (Tuần 4 - FR03 - Phúc)
     * @param int|null $categoryId Lọc theo chuyên mục hoặc null nếu lấy tất cả
     * @param int $page Số trang hiện tại (bắt đầu từ 1)
     * @param int $limit Số lượng bài viết trên 1 trang
     * @param string|null $status Lọc trạng thái ('published', 'pending', 'hidden') hoặc null
     * @return array Danh sách bài viết
     */
    public function getPaginated(?int $categoryId = null, int $page = 1, int $limit = 6, ?string $status = 'published'): array {
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        try {
            return Database::call('sp_post_list_paginated', [$categoryId, $status, $limit, $offset]);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_list_paginated fallback query: ' . $e->getMessage());

            $sql = "SELECT p.*, u.username, c.name AS category_name,
                    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes,
                    (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN categories c ON p.category_id = c.id
                    WHERE 1=1";
            
            $params = [];

            if ($categoryId !== null) {
                $sql .= " AND p.category_id = :category_id";
                $params['category_id'] = $categoryId;
            }

            if ($status !== null) {
                $sql .= " AND p.status = :status";
                $params['status'] = $status;
            }

            $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

            try {
                $stmt = $this->db->prepare($sql);
                foreach ($params as $paramKey => $paramVal) {
                    $stmt->bindValue(':' . $paramKey, $paramVal);
                }
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                error_log('Error getting paginated posts: ' . $ex->getMessage());
                return [];
            }
        }
    }

    /**
     * Đếm tổng số bài viết theo chuyên mục và trạng thái phục vụ phân trang (Tuần 4 - FR03 - Phúc)
     * @param int|null $categoryId ID chuyên mục hoặc null
     * @param string|null $status Trạng thái bài viết hoặc null
     * @return int Tổng số bài viết
     */
    public function countFiltered(?int $categoryId = null, ?string $status = 'published'): int {
        try {
            $res = Database::call('sp_post_count_filtered', [$categoryId, $status]);
            return (int)($res[0]['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_count_filtered fallback query: ' . $e->getMessage());

            $sql = "SELECT COUNT(*) AS total FROM posts WHERE 1=1";
            $params = [];

            if ($categoryId !== null) {
                $sql .= " AND category_id = :category_id";
                $params['category_id'] = $categoryId;
            }

            if ($status !== null) {
                $sql .= " AND status = :status";
                $params['status'] = $status;
            }

            try {
                $res = Database::query($sql, $params);
                return (int)($res[0]['total'] ?? 0);
            } catch (PDOException $ex) {
                error_log('Error counting filtered posts: ' . $ex->getMessage());
                return 0;
            }
        }
    }

    /**
     * Cập nhật trạng thái bài viết: duyệt, ẩn (Tuần 4 - FR13 - Phúc)
     * @param int $id ID bài viết
     * @param string $status Trạng thái mới ('published', 'pending', 'hidden')
     * @return bool Thành công hay thất bại
     */
    public function updateStatus(int $id, string $status): bool {
        if (!in_array($status, ['published', 'pending', 'hidden'], true)) {
            return false;
        }

        try {
            Database::call('sp_post_update_status', [$id, $status]);
            return true;
        } catch (PDOException $e) {
            error_log('Notice: sp_post_update_status fallback query: ' . $e->getMessage());
            try {
                $stmt = $this->db->prepare("UPDATE posts SET status = :status WHERE id = :id");
                return $stmt->execute([':status' => $status, ':id' => $id]);
            } catch (PDOException $ex) {
                error_log('Error updating post status: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Lấy danh sách bài viết phục vụ trang quản trị Admin (Tuần 4 - FR13 - Phúc)
     * Cho phép lọc theo trạng thái, chuyên mục và có phân trang
     * @param string|null $status Trạng thái hoặc null/all nếu lấy tất cả
     * @param int|null $categoryId ID chuyên mục hoặc null
     * @param int $page Trang hiện tại
     * @param int $limit Số dòng trên 1 trang
     * @return array Danh sách bài viết
     */
    public function getAllForAdmin(?string $status = null, ?int $categoryId = null, int $page = 1, int $limit = 10): array {
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        $sql = "SELECT p.*, u.username, c.name AS category_name,
                (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes,
                (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN categories c ON p.category_id = c.id
                WHERE 1=1";
        
        $params = [];

        if ($status !== null && $status !== '' && $status !== 'all') {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $paramKey => $paramVal) {
                $stmt->bindValue(':' . $paramKey, $paramVal);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            error_log('Error getting posts for admin: ' . $ex->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số bài viết trong trang quản trị Admin theo bộ lọc (Tuần 4 - FR13 - Phúc)
     * @param string|null $status Trạng thái hoặc null/all
     * @param int|null $categoryId ID chuyên mục hoặc null
     * @return int Tổng số bài viết
     */
    public function countForAdmin(?string $status = null, ?int $categoryId = null): int {
        $sql = "SELECT COUNT(*) AS total FROM posts WHERE 1=1";
        $params = [];

        if ($status !== null && $status !== '' && $status !== 'all') {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        try {
            $res = Database::query($sql, $params);
            return (int)($res[0]['total'] ?? 0);
        } catch (PDOException $ex) {
            error_log('Error counting posts for admin: ' . $ex->getMessage());
            return 0;
        }
    }

    /**
     * Lấy chi tiết bài viết theo ID (gọi routine sp_post_get)
     * @param int $id ID bài viết
     * @return array|false Mảng dữ liệu bài viết hoặc false nếu không tìm thấy
     */
    public function getById(int $id): array|false {
        try {
            $posts = Database::call('sp_post_get', [$id]);
            return $posts[0] ?? false;
        } catch (PDOException $e) {
            error_log('Notice: sp_post_get fallback query: ' . $e->getMessage());

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
     * @param float|null $lat Vĩ độ
     * @param float|null $lng Kinh độ
     * @param string $status Trạng thái bài viết ('published', 'pending', 'hidden')
     * @return int ID bài viết vừa tạo (0 nếu thất bại)
     */
    public function create(
        int $userId, 
        int $categoryId, 
        string $title, 
        string $content, 
        ?string $location = null, 
        ?string $imageUrl = null,
        ?float $lat = null,
        ?float $lng = null,
        string $status = 'published'
    ): int {
        $postId = 0;

        try {
            $result = Database::call('sp_post_create', [$userId, $categoryId, $title, $content, $location, $lat, $lng, $status]);
            $postId = (int)($result[0]['post_id'] ?? 0);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_create fallback query: ' . $e->getMessage());

            try {
                $sql = "INSERT INTO posts (user_id, category_id, title, content, location, lat, lng, status, created_at) 
                        VALUES (:user_id, :category_id, :title, :content, :location, :lat, :lng, :status, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':category_id' => $categoryId,
                    ':title' => $title,
                    ':content' => $content,
                    ':location' => $location,
                    ':lat' => $lat,
                    ':lng' => $lng,
                    ':status' => $status
                ]);
                $postId = (int)$this->db->lastInsertId();
            } catch (PDOException $ex) {
                // Thử lại nếu DB chưa có cột lat/lng/status
                try {
                    $sql2 = "INSERT INTO posts (user_id, category_id, title, content, location, created_at) 
                             VALUES (:user_id, :category_id, :title, :content, :location, NOW())";
                    $stmt2 = $this->db->prepare($sql2);
                    $stmt2->execute([
                        ':user_id' => $userId,
                        ':category_id' => $categoryId,
                        ':title' => $title,
                        ':content' => $content,
                        ':location' => $location
                    ]);
                    $postId = (int)$this->db->lastInsertId();
                } catch (PDOException $ex2) {
                    error_log('Error creating post fallback: ' . $ex2->getMessage());
                    return 0;
                }
            }
        }

        // Lưu ảnh bìa vào bảng post_images nếu có
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
     * Cập nhật bài viết (Tuần 3 - Phúc)
     * Gọi stored procedure sp_post_update với fallback truy vấn trực tiếp
     */
    public function update(
        int $id,
        int $categoryId,
        string $title,
        string $content,
        ?string $location = null,
        ?string $imageUrl = null,
        ?float $lat = null,
        ?float $lng = null,
        ?string $status = null
    ): bool {
        $updated = false;

        try {
            Database::call('sp_post_update', [$id, $categoryId, $title, $content, $location, $lat, $lng, $status]);
            $updated = true;
        } catch (PDOException $e) {
            error_log('Notice: sp_post_update fallback query: ' . $e->getMessage());

            try {
                $sql = "UPDATE posts 
                        SET category_id = :category_id,
                            title = :title,
                            content = :content,
                            location = :location,
                            lat = :lat,
                            lng = :lng,
                            status = COALESCE(:status, status)
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $updated = $stmt->execute([
                    ':category_id' => $categoryId,
                    ':title' => $title,
                    ':content' => $content,
                    ':location' => $location,
                    ':lat' => $lat,
                    ':lng' => $lng,
                    ':status' => $status,
                    ':id' => $id
                ]);
            } catch (PDOException $ex) {
                // Thử lại nếu DB chưa có cột lat/lng/status
                try {
                    $sql2 = "UPDATE posts 
                             SET category_id = :category_id,
                                 title = :title,
                                 content = :content,
                                 location = :location
                             WHERE id = :id";
                    $stmt2 = $this->db->prepare($sql2);
                    $updated = $stmt2->execute([
                        ':category_id' => $categoryId,
                        ':title' => $title,
                        ':content' => $content,
                        ':location' => $location,
                        ':id' => $id
                    ]);
                } catch (PDOException $ex2) {
                    error_log('Error updating post: ' . $ex2->getMessage());
                    return false;
                }
            }
        }

        // Cập nhật ảnh bìa mới nếu được tải lên
        if ($imageUrl) {
            try {
                $exists = Database::query("SELECT id FROM post_images WHERE post_id = :post_id LIMIT 1", ['post_id' => $id]);
                if (!empty($exists)) {
                    Database::execute("UPDATE post_images SET image_url = :image_url WHERE post_id = :post_id", [
                        'image_url' => $imageUrl,
                        'post_id' => $id
                    ]);
                } else {
                    Database::execute("INSERT INTO post_images (post_id, image_url) VALUES (:post_id, :image_url)", [
                        'post_id' => $id,
                        'image_url' => $imageUrl
                    ]);
                }
            } catch (PDOException $ex) {
                error_log('Error updating post image: ' . $ex->getMessage());
            }
        }

        return $updated;
    }

    /**
     * Xóa bài viết theo ID (Tuần 3 - Phúc)
     * Gọi stored procedure sp_post_delete với fallback
     */
    public function delete(int $id): bool {
        try {
            Database::call('sp_post_delete', [$id]);
            return true;
        } catch (PDOException $e) {
            error_log('Notice: sp_post_delete fallback query: ' . $e->getMessage());

            try {
                $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
                return $stmt->execute([':id' => $id]);
            } catch (PDOException $ex) {
                error_log('Error deleting post: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Đếm tổng số bài viết trong hệ thống (gọi routine sp_post_count)
     */
    public function count(): int {
        try {
            $res = Database::call('sp_post_count');
            return (int)($res[0]['total'] ?? 0);
        } catch (PDOException $e) {
            try {
                $res = Database::query("SELECT COUNT(*) AS total FROM posts");
                return (int)($res[0]['total'] ?? 0);
            } catch (PDOException $ex) {
                error_log('Error counting posts: ' . $ex->getMessage());
                return 0;
            }
        }
    }

    /**
     * Tìm kiếm bài viết theo từ khóa và chuyên mục (gọi routine sp_post_search)
     */
    public function search(string $keyword, ?int $categoryId = null): array {
        try {
            return Database::call('sp_post_search', [$keyword, $categoryId]);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_search fallback query: ' . $e->getMessage());
            try {
                $sql = "SELECT p.*, u.username, c.name AS category_name,
                               (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                               (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes,
                               (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                        FROM posts p
                        JOIN users u ON u.id = p.user_id
                        JOIN categories c ON c.id = p.category_id
                        WHERE (:cat IS NULL OR p.category_id = :cat)
                          AND (p.title LIKE :kw OR p.content LIKE :kw OR p.location LIKE :kw)
                        ORDER BY p.created_at DESC";
                $pattern = '%' . trim($keyword) . '%';
                return Database::query($sql, ['cat' => $categoryId, 'kw' => $pattern]);
            } catch (PDOException $ex) {
                return [];
            }
        }
    }

    /**
     * Gợi ý từ khóa tìm kiếm khi người dùng đang gõ AJAX (gọi routine sp_post_search_suggest)
     */
    public function suggest(string $keyword): array {
        try {
            return Database::call('sp_post_search_suggest', [$keyword]);
        } catch (PDOException $e) {
            try {
                $pattern = '%' . trim($keyword) . '%';
                return Database::query(
                    "SELECT id, title, location FROM posts WHERE title LIKE :kw OR location LIKE :kw ORDER BY created_at DESC LIMIT 8",
                    ['kw' => $pattern]
                );
            } catch (PDOException $ex) {
                return [];
            }
        }
    }

    /**
     * Kiểm tra người dùng đã thích bài viết chưa (gọi routine sp_post_is_liked)
     */
    public function isLiked(int $postId, int $userId): bool {
        try {
            $rows = Database::call('sp_post_is_liked', [$postId, $userId]);
            return !empty($rows) && (bool)($rows[0]['is_liked'] ?? false);
        } catch (PDOException $e) {
            $rows = Database::query("SELECT 1 FROM post_likes WHERE post_id = :p AND user_id = :u", ['p' => $postId, 'u' => $userId]);
            return !empty($rows);
        }
    }

    /**
     * Thích hoặc bỏ thích bài viết (gọi routine sp_toggle_like hoặc sp_post_toggle_like)
     */
    public function toggleLike(int $postId, int $userId): int {
        try {
            $results = Database::call('sp_toggle_like', [$postId, $userId]);
            return (int) ($results[0]['likes'] ?? 0);
        } catch (PDOException $e) {
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
