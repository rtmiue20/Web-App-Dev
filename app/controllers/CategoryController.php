<?php
require_once __DIR__ . '/../core/Controller.php';

class CategoryController extends Controller {
    public function __construct() {
        // AdminMiddleware::handle(); 
    }

    public function create() {
        $this->view('categories/create');
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            header('Content-Type: application/json');

            if (empty($name) || mb_strlen($name) < 2) {
                echo json_encode(["status" => "error", "message" => "Tên danh mục phải từ 2 ký tự trở lên!"]);
                exit;
            }
            
            // 1. Khởi tạo Entity
            $categoryModel = $this->model('Category'); 
            
            // 2. Gán dữ liệu vào Object thông qua Setters
            $categoryModel->setName($name);
            $categoryModel->setDescription($description);
            
            // 3. Thực thi hành động lưu (không truyền tham số)
            $result = $categoryModel->create();
            
            if ($result) {
                echo json_encode(["status" => "success", "message" => "Đã thêm danh mục thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Thêm thất bại! Danh mục có thể đã tồn tại."]);
            }
            exit;
        }
    }
}
?>