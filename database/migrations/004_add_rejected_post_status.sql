-- Migration 004: Bổ sung trạng thái 'rejected' và đặt mặc định 'pending' cho bảng posts
ALTER TABLE posts MODIFY COLUMN status ENUM('pending', 'published', 'rejected', 'hidden') NOT NULL DEFAULT 'pending';
