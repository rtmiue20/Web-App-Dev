<?php
declare(strict_types=1);

/** Uniform JSON envelope: {"success": bool, "data": mixed, "message": string}. */
final class Response
{
    public static function json(bool $success, mixed $data = null, string $message = '', int $status = 200): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
        }
        echo json_encode(
            ['success' => $success, 'data' => $data, 'message' => $message],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
        exit;
    }

    public static function ok(mixed $data = null, string $message = '', int $status = 200): void
    {
        self::json(true, $data, $message, $status);
    }

    public static function fail(string $message, int $status = 400, mixed $data = null): void
    {
        self::json(false, $data, $message, $status);
    }

    /** Restricts an API action to the given HTTP method(s). */
    public static function requireMethod(string ...$methods): void
    {
        $current = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($current, array_map('strtoupper', $methods), true)) {
            if (!headers_sent()) {
                header('Allow: ' . implode(', ', $methods));
            }
            self::fail('Phương thức không được hỗ trợ.', 405);
        }
    }
}
