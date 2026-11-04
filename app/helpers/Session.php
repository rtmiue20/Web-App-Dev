<?php

/**
 * Quản lý Session an toàn (Chống Session Fixation, Session Hijacking & XSS đọc Cookie).
 */
class Session
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                $savePath = session_save_path();
                if (PHP_SAPI === 'cli' || $savePath === '' || !is_dir($savePath) || !is_writable($savePath)) {
                    $fallbackDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
                    if (!is_dir($fallbackDir)) {
                        @mkdir($fallbackDir, 0755, true);
                    }
                    if (is_dir($fallbackDir)) {
                        session_save_path($fallbackDir);
                    }
                }

                ini_set('session.use_only_cookies', '1');
                ini_set('session.use_strict_mode', '1');
                ini_set('session.cookie_httponly', '1');

                $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);

                session_set_cookie_params([
                    'lifetime' => 0,
                    'path'     => '/',
                    'domain'   => '',
                    'secure'   => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);

                session_start();
            } else {
                $_SESSION = $_SESSION ?? [];
            }
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::init();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::init();
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::init();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        self::init();

        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return $value;
        }

        if (isset($_SESSION['_flash'][$key])) {
            $flash = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $flash;
        }

        return null;
    }

    public static function destroy(): void
    {
        self::init();
        $_SESSION = [];
        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                (bool)($params['secure'] ?? false),
                (bool)($params['httponly'] ?? true)
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    public static function regenerate(): void
    {
        self::init();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }
}
