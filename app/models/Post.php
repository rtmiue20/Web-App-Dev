<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Post - Quản lý dữ liệu bài viết, Hệ thống Vote (Upvote/Downvote) & Sắp xếp (Hot/Mới/Top)
 * Kế thừa BaseModel / Model theo kiến trúc MVC & OOP
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
    private ?string $status = 'pending';
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
     * Chuẩn hóa tiêu chí sắp xếp bài viết ('hot', 'new', 'top')
     */
    public static function normalizeSort(?string $sort): string {
        $clean = strtolower(trim((string)$sort));
        if (in_array($clean, ['hot', 'new', 'top'], true)) {
            return $clean;
        }
        return 'hot';
    }

    /**
     * Sinh mệnh đề ORDER BY tương ứng với tiêu chí sắp xếp:
     * - 'new' (Mới nhất): Sắp xếp theo thời gian đăng mới nhất
     * - 'top' (Top Vote): Sắp xếp theo tổng điểm vote (Upvote - Downvote) cao nhất
     * - 'hot' (Thịnh hành): Kết hợp điểm vote (x2) + số bình luận thảo luận (x3) và độ mới
     */
    private function buildOrderByClause(string $sort): string {
        $sort = self::normalizeSort($sort);
        return match ($sort) {
            'top' => " ORDER BY vote_score DESC, upvotes DESC, comments_count DESC, p.created_at DESC, p.id DESC",
            'new' => " ORDER BY p.created_at DESC, p.id DESC",
            default => " ORDER BY (COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) * 2 + (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) * 3) DESC, p.created_at DESC, p.id DESC",
        };
    }

    /**
     * Sinh mệnh đề ORDER BY trên SQL View `vw_post_details`:
     * - 'new' (Mới nhất): Sắp xếp theo thời gian đăng mới nhất
     * - 'top' (Top Vote): Sắp xếp theo tổng điểm vote (Upvote - Downvote) cao nhất
     * - 'hot' (Thịnh hành): Sắp xếp theo hot_score (vote_score * 2 + comments_count * 3)
     */
    private function buildViewOrderByClause(string $sort): string {
        $sort = self::normalizeSort($sort);
        return match ($sort) {
            'top' => " ORDER BY vote_score DESC, upvotes DESC, comments_count DESC, created_at DESC, id DESC",
            'new' => " ORDER BY created_at DESC, id DESC",
            default => " ORDER BY hot_score DESC, created_at DESC, id DESC",
        };
    }

    /**
     * Lấy danh sách bài viết từ SQL View `vw_post_details` có hỗ trợ lọc chuyên mục và sắp xếp theo Hot / Mới / Top
     * @param int|null $categoryId Lọc theo chuyên mục hoặc null nếu lấy tất cả
     * @param string $sort 'hot' | 'new' | 'top'
     * @return array Danh sách bài viết
     */
    public function getAll(?int $categoryId = null, string $sort = 'hot'): array {
        $params = [];
        $viewSql = "SELECT * FROM vw_post_details WHERE status = 'published'";
        if ($categoryId !== null) {
            $viewSql .= " AND category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        $viewSql .= $this->buildViewOrderByClause($sort);

        try {
            return Database::query($viewSql, $params);
        } catch (PDOException $viewEx) {
            // Fallback nếu View vw_post_details chưa được khởi tạo
            $sql = "SELECT p.*, u.username, c.name AS category_name,
                    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                    (SELECT COUNT(pi.id) FROM post_images pi WHERE pi.post_id = p.id) AS images_count,
                    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS vote_score,
                    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = 1) AS upvotes,
                    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = -1) AS downvotes,
                    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS likes,
                    (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'published'";
            if ($categoryId !== null) {
                $sql .= " AND p.category_id = :category_id";
            }
            $sql .= $this->buildOrderByClause($sort);

            try {
                return Database::query($sql, $params);
            } catch (PDOException $ex) {
                error_log('Error getting posts: ' . $ex->getMessage());
                return [];
            }
        }
    }

    /**
     * Lấy danh sách bài viết từ SQL View `vw_post_details` theo chuyên mục, sắp xếp (hot/new/top) và phân trang
     * @param int|null $categoryId Lọc theo chuyên mục hoặc null nếu lấy tất cả
     * @param int $page Số trang hiện tại (bắt đầu từ 1)
     * @param int $limit Số lượng bài viết trên 1 trang
     * @param string|null $status Lọc trạng thái ('published', 'pending', 'hidden') hoặc null
     * @param string $sort Tiêu chí sắp xếp ('hot', 'new', 'top')
     * @return array Danh sách bài viết
     */
    public function getPaginated(
        ?int $categoryId = null,
        int $page = 1,
        int $limit = 6,
        ?string $status = 'published',
        string $sort = 'hot'
    ): array {
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        $params = [];
        $viewSql = "SELECT * FROM vw_post_details WHERE 1=1";
        if ($categoryId !== null) {
            $viewSql .= " AND category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        if ($status !== null) {
            $viewSql .= " AND status = :status";
            $params['status'] = $status;
        }
        $viewSql .= $this->buildViewOrderByClause($sort) . " LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($viewSql);
            foreach ($params as $paramKey => $paramVal) {
                $stmt->bindValue(':' . $paramKey, $paramVal);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $viewEx) {
            $sql = "SELECT p.*, u.username, c.name AS category_name,
                    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                    (SELECT COUNT(pi.id) FROM post_images pi WHERE pi.post_id = p.id) AS images_count,
                    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS vote_score,
                    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = 1) AS upvotes,
                    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = -1) AS downvotes,
                    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS likes,
                    (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN categories c ON p.category_id = c.id
                    WHERE 1=1";

            if ($categoryId !== null) {
                $sql .= " AND p.category_id = :category_id";
            }
            if ($status !== null) {
                $sql .= " AND p.status = :status";
            }
            $sql .= $this->buildOrderByClause($sort) . " LIMIT :limit OFFSET :offset";

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
        if (!in_array($status, ['published', 'pending', 'rejected', 'hidden'], true)) {
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
     */
    public function getAllForAdmin(?string $status = null, ?int $categoryId = null, int $page = 1, int $limit = 10): array {
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        $params = [];
        $viewSql = "SELECT * FROM vw_post_details WHERE 1=1";
        if ($status !== null && $status !== '' && $status !== 'all') {
            $viewSql .= " AND status = :status";
            $params['status'] = $status;
        }
        if ($categoryId !== null && $categoryId > 0) {
            $viewSql .= " AND category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        $viewSql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($viewSql);
            foreach ($params as $paramKey => $paramVal) {
                $stmt->bindValue(':' . $paramKey, $paramVal);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $viewEx) {
            $sql = "SELECT p.*, u.username, c.name AS category_name,
                    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                    (SELECT COUNT(pi.id) FROM post_images pi WHERE pi.post_id = p.id) AS images_count,
                    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS vote_score,
                    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = 1) AS upvotes,
                    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = -1) AS downvotes,
                    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS likes,
                    (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN categories c ON p.category_id = c.id
                    WHERE 1=1";

            if ($status !== null && $status !== '' && $status !== 'all') {
                $sql .= " AND p.status = :status";
            }
            if ($categoryId !== null && $categoryId > 0) {
                $sql .= " AND p.category_id = :category_id";
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
    }

    /**
     * Đếm tổng số bài viết trong trang quản trị Admin theo bộ lọc (Tuần 4 - FR13 - Phúc)
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
     * Lấy chi tiết bài viết theo ID kèm đầy đủ thông tin Vote (score, upvotes, downvotes)
     * @param int $id ID bài viết
     * @return array|false Mảng dữ liệu bài viết hoặc false nếu không tìm thấy
     */
    public function getById(int $id): array|false {
        $post = false;
        try {
            $posts = Database::call('sp_post_get', [$id]);
            $post = $posts[0] ?? false;
        } catch (PDOException $e) {
            error_log('Notice: sp_post_get fallback query: ' . $e->getMessage());

            $sql = "SELECT p.*, u.username, c.name AS category_name,
                    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN categories c ON p.category_id = c.id 
                    WHERE p.id = :id";
            try {
                $posts = Database::query($sql, ['id' => $id]);
                $post = $posts[0] ?? false;
            } catch (PDOException $ex) {
                error_log('Error getting post by ID: ' . $ex->getMessage());
                return false;
            }
        }

        if ($post) {
            $stats = $this->getVoteStats($id);
            $post['vote_score'] = $stats['score'];
            $post['upvotes']    = $stats['upvotes'];
            $post['downvotes']  = $stats['downvotes'];
            $post['likes']      = $stats['score'];

            $images = $this->getImages($id);
            if (empty($images) && !empty($post['image_url'])) {
                $images = [['id' => 0, 'post_id' => $id, 'image_url' => $post['image_url']]];
            }
            $post['images'] = $images;
        }

        return $post;
    }

    /**
     * Tạo bài viết mới (gọi routine sp_post_create)
     */
    public function create(
        int $userId, 
        int $categoryId, 
        string $title, 
        string $content, 
        ?string $location = null, 
        string|array|null $imageUrl = null,
        ?float $lat = null,
        ?float $lng = null,
        string $status = 'pending',
        array $tripData = []
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

        if ($postId > 0 && !empty($imageUrl)) {
            if (is_array($imageUrl)) {
                $this->addImages($postId, $imageUrl);
            } elseif (is_string($imageUrl)) {
                $this->addImages($postId, [$imageUrl]);
            }
        }

        if ($postId > 0 && !empty($tripData)) {
            $this->updateTripData($postId, $tripData);
        }

        return $postId;
    }

    /**
     * Cập nhật bài viết (Tuần 3 - Phúc)
     */
    public function update(
        int $id,
        int $categoryId,
        string $title,
        string $content,
        ?string $location = null,
        string|array|null $imageUrl = null,
        ?float $lat = null,
        ?float $lng = null,
        ?string $status = null,
        array $tripData = []
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

        if (!empty($imageUrl)) {
            $urls = is_array($imageUrl) ? $imageUrl : [$imageUrl];
            $this->addImages($id, $urls);
        }

        if (!empty($tripData)) {
            $this->updateTripData($id, $tripData);
        }

        return $updated;
    }

    /**
     * Cập nhật thông tin bài viết dạng "Chuyến đi" (Trip details)
     */
    public function updateTripData(int $postId, array $data): bool {
        $allowed = ['route', 'trip_duration', 'estimated_cost', 'gear_needed', 'difficulty', 'region', 'activity_type'];
        $fields = [];
        $params = ['id' => $postId];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = :$f";
                $val = $data[$f];
                $params[$f] = ($val !== null && $val !== '') ? trim((string)$val) : null;
            }
        }
        if (empty($fields)) {
            return false;
        }
        try {
            $sql = "UPDATE posts SET " . implode(', ', $fields) . " WHERE id = :id";
            Database::execute($sql, $params);
            return true;
        } catch (PDOException $e) {
            error_log('Error updating trip data: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa bài viết theo ID (Tuần 3 - Phúc)
     */
    public function delete(int $id): bool {
        $deleted = false;
        try {
            Database::call('sp_post_delete', [$id]);
            $deleted = true;
        } catch (PDOException $e) {
            error_log('Notice: sp_post_delete fallback query: ' . $e->getMessage());

            try {
                $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
                $deleted = $stmt->execute([':id' => $id]);
            } catch (PDOException $ex) {
                error_log('Error deleting post: ' . $ex->getMessage());
                return false;
            }
        }

        if ($deleted) {
            foreach (['post_bookmarks', 'post_products', 'post_tags', 'post_images', 'post_likes', 'place_reviews'] as $childTable) {
                try {
                    Database::execute("DELETE FROM {$childTable} WHERE post_id = :id", ['id' => $id]);
                } catch (Throwable $cleanupEx) {
                }
            }
            try {
                // Tự động dọn dẹp các dụng cụ tự nhập do thành viên tạo nếu không còn bài viết nào sử dụng
                Database::execute("
                    DELETE FROM products 
                    WHERE user_id IS NOT NULL 
                      AND user_id NOT IN (SELECT u.id FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.name IN ('admin', 'moderator'))
                      AND id NOT IN (SELECT product_id FROM post_products)
                ");
            } catch (Throwable $orphanEx) {
            }
            try {
                Database::execute(
                    "DELETE FROM notifications WHERE target_id = :id AND type IN ('create_post', 'admin_pending_post', 'like_post', 'comment_post', 'reply_comment', 'review_place', 'bookmark_post')",
                    ['id' => $id]
                );
            } catch (Throwable $notifEx) {
            }
        }

        return $deleted;
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
     * Tìm kiếm bài viết theo từ khóa, chuyên mục và sắp xếp
     */
    public function search(string $keyword, ?int $categoryId = null, string $sort = 'hot'): array {
        try {
            $params = [];
            $where = ["p.status = 'published'"];

            if ($categoryId !== null) {
                $where[] = "p.category_id = :cat";
                $params['cat'] = $categoryId;
            }

            $pattern = '%' . trim($keyword) . '%';
            $where[] = "(p.title LIKE :kw1 OR p.content LIKE :kw2 OR p.location LIKE :kw3)";
            $params['kw1'] = $pattern;
            $params['kw2'] = $pattern;
            $params['kw3'] = $pattern;

            $sql = "SELECT p.*, u.username, c.name AS category_name,
                           (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                           COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS vote_score,
                           (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = 1) AS upvotes,
                           (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = -1) AS downvotes,
                           COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS likes,
                           (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                    FROM posts p
                    JOIN users u ON u.id = p.user_id
                    JOIN categories c ON c.id = p.category_id
                    WHERE " . implode(' AND ', $where)
                    . $this->buildOrderByClause($sort);
            return Database::query($sql, $params);
        } catch (PDOException $ex) {
            return [];
        }
    }

    /**
     * Tìm kiếm nâng cao: MariaDB FULLTEXT kết hợp bộ lọc đa tiêu chí (độ khó, vùng miền, loại hình, tag)
     */
    public function advancedSearch(array $filters = [], int $page = 1, int $limit = 6): array {
        $offset = ($page - 1) * $limit;
        if ($offset < 0) {
            $offset = 0;
        }

        $params = [];
        $where = ["p.status = 'published'"];

        $keyword = trim((string)($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $words = preg_split('/\s+/', $keyword);
            $booleanTokens = [];
            foreach ($words as $w) {
                $w = trim($w);
                if ($w !== '') {
                    $booleanTokens[] = '+' . $w . '*';
                }
            }
            $booleanQuery = implode(' ', $booleanTokens);

            $where[] = "(MATCH(p.title, p.content, p.location) AGAINST(:kw_boolean IN BOOLEAN MODE) OR p.title LIKE :kw_like1 OR p.location LIKE :kw_like2 OR p.content LIKE :kw_like3)";
            $params['kw_boolean'] = $booleanQuery;
            $params['kw_like1'] = '%' . $keyword . '%';
            $params['kw_like2'] = '%' . $keyword . '%';
            $params['kw_like3'] = '%' . $keyword . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :cat_id";
            $params['cat_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['activity_type'])) {
            $where[] = "p.activity_type = :act_type";
            $params['act_type'] = $filters['activity_type'];
        }

        if (!empty($filters['difficulty'])) {
            $where[] = "p.difficulty = :difficulty";
            $params['difficulty'] = $filters['difficulty'];
        }

        if (!empty($filters['region'])) {
            $where[] = "p.region = :region";
            $params['region'] = $filters['region'];
        }

        if (!empty($filters['tag_id'])) {
            $where[] = "EXISTS (SELECT 1 FROM post_tags pt WHERE pt.post_id = p.id AND pt.tag_id = :tag_id)";
            $params['tag_id'] = (int)$filters['tag_id'];
        } elseif (!empty($filters['tag_name'])) {
            $where[] = "EXISTS (SELECT 1 FROM post_tags pt JOIN tags t ON t.id = pt.tag_id WHERE pt.post_id = p.id AND t.name = :tag_name)";
            $params['tag_name'] = $filters['tag_name'];
        }

        $sort = self::normalizeSort($filters['sort'] ?? 'hot');

        $sql = "SELECT p.*, u.username, c.name AS category_name,
                       (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                       COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS vote_score,
                       (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = 1) AS upvotes,
                       (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = -1) AS downvotes,
                       COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS likes,
                       (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
                FROM posts p
                JOIN users u ON u.id = p.user_id
                JOIN categories c ON c.id = p.category_id
                WHERE " . implode(' AND ', $where) . " " . $this->buildOrderByClause($sort) . " LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue(':' . $k, $v);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error in advancedSearch: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm tổng số kết quả tìm kiếm nâng cao phục vụ phân trang
     */
    public function countAdvancedSearch(array $filters = []): int {
        $params = [];
        $where = ["p.status = 'published'"];

        $keyword = trim((string)($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $words = preg_split('/\s+/', $keyword);
            $booleanTokens = [];
            foreach ($words as $w) {
                $w = trim($w);
                if ($w !== '') {
                    $booleanTokens[] = '+' . $w . '*';
                }
            }
            $booleanQuery = implode(' ', $booleanTokens);
            $where[] = "(MATCH(p.title, p.content, p.location) AGAINST(:kw_boolean IN BOOLEAN MODE) OR p.title LIKE :kw_like1 OR p.location LIKE :kw_like2 OR p.content LIKE :kw_like3)";
            $params['kw_boolean'] = $booleanQuery;
            $params['kw_like1'] = '%' . $keyword . '%';
            $params['kw_like2'] = '%' . $keyword . '%';
            $params['kw_like3'] = '%' . $keyword . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :cat_id";
            $params['cat_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['activity_type'])) {
            $where[] = "p.activity_type = :act_type";
            $params['act_type'] = $filters['activity_type'];
        }

        if (!empty($filters['difficulty'])) {
            $where[] = "p.difficulty = :difficulty";
            $params['difficulty'] = $filters['difficulty'];
        }

        if (!empty($filters['region'])) {
            $where[] = "p.region = :region";
            $params['region'] = $filters['region'];
        }

        if (!empty($filters['tag_id'])) {
            $where[] = "EXISTS (SELECT 1 FROM post_tags pt WHERE pt.post_id = p.id AND pt.tag_id = :tag_id)";
            $params['tag_id'] = (int)$filters['tag_id'];
        } elseif (!empty($filters['tag_name'])) {
            $where[] = "EXISTS (SELECT 1 FROM post_tags pt JOIN tags t ON t.id = pt.tag_id WHERE pt.post_id = p.id AND t.name = :tag_name)";
            $params['tag_name'] = $filters['tag_name'];
        }

        $sql = "SELECT COUNT(*) AS total FROM posts p WHERE " . implode(' AND ', $where);

        try {
            $res = Database::query($sql, $params);
            return (int)($res[0]['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('Error counting advancedSearch: ' . $e->getMessage());
            return 0;
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
     * Lấy thống kê điểm Vote (score, upvotes, downvotes) của 1 bài viết
     */
    public function getVoteStats(int $postId): array {
        try {
            $rows = Database::query(
                "SELECT
                    COALESCE(SUM(vote), 0) AS score,
                    COUNT(CASE WHEN vote = 1 THEN 1 END) AS upvotes,
                    COUNT(CASE WHEN vote = -1 THEN 1 END) AS downvotes
                 FROM post_likes
                 WHERE post_id = :p",
                ['p' => $postId]
            );
            return [
                'score'     => (int)($rows[0]['score'] ?? 0),
                'upvotes'   => (int)($rows[0]['upvotes'] ?? 0),
                'downvotes' => (int)($rows[0]['downvotes'] ?? 0),
            ];
        } catch (PDOException $e) {
            return ['score' => 0, 'upvotes' => 0, 'downvotes' => 0];
        }
    }

    /**
     * Lấy trạng thái vote hiện tại của người dùng trên bài viết (1: Upvote, -1: Downvote, 0: Chưa vote)
     */
    public function getUserVote(int $postId, int $userId): int {
        if ($postId <= 0 || $userId <= 0) {
            return 0;
        }
        try {
            $rows = Database::query(
                "SELECT vote FROM post_likes WHERE post_id = :p AND user_id = :u LIMIT 1",
                ['p' => $postId, 'u' => $userId]
            );
            return !empty($rows) ? (int)$rows[0]['vote'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Xử lý Upvote (+1) hoặc Downvote (-1) bài viết:
     * - Nếu chưa vote -> thêm vote mới (+1 hoặc -1)
     * - Nếu bấm lại đúng nút đã vote -> hủy vote (trở về 0)
     * - Nếu đang Upvote mà bấm Downvote (hoặc ngược lại) -> đảo chiều vote
     */
    public function vote(int $postId, int $userId, int $voteType = 1): array {
        $voteVal = ($voteType < 0) ? -1 : 1;
        $userVote = 0;

        try {
            $existing = Database::query(
                "SELECT vote FROM post_likes WHERE post_id = :p AND user_id = :u LIMIT 1",
                ['p' => $postId, 'u' => $userId]
            );

            if (!empty($existing)) {
                $currentVote = (int)$existing[0]['vote'];
                if ($currentVote === $voteVal) {
                    Database::execute(
                        "DELETE FROM post_likes WHERE post_id = :p AND user_id = :u",
                        ['p' => $postId, 'u' => $userId]
                    );
                    $userVote = 0;
                } else {
                    Database::execute(
                        "UPDATE post_likes SET vote = :v WHERE post_id = :p AND user_id = :u",
                        ['v' => $voteVal, 'p' => $postId, 'u' => $userId]
                    );
                    $userVote = $voteVal;
                }
            } else {
                Database::execute(
                    "INSERT INTO post_likes (post_id, user_id, vote) VALUES (:p, :u, :v)",
                    ['p' => $postId, 'u' => $userId, 'v' => $voteVal]
                );
                $userVote = $voteVal;
            }
        } catch (PDOException $e) {
            error_log('Error voting on post: ' . $e->getMessage());
        }

        $stats = $this->getVoteStats($postId);
        return [
            'score'     => $stats['score'],
            'upvotes'   => $stats['upvotes'],
            'downvotes' => $stats['downvotes'],
            'user_vote' => $userVote,
            'likes'     => $stats['score'],
            'liked'     => ($userVote === 1),
        ];
    }

    /**
     * Kiểm tra người dùng đã Upvote bài viết chưa (tương thích ngược)
     */
    public function isLiked(int $postId, int $userId): bool {
        return $this->getUserVote($postId, $userId) === 1;
    }

    /**
     * Thích hoặc bỏ thích bài viết (tương thích ngược với Upvote +1)
     */
    public function toggleLike(int $postId, int $userId): int {
        $result = $this->vote($postId, $userId, 1);
        return (int)$result['score'];
    }

    /**
     * Lấy toàn bộ danh sách ảnh của bài viết từ bảng post_images
     */
    public function getImages(int $postId): array {
        try {
            $sql = "SELECT id, post_id, image_url FROM post_images WHERE post_id = :post_id ORDER BY id ASC";
            return Database::query($sql, ['post_id' => $postId]);
        } catch (PDOException $e) {
            error_log('Error getting post images: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Thêm nhiều ảnh vào bảng post_images cho bài viết
     */
    public function addImages(int $postId, array $imageUrls): bool {
        if (empty($imageUrls)) {
            return true;
        }
        try {
            $sql = "INSERT INTO post_images (post_id, image_url) VALUES (:post_id, :image_url)";
            $stmt = $this->db->prepare($sql);
            foreach ($imageUrls as $url) {
                if (!empty($url)) {
                    $stmt->execute([':post_id' => $postId, ':image_url' => $url]);
                }
            }
            return true;
        } catch (PDOException $e) {
            error_log('Error adding post images: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa danh sách ảnh cụ thể của một bài viết theo ID ảnh
     */
    public function deleteImages(int $postId, array $imageIds): bool {
        if (empty($imageIds)) {
            return true;
        }
        $cleanIds = [];
        foreach ($imageIds as $id) {
            $val = (int)$id;
            if ($val > 0) {
                $cleanIds[] = $val;
            }
        }
        if (empty($cleanIds)) {
            return true;
        }
        try {
            $inPlaceholders = implode(',', array_fill(0, count($cleanIds), '?'));
            $sql = "DELETE FROM post_images WHERE post_id = ? AND id IN ($inPlaceholders)";
            $params = array_merge([$postId], $cleanIds);
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Error deleting post images: ' . $e->getMessage());
            return false;
        }
    }
}
