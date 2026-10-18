<?php
class Controller
{
    public function model($model) {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $data = []) {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die("Lỗi: File view '" . $view . ".php' không tồn tại!");
        }
    }

    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new RuntimeException('Không tìm thấy view: ' . $view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        $layoutPath = __DIR__ . '/../views/layouts/' . $layout . '.php';
        if (is_file($layoutPath)) {
            require $layoutPath;
        } else {
            require __DIR__ . '/../views/layouts/main.php';
        }
    }

    protected function requireLogin(): void
    {
        if (!Session::has('user')) {
            Session::flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('/login');
        }
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
