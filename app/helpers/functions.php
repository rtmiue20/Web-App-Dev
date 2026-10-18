<?php
declare(strict_types=1);

/** Escape for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
