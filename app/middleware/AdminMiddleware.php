<?php

class AdminMiddleware
{
    public static function handle(): bool
    {
        $user = Session::get('user');
        if (!is_array($user)) {
            Session::flash('error', 'Vui lòng đăng nhập bằng tài khoản Quản trị viên để tiếp tục.');
            header('Location: ' . BASE_URL . '/login', true, 303);
            return false;
        }

        $roleName = $user['role_name'] ?? null;
        $roleId = (int)($user['role_id'] ?? 0);

        if ($roleName === 'admin' || $roleId === 2) {
            return true;
        }

        http_response_code(403);
        $errorFile = __DIR__ . '/../views/errors/403.php';
        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            echo '403 Forbidden - Bạn không có quyền quản trị viên để truy cập chức năng này.';
        }
        return false;
    }
}
