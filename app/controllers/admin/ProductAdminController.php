<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../helpers/Upload.php';

/**
 * Controller ProductAdminController - Quản trị Dụng cụ & Sản phẩm dã ngoại (FR12 - Vương)
 * Cho phép Admin: Xem danh sách, Thêm mới, Chỉnh sửa, Xóa sản phẩm/dụng cụ
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
     * Danh sách sản phẩm/dụng cụ trong trang Admin
     */
    public function index(): void
    {
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
        if ($categoryId !== null && $categoryId <= 0) {
            $categoryId = null;
        }

        $productsList = $this->products->getAll($categoryId);
        $categoriesList = $this->categories->getAll();

        $this->render('admin/products/index', [
            'title'              => 'Quản lý Dụng cụ & Sản phẩm',
            'products'           => $productsList,
            'categories'         => $categoriesList,
            'selectedCategoryId' => $categoryId
        ], 'admin');
    }

    /**
     * Form thêm dụng cụ mới
     */
    public function create(): void
    {
        $categoriesList = $this->categories->getAll();
        $this->render('admin/products/create', [
            'title'      => 'Thêm dụng cụ dã ngoại mới',
            'categories' => $categoriesList,
            'error'      => null,
            'old'        => []
        ], 'admin');
    }

    /**
     * Xử lý lưu dụng cụ mới
     */
    public function store(): void
    {
        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/admin/products/create');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $price = isset($_POST['price']) && is_numeric($_POST['price']) ? max(0, (float)$_POST['price']) : 0;
        $description = trim((string)($_POST['description'] ?? ''));
        $link = trim((string)($_POST['link'] ?? ''));
        $imageUrl = trim((string)($_POST['image_url'] ?? ''));

        if (mb_strlen($name) < 2) {
            $this->render('admin/products/create', [
                'title'      => 'Thêm dụng cụ dã ngoại mới',
                'categories' => $this->categories->getAll(),
                'error'      => 'Tên dụng cụ phải có ít nhất 2 ký tự.',
                'old'        => $_POST
            ], 'admin');
            return;
        }

        // Hỗ trợ upload file ảnh trực tiếp nếu có chọn file
        if (!empty($_FILES['image']['name'])) {
            try {
                $uploadedPath = Upload::image($_FILES['image'], 'products');
                if (!empty($uploadedPath)) {
                    $imageUrl = $uploadedPath;
                }
            } catch (Exception $e) {
                $this->render('admin/products/create', [
                    'title'      => 'Thêm dụng cụ dã ngoại mới',
                    'categories' => $this->categories->getAll(),
                    'error'      => $e->getMessage(),
                    'old'        => $_POST
                ], 'admin');
                return;
            }
        }

        if ($imageUrl === '') {
            $imageUrl = 'assets/img/tent-forest.png';
        }

        $productId = $this->products->create(
            $categoryId,
            $name,
            $price,
            $imageUrl,
            $description !== '' ? $description : null,
            $link !== '' ? $link : null
        );

        if ($productId > 0) {
            Session::flash('success', 'Đã thêm dụng cụ "' . $name . '" thành công!');
            $this->redirect('/admin/products');
            return;
        }

        $this->render('admin/products/create', [
            'title'      => 'Thêm dụng cụ dã ngoại mới',
            'categories' => $this->categories->getAll(),
            'error'      => 'Không thể lưu dụng cụ vào cơ sở dữ liệu.',
            'old'        => $_POST
        ], 'admin');
    }

    /**
     * Form chỉnh sửa dụng cụ
     */
    public function edit(int $id): void
    {
        $product = $this->products->findById($id);
        if (!$product) {
            Session::flash('error', 'Không tìm thấy dụng cụ cần chỉnh sửa.');
            $this->redirect('/admin/products');
            return;
        }

        $this->render('admin/products/edit', [
            'title'      => 'Chỉnh sửa dụng cụ #' . $id,
            'product'    => $product,
            'categories' => $this->categories->getAll(),
            'error'      => null
        ], 'admin');
    }

    /**
     * Xử lý cập nhật dụng cụ
     */
    public function update(int $id): void
    {
        $product = $this->products->findById($id);
        if (!$product) {
            Session::flash('error', 'Không tìm thấy dụng cụ.');
            $this->redirect('/admin/products');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/admin/products/edit/' . $id);
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $price = isset($_POST['price']) && is_numeric($_POST['price']) ? max(0, (float)$_POST['price']) : 0;
        $description = trim((string)($_POST['description'] ?? ''));
        $link = trim((string)($_POST['link'] ?? ''));
        $imageUrl = trim((string)($_POST['image_url'] ?? ''));

        if (mb_strlen($name) < 2) {
            $this->render('admin/products/edit', [
                'title'      => 'Chỉnh sửa dụng cụ #' . $id,
                'product'    => array_merge($product, $_POST),
                'categories' => $this->categories->getAll(),
                'error'      => 'Tên dụng cụ phải có ít nhất 2 ký tự.'
            ], 'admin');
            return;
        }

        if (!empty($_FILES['image']['name'])) {
            try {
                $uploadedPath = Upload::image($_FILES['image'], 'products');
                if (!empty($uploadedPath)) {
                    $imageUrl = $uploadedPath;
                }
            } catch (Exception $e) {
                $this->render('admin/products/edit', [
                    'title'      => 'Chỉnh sửa dụng cụ #' . $id,
                    'product'    => array_merge($product, $_POST),
                    'categories' => $this->categories->getAll(),
                    'error'      => $e->getMessage()
                ], 'admin');
                return;
            }
        }

        $finalImage = $imageUrl !== '' ? $imageUrl : ($product['image_url'] ?? 'assets/img/tent-forest.png');

        $ok = $this->products->update(
            $id,
            $categoryId,
            $name,
            $price,
            $finalImage,
            $description !== '' ? $description : null,
            $link !== '' ? $link : null
        );

        if ($ok) {
            Session::flash('success', 'Cập nhật dụng cụ "' . $name . '" thành công!');
            $this->redirect('/admin/products');
            return;
        }

        $this->render('admin/products/edit', [
            'title'      => 'Chỉnh sửa dụng cụ #' . $id,
            'product'    => array_merge($product, $_POST),
            'categories' => $this->categories->getAll(),
            'error'      => 'Không thể cập nhật dụng cụ.'
        ], 'admin');
    }

    /**
     * Xóa dụng cụ
     */
    public function delete(int $id): void
    {
        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc đã hết hạn.');
            $this->redirect('/admin/products');
            return;
        }

        $deleted = $this->products->delete($id);
        if ($deleted) {
            Session::flash('success', 'Đã xóa dụng cụ thành công!');
        } else {
            Session::flash('error', 'Không thể xóa dụng cụ.');
        }
        $this->redirect('/admin/products');
    }
}

