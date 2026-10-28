<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Product - Quản lý dữ liệu Dụng cụ & Trang thiết bị dã ngoại
 * Kế thừa Model theo kiến trúc MVC & OOP
 * Gọi các Stored Procedures (sp_product_list_by_category, sp_product_get, sp_post_products_get, v.v.)
 */
class Product extends Model
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?string $name = null;
    private ?float $price = null;
    private ?string $imageUrl = null;
    private ?string $description = null;
    private ?string $link = null;
    private ?string $createdAt = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getCategoryId(): ?int { return $this->categoryId; }
    public function setCategoryId(?int $categoryId): void { $this->categoryId = $categoryId; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): void { $this->name = $name; }

    public function getPrice(): ?float { return $this->price; }
    public function setPrice(?float $price): void { $this->price = $price; }

    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $imageUrl): void { $this->imageUrl = $imageUrl; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }

    public function getLink(): ?string { return $this->link; }
    public function setLink(?string $link): void { $this->link = $link; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

    /**
     * Lấy danh sách dụng cụ / sản phẩm (lọc theo danh mục hoặc lấy tất cả nếu null)
     * @param int|null $categoryId ID chuyên mục cần lọc
     * @return array Danh sách sản phẩm
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

                if ($categoryId !== null && $categoryId > 0) {
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
     * Lấy chi tiết 1 dụng cụ theo ID
     * @param int $id ID dụng cụ
     * @return array|null Dữ liệu dụng cụ hoặc null nếu không tìm thấy
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
     * Lấy danh sách dụng cụ đã được sử dụng trong chuyến đi của một bài viết (Tools & Gear)
     * @param int $postId ID bài viết
     * @return array Danh sách dụng cụ
     */
    public function getByPostId(int $postId): array
    {
        try {
            return Database::call('sp_post_products_get', [$postId]);
        } catch (PDOException $e) {
            error_log('Notice: sp_post_products_get fallback query: ' . $e->getMessage());

            try {
                $sql = 'SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link,
                               c.name AS category_name
                        FROM post_products pp
                        JOIN products p ON p.id = pp.product_id
                        LEFT JOIN categories c ON c.id = p.category_id
                        WHERE pp.post_id = :post_id
                        ORDER BY p.name ASC';
                return Database::query($sql, ['post_id' => $postId]);
            } catch (PDOException $ex) {
                error_log('Error getting tools by post ID: ' . $ex->getMessage());
                return [];
            }
        }
    }

    /**
     * Đồng bộ danh sách dụng cụ cho bài viết (thêm mới hoặc cập nhật khi sửa bài viết)
     * @param int $postId ID bài viết
     * @param array $productIds Mảng các ID dụng cụ được chọn
     */
    public function syncPostProducts(int $postId, array $productIds): void
    {
        if ($postId <= 0) {
            return;
        }

        // 1. Xóa các liên kết dụng cụ cũ của bài viết
        try {
            Database::call('sp_post_products_clear', [$postId]);
        } catch (PDOException $e) {
            try {
                Database::execute('DELETE FROM post_products WHERE post_id = :post_id', ['post_id' => $postId]);
            } catch (PDOException $ex) {
                error_log('Error clearing post products: ' . $ex->getMessage());
            }
        }

        // 2. Thêm từng dụng cụ đã chọn vào bài viết (dùng vòng lặp foreach, không dùng lambda)
        foreach ($productIds as $pId) {
            $cleanedId = (int)$pId;
            if ($cleanedId > 0) {
                try {
                    Database::call('sp_post_product_add', [$postId, $cleanedId]);
                } catch (PDOException $e) {
                    try {
                        Database::execute(
                            'INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:post_id, :product_id)',
                            ['post_id' => $postId, 'product_id' => $cleanedId]
                        );
                    } catch (PDOException $ex) {
                        error_log('Error adding post product: ' . $ex->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Thêm dụng cụ mới vào danh mục sản phẩm
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
                error_log('Error creating product: ' . $ex->getMessage());
                return 0;
            }
        }
    }

    /**
     * Cập nhật thông tin dụng cụ
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
                error_log('Error updating product: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Xóa dụng cụ
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
