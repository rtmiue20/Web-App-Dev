<?php
$mysqldump = 'd:\\xampp\\mysql\\bin\\mysqldump.exe';
$outputFile1 = __DIR__ . '/outdoor_forum_full_infinityfree.sql';
$outputFile2 = __DIR__ . '/outdoor_forum_full_import.sql';

// Run mysqldump with utf8mb4, routines, triggers, and no create db
$cmd = "\"$mysqldump\" -h 127.0.0.1 -P 3306 -u root -p049206 --default-character-set=utf8mb4 --routines --triggers --no-create-db outdoor_forum";
exec($cmd, $lines, $ret);

if ($ret !== 0 || empty($lines)) {
    die("Error dumping database with mysqldump.\n");
}

$raw = implode("\n", $lines);

// Remove DEFINER statements and permissions that cause Access Denied on shared hosts
$sql = preg_replace('/\s*DEFINER\s*=\s*`[^`]+`@`[^`]+`/i', '', $raw);
$sql = preg_replace('/\s*DEFINER\s*=\s*[^\s*]+/i', '', $sql);
$sql = preg_replace('/\/\*!50013\s*SQL SECURITY DEFINER\s*\*\//i', '', $sql);
$sql = preg_replace('/\/\*!50013\s*\*\//i', '', $sql);
$sql = preg_replace('/SQL SECURITY DEFINER/i', '', $sql);

// Replace modern MariaDB 11/12 collation that causes '#1273 - Unknown collation' in standard MySQL
$sql = str_replace('utf8mb4_uca1400_ai_ci', 'utf8mb4_unicode_ci', $sql);

// Remove any accidental USE or CREATE DATABASE
$sql = preg_replace('/^CREATE DATABASE .*?;/mi', '', $sql);
$sql = preg_replace('/^USE .*?;/mi', '', $sql);

// Build final SQL with safe headers and footers
$header = "-- ==============================================================================\n" .
          "-- OUTDOOR FORUM - FULL DATABASE DUMP (TABLES + DATA + VIEWS + 56 ROUTINES)\n" .
          "-- Tương thích 100% với phpMyAdmin trên InfinityFree (Host: sql310.infinityfree.com)\n" .
          "-- Cơ sở dữ liệu: if0_43424066_outdoor_forum\n" .
          "-- Không chứa CREATE DATABASE / USE hay DEFINER để tránh lỗi quyền (SUPER privilege)\n" .
          "-- Bảng mã: UTF-8 (utf8mb4_unicode_ci)\n" .
          "-- ==============================================================================\n\n" .
          "SET FOREIGN_KEY_CHECKS = 0;\n" .
          "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n" .
          "SET time_zone = '+07:00';\n" .
          "SET NAMES utf8mb4;\n\n";

$footer = "\n\nSET FOREIGN_KEY_CHECKS = 1;\n" .
          "-- ==============================================================================\n" .
          "-- HOÀN TẤT IMPORT THÀNH CÔNG CHO OUTDOOR FORUM\n" .
          "-- ==============================================================================\n";

$finalSql = $header . $sql . $footer;

file_put_contents($outputFile1, $finalSql);
file_put_contents($outputFile2, $finalSql);

echo "Successfully exported " . strlen($finalSql) . " bytes to:\n";
echo "1. $outputFile1\n";
echo "2. $outputFile2\n";
