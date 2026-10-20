<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Category.php';

class CategoryController extends Controller {
    private Category $categories;

    public function __construct() {
        $this->categories = new Category();
    }

    /**
     * Trang xem danh sách chuyên mục công khai cho khách và thành viên
     */
    public function index(): void {
        $categoriesList = $this->categories->getAll();
        $this->render('categories/index', [
            'title' => 'Chuyên mục dã ngoại',
            'categories' => $categoriesList
        ]);
    }

    /**
     * Trang quản lý chuyên mục dành cho Quản trị viên (/admin/categories)
     */
    public function adminIndex(): void {
        $categoriesList = $this->categories->getAll();
        $this->render('admin/categories/index', [
            'title' => 'Quản lý Chuyên mục',
            'categories' => $categoriesList
        ], 'admin');
    }

    /**
     * Giao diện form tạo chuyên mục mới (/admin/categories/create)
     */
    public function create(): void {
        $this->render('admin/categories/create', [
            'title' => 'Thêm chuyên mục mới',
            'error' => null,
            'old' => []
        ], 'admin');
    }

    /**
     * Xử lý lưu chuyên mục mới (POST /admin/categories/store)
     */
    public function store(): void {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                  || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        // 1. Kiểm tra CSRF token
        if (!$this->verifyCsrfToken()) {
            if ($isAjax) {
                http_response_code(419);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["status" => "error", "message" => "Phiên làm việc đã hết hạn. Vui lòng tải lại trang."]);
                exit;
            }
            $this->render('admin/categories/create', [
                'title' => 'Thêm chuyên mục mới',
                'error' => 'Phiên làm việc đã hết hạn (CSRF). Vui lòng thử lại.',
                'old' => $_POST
            ], 'admin');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        // 2. Validate dữ liệu
        if (empty($name) || mb_strlen($name) < 2) {
            $msg = 'Tên danh mục không được để trống và phải từ 2 ký tự trở lên!';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["status" => "error", "message" => $msg]);
                exit;
            }
            $this->render('admin/categories/create', [
                'title' => 'Thêm chuyên mục mới',
                'error' => $msg,
                'old' => ['name' => $name, 'description' => $description]
            ], 'admin');
            return;
        }

        if (mb_strlen($name) > 100) {
            $msg = 'Tên danh mục không được vượt quá 100 ký tự!';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["status" => "error", "message" => $msg]);
                exit;
            }
            $this->render('admin/categories/create', [
                'title' => 'Thêm chuyên mục mới',
                'error' => $msg,
                'old' => ['name' => $name, 'description' => $description]
            ], 'admin');
            return;
        }

        // 3. Kiểm tra trùng tên danh mục
        if ($this->categories->findByName($name) !== null) {
            $msg = 'Tên chuyên mục này đã tồn tại trên hệ thống. Vui lòng chọn tên khác!';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["status" => "error", "message" => $msg]);
                exit;
            }
            $this->render('admin/categories/create', [
                'title' => 'Thêm chuyên mục mới',
                'error' => $msg,
                'old' => ['name' => $name, 'description' => $description]
            ], 'admin');
            return;
        }

        // 4. Lưu vào CSDL
        $this->categories->setName($name);
        $this->categories->setDescription($description !== '' ? $description : null);
        $result = $this->categories->create();

        if ($result) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["status" => "success", "message" => "Đã thêm chuyên mục thành công!"]);
                exit;
            }
            Session::flash('success', "Đã tạo chuyên mục \"{$name}\" thành công!");
            $this->redirect('/admin/categories');
        } else {
            $msg = 'Thêm thất bại do lỗi hệ thống CSDL. Vui lòng thử lại sau.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["status" => "error", "message" => $msg]);
                exit;
            }
            $this->render('admin/categories/create', [
                'title' => 'Thêm chuyên mục mới',
                'error' => $msg,
                'old' => ['name' => $name, 'description' => $description]
            ], 'admin');
        }
    }

    /**
     * Giao diện form chỉnh sửa chuyên mục (/admin/categories/edit/{id})
     */
    public function edit(int $id): void {
        $category = $this->categories->findById($id);
        if ($category === null) {
            Session::flash('error', 'Không tìm thấy chuyên mục yêu cầu.');
            $this->redirect('/admin/categories');
            return;
        }

        $this->render('admin/categories/edit', [
            'title' => 'Chỉnh sửa chuyên mục',
            'category' => $category,
            'error' => null,
            'old' => []
        ], 'admin');
    }

    /**
     * Xử lý cập nhật thông tin chuyên mục (POST /admin/categories/update/{id})
     */
    public function update(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        $category = $this->categories->findById($id);
        if ($category === null) {
            Session::flash('error', 'Chuyên mục cần cập nhật không tồn tại.');
            $this->redirect('/admin/categories');
            return;
        }

        // 1. Kiểm tra CSRF token
        if (!$this->verifyCsrfToken()) {
            $this->render('admin/categories/edit', [
                'title' => 'Chỉnh sửa chuyên mục',
                'category' => $category,
                'error' => 'Phiên làm việc đã hết hạn (CSRF). Vui lòng thử lại.',
                'old' => $_POST
            ], 'admin');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        // 2. Validate dữ liệu
        if (empty($name) || mb_strlen($name) < 2) {
            $this->render('admin/categories/edit', [
                'title' => 'Chỉnh sửa chuyên mục',
                'category' => $category,
                'error' => 'Tên danh mục không được để trống và phải từ 2 ký tự trở lên!',
                'old' => ['name' => $name, 'description' => $description]
            ], 'admin');
            return;
        }

        if (mb_strlen($name) > 100) {
            $this->render('admin/categories/edit', [
                'title' => 'Chỉnh sửa chuyên mục',
                'category' => $category,
                'error' => 'Tên danh mục không được vượt quá 100 ký tự!',
                'old' => ['name' => $name, 'description' => $description]
            ], 'admin');
            return;
        }

        // 3. Kiểm tra xem tên có bị trùng với danh mục KHÁC không
        $existing = $this->categories->findByName($name, $id);
        if ($existing !== null) {
            $this->render('admin/categories/edit', [
                'title' => 'Chỉnh sửa chuyên mục',
                'category' => $category,
                'error' => 'Tên chuyên mục này đã được sử dụng bởi một mục khác. Vui lòng chọn tên khác!',
                'old' => ['name' => $name, 'description' => $description]
            ], 'admin');
            return;
        }

        // 4. Cập nhật vào DB
        $updated = $this->categories->update($id, $name, $description !== '' ? $description : null);
        if ($updated) {
            Session::flash('success', "Đã cập nhật chuyên mục \"{$name}\" thành công!");
            $this->redirect('/admin/categories');
        } else {
            $this->render('admin/categories/edit', [
                'title' => 'Chỉnh sửa chuyên mục',
                'category' => $category,
                'error' => 'Cập nhật thất bại do lỗi hệ thống. Vui lòng thử lại sau.',
                'old' => ['name' => $name, 'description' => $description]
            ], 'admin');
        }
    }

    /**
     * Xử lý xóa chuyên mục (POST /admin/categories/delete/{id})
     * Kiểm tra ràng buộc bài viết và yêu cầu xác nhận khi xóa (FR11)
     */
    public function delete(int $id): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        // 1. Kiểm tra CSRF token
        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/admin/categories');
            return;
        }

        $category = $this->categories->findById($id);
        if ($category === null) {
            Session::flash('error', 'Chuyên mục cần xóa không tồn tại.');
            $this->redirect('/admin/categories');
            return;
        }

        // 2. Kiểm tra ràng buộc: chuyên mục có bài viết không
        $postCount = $this->categories->countPosts($id);
        if ($postCount > 0) {
            Session::flash('error', "Không thể xóa chuyên mục \"{$category['name']}\" vì đang có {$postCount} bài viết trực thuộc. Vui lòng xóa hoặc di chuyển các bài viết trước.");
            $this->redirect('/admin/categories');
            return;
        }

        // 3. Thực hiện xóa
        $deleted = $this->categories->delete($id);
        if ($deleted) {
            Session::flash('success', "Đã xóa chuyên mục \"{$category['name']}\" thành công!");
        } else {
            Session::flash('error', "Không thể xóa chuyên mục do lỗi CSDL. Vui lòng kiểm tra lại.");
        }

        $this->redirect('/admin/categories');
    }
}
