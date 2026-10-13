<?php

class AdminMiddleware
{
    public static function handle(): bool
    {
        $user = Session::get('user');
        if (!is_array($user)) {
            return AuthMiddleware::handle();
        }
        if (is_array($user) && ($user['role_name'] ?? null) === 'admin') {
            return true;
        }

        http_response_code(403);
        echo 'Bạn không có quyền truy cập chức năng này.';
        return false;
    }
}