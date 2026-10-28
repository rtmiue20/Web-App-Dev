<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

/**
 * Controller ProductController - Quản lý giao diện và danh mục Dụng cụ / Thiết bị dã ngoại
 * Tuân thủ MVC & OOP, hoàn toàn không dùng lambda
 */
class ProductController extends Controller
{
    private Product $products;
    private Category $categories;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
    }

    /**
     * Danh sách sản phẩm / dụng cụ dã ngoại (công khai cho tất cả thành viên)
     * Có hỗ trợ lọc theo chuyên mục
     */
    public function index(): void
    {
        $selectedCategoryId = null;
        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $selectedCategoryId = (int)$_GET['category'];
            if ($selectedCategoryId <= 0) {
                $selectedCategoryId = null;
            }
        }

        $allCategories = $this->categories->getAll();
        $allProducts = $this->products->getAll($selectedCategoryId);

        $this->render('products/index', [
            'title' => 'Dụng cụ & Trang thiết bị dã ngoại',
            'products' => $allProducts,
            'categories' => $allCategories,
            'selectedCategoryId' => $selectedCategoryId
        ]);
    }

    /**
     * Chi tiết sản phẩm / dụng cụ theo ID
     */
    public function show(int $id): void
    {
        $product = $this->products->findById($id);
        if (!$product) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy dụng cụ']);
            return;
        }

        // Nếu là yêu cầu AJAX trả về JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'product' => $product
            ]);
            return;
        }

        $allCategories = $this->categories->getAll();
        $this->render('products/index', [
            'title' => $product['name'] . ' - Dụng cụ dã ngoại',
            'products' => [$product],
            'categories' => $allCategories,
            'selectedCategoryId' => (int)($product['category_id'] ?? 0)
        ]);
    }
}
