<?php
require_once __DIR__ . '/../helpers/Auth.php';

/**
 * Middleware AdminMiddleware - Bảo vệ các chức năng Quản trị hệ thống cao nhất (RBAC: chỉ admin)
 */
class AdminMiddleware
{
    public static function handle(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Vui lòng đăng nhập bằng tài khoản Quản trị viên để tiếp tục.');
            header('Location: ' . BASE_URL . '/login', true, 303);
            return false;
        }

        if (Auth::isAdmin()) {
            return true;
        }

        http_response_code(403);
        $errorFile = __DIR__ . '/../views/errors/403.php';
        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            echo '403 Forbidden - Bạn không có quyền Quản trị viên (Admin) để truy cập chức năng này.';
        }
        return false;
    }
}
