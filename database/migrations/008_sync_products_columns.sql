-- Migration 008: Đồng bộ cột user_id và is_shared cho bảng products theo đúng schema.sql
ALTER TABLE products ADD COLUMN IF NOT EXISTS user_id INT UNSIGNED NULL AFTER category_id;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_shared TINYINT(1) NOT NULL DEFAULT 1 AFTER link;

