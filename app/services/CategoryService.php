<?php
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../repositories/CategoryRepository.php';

/**
 * CategoryService - Tầng nghiệp vụ Quản lý Chuyên mục dã ngoại (Service Layer - FR11).
 * Đóng gói kiểm tra hợp lệ, kiểm tra trùng tên và kiểm tra ràng buộc khóa ngoại trước khi xóa.
 */
class CategoryService extends BaseService
{
    private CategoryRepository $categoryRepository;

    public function __construct(?CategoryRepository $categoryRepository = null)
    {
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();
    }

    /**
     * Lấy danh sách toàn bộ chuyên mục kèm số lượng bài viết.
     */
    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    /**
     * Tìm chuyên mục theo ID.
     */
    public function getCategoryById(int $id): ?array
    {
        return $this->categoryRepository->findById($id);
    }

    /**
     * Đếm tổng số chuyên mục.
     */
    public function countCategories(): int
    {
        return $this->categoryRepository->count();
    }

    /**
     * Nghiệp vụ tạo mới chuyên mục.
     */
    public function createCategory(string $name, ?string $description = null): array
    {
        $name = $this->cleanString($name);
        $description = $description !== null ? $this->cleanString($description) : null;

        if ($name === '' || mb_strlen($name) < 2) {
            return $this->failure('Tên danh mục không được để trống và phải từ 2 ký tự trở lên!');
        }

        if (mb_strlen($name) > 100) {
            return $this->failure('Tên danh mục không được vượt quá 100 ký tự!');
        }

        if ($this->categoryRepository->findByName($name) !== null) {
            return $this->failure('Tên chuyên mục này đã tồn tại trên hệ thống. Vui lòng chọn tên khác!', [], 409);
        }

        $newId = $this->categoryRepository->createCategory($name, $description !== '' ? $description : null);
        if ($newId <= 0) {
            return $this->failure('Thêm thất bại do lỗi hệ thống CSDL. Vui lòng thử lại sau.', [], 500);
        }

        return $this->success(['id' => $newId, 'name' => $name], "Đã tạo chuyên mục \"{$name}\" thành công!");
    }

    /**
     * Nghiệp vụ cập nhật chuyên mục.
     */
    public function updateCategory(int $id, string $name, ?string $description = null): array
    {
        $existingCategory = $this->categoryRepository->findById($id);
        if ($existingCategory === null) {
            return $this->failure('Chuyên mục cần cập nhật không tồn tại.', [], 404);
        }

        $name = $this->cleanString($name);
        $description = $description !== null ? $this->cleanString($description) : null;

        if ($name === '' || mb_strlen($name) < 2) {
            return $this->failure('Tên danh mục không được để trống và phải từ 2 ký tự trở lên!');
        }

        if (mb_strlen($name) > 100) {
            return $this->failure('Tên danh mục không được vượt quá 100 ký tự!');
        }

        if ($this->categoryRepository->findByName($name, $id) !== null) {
            return $this->failure('Tên chuyên mục này đã được sử dụng bởi một mục khác. Vui lòng chọn tên khác!', [], 409);
        }

        $updated = $this->categoryRepository->updateCategory($id, $name, $description !== '' ? $description : null);
        if (!$updated) {
            return $this->failure('Cập nhật thất bại do lỗi hệ thống. Vui lòng thử lại sau.', [], 500);
        }

        return $this->success(['id' => $id, 'name' => $name], "Đã cập nhật chuyên mục \"{$name}\" thành công!");
    }

    /**
     * Nghiệp vụ xóa chuyên mục an toàn (Kiểm tra ràng buộc bài viết trực thuộc trước khi xóa).
     */
    public function deleteCategory(int $id): array
    {
        $category = $this->categoryRepository->findById($id);
        if ($category === null) {
            return $this->failure('Chuyên mục cần xóa không tồn tại.', [], 404);
        }

        $postCount = $this->categoryRepository->countPosts($id);
        if ($postCount > 0) {
            return $this->failure(
                "Không thể xóa chuyên mục \"{$category['name']}\" vì đang có {$postCount} bài viết trực thuộc. Vui lòng xóa hoặc di chuyển các bài viết trước.",
                [],
                409
            );
        }

        $deleted = $this->categoryRepository->delete($id);
        if (!$deleted) {
            return $this->failure('Không thể xóa chuyên mục do lỗi CSDL. Vui lòng kiểm tra lại.', [], 500);
        }

        return $this->success(['id' => $id], "Đã xóa chuyên mục \"{$category['name']}\" thành công!");
    }
}

