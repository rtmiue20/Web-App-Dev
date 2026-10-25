<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../helpers/Upload.php';

/**
 * Controller ProductAdminController - Quản trị sản phẩm / thiết bị dã ngoại (FR12 - Vương)
 * Chức năng: Danh sách, Thêm, Sửa, Xóa sản phẩm (ảnh, mô tả, giá, liên kết)
 * Bảo mật: Chỉ Admin truy cập qua AdminMiddleware
 */
class ProductAdminController extends Controller
{
    private Product $products;
    private Category $categories;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
    }

    /**
     * Danh sách sản phẩm quản trị (/admin/products)
     */
    public function index(): void
    {
        $categoryId = null;
        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $categoryId = (int)$_GET['category'];
            if ($categoryId <= 0) {
                $categoryId = null;
            }
        }

        $this->render('admin/products/index', [
            'title' => 'Quản lý Sản phẩm',
            'products' => $this->products->getAll($categoryId),
            'categories' => $this->categories->getAll(),
            'selectedCategoryId' => $categoryId
        ], 'admin');
    }

    /**
     * Form thêm sản phẩm (/admin/products/create)
     */
    public function create(): void
    {
        $this->render('admin/products/create', [
            'title' => 'Thêm sản phẩm mới',
            'categories' => $this->categories->getAll(),
            'errors' => [],
            'old' => []
        ], 'admin');
    }

    /**
     * Lưu sản phẩm mới (POST /admin/products/store)
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $this->render('admin/products/create', [
                'title' => 'Thêm sản phẩm mới',
                'categories' => $this->categories->getAll(),
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => $_POST
            ], 'admin');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $priceText = trim((string)($_POST['price'] ?? '0'));
        $description = trim((string)($_POST['description'] ?? ''));
        $link = trim((string)($_POST['link'] ?? ''));

        $errors = $this->validate($name, $categoryId, $priceText, $description, $link);

        $imageUrl = null;
        if (empty($errors['image']) && !empty($_FILES['image']['name'])) {
            try {
                $imageUrl = Upload::image($_FILES['image'], 'products');
            } catch (Exception $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $this->deleteImageFile($imageUrl);

            $this->render('admin/products/create', [
                'title' => 'Thêm sản phẩm mới',
                'categories' => $this->categories->getAll(),
                'errors' => $errors,
                'old' => $_POST
            ], 'admin');
            return;
        }

        $newId = $this->products->create(
            $categoryId > 0 ? $categoryId : null,
            $name,
            (float)$priceText,
            $imageUrl,
            $description !== '' ? $description : null,
            $link !== '' ? $link : null
        );

        if ($newId > 0) {
            Session::flash('success', 'Đã thêm sản phẩm "' . $name . '" thành công.');
            $this->redirect('/admin/products');
            return;
        }

        $this->deleteImageFile($imageUrl);
        $this->render('admin/products/create', [
            'title' => 'Thêm sản phẩm mới',
            'categories' => $this->categories->getAll(),
            'errors' => ['form' => 'Không thể lưu sản phẩm. Vui lòng thử lại.'],
            'old' => $_POST
        ], 'admin');
    }

    /**
     * Form sửa sản phẩm (/admin/products/{id}/edit)
     */
    public function edit($id): void
    {
        $productId = (int)$id;
        $product = $this->products->findById($productId);
        if ($product === null) {
            Session::flash('error', 'Sản phẩm không tồn tại.');
            $this->redirect('/admin/products');
            return;
        }

        $this->render('admin/products/edit', [
            'title' => 'Chỉnh sửa sản phẩm',
            'product' => $product,
            'categories' => $this->categories->getAll(),
            'errors' => [],
            'old' => []
        ], 'admin');
    }

    /**
     * Cập nhật sản phẩm (POST /admin/products/{id}/update)
     */
    public function update($id): void
    {
        $productId = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        $product = $this->products->findById($productId);
        if ($product === null) {
            Session::flash('error', 'Sản phẩm cần cập nhật không tồn tại.');
            $this->redirect('/admin/products');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $this->render('admin/products/edit', [
                'title' => 'Chỉnh sửa sản phẩm',
                'product' => $product,
                'categories' => $this->categories->getAll(),
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => $_POST
            ], 'admin');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $priceText = trim((string)($_POST['price'] ?? '0'));
        $description = trim((string)($_POST['description'] ?? ''));
        $link = trim((string)($_POST['link'] ?? ''));

        $errors = $this->validate($name, $categoryId, $priceText, $description, $link);

        $newImageUrl = null;
        if (empty($errors['image']) && !empty($_FILES['image']['name'])) {
            try {
                $newImageUrl = Upload::image($_FILES['image'], 'products');
            } catch (Exception $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $this->deleteImageFile($newImageUrl);

            $this->render('admin/products/edit', [
                'title' => 'Chỉnh sửa sản phẩm',
                'product' => $product,
                'categories' => $this->categories->getAll(),
                'errors' => $errors,
                'old' => $_POST
            ], 'admin');
            return;
        }

        $updated = $this->products->update(
            $productId,
            $categoryId > 0 ? $categoryId : null,
            $name,
            (float)$priceText,
            $newImageUrl,
            $description !== '' ? $description : null,
            $link !== '' ? $link : null
        );

        if ($updated) {
            if ($newImageUrl !== null) {
                $this->deleteImageFile($product['image_url'] ?? null);
            }
            Session::flash('success', 'Đã cập nhật sản phẩm "' . $name . '" thành công.');
            $this->redirect('/admin/products');
            return;
        }

        $this->deleteImageFile($newImageUrl);
        $this->render('admin/products/edit', [
            'title' => 'Chỉnh sửa sản phẩm',
            'product' => $product,
            'categories' => $this->categories->getAll(),
            'errors' => ['form' => 'Không thể lưu thay đổi. Vui lòng thử lại.'],
            'old' => $_POST
        ], 'admin');
    }

    /**
     * Xóa sản phẩm (POST /admin/products/{id}/delete)
     */
    public function delete($id): void
    {
        $productId = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.');
            $this->redirect('/admin/products');
            return;
        }

        $product = $this->products->findById($productId);
        if ($product === null) {
            Session::flash('error', 'Sản phẩm không tồn tại.');
            $this->redirect('/admin/products');
            return;
        }

        $this->deleteImageFile($product['image_url'] ?? null);

        if ($this->products->delete($productId)) {
            Session::flash('success', 'Đã xóa sản phẩm "' . $product['name'] . '" thành công.');
        } else {
            Session::flash('error', 'Không thể xóa sản phẩm. Vui lòng thử lại.');
        }

        $this->redirect('/admin/products');
    }

    /**
     * Kiểm tra dữ liệu form, trả về mảng lỗi (rỗng nếu hợp lệ)
     */
    private function validate(string $name, int $categoryId, string $priceText, string $description, string $link): array
    {
        $errors = [];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            $errors['name'] = 'Tên sản phẩm phải từ 2 đến 150 ký tự.';
        }
        if ($categoryId <= 0) {
            $errors['category_id'] = 'Vui lòng chọn một chuyên mục.';
        } elseif ($this->categories->findById($categoryId) === null) {
            $errors['category_id'] = 'Chuyên mục đã chọn không tồn tại.';
        }
        if ($priceText === '' || !is_numeric($priceText) || (float)$priceText < 0) {
            $errors['price'] = 'Giá phải là một số không âm.';
        } elseif ((float)$priceText > 99999999) {
            $errors['price'] = 'Giá quá lớn (tối đa 99.999.999 VNĐ).';
        }
        if (mb_strlen($description) > 255) {
            $errors['description'] = 'Mô tả không được vượt quá 255 ký tự.';
        }
        if ($link !== '') {
            if (mb_strlen($link) > 255 || filter_var($link, FILTER_VALIDATE_URL) === false) {
                $errors['link'] = 'Liên kết sản phẩm phải là URL hợp lệ (bắt đầu bằng http:// hoặc https://).';
            }
        }

        return $errors;
    }

    /**
     * Xóa file ảnh vật lý trong public/uploads/products/ bằng unlink()
     */
    private function deleteImageFile(?string $imageUrl): void
    {
        if ($imageUrl === null || $imageUrl === '') {
            return;
        }

        $rootDir = dirname(__DIR__, 3);
        $uploadDir = realpath($rootDir . '/public/uploads/products');
        if ($uploadDir === false) {
            return;
        }

        $filePath = realpath($rootDir . '/public/' . ltrim($imageUrl, '/'));
        if ($filePath !== false && str_starts_with($filePath, $uploadDir) && is_file($filePath)) {
            unlink($filePath);
        }
    }
}
