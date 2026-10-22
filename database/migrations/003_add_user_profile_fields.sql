-- Migration 003: Thêm phần giới thiệu và ảnh đại diện cho FR02 (hồ sơ cá nhân)

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS bio VARCHAR(500) NULL AFTER role_id,
  ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) NULL AFTER bio;
