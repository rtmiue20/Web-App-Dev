<?php
require_once __DIR__ . '/../core/BaseRepository.php';

/**
 * CategoryRepository (DAO) - Tầng truy cập dữ liệu Chuyên mục diễn đàn.
 * Thực thi các Stored Procedures: sp_category_list, sp_category_get, sp_category_get_by_name,
 * sp_category_create, sp_category_update, sp_category_delete, sp_category_count_posts, sp_category_count.
 */
class CategoryRepository extends BaseRepository
{
    protected string $table = 'categories';

    /**
     * Lấy danh sách chuyên mục kèm số lượng bài viết (sp_category_list / View vw_category_summary).
     */
    public function findAll(): array
    {
        try {
            return $this->callProcedure('sp_category_list');
        } catch (PDOException $e) {
            try {
                return $this->query('SELECT * FROM vw_category_summary ORDER BY id ASC');
            } catch (PDOException $viewEx) {
                try {
                    return $this->query(
                        'SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
                         FROM categories c
                         LEFT JOIN posts p ON p.category_id = c.id
                         GROUP BY c.id, c.name, c.description
                         ORDER BY c.id ASC'
                    );
                } catch (PDOException $ex) {
                    return [];
                }
            }
        }
    }

    /**
     * Thống kê số bài viết theo từng chuyên mục qua Stored Procedure `sp_stats_posts_by_category` (hoặc `lk_posts_incategory`).
     */
    public function getStatsByCategory(): array
    {
        try {
            return $this->callProcedure('sp_stats_posts_by_category');
        } catch (PDOException $e) {
            try {
                return $this->callProcedure('lk_posts_incategory');
            } catch (PDOException $ex) {
                return $this->findAll();
            }
        }
    }

    /**
     * Tìm chuyên mục theo ID (sp_category_get).
     */
    public function findById(int $id): ?array
    {
        try {
            $rows = $this->callProcedure('sp_category_get', [$id]);
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $e) {
            try {
                return $this->queryOne(
                    'SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
                     FROM categories c
                     LEFT JOIN posts p ON p.category_id = c.id
                     WHERE c.id = :id
                     GROUP BY c.id, c.name, c.description
                     LIMIT 1',
                    ['id' => $id]
                );
            } catch (PDOException $ex) {
                return null;
            }
        }
    }

    /**
     * Tìm chuyên mục theo tên, hỗ trợ loại trừ 1 ID khi cập nhật (sp_category_get_by_name).
     */
    public function findByName(string $name, ?int $excludeId = null): ?array
    {
        try {
            $rows = $this->callProcedure('sp_category_get_by_name', [$name, $excludeId]);
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $e) {
            try {
                if ($excludeId !== null) {
                    return $this->queryOne(
                        'SELECT * FROM categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) AND id != :excludeId LIMIT 1',
                        ['name' => $name, 'excludeId' => $excludeId]
                    );
                }
                return $this->queryOne(
                    'SELECT * FROM categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) LIMIT 1',
                    ['name' => $name]
                );
            } catch (PDOException $ex) {
                return null;
            }
        }
    }

    /**
     * Tạo mới chuyên mục (sp_category_create), trả về ID chuyên mục mới.
     */
    public function createCategory(string $name, ?string $description = null): int
    {
        try {
            $rows = $this->callProcedure('sp_category_create', [$name, $description]);
            return (int)($rows[0]['category_id'] ?? 0);
        } catch (PDOException $e) {
            try {
                $this->execute(
                    'INSERT INTO categories (name, description) VALUES (:name, :description)',
                    ['name' => $name, 'description' => $description]
                );
                return $this->lastInsertId();
            } catch (PDOException $ex) {
                return 0;
            }
        }
    }

    /**
     * Cập nhật chuyên mục (sp_category_update).
     */
    public function updateCategory(int $id, string $name, ?string $description = null): bool
    {
        try {
            $this->callProcedure('sp_category_update', [$id, $name, $description]);
            return true;
        } catch (PDOException $e) {
            try {
                return $this->execute(
                    'UPDATE categories SET name = :name, description = :description WHERE id = :id',
                    ['name' => $name, 'description' => $description, 'id' => $id]
                ) >= 0;
            } catch (PDOException $ex) {
                return false;
            }
        }
    }

    /**
     * Xóa chuyên mục (sp_category_delete).
     */
    public function delete(int $id): bool
    {
        try {
            $this->callProcedure('sp_category_delete', [$id]);
            return true;
        } catch (PDOException $e) {
            try {
                return parent::delete($id);
            } catch (PDOException $ex) {
                return false;
            }
        }
    }

    /**
     * Đếm số bài viết thuộc chuyên mục (sp_category_count_posts).
     */
    public function countPosts(int $id): int
    {
        try {
            $rows = $this->callProcedure('sp_category_count_posts', [$id]);
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            try {
                $row = $this->queryOne('SELECT COUNT(*) AS total FROM posts WHERE category_id = :id', ['id' => $id]);
                return (int)($row['total'] ?? 0);
            } catch (PDOException $ex) {
                return 0;
            }
        }
    }

    /**
     * Đếm tổng số chuyên mục (sp_category_count).
     */
    public function count(): int
    {
        try {
            $rows = $this->callProcedure('sp_category_count');
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            return parent::count();
        }
    }
}

