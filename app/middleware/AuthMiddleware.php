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

        // Nếu tài khoản bị gán quyền 'guest' (chỉ xem), không cho phép đăng/sửa/xóa hoặc tương tác tạo dữ liệu
        $uri = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (Auth::isGuest() && !str_contains($uri, '/logout') && !str_contains($uri, '/profile')) {
            $msg = 'Tài khoản của bạn đang ở vai trò Khách (Chỉ xem) nên chỉ có quyền xem nội dung, không thể đăng bài hoặc tạo dữ liệu mới.';
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

            if ($isAjax) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $msg]);
                return false;
            }

            Session::flash('error', $msg);
            http_response_code(403);
            $errorFile = __DIR__ . '/../views/errors/403.php';
            if (file_exists($errorFile)) {
                $title = 'Chế độ Khách (Chỉ xem)';
                $message = $msg;
                require $errorFile;
            } else {
                echo '403 Forbidden - ' . $msg;
            }
            return false;
        }

        return true;
    }
}
