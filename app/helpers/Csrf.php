<?php

/**
 * Bảo mật chống tấn công CSRF (Cross-Site Request Forgery).
 * Hỗ trợ xác thực token từ cả Form POST (_csrf) và Header AJAX (X-CSRF-Token).
 */
class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }

        return $token;
    }

    public static function verify(mixed $token = null): bool
    {
        if ($token === null) {
            $token = $_POST['_csrf']
                ?? $_SERVER['HTTP_X_CSRF_TOKEN']
                ?? $_GET['_csrf']
                ?? null;
        }

        $knownToken = Session::get('_csrf_token');
        return is_string($token)
            && $token !== ''
            && is_string($knownToken)
            && $knownToken !== ''
            && hash_equals($knownToken, $token);
    }

    /** Làm mới CSRF token sau khi đăng nhập / đăng xuất. */
    public static function rotate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('_csrf_token', $token);
        return $token;
    }
}
