<?php
/**
 * config/secrets.example.php
 * File cấu hình mẫu chứa khóa API bí mật (Secret Keys).
 *
 * HƯỚNG DẪN BẢO MẬT:
 * 1. Sao chép file này và đổi tên thành `config/secrets.php` (hoặc hệ thống sẽ tự động khởi tạo khi chạy)
 * 2. Điền Google Gemini API Key của bạn vào biến API_KEY bên dưới
 * 3. File `config/secrets.php` đã nằm trong `.gitignore` nên an toàn tuyệt đối, không bị đưa lên GitHub.
 */

const API_KEY = "YOUR_GEMINI_API_KEY_HERE"; // Thay bằng Gemini API Key của bạn

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', API_KEY);
}
