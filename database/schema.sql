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
  bio           VARCHAR(500) NULL,
  avatar_path   VARCHAR(255) NULL,
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

-- ==============================================================================
-- FORUM DÃ NGOẠI - TỔNG HỢP CÁC STORED PROCEDURES (ROUTINES)
-- Tương thích: MariaDB / MySQL (XAMPP)
-- ==============================================================================

USE outdoor_forum;

DELIMITER $$

-- ==============================================================================
-- 1. CHUYÊN MỤC (CATEGORIES)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_category_list$$
CREATE PROCEDURE sp_category_list()
BEGIN
    SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    GROUP BY c.id, c.name, c.description
    ORDER BY c.id ASC;
END$$

DROP PROCEDURE IF EXISTS sp_category_get$$
CREATE PROCEDURE sp_category_get(IN p_id INT)
BEGIN
    SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    WHERE c.id = p_id
    GROUP BY c.id, c.name, c.description
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_category_get_by_name$$
CREATE PROCEDURE sp_category_get_by_name(IN p_name VARCHAR(100), IN p_exclude_id INT)
BEGIN
    IF p_exclude_id IS NOT NULL THEN
        SELECT id, name, description
        FROM categories
        WHERE LOWER(TRIM(name)) = LOWER(TRIM(p_name)) AND id != p_exclude_id
        LIMIT 1;
    ELSE
        SELECT id, name, description
        FROM categories
        WHERE LOWER(TRIM(name)) = LOWER(TRIM(p_name))
        LIMIT 1;
    END IF;
END$$

DROP PROCEDURE IF EXISTS sp_category_create$$
CREATE PROCEDURE sp_category_create(IN p_name VARCHAR(100), IN p_description VARCHAR(255))
BEGIN
    INSERT INTO categories (name, description) VALUES (p_name, p_description);
    SELECT LAST_INSERT_ID() AS category_id;
END$$

DROP PROCEDURE IF EXISTS sp_category_update$$
CREATE PROCEDURE sp_category_update(IN p_id INT, IN p_name VARCHAR(100), IN p_description VARCHAR(255))
BEGIN
    UPDATE categories
    SET name = p_name,
        description = p_description
    WHERE id = p_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$

DROP PROCEDURE IF EXISTS sp_category_delete$$
CREATE PROCEDURE sp_category_delete(IN p_id INT)
BEGIN
    DELETE FROM categories WHERE id = p_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$

DROP PROCEDURE IF EXISTS sp_category_count_posts$$
CREATE PROCEDURE sp_category_count_posts(IN p_id INT)
BEGIN
    SELECT COUNT(*) AS total FROM posts WHERE category_id = p_id;
END$$

DROP PROCEDURE IF EXISTS sp_category_count$$
CREATE PROCEDURE sp_category_count()
BEGIN
    SELECT COUNT(*) AS total FROM categories;
END$$


-- ==============================================================================
-- 2. NGƯỜI DÙNG & XÁC THỰC (USERS & AUTH)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_user_register$$
CREATE PROCEDURE sp_user_register(
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(120),
    IN p_password_hash VARCHAR(255)
)
BEGIN
    DECLARE v_role_id INT;
    SELECT id INTO v_role_id FROM roles WHERE name = 'member' LIMIT 1;
    IF v_role_id IS NULL THEN
        SET v_role_id = 1;
    END IF;
    
    INSERT INTO users (username, email, password_hash, role_id, created_at)
    VALUES (p_username, p_email, p_password_hash, v_role_id, NOW());
    
    SELECT LAST_INSERT_ID() AS user_id;
END$$

DROP PROCEDURE IF EXISTS sp_user_get_by_email$$
CREATE PROCEDURE sp_user_get_by_email(IN p_email VARCHAR(120))
BEGIN
    SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
           u.bio, u.avatar_path, u.created_at
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    WHERE u.email = p_email
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_user_get_by_id$$
CREATE PROCEDURE sp_user_get_by_id(IN p_id INT)
BEGIN
    SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
           u.bio, u.avatar_path, u.created_at
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    WHERE u.id = p_id
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_user_update_profile$$
CREATE PROCEDURE sp_user_update_profile(
    IN p_user_id INT,
    IN p_username VARCHAR(50),
    IN p_bio VARCHAR(500),
    IN p_avatar_path VARCHAR(255)
)
BEGIN
    UPDATE users
    SET username = p_username, bio = p_bio, avatar_path = p_avatar_path
    WHERE id = p_user_id;
END$$

DROP PROCEDURE IF EXISTS sp_user_change_password$$
CREATE PROCEDURE sp_user_change_password(
    IN p_user_id INT,
    IN p_password_hash VARCHAR(255)
)
BEGIN
    UPDATE users SET password_hash = p_password_hash WHERE id = p_user_id;
END$$

DROP PROCEDURE IF EXISTS sp_user_exists$$
CREATE PROCEDURE sp_user_exists(IN p_email VARCHAR(120), IN p_username VARCHAR(50))
BEGIN
    SELECT EXISTS (
        SELECT 1 FROM users WHERE email = p_email OR username = p_username
    ) AS is_exists;
END$$

DROP PROCEDURE IF EXISTS sp_user_count$$
CREATE PROCEDURE sp_user_count()
BEGIN
    SELECT COUNT(*) AS total FROM users;
END$$

DROP PROCEDURE IF EXISTS sp_user_list$$
CREATE PROCEDURE sp_user_list()
BEGIN
    SELECT u.id, u.username, u.email, u.role_id, r.name AS role_name, u.created_at,
           COUNT(p.id) AS total_posts
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    LEFT JOIN posts p  ON p.user_id = u.id
    GROUP BY u.id, u.username, u.email, u.role_id, r.name, u.created_at
    ORDER BY u.id ASC;
END$$


-- ==============================================================================
-- 3. BÀI VIẾT (POSTS)
-- ==============================================================================

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

DROP PROCEDURE IF EXISTS sp_post_delete$$
CREATE PROCEDURE sp_post_delete(IN p_id INT)
BEGIN
    DELETE FROM posts WHERE id = p_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$

DROP PROCEDURE IF EXISTS sp_post_count$$
CREATE PROCEDURE sp_post_count()
BEGIN
    SELECT COUNT(*) AS total FROM posts;
END$$

DROP PROCEDURE IF EXISTS sp_post_search$$
CREATE PROCEDURE sp_post_search(IN p_keyword VARCHAR(255), IN p_category_id INT)
BEGIN
    SET @pattern = CONCAT('%', TRIM(p_keyword), '%');
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
      AND (p.title LIKE @pattern OR p.content LIKE @pattern OR p.location LIKE @pattern)
    ORDER BY p.created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_post_search_suggest$$
CREATE PROCEDURE sp_post_search_suggest(IN p_keyword VARCHAR(100))
BEGIN
    SET @pattern = CONCAT('%', TRIM(p_keyword), '%');
    SELECT id, title, location
    FROM posts
    WHERE title LIKE @pattern OR location LIKE @pattern
    ORDER BY created_at DESC
    LIMIT 8;
END$$


-- ==============================================================================
-- 4. BÌNH LUẬN (COMMENTS)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_comment_list$$
CREATE PROCEDURE sp_comment_list(IN p_post_id INT)
BEGIN
    SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = p_post_id
    ORDER BY c.created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_comment_create$$
CREATE PROCEDURE sp_comment_create(IN p_post_id INT, IN p_user_id INT, IN p_content TEXT)
BEGIN
    INSERT INTO comments (post_id, user_id, content, created_at)
    VALUES (p_post_id, p_user_id, p_content, NOW());
    
    SELECT LAST_INSERT_ID() AS comment_id;
END$$

DROP PROCEDURE IF EXISTS sp_comment_delete$$
CREATE PROCEDURE sp_comment_delete(IN p_id INT, IN p_user_id INT, IN p_is_admin TINYINT)
BEGIN
    DELETE FROM comments 
    WHERE id = p_id AND (p_is_admin = 1 OR user_id = p_user_id);
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

DROP PROCEDURE IF EXISTS sp_comment_count_by_post$$
CREATE PROCEDURE sp_comment_count_by_post(IN p_post_id INT)
BEGIN
    SELECT COUNT(*) AS total FROM comments WHERE post_id = p_post_id;
END$$


-- ==============================================================================
-- 5. LƯỢT THÍCH (POST LIKES)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_toggle_like$$
DROP PROCEDURE IF EXISTS sp_post_toggle_like$$
CREATE PROCEDURE sp_post_toggle_like(IN p_post_id INT, IN p_user_id INT)
BEGIN
    DECLARE v_liked INT DEFAULT 0;
    
    IF EXISTS (SELECT 1 FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id) THEN
        DELETE FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id;
        SET v_liked = 0;
    ELSE
        INSERT INTO post_likes (post_id, user_id) VALUES (p_post_id, p_user_id);
        SET v_liked = 1;
    END IF;
    
    SELECT COUNT(*) AS likes, v_liked AS is_liked 
    FROM post_likes 
    WHERE post_id = p_post_id;
END$$

-- Giữ sp_toggle_like để tương thích ngược
CREATE PROCEDURE sp_toggle_like(IN p_post_id INT, IN p_user_id INT)
BEGIN
    CALL sp_post_toggle_like(p_post_id, p_user_id);
END$$

DROP PROCEDURE IF EXISTS sp_post_is_liked$$
CREATE PROCEDURE sp_post_is_liked(IN p_post_id INT, IN p_user_id INT)
BEGIN
    SELECT EXISTS (
        SELECT 1 FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id
    ) AS is_liked;
END$$


-- ==============================================================================
-- 6. THẺ BÀI VIẾT (TAGS)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_tag_find_or_create$$
CREATE PROCEDURE sp_tag_find_or_create(IN p_name VARCHAR(50))
BEGIN
    DECLARE v_id INT DEFAULT NULL;
    DECLARE v_clean_name VARCHAR(50);
    SET v_clean_name = LOWER(TRIM(p_name));
    
    SELECT id INTO v_id FROM tags WHERE LOWER(name) = v_clean_name LIMIT 1;
    
    IF v_id IS NULL THEN
        INSERT INTO tags (name) VALUES (v_clean_name);
        SET v_id = LAST_INSERT_ID();
    END IF;
    
    SELECT v_id AS tag_id;
END$$

DROP PROCEDURE IF EXISTS sp_post_tags_get$$
CREATE PROCEDURE sp_post_tags_get(IN p_post_id INT)
BEGIN
    SELECT t.id, t.name
    FROM post_tags pt
    JOIN tags t ON t.id = pt.tag_id
    WHERE pt.post_id = p_post_id
    ORDER BY t.name;
END$$

DROP PROCEDURE IF EXISTS sp_post_tag_add$$
CREATE PROCEDURE sp_post_tag_add(IN p_post_id INT, IN p_tag_name VARCHAR(50))
BEGIN
    DECLARE v_tag_id INT DEFAULT NULL;
    DECLARE v_clean_name VARCHAR(50);
    SET v_clean_name = LOWER(TRIM(p_tag_name));
    
    SELECT id INTO v_tag_id FROM tags WHERE LOWER(name) = v_clean_name LIMIT 1;
    IF v_tag_id IS NULL THEN
        INSERT INTO tags (name) VALUES (v_clean_name);
        SET v_tag_id = LAST_INSERT_ID();
    END IF;
    
    INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (p_post_id, v_tag_id);
    SELECT v_tag_id AS tag_id;
END$$

DROP PROCEDURE IF EXISTS sp_post_tag_remove$$
CREATE PROCEDURE sp_post_tag_remove(IN p_post_id INT, IN p_tag_id INT)
BEGIN
    DELETE FROM post_tags WHERE post_id = p_post_id AND tag_id = p_tag_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$


-- ==============================================================================
-- 7. THỐNG KÊ & DASHBOARD (STATISTICS & REPORTS)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_dashboard_stats$$
CREATE PROCEDURE sp_dashboard_stats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM posts) AS total_posts,
        (SELECT COUNT(*) FROM categories) AS total_categories,
        (SELECT COUNT(*) FROM comments) AS total_comments;
END$$

DROP PROCEDURE IF EXISTS lk_posts_incategory$$
CREATE PROCEDURE lk_posts_incategory()
BEGIN
    SELECT c.id, c.name AS category, COUNT(p.id) AS total_posts
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    GROUP BY c.id, c.name
    ORDER BY total_posts DESC;
END$$

DROP PROCEDURE IF EXISTS lk_posts_byuser$$
CREATE PROCEDURE lk_posts_byuser()
BEGIN
    SELECT u.id, u.username, COUNT(p.id) AS total_posts
    FROM users u
    LEFT JOIN posts p ON p.user_id = u.id
    GROUP BY u.id, u.username
    ORDER BY total_posts DESC;
END$$

DELIMITER ;


-- ===== (Tuỳ chọn) Cho máy khác trong LAN kết nối DB =====
-- CREATE USER 'team'@'%' IDENTIFIED BY 'doi_mat_khau';
-- GRANT ALL PRIVILEGES ON outdoor_forum.* TO 'team'@'%' ;
-- FLUSH PRIVILEGES;
