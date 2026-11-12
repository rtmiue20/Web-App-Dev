<?php
/**
 * Cấu hình chung & Khóa tích hợp Web Service (API Keys) của hệ thống Outdoor Forum.
 * Bảo mật: Tuân thủ tiêu chuẩn OWASP & GitGuardian - không hardcode trực tiếp secret tokens vào Git.
 * Các khóa API được nạp an toàn từ Biến môi trường hệ thống (System Environment Variables).
 */
return [
    'qr_api_key'         => getenv('QR_API_KEY') ?: getenv('QUICKCHART_API_KEY') ?: '',
    'quickchart_api_key' => getenv('QUICKCHART_API_KEY') ?: '',
    'imgbb_api_key'      => getenv('IMGBB_API_KEY') ?: '',
];
