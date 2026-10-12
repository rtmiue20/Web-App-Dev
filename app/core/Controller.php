<?php
class Controller {
    // Hàm khởi tạo Model
    public function model($model) {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model();
    }

    // Hàm gọi View
    public function view($view, $data = []) {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die("Lỗi: File view '" . $view . ".php' không tồn tại!");
        }
    }
}
?>