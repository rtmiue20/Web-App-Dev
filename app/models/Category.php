<?php
require_once __DIR__ . '/../core/Model.php';

class Category extends Model {
    private ?int $id = null;
    private ?string $name = null;
    private ?string $description = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setName(?string $name): void { $this->name = $name; }
    public function setDescription(?string $description): void { $this->description = $description; }

    /**
     * Thêm mới chuyên mục vào cơ sở dữ liệu
     */
    public function create(): bool {
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([
                ':name' => $this->name, 
                ':description' => $this->description
            ]);
        } catch (PDOException $e) {
            error_log('Category create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách tất cả chuyên mục kèm theo số lượng bài viết của từng mục
     */
    public function getAll(): array {
        try {
            // Thử gọi stored procedure lk_posts_incategory nếu có
            try {
                $stmt = $this->db->query("CALL lk_posts_incategory()");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                // Bổ sung description nếu procedure chỉ trả về (id, category, total_posts)
                if (!empty($rows) && isset($rows[0]['total_posts']) && !isset($rows[0]['description'])) {
                    $descStmt = $this->db->query("SELECT id, description FROM categories");
                    $descMap = [];
                    foreach ($descStmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
                        $descMap[$d['id']] = $d['description'];
                    }
                    foreach ($rows as &$r) {
                        $r['name'] = $r['category'] ?? ($r['name'] ?? '');
                        $r['description'] = $descMap[$r['id']] ?? null;
                        $r['post_count'] = $r['total_posts'] ?? 0;
                    }
                    unset($r);
                    return $rows;
                }
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (PDOException $e) {
                // Fallback nếu procedure chưa được khởi tạo
            }

            $sql = "SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count 
                    FROM categories c 
                    LEFT JOIN posts p ON p.category_id = c.id 
                    GROUP BY c.id, c.name, c.description 
                    ORDER BY c.id ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting categories: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Tìm chuyên mục theo ID
     */
    public function findById(int $id): ?array {
        try {
            $sql = "SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
                    FROM categories c
                    LEFT JOIN posts p ON p.category_id = c.id
                    WHERE c.id = :id
                    GROUP BY c.id, c.name, c.description
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            return $category ?: null;
        } catch (PDOException $e) {
            error_log('Category findById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tìm chuyên mục theo tên (hỗ trợ kiểm tra trùng khi thêm hoặc cập nhật)
     */
    public function findByName(string $name, ?int $excludeId = null): ?array {
        try {
            if ($excludeId !== null) {
                $sql = "SELECT * FROM categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) AND id != :excludeId LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':name' => $name, ':excludeId' => $excludeId]);
            } else {
                $sql = "SELECT * FROM categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':name' => $name]);
            }
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (PDOException $e) {
            error_log('Category findByName error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cập nhật thông tin chuyên mục
     */
    public function update(int $id, string $name, ?string $description = null): bool {
        try {
            $sql = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log('Category update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Đếm số lượng bài viết đang thuộc về chuyên mục này
     */
    public function countPosts(int $id): int {
        try {
            $sql = "SELECT COUNT(*) AS total FROM posts WHERE category_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($res['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('Category countPosts error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Xóa chuyên mục khỏi cơ sở dữ liệu
     */
    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Category delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Đếm tổng số chuyên mục trong hệ thống
     */
    public function count(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM categories");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($res['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }
}
