<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/CategoryService.php';

/**
 * CategoryController - Điều khiển Chuyên mục công khai & Quản trị Chuyên mục (FR11).
 * Ủy quyền xử lý nghiệp vụ và kiểm tra ràng buộc cho CategoryService -> CategoryRepository.
 */
class CategoryController extends Controller
{
    private CategoryService $categoryService;

    public function __construct(?CategoryService $categoryService = null)
    {
        $this->categoryService = $categoryService ?? new CategoryService();
    }

    /**
     * Trang xem danh sách chuyên mục công khai cho khách và thành viên.
     */
    public function index(): void
    {
        $this->render('categories/index', [
            'title'      => 'Chuyên mục dã ngoại',
            'categories' => $this->categoryService->getAllCategories(),
        ]);
    }

    /**
     * Trang quản lý chuyên mục dành cho Quản trị viên (/admin/categories).
     */
    public function adminIndex(): void
    {
        $this->render('admin/categories/index', [
            'title'      => 'Quản lý Chuyên mục',
            'categories' => $this->categoryService->getAllCategories(),
        ], 'admin');
    }

    /**
     * Giao diện form tạo chuyên mục mới (/admin/categories/create).
     */
    public function create(): void
    {
        $this->render('admin/categories/create', [
            'title' => 'Thêm chuyên mục mới',
            'error' => null,
            'old'   => [],
        ], 'admin');
    }

    /**
     * Xử lý lưu chuyên mục mới (POST /admin/categories/store).
     */
    public function store(): void
    {
        $isAjax = $this->isAjaxRequest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            if ($isAjax) {
                http_response_code(419);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.']);
                exit;
            }
            $this->render('admin/categories/create', [
                'title' => 'Thêm chuyên mục mới',
                'error' => 'Phiên làm việc đã hết hạn (CSRF). Vui lòng thử lại.',
                'old'   => $_POST,
            ], 'admin');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        $result = $this->categoryService->createCategory($name, $description);

        if ($result['success']) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'success', 'message' => 'Đã thêm chuyên mục thành công!']);
                exit;
            }
            Session::flash('success', $result['message']);
            $this->redirect('/admin/categories');
            return;
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
            exit;
        }

        $this->render('admin/categories/create', [
            'title' => 'Thêm chuyên mục mới',
            'error' => $result['message'],
            'old'   => ['name' => $name, 'description' => $description],
        ], 'admin');
    }

    /**
     * Giao diện form chỉnh sửa chuyên mục (/admin/categories/edit/{id}).
     */
    public function edit(int $id): void
    {
        $category = $this->categoryService->getCategoryById($id);
        if ($category === null) {
            Session::flash('error', 'Không tìm thấy chuyên mục yêu cầu.');
            $this->redirect('/admin/categories');
            return;
        }

        $this->render('admin/categories/edit', [
            'title'    => 'Chỉnh sửa chuyên mục',
            'category' => $category,
            'error'    => null,
            'old'      => [],
        ], 'admin');
    }

    /**
     * Xử lý cập nhật thông tin chuyên mục (POST /admin/categories/update/{id}).
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        $category = $this->categoryService->getCategoryById($id);
        if ($category === null) {
            Session::flash('error', 'Chuyên mục cần cập nhật không tồn tại.');
            $this->redirect('/admin/categories');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            $this->render('admin/categories/edit', [
                'title'    => 'Chỉnh sửa chuyên mục',
                'category' => $category,
                'error'    => 'Phiên làm việc đã hết hạn (CSRF). Vui lòng thử lại.',
                'old'      => $_POST,
            ], 'admin');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        $result = $this->categoryService->updateCategory($id, $name, $description);
        if ($result['success']) {
            Session::flash('success', $result['message']);
            $this->redirect('/admin/categories');
            return;
        }

        $this->render('admin/categories/edit', [
            'title'    => 'Chỉnh sửa chuyên mục',
            'category' => $category,
            'error'    => $result['message'],
            'old'      => ['name' => $name, 'description' => $description],
        ], 'admin');
    }

    /**
     * Xử lý xóa chuyên mục (POST /admin/categories/delete/{id}).
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/admin/categories');
            return;
        }

        $result = $this->categoryService->deleteCategory($id);
        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('/admin/categories');
    }
}
