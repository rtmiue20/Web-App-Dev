<?php
// Gọi đường dẫn tới lớp Controller gốc để VS Code nhận diện được hàm view() và model()
require_once __DIR__ . '/../core/Controller.php';

class CategoryController extends Controller {
    public function __construct() {
        // Mở comment dòng dưới nếu nhóm bạn đã code xong AdminMiddleware
        // AdminMiddleware::handle(); 
    }

    // Hiển thị giao diện form thêm danh mục
    public function create() {
        $this->view('categories/create');
    }

    // Xử lý dữ liệu từ form bằng AJAX (POST)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            // Ép kiểu header trả về chuẩn JSON
            header('Content-Type: application/json');

            // Validate: Không để trống và phải từ 2 ký tự trở lên
            if (empty($name) || mb_strlen($name) < 2) {
                echo json_encode(["status" => "error", "message" => "Tên danh mục phải từ 2 ký tự trở lên!"]);
                exit;
            }
            
            // Gọi model Category và lưu vào CSDL
            $categoryModel = $this->model('Category'); 
            $result = $categoryModel->create($name, $description);
            
            // Kiểm tra kết quả trả về
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