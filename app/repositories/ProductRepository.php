<?php
require_once __DIR__ . '/../core/BaseRepository.php';

/**
 * ProductRepository (DAO) - Tầng truy cập dữ liệu Dụng cụ & Thiết bị dã ngoại.
 * Thực thi các Stored Procedures: sp_product_list_by_category, sp_product_get,
 * sp_product_create, sp_product_update, sp_product_delete,
 * sp_post_products_get, sp_post_product_add, sp_post_products_clear.
 */
class ProductRepository extends BaseRepository
{
    protected string $table = 'products';

    /**
     * Lấy danh sách dụng cụ theo danh mục (hoặc toàn bộ nếu $categoryId = null).
     */
    public function findByCategory(?int $categoryId = null): array
    {
        try {
            $rows = $this->callProcedure('sp_product_list_by_category', [$categoryId]);
            if (empty($rows) || (array_key_exists('user_id', $rows[0]) && array_key_exists('author_username', $rows[0]))) {
                return $rows;
            }
        } catch (PDOException $e) {
            // Chuyển sang câu lệnh SQL đầy đủ bên dưới
        }

        try {
            $sql = 'SELECT p.*, c.name AS category_name,
                           u.username AS author_username, u.avatar_path AS author_avatar, r.name AS author_role
                    FROM products p
                    LEFT JOIN categories c ON c.id = p.category_id
                    LEFT JOIN users u ON u.id = p.user_id
                    LEFT JOIN roles r ON r.id = u.role_id
                    WHERE p.is_shared = 1
                      AND (
                          r.name IN (\'admin\', \'moderator\')
                          OR p.user_id IS NULL
                          OR EXISTS (
                              SELECT 1
                              FROM post_products pp
                              INNER JOIN posts po ON po.id = pp.post_id
                              WHERE pp.product_id = p.id
                                AND po.status = \'published\'
                          )
                      )';
            $params = [];

            if ($categoryId !== null && $categoryId > 0) {
                $sql .= ' AND p.category_id = :category_id';
                $params['category_id'] = $categoryId;
            }

            $sql .= ' ORDER BY p.id DESC';
            return $this->query($sql, $params);
        } catch (PDOException $ex) {
            try {
                $sql2 = 'SELECT p.*, c.name AS category_name
                         FROM products p
                         LEFT JOIN categories c ON c.id = p.category_id
                         WHERE p.is_shared = 1
                           AND (
                               p.user_id IS NULL
                               OR p.user_id IN (SELECT u2.id FROM users u2 INNER JOIN roles r2 ON r2.id = u2.role_id WHERE r2.name IN (\'admin\', \'moderator\'))
                               OR EXISTS (
                                   SELECT 1
                                   FROM post_products pp
                                   INNER JOIN posts po ON po.id = pp.post_id
                                   WHERE pp.product_id = p.id
                                     AND po.status = \'published\'
                               )
                           )';
                $params2 = [];
                if ($categoryId !== null && $categoryId > 0) {
                    $sql2 .= ' AND p.category_id = :category_id';
                    $params2['category_id'] = $categoryId;
                }
                $sql2 .= ' ORDER BY p.id DESC';
                return $this->query($sql2, $params2);
            } catch (PDOException $ex2) {
                return [];
            }
        }
    }

    /**
     * Lấy toàn bộ dụng cụ dành cho Quản trị viên (Admin Panel) để quản lý
     */
    public function findAllForAdmin(?int $categoryId = null): array
    {
        try {
            $sql = 'SELECT p.*, c.name AS category_name,
                           u.username AS author_username, u.avatar_path AS author_avatar, r.name AS author_role
                    FROM products p
                    LEFT JOIN categories c ON c.id = p.category_id
                    LEFT JOIN users u ON u.id = p.user_id
                    LEFT JOIN roles r ON r.id = u.role_id';
            $params = [];
            if ($categoryId !== null && $categoryId > 0) {
                $sql .= ' WHERE p.category_id = :category_id';
                $params['category_id'] = $categoryId;
            }
            $sql .= ' ORDER BY p.id DESC';
            return $this->query($sql, $params);
        } catch (PDOException $ex) {
            return $this->findByCategory($categoryId);
        }
    }

    public function findAll(): array
    {
        return $this->findByCategory(null);
    }

    /**
     * Tìm chi tiết dụng cụ theo ID (sp_product_get).
     */
    public function findById(int $id): ?array
    {
        try {
            $rows = $this->callProcedure('sp_product_get', [$id]);
            if (!empty($rows) && array_key_exists('user_id', $rows[0]) && array_key_exists('is_shared', $rows[0])) {
                return $rows[0];
            }
        } catch (PDOException $e) {
            // Chuyển sang câu lệnh SQL đầy đủ bên dưới
        }

        try {
            return $this->queryOne(
                'SELECT p.*, c.name AS category_name,
                        u.username AS author_username, u.avatar_path AS author_avatar, r.name AS author_role
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 LEFT JOIN users u ON u.id = p.user_id
                 LEFT JOIN roles r ON r.id = u.role_id
                 WHERE p.id = :id
                 LIMIT 1',
                ['id' => $id]
            );
        } catch (PDOException $ex) {
            try {
                return $this->queryOne(
                    'SELECT p.*, c.name AS category_name
                     FROM products p
                     LEFT JOIN categories c ON c.id = p.category_id
                     WHERE p.id = :id
                     LIMIT 1',
                    ['id' => $id]
                );
            } catch (PDOException $ex2) {
                return null;
            }
        }
    }

    /**
     * Lấy danh sách dụng cụ đính kèm trong một bài viết (sp_post_products_get).
     */
    public function findByPostId(int $postId): array
    {
        try {
            return $this->callProcedure('sp_post_products_get', [$postId]);
        } catch (PDOException $e) {
            try {
                return $this->query(
                    'SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link,
                            c.name AS category_name
                     FROM post_products pp
                     JOIN products p ON p.id = pp.product_id
                     LEFT JOIN categories c ON c.id = p.category_id
                     WHERE pp.post_id = :post_id
                     ORDER BY p.name ASC',
                    ['post_id' => $postId]
                );
            } catch (PDOException $ex) {
                return [];
            }
        }
    }

    /**
     * Đồng bộ danh sách dụng cụ sử dụng trong bài viết.
     */
    public function syncPostProducts(int $postId, array $productIds): void
    {
        if ($postId <= 0) {
            return;
        }

        try {
            $this->callProcedure('sp_post_products_clear', [$postId]);
        } catch (PDOException $e) {
            $this->execute('DELETE FROM post_products WHERE post_id = :post_id', ['post_id' => $postId]);
        }

        foreach ($productIds as $pId) {
            $cleanedId = (int)$pId;
            if ($cleanedId > 0) {
                try {
                    $this->callProcedure('sp_post_product_add', [$postId, $cleanedId]);
                } catch (PDOException $e) {
                    $this->execute(
                        'INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:post_id, :product_id)',
                        ['post_id' => $postId, 'product_id' => $cleanedId]
                    );
                }
            }
        }
    }

    /**
     * Thêm mới dụng cụ (sp_product_create).
     */
    public function createProduct(
        ?int $categoryId,
        string $name,
        float $price,
        ?string $imageUrl,
        ?string $description,
        ?string $link = null,
        int $isShared = 1,
        ?int $userId = null
    ): int {
        try {
            $rows = $this->callProcedure('sp_product_create', [
                $categoryId,
                $userId,
                $name,
                $price,
                $imageUrl,
                $description,
                $link,
                $isShared,
            ]);
            return (int)($rows[0]['product_id'] ?? 0);
        } catch (PDOException $e) {
            try {
                $this->execute(
                    'INSERT INTO products (category_id, user_id, name, price, image_url, description, link, is_shared)
                     VALUES (:category_id, :user_id, :name, :price, :image_url, :description, :link, :is_shared)',
                    [
                        'category_id' => $categoryId,
                        'user_id'     => $userId,
                        'name'        => $name,
                        'price'       => $price,
                        'image_url'   => $imageUrl,
                        'description' => $description,
                        'link'        => $link,
                        'is_shared'   => $isShared,
                    ]
                );
                return $this->lastInsertId();
            } catch (PDOException $ex) {
                try {
                    $this->execute(
                        'INSERT INTO products (category_id, name, price, image_url, description, link)
                         VALUES (:category_id, :name, :price, :image_url, :description, :link)',
                        [
                            'category_id' => $categoryId,
                            'name'        => $name,
                            'price'       => $price,
                            'image_url'   => $imageUrl,
                            'description' => $description,
                            'link'        => $link,
                        ]
                    );
                    return $this->lastInsertId();
                } catch (PDOException $ex2) {
                    return 0;
                }
            }
        }
    }

    /**
     * Cập nhật dụng cụ (sp_product_update).
     */
    public function updateProduct(
        int $id,
        ?int $categoryId,
        string $name,
        float $price,
        ?string $imageUrl,
        ?string $description,
        ?string $link = null,
        int $isShared = 1
    ): bool {
        try {
            $this->callProcedure('sp_product_update', [
                $id,
                $categoryId,
                $name,
                $price,
                $imageUrl,
                $description,
                $link,
                $isShared,
            ]);
            return true;
        } catch (PDOException $e) {
            try {
                $this->execute(
                    'UPDATE products
                     SET category_id = :category_id,
                         name = :name,
                         price = :price,
                         image_url = COALESCE(:image_url, image_url),
                         description = :description,
                         link = :link,
                         is_shared = :is_shared
                     WHERE id = :id',
                    [
                        'category_id' => $categoryId,
                        'name'        => $name,
                        'price'       => $price,
                        'image_url'   => $imageUrl,
                        'description' => $description,
                        'link'        => $link,
                        'is_shared'   => $isShared,
                        'id'          => $id,
                    ]
                );
                return true;
            } catch (PDOException $ex) {
                try {
                    $this->execute(
                        'UPDATE products
                         SET category_id = :category_id,
                             name = :name,
                             price = :price,
                             image_url = COALESCE(:image_url, image_url),
                             description = :description,
                             link = :link
                         WHERE id = :id',
                        [
                            'category_id' => $categoryId,
                            'name'        => $name,
                            'price'       => $price,
                            'image_url'   => $imageUrl,
                            'description' => $description,
                            'link'        => $link,
                            'id'          => $id,
                        ]
                    );
                    return true;
                } catch (PDOException $ex2) {
                    return false;
                }
            }
        }
    }

    /**
     * Xóa dụng cụ (sp_product_delete).
     */
    public function delete(int $id): bool
    {
        $deleted = false;
        try {
            $this->callProcedure('sp_product_delete', [$id]);
            $deleted = true;
        } catch (PDOException $e) {
            try {
                $deleted = parent::delete($id);
            } catch (PDOException $ex) {
                return false;
            }
        }

        if ($deleted) {
            try {
                $this->execute('DELETE FROM post_products WHERE product_id = :id', ['id' => $id]);
            } catch (Throwable $e) {
            }
        }

        return $deleted;
    }
}

