<?php
declare(strict_types=1);

/** Uniform JSON envelope: {"success": bool, "data": mixed, "message": string}. */
final class Response
{
    public static function json(bool|array $success, mixed $data = null, string $message = '', int $status = 200): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (is_array($success)) {
            $payload = $success;
            if (is_int($data) && $status === 200) {
                $status = $data;
            }
            if (!array_key_exists('success', $payload)) {
                $payload['success'] = ($status >= 200 && $status < 300);
            }
            if (!array_key_exists('data', $payload)) {
                $payload['data'] = null;
            }
            if (!array_key_exists('message', $payload)) {
                $payload['message'] = '';
            }
        } else {
            $payload = [
                'success' => $success,
                'data'    => $data,
                'message' => $message,
            ];
        }

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-store');
        }

        echo json_encode(
            $payload,
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
