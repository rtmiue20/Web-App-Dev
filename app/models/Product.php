<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Product - Quản lý dụng cụ / thiết bị dã ngoại (Vương - Tuần 2 & Tuần 3: FR09, FR12)
 * Gọi các Stored Procedures: sp_product_list_by_category, sp_product_get,
 * sp_product_create, sp_product_update, sp_product_delete
 */
class Product extends Model
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?string $name = null;
    private float $price = 0.0;
    private ?string $imageUrl = null;
    private ?string $description = null;
    private ?string $link = null;
    private ?string $categoryName = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getCategoryId(): ?int { return $this->categoryId; }
    public function setCategoryId(?int $categoryId): void { $this->categoryId = $categoryId; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): void { $this->name = $name; }

    public function getPrice(): float { return $this->price; }
    public function setPrice(float $price): void { $this->price = $price; }

    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }

    public function getLink(): ?string { return $this->link; }
    public function setLink(?string $link): void { $this->link = $link; }

    public function getCategoryName(): ?string { return $this->categoryName; }
    public function setCategoryName(?string $categoryName): void { $this->categoryName = $categoryName; }

    /**
     * Lấy danh sách sản phẩm, lọc theo chuyên mục (null = tất cả)
     */
    public function getAll(?int $categoryId = null): array
    {
        try {
            return Database::call('sp_product_list_by_category', [$categoryId]);
        } catch (PDOException $e) {
            error_log('Notice: sp_product_list_by_category fallback query: ' . $e->getMessage());

            try {
                $sql = 'SELECT p.*, c.name AS category_name
                        FROM products p
                        LEFT JOIN categories c ON c.id = p.category_id
                        WHERE 1=1';
                $params = [];

                if ($categoryId !== null) {
                    $sql .= ' AND p.category_id = :category_id';
                    $params['category_id'] = $categoryId;
                }

                $sql .= ' ORDER BY p.id DESC';
                return Database::query($sql, $params);
            } catch (PDOException $ex) {
                error_log('Error getting products: ' . $ex->getMessage());
                return [];
            }
        }
    }

    /**
     * Lấy 1 sản phẩm theo id, trả về null nếu không có
     */
    public function findById(int $id): ?array
    {
        try {
            $rows = Database::call('sp_product_get', [$id]);
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $e) {
            error_log('Notice: sp_product_get fallback query: ' . $e->getMessage());

            try {
                $sql = 'SELECT p.*, c.name AS category_name
                        FROM products p
                        LEFT JOIN categories c ON c.id = p.category_id
                        WHERE p.id = :id
                        LIMIT 1';
                $rows = Database::query($sql, ['id' => $id]);
                return !empty($rows) ? $rows[0] : null;
            } catch (PDOException $ex) {
                error_log('Error finding product: ' . $ex->getMessage());
                return null;
            }
        }
    }

    /**
     * Thêm sản phẩm mới, trả về id (0 nếu thất bại)
     */
    public function create(
        ?int $categoryId,
        string $name,
        float $price,
        ?string $imageUrl,
        ?string $description,
        ?string $link = null
    ): int {
        try {
            $rows = Database::call('sp_product_create', [
                $categoryId,
                $name,
                $price,
                $imageUrl,
                $description,
                $link
            ]);
            $productId = (int)($rows[0]['product_id'] ?? 0);
            if ($productId > 0) {
                $this->id = $productId;
            }
            return $productId;
        } catch (PDOException $e) {
            error_log('Notice: sp_product_create fallback query: ' . $e->getMessage());

            // Thử INSERT có cột link trước, nếu bảng cũ chưa có cột link thì fallback về 5 cột
            try {
                Database::execute(
                    'INSERT INTO products (category_id, name, price, image_url, description, link)
                     VALUES (:category_id, :name, :price, :image_url, :description, :link)',
                    [
                        'category_id' => $categoryId,
                        'name' => $name,
                        'price' => $price,
                        'image_url' => $imageUrl,
                        'description' => $description,
                        'link' => $link
                    ]
                );
                $productId = (int)$this->db->lastInsertId();
                $this->id = $productId;
                return $productId;
            } catch (PDOException $ex) {
                try {
                    Database::execute(
                        'INSERT INTO products (category_id, name, price, image_url, description)
                         VALUES (:category_id, :name, :price, :image_url, :description)',
                        [
                            'category_id' => $categoryId,
                            'name' => $name,
                            'price' => $price,
                            'image_url' => $imageUrl,
                            'description' => $description
                        ]
                    );
                    $productId = (int)$this->db->lastInsertId();
                    $this->id = $productId;
                    return $productId;
                } catch (PDOException $ex2) {
                    error_log('Error creating product: ' . $ex2->getMessage());
                    return 0;
                }
            }
        }
    }

    /**
     * Cập nhật sản phẩm ($imageUrl = null thì giữ ảnh cũ)
     */
    public function update(
        int $id,
        ?int $categoryId,
        string $name,
        float $price,
        ?string $imageUrl,
        ?string $description,
        ?string $link = null
    ): bool {
        try {
            Database::call('sp_product_update', [
                $id,
                $categoryId,
                $name,
                $price,
                $imageUrl,
                $description,
                $link
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Notice: sp_product_update fallback query: ' . $e->getMessage());

            try {
                Database::execute(
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
                        'name' => $name,
                        'price' => $price,
                        'image_url' => $imageUrl,
                        'description' => $description,
                        'link' => $link,
                        'id' => $id
                    ]
                );
                return true;
            } catch (PDOException $ex) {
                try {
                    Database::execute(
                        'UPDATE products
                         SET category_id = :category_id,
                             name = :name,
                             price = :price,
                             image_url = COALESCE(:image_url, image_url),
                             description = :description
                         WHERE id = :id',
                        [
                            'category_id' => $categoryId,
                            'name' => $name,
                            'price' => $price,
                            'image_url' => $imageUrl,
                            'description' => $description,
                            'id' => $id
                        ]
                    );
                    return true;
                } catch (PDOException $ex2) {
                    error_log('Error updating product: ' . $ex2->getMessage());
                    return false;
                }
            }
        }
    }

    /**
     * Xóa sản phẩm
     */
    public function delete(int $id): bool
    {
        try {
            Database::call('sp_product_delete', [$id]);
            return true;
        } catch (PDOException $e) {
            error_log('Notice: sp_product_delete fallback query: ' . $e->getMessage());

            try {
                Database::execute('DELETE FROM products WHERE id = :id', ['id' => $id]);
                return true;
            } catch (PDOException $ex) {
                error_log('Error deleting product: ' . $ex->getMessage());
                return false;
            }
        }
    }
}
