<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Category - Quản lý dữ liệu chuyên mục (Leader - Tuần 2 & 3)
 * Gọi các Routines: sp_category_list, sp_category_get, sp_category_get_by_name,
 * sp_category_create, sp_category_update, sp_category_delete, sp_category_count_posts, sp_category_count
 */
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
     * Thêm mới chuyên mục vào cơ sở dữ liệu (gọi routine sp_category_create)
     */
    public function create(): bool {
        try {
            $rows = Database::call('sp_category_create', [$this->name, $this->description]);
            $newId = (int)($rows[0]['category_id'] ?? 0);
            if ($newId > 0) {
                $this->id = $newId;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log('Notice: sp_category_create fallback query: ' . $e->getMessage());
            $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
            $stmt = $this->db->prepare($sql);
            try {
                $success = $stmt->execute([
                    ':name' => $this->name, 
                    ':description' => $this->description
                ]);
                if ($success) {
                    $this->id = (int)$this->db->lastInsertId();
                }
                return $success;
            } catch (PDOException $ex) {
                error_log('Category create error: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Lấy danh sách tất cả chuyên mục kèm theo số lượng bài viết của từng mục (gọi routine sp_category_list)
     */
    public function getAll(): array {
        try {
            return Database::call('sp_category_list');
        } catch (PDOException $e) {
            error_log('Notice: sp_category_list fallback query: ' . $e->getMessage());

            try {
                $sql = "SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count 
                        FROM categories c 
                        LEFT JOIN posts p ON p.category_id = c.id 
                        GROUP BY c.id, c.name, c.description 
                        ORDER BY c.id ASC";
                return Database::query($sql);
            } catch (PDOException $ex) {
                error_log('Error getting categories: ' . $ex->getMessage());
                return [];
            }
        }
    }

    /**
     * Tìm chuyên mục theo ID (gọi routine sp_category_get)
     */
    public function findById(int $id): ?array {
        try {
            $rows = Database::call('sp_category_get', [$id]);
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $e) {
            error_log('Notice: sp_category_get fallback query: ' . $e->getMessage());

            try {
                $sql = "SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
                        FROM categories c
                        LEFT JOIN posts p ON p.category_id = c.id
                        WHERE c.id = :id
                        GROUP BY c.id, c.name, c.description
                        LIMIT 1";
                $rows = Database::query($sql, ['id' => $id]);
                return !empty($rows) ? $rows[0] : null;
            } catch (PDOException $ex) {
                error_log('Category findById error: ' . $ex->getMessage());
                return null;
            }
        }
    }

    /**
     * Tìm chuyên mục theo tên (gọi routine sp_category_get_by_name)
     */
    public function findByName(string $name, ?int $excludeId = null): ?array {
        try {
            $rows = Database::call('sp_category_get_by_name', [$name, $excludeId]);
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $e) {
            error_log('Notice: sp_category_get_by_name fallback query: ' . $e->getMessage());

            try {
                if ($excludeId !== null) {
                    $sql = "SELECT * FROM categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) AND id != :excludeId LIMIT 1";
                    $rows = Database::query($sql, ['name' => $name, 'excludeId' => $excludeId]);
                } else {
                    $sql = "SELECT * FROM categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) LIMIT 1";
                    $rows = Database::query($sql, ['name' => $name]);
                }
                return !empty($rows) ? $rows[0] : null;
            } catch (PDOException $ex) {
                error_log('Category findByName error: ' . $ex->getMessage());
                return null;
            }
        }
    }

    /**
     * Cập nhật thông tin chuyên mục (gọi routine sp_category_update)
     */
    public function update(int $id, string $name, ?string $description = null): bool {
        try {
            Database::call('sp_category_update', [$id, $name, $description]);
            return true;
        } catch (PDOException $e) {
            error_log('Notice: sp_category_update fallback query: ' . $e->getMessage());

            try {
                $sql = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
                return Database::execute($sql, [
                    'name' => $name,
                    'description' => $description,
                    'id' => $id
                ]) >= 0;
            } catch (PDOException $ex) {
                error_log('Category update error: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Đếm số lượng bài viết đang thuộc về chuyên mục này (gọi routine sp_category_count_posts)
     */
    public function countPosts(int $id): int {
        try {
            $rows = Database::call('sp_category_count_posts', [$id]);
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('Notice: sp_category_count_posts fallback query: ' . $e->getMessage());

            try {
                $sql = "SELECT COUNT(*) AS total FROM posts WHERE category_id = :id";
                $res = Database::query($sql, ['id' => $id]);
                return (int)($res[0]['total'] ?? 0);
            } catch (PDOException $ex) {
                error_log('Category countPosts error: ' . $ex->getMessage());
                return 0;
            }
        }
    }

    /**
     * Xóa chuyên mục khỏi cơ sở dữ liệu (gọi routine sp_category_delete)
     */
    public function delete(int $id): bool {
        try {
            Database::call('sp_category_delete', [$id]);
            return true;
        } catch (PDOException $e) {
            error_log('Notice: sp_category_delete fallback query: ' . $e->getMessage());

            try {
                $sql = "DELETE FROM categories WHERE id = :id";
                return Database::execute($sql, ['id' => $id]) > 0;
            } catch (PDOException $ex) {
                error_log('Category delete error: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Đếm tổng số chuyên mục trong hệ thống (gọi routine sp_category_count)
     */
    public function count(): int {
        try {
            $rows = Database::call('sp_category_count');
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            try {
                $res = Database::query("SELECT COUNT(*) AS total FROM categories");
                return (int)($res[0]['total'] ?? 0);
            } catch (PDOException $ex) {
                return 0;
            }
        }
    }
}
