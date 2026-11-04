<?php
/**
 * Cấu hình kết nối MariaDB.
 * File này nằm trong .gitignore: mỗi thành viên tự sửa theo máy mình.
 * Mẫu để commit: config/database.example.php
 */
return [
    'host'    => '127.0.0.1',   // máy host DB: dùng IP Tailscale của máy host thay cho localhost
    'port'    => 3306,
    'name'    => 'outdoor_forum',
    'user'    => 'root',        // XAMPP mặc định là root
    'pass'    => '049206',      // Mật khẩu root MariaDB
    'charset' => 'utf8mb4',
];
