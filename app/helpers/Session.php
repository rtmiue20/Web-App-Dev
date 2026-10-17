<?php

class Session
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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
        session_unset();
        session_destroy();
    }

    public static function regenerate(): void
    {
        self::init();
        session_regenerate_id(true);
    }
}

