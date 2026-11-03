<?php
require_once __DIR__ . '/../helpers/Auth.php';

/**
 * Middleware ModeratorMiddleware - Bảo vệ khu vực Kiểm duyệt nội dung (RBAC: moderator & admin)
 * Cho phép: Điều hành viên (moderator) và Quản trị viên (admin)
 * Từ chối: Khách (guest) và Thành viên thường (member)
 */
class ModeratorMiddleware
{
    public static function handle(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Vui lòng đăng nhập bằng tài khoản Điều hành viên hoặc Quản trị viên để tiếp tục.');
            header('Location: ' . BASE_URL . '/login', true, 303);
            return false;
        }

        if (Auth::isModerator()) {
            return true;
        }

        http_response_code(403);
        $errorFile = __DIR__ . '/../views/errors/403.php';
        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            echo '403 Forbidden - Khu vực này yêu cầu quyền Điều hành viên (Moderator) hoặc Quản trị viên (Admin).';
        }
        return false;
    }
}
