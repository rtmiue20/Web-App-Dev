<?php
class Controller
{
    // Hàm khởi tạo Model
    public function model($model) {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model();
    }

    // Hàm gọi View cơ bản (không kèm layout)
    public function view($view, $data = []) {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die("Lỗi: File view '" . $view . ".php' không tồn tại!");
        }
    }
    protected function render(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new RuntimeException('Không tìm thấy view: ' . $view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/main.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path, true, 303);
        exit;
    }

    protected function verifyCsrfToken(): bool
    {
        return Csrf::verify($_POST['_csrf'] ?? null);
    }
}
?>
