-- Forum dã ngoại (bản gọn) — MariaDB / XAMPP
-- Quy trình: tạo DB -> tạo bảng -> thiết lập cột -> tạo quan hệ (PK/FK) -> routines
-- Gồm 8 bảng nghiệp vụ và bảng roles dùng để phân quyền.

CREATE DATABASE IF NOT EXISTS outdoor_forum
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE outdoor_forum;

-- ===== Người 1: roles, users, categories =====
CREATE TABLE roles (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO roles (name) VALUES ('member'), ('admin');

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,          -- PK
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role_id       INT UNSIGNED NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE categories (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,            -- PK
  name        VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255)
) ENGINE=InnoDB;

-- ===== Người 2: posts, post_images =====
CREATE TABLE posts (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,            -- PK
  user_id     INT UNSIGNED NOT NULL,                              -- FK -> users
  category_id INT UNSIGNED NOT NULL,                              -- FK -> categories
  title       VARCHAR(200) NOT NULL,
  content     TEXT NOT NULL,
  location    VARCHAR(150),
  lat         DECIMAL(10, 7) NULL,
  lng         DECIMAL(10, 7) NULL,
  status      ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'published',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE post_images (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,              -- PK
  post_id   INT UNSIGNED NOT NULL,                                -- FK -> posts
  image_url VARCHAR(255) NOT NULL,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Người 3: comments, post_likes =====
CREATE TABLE comments (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,             -- PK
  post_id    INT UNSIGNED NOT NULL,                               -- FK -> posts
  user_id    INT UNSIGNED NOT NULL,                               -- FK -> users
  content    TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE post_likes (
  post_id INT UNSIGNED NOT NULL,                                  -- PK + FK -> posts
  user_id INT UNSIGNED NOT NULL,                                  -- PK + FK -> users
  PRIMARY KEY (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Người 4: tags, post_tags =====
CREATE TABLE tags (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,                   -- PK
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE post_tags (
  post_id INT UNSIGNED NOT NULL,                                  -- PK + FK -> posts
  tag_id  INT UNSIGNED NOT NULL,                                  -- PK + FK -> tags
  PRIMARY KEY (post_id, tag_id),
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id)  REFERENCES tags(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Dữ liệu mẫu (để test routines) =====
INSERT INTO users (username, email, password_hash, role_id) VALUES
  ('admin', 'admin@forum.local', 'hash_demo', 2),
  ('minh',  'minh@forum.local',  'hash_demo', 1),
  ('lan',   'lan@forum.local',   'hash_demo', 1);
INSERT INTO categories (name, description) VALUES
  ('Cắm trại', 'Kinh nghiệm cắm trại'),
  ('Trekking', 'Leo núi, đi rừng'),
  ('Dụng cụ',  'Review đồ dã ngoại');
INSERT INTO posts (user_id, category_id, title, content, location) VALUES
  (2, 1, 'Cắm trại Đà Lạt', 'Chia sẻ kinh nghiệm...', 'Đà Lạt'),
  (2, 2, 'Trekking Tà Năng', 'Lịch trình 2 ngày...', 'Lâm Đồng'),
  (3, 1, 'Chọn lều cho người mới', 'Nên chọn lều 2 lớp...', NULL);

-- ===== ROUTINES =====
DELIMITER $$

-- Thống kê số bài viết của mỗi chuyên mục (không tham số)
CREATE PROCEDURE lk_posts_incategory()
BEGIN
  SELECT c.id, c.name AS category, COUNT(p.id) AS total_posts
  FROM categories c
  LEFT JOIN posts p ON p.category_id = c.id
  GROUP BY c.id, c.name
  ORDER BY total_posts DESC;
END$$

-- Thống kê số bài viết của mỗi user (không tham số)
CREATE PROCEDURE lk_posts_byuser()
BEGIN
  SELECT u.id, u.username, COUNT(p.id) AS total_posts
  FROM users u
  LEFT JOIN posts p ON p.user_id = u.id
  GROUP BY u.id, u.username
  ORDER BY total_posts DESC;
END$$

-- Lấy danh sách bài viết (sp_post_list)
DROP PROCEDURE IF EXISTS sp_post_list$$
CREATE PROCEDURE sp_post_list(IN p_category_id INT)
BEGIN
  SELECT p.*, 
         u.username, 
         c.name AS category_name,
         (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
         (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes,
         (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
  FROM posts p
  JOIN users u      ON u.id = p.user_id
  JOIN categories c ON c.id = p.category_id
  WHERE (p_category_id IS NULL OR p.category_id = p_category_id)
  ORDER BY p.created_at DESC;
END$$

-- Lấy chi tiết bài viết (sp_post_get)
DROP PROCEDURE IF EXISTS sp_post_get$$
CREATE PROCEDURE sp_post_get(IN p_id INT)
BEGIN
  SELECT p.*, 
         u.username, 
         c.name AS category_name,
         (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
         (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes
  FROM posts p
  JOIN users u      ON u.id = p.user_id
  JOIN categories c ON c.id = p.category_id
  WHERE p.id = p_id;
END$$

-- Thêm bài viết mới (sp_post_create)
DROP PROCEDURE IF EXISTS sp_post_create$$
CREATE PROCEDURE sp_post_create(
  IN p_user_id INT,
  IN p_category_id INT,
  IN p_title VARCHAR(200),
  IN p_content TEXT,
  IN p_location VARCHAR(150),
  IN p_lat DECIMAL(10, 7),
  IN p_lng DECIMAL(10, 7),
  IN p_status VARCHAR(20)
)
BEGIN
  INSERT INTO posts (user_id, category_id, title, content, location, lat, lng, status, created_at)
  VALUES (p_user_id, p_category_id, p_title, p_content, p_location, p_lat, p_lng, IFNULL(p_status, 'published'), NOW());
  SELECT LAST_INSERT_ID() AS post_id;
END$$

-- Cập nhật bài viết (sp_post_update)
DROP PROCEDURE IF EXISTS sp_post_update$$
CREATE PROCEDURE sp_post_update(
  IN p_id INT,
  IN p_category_id INT,
  IN p_title VARCHAR(200),
  IN p_content TEXT,
  IN p_location VARCHAR(150),
  IN p_lat DECIMAL(10, 7),
  IN p_lng DECIMAL(10, 7),
  IN p_status VARCHAR(20)
)
BEGIN
  UPDATE posts
  SET category_id = p_category_id,
      title = p_title,
      content = p_content,
      location = p_location,
      lat = p_lat,
      lng = p_lng,
      status = IFNULL(p_status, status)
  WHERE id = p_id;
  SELECT ROW_COUNT() AS affected_rows;
END$$

-- Xóa bài viết (sp_post_delete)
DROP PROCEDURE IF EXISTS sp_post_delete$$
CREATE PROCEDURE sp_post_delete(IN p_id INT)
BEGIN
  DELETE FROM posts WHERE id = p_id;
  SELECT ROW_COUNT() AS affected_rows;
END$$

-- Thích / bỏ thích bài viết
CREATE PROCEDURE sp_toggle_like(IN p_post_id INT, IN p_user_id INT)
BEGIN
  IF EXISTS (SELECT 1 FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id) THEN
    DELETE FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id;
  ELSE
    INSERT INTO post_likes (post_id, user_id) VALUES (p_post_id, p_user_id);
  END IF;
  SELECT COUNT(*) AS likes FROM post_likes WHERE post_id = p_post_id;
END$$

DELIMITER ;

-- ===== (Tuỳ chọn) Cho máy khác trong LAN kết nối DB =====
-- CREATE USER 'team'@'%' IDENTIFIED BY 'doi_mat_khau';
-- GRANT ALL PRIVILEGES ON outdoor_forum.* TO 'team'@'%' ;
-- FLUSH PRIVILEGES;
