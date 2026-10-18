<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Category.php';

class CategoryController extends Controller {
    private Category $categories;

    public function __construct() {
        $this->categories = new Category();
    }

    public function index(): void {
        $categoriesList = $this->categories->getAll();
        $this->render('categories/index', [
            'title' => 'Chuyên mục dã ngoại',
            'categories' => $categoriesList
        ]);
    }

    public function create(): void {
        $this->render('categories/create', [
            'title' => 'Thêm chuyên mục mới'
        ]);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim((string)($_POST['name'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            
            header('Content-Type: application/json; charset=utf-8');

            if (empty($name) || mb_strlen($name) < 2) {
                echo json_encode(["status" => "error", "message" => "Tên danh mục phải từ 2 ký tự trở lên!"]);
                exit;
            }
            
            $this->categories->setName($name);
            $this->categories->setDescription($description);
            
            $result = $this->categories->create();
            
            if ($result) {
                echo json_encode(["status" => "success", "message" => "Đã thêm danh mục thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Thêm thất bại! Danh mục có thể đã tồn tại."]);
            }
            exit;
        }
    }
}