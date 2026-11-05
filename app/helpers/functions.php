<?php
declare(strict_types=1);

/** Escape for HTML output (chống XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Escape for HTML attribute context. */
function e_attr(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/** Escape for JavaScript inline string context. */
function e_js(mixed $value): string
{
    return json_encode((string) ($value ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?: '""';
}

/**
 * Sanitize URL để chống XSS qua lược đồ nguy hiểm (`javascript:`, `data:`, `vbscript:`).
 * Chỉ cho phép `http://`, `https://`, hoặc đường dẫn tương đối an toàn.
 */
function safe_url(?string $url, string $default = '#'): string
{
    $clean = trim((string) ($url ?? ''));
    if ($clean === '') {
        return $default;
    }
    // Loại bỏ các ký tự điều khiển ASCII (0x00-0x1F, 0x7F) thường dùng để bypass filter
    $normalized = preg_replace('/[\x00-\x20\x7F]+/', '', $clean) ?? '';
    if (preg_match('/\A(javascript|data|vbscript|file):/i', $normalized)) {
        return $default;
    }
    if (preg_match('/\Ahttps?:\/\//i', $clean) || str_starts_with($clean, '/') || !str_contains($clean, ':')) {
        return e($clean);
    }
    return $default;
}

function base_url(string $path = ''): string
{
    $base = defined('BASE_URL') ? BASE_URL : '';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function upload_url(?string $path): ?string
{
    return $path ? base_url(ltrim($path, '/')) : null;
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function format_date(?string $datetime, string $format = 'd/m/Y'): string
{
    if (!$datetime) {
        return '';
    }
    $timestamp = strtotime($datetime);
    return $timestamp ? date($format, $timestamp) : '';
}

function time_ago(?string $datetime): string
{
    $timestamp = $datetime ? strtotime($datetime) : false;
    if (!$timestamp) {
        return '';
    }
    $diff = time() - $timestamp;
    return match (true) {
        $diff < 60 => 'vừa xong',
        $diff < 3600 => floor($diff / 60) . ' phút trước',
        $diff < 86400 => floor($diff / 3600) . ' giờ trước',
        $diff < 604800 => floor($diff / 86400) . ' ngày trước',
        default => date('d/m/Y', $timestamp),
    };
}
