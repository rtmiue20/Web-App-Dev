<?php

class Session {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'samesite' => 'Lax',
                'path' => '/',
            ]);
            session_start();
        }
    }

    public static function set($key, $value) {
        self::init();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        self::init();
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        self::init();
        unset($_SESSION[$key]);
    }

    public static function regenerate() {
        self::init();
        if (!session_regenerate_id(true)) {
            throw new RuntimeException('Không thể tạo lại session ID.');
        }
    }

    public static function flash($key, $value = null) {
        self::init();
        $flashKey = '_flash_' . $key;

        if (func_num_args() > 1) {
            $_SESSION[$flashKey] = $value;
            return null;
        }

        $message = $_SESSION[$flashKey] ?? null;
        unset($_SESSION[$flashKey]);
        return $message;
    }

    public static function destroy() {
        if (session_status() === PHP_SESSION_NONE) {
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        if (!session_destroy()) {
            throw new RuntimeException('Không thể hủy session.');
        }
    }
}
