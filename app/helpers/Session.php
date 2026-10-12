<?php

class Session {
    /**
     * Khởi tạo Session nếu chưa bắt đầu
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Gán giá trị vào Session
     */
    public static function set($key, $value) {
        self::init();
        $_SESSION[$key] = $value;
    }

    /**
     * Lấy giá trị từ Session
     */
    public static function get($key, $default = null) {
        self::init();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * Kiểm tra sự tồn tại của phím trong Session
     */
    public static function has($key) {
        self::init();
        return isset($_SESSION[$key]);
    }

    /**
     * Xóa 1 giá trị trong Session
     */
    public static function remove($key) {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Hủy toàn bộ Session
     */
    public static function destroy() {
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
        }
    }
}