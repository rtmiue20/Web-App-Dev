<?php
require_once __DIR__ . '/../helpers/Auth.php';

/**
 * Middleware AuthMiddleware - Bảo vệ các route yêu cầu đã đăng nhập và có quyền từ Member trở lên
 */
class AuthMiddleware
{
    public static function handle(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: ' . BASE_URL . '/login', true, 303);
            return false;
        }

        // Nếu tài khoản bị gán quyền 'guest' (chỉ xem), không cho phép đăng/sửa/xóa bài
        $uri = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (Auth::isGuest() && !str_contains($uri, '/logout') && !str_contains($uri, '/profile')) {
            http_response_code(403);
            $errorFile = __DIR__ . '/../views/errors/403.php';
            if (file_exists($errorFile)) {
                require $errorFile;
            } else {
                echo '403 Forbidden - Tài khoản Guest chỉ có quyền xem nội dung.';
            }
            return false;
        }

        return true;
    }
}
