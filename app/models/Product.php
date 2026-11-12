<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../repositories/ProductRepository.php';

/**
 * Model Product - Thực thể Dụng cụ & Trang thiết bị dã ngoại kết nối với ProductRepository (DAO).
 * Gọi các Stored Procedures qua ProductRepository: sp_product_list_by_category, sp_product_get, sp_post_products_get, v.v.
 */
class Product extends Model
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?int $userId = null;
    private ?string $name = null;
    private ?float $price = null;
    private ?string $imageUrl = null;
    private ?string $description = null;
    private ?string $link = null;
    private ?string $createdAt = null;
    private ProductRepository $repository;

    public function __construct(?ProductRepository $repository = null)
    {
        parent::__construct();
        $this->repository = $repository ?? new ProductRepository($this->db);
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getCategoryId(): ?int { return $this->categoryId; }
    public function setCategoryId(?int $categoryId): void { $this->categoryId = $categoryId; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): void { $this->userId = $userId; }

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

    public function getAll(?int $categoryId = null): array
    {
        return $this->repository->findByCategory($categoryId);
    }

    public function getAllForAdmin(?int $categoryId = null): array
    {
        return $this->repository->findAllForAdmin($categoryId);
    }

    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function getByPostId(int $postId): array
    {
        return $this->repository->findByPostId($postId);
    }

    public function syncPostProducts(int $postId, array $productIds): void
    {
        $this->repository->syncPostProducts($postId, $productIds);
    }

    public function create(
        ?int $categoryId,
        string $name,
        float $price,
        ?string $imageUrl,
        ?string $description,
        ?string $link = null,
        int $isShared = 1,
        ?int $userId = null
    ): int {
        $productId = $this->repository->createProduct($categoryId, $name, $price, $imageUrl, $description, $link, $isShared, $userId);
        if ($productId > 0) {
            $this->id = $productId;
        }
        return $productId;
    }

    public function update(
        int $id,
        ?int $categoryId,
        string $name,
        float $price,
        ?string $imageUrl,
        ?string $description,
        ?string $link = null,
        int $isShared = 1
    ): bool {
        return $this->repository->updateProduct($id, $categoryId, $name, $price, $imageUrl, $description, $link, $isShared);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
