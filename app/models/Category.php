<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../repositories/CategoryRepository.php';

/**
 * Model Category - Thực thể Chuyên mục (Entity Model) kết nối với CategoryRepository (DAO).
 * Gọi các Routines qua CategoryRepository: sp_category_list, sp_category_get, sp_category_get_by_name,
 * sp_category_create, sp_category_update, sp_category_delete, sp_category_count_posts, sp_category_count.
 */
class Category extends Model
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $description = null;
    private CategoryRepository $repository;

    public function __construct(?CategoryRepository $repository = null)
    {
        parent::__construct();
        $this->repository = $repository ?? new CategoryRepository($this->db);
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setName(?string $name): void { $this->name = $name; }
    public function setDescription(?string $description): void { $this->description = $description; }

    /**
     * Thêm mới chuyên mục vào cơ sở dữ liệu qua CategoryRepository (sp_category_create).
     */
    public function create(): bool
    {
        $newId = $this->repository->createCategory((string)$this->name, $this->description);
        if ($newId > 0) {
            $this->id = $newId;
            return true;
        }
        return false;
    }

    /**
     * Lấy danh sách tất cả chuyên mục kèm theo số lượng bài viết của từng mục (sp_category_list).
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Tìm chuyên mục theo ID (sp_category_get).
     */
    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    /**
     * Tìm chuyên mục theo tên (sp_category_get_by_name).
     */
    public function findByName(string $name, ?int $excludeId = null): ?array
    {
        return $this->repository->findByName($name, $excludeId);
    }

    /**
     * Cập nhật thông tin chuyên mục (sp_category_update).
     */
    public function update(int $id, string $name, ?string $description = null): bool
    {
        return $this->repository->updateCategory($id, $name, $description);
    }

    /**
     * Đếm số lượng bài viết đang thuộc về chuyên mục này (sp_category_count_posts).
     */
    public function countPosts(int $id): int
    {
        return $this->repository->countPosts($id);
    }

    /**
     * Xóa chuyên mục khỏi cơ sở dữ liệu (sp_category_delete).
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Đếm tổng số chuyên mục trong hệ thống (sp_category_count).
     */
    public function count(): int
    {
        return $this->repository->count();
    }
}
