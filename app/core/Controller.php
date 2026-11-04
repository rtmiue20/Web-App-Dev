<?php

/**
 * Lớp cơ sở Controller (BaseController) trong kiến trúc MVC.
 * Cung cấp các phương thức khởi tạo Model, Repository, Service, Render View và kiểm tra Bảo mật (CSRF, Auth, AJAX).
 */
class Controller
{
    public function model(string $model): object
    {
        $file = __DIR__ . '/../models/' . $model . '.php';
        if (is_file($file)) {
            require_once $file;
        }
        return new $model();
    }

    /**
     * Khởi tạo một lớp Repository / DAO trong tầng dữ liệu (app/repositories/).
     */
    protected function repository(string $repository): object
    {
        $file = __DIR__ . '/../repositories/' . $repository . '.php';
        if (is_file($file)) {
            require_once $file;
        }
        return new $repository();
    }

    /**
     * Khởi tạo một lớp Service trong tầng nghiệp vụ (app/services/).
     */
    protected function service(string $service): object
    {
        $file = __DIR__ . '/../services/' . $service . '.php';
        if (is_file($file)) {
            require_once $file;
        }
        return new $service();
    }

    public function view(string $view, array $data = []): void
    {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data, EXTR_SKIP);
            require_once $viewFile;
        } else {
            die("Lỗi: File view '" . htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . ".php' không tồn tại!");
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

    /**
     * Kiểm tra CSRF Token từ cả Form POST (_csrf) và Header AJAX (X-CSRF-Token).
     */
    protected function verifyCsrfToken(): bool
    {
        return Csrf::verify();
    }

    /**
     * Kiểm tra request hiện tại có phải là AJAX / JSON request hay không.
     */
    protected function isAjaxRequest(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains((string)$_SERVER['HTTP_ACCEPT'], 'application/json'))
            || isset($_POST['ajax']);
    }
}

if (!class_exists('BaseController', false)) {
    class_alias(Controller::class, 'BaseController');
}
