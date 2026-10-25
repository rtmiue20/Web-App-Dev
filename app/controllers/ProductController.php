<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

/**
 * Controller ProductController - Hiển thị danh sách thiết bị / dụng cụ dã ngoại theo danh mục (FR09 - Vương)
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
     * Danh sách sản phẩm công khai (/products, lọc theo ?category=ID)
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

        $this->render('products/index', [
            'title' => 'Dụng cụ dã ngoại',
            'products' => $this->products->getAll($categoryId),
            'categories' => $this->categories->getAll(),
            'selectedCategoryId' => $categoryId
        ]);
    }
}
