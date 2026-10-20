-- Migration 002: Thêm tọa độ địa lý (lat, lng) và trạng thái bài viết (status) cho bảng posts
-- Phục vụ FR05 (CRUD bài viết) và FR06 (bản đồ, thời tiết)

ALTER TABLE posts ADD COLUMN IF NOT EXISTS lat DECIMAL(10, 7) NULL AFTER location;
ALTER TABLE posts ADD COLUMN IF NOT EXISTS lng DECIMAL(10, 7) NULL AFTER lat;
ALTER TABLE posts ADD COLUMN IF NOT EXISTS status ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'published' AFTER lng;
