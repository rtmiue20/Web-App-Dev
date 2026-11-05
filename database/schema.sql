-- Forum dã ngoại (bản gọn) — MariaDB / XAMPP
-- Quy trình: tạo DB -> tạo bảng -> thiết lập cột -> tạo quan hệ (PK/FK) -> routines
-- Gồm 8 bảng nghiệp vụ và bảng roles dùng để phân quyền.

DROP DATABASE IF EXISTS outdoor_forum;
CREATE DATABASE IF NOT EXISTS outdoor_forum
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE outdoor_forum;

-- ===== Người 1: roles, users, categories =====
CREATE TABLE roles (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO roles (name) VALUES ('member'), ('admin'), ('moderator'), ('guest');

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
  route       VARCHAR(255) NULL,                                  -- Cung đường chuyến đi
  trip_duration VARCHAR(100) NULL,                                -- Thời gian chuyến đi
  estimated_cost VARCHAR(100) NULL,                               -- Chi phí dự tính
  gear_needed TEXT NULL,                                          -- Đồ cần mang
  difficulty  ENUM('easy', 'medium', 'hard', 'extreme') NULL DEFAULT 'medium', -- Mức độ khó
  region      VARCHAR(50) NULL,                                   -- Vùng miền (Bắc, Trung, Nam, Tây Bắc, Tây Nguyên)
  activity_type VARCHAR(50) NULL,                                 -- Loại hình (trekking, camping, leo_nui, phuot, da_ngoai)
  lat         DECIMAL(10, 7) NULL,
  lng         DECIMAL(10, 7) NULL,
  status      ENUM('pending', 'published', 'rejected', 'hidden') NOT NULL DEFAULT 'pending',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FULLTEXT idx_posts_fulltext (title, content, location),
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
  parent_id  INT UNSIGNED NULL DEFAULT NULL,                      -- FK -> comments (Bình luận lồng nhau nhiều cấp)
  content    TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id)   REFERENCES posts(id)    ON DELETE CASCADE,
  FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE post_likes (
  post_id INT UNSIGNED NOT NULL,                                  -- PK + FK -> posts
  user_id INT UNSIGNED NOT NULL,                                  -- PK + FK -> users
  vote    TINYINT NOT NULL DEFAULT 1,                             -- 1: Upvote (+1), -1: Downvote (-1)
  PRIMARY KEY (post_id, user_id),
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comment_likes (
  comment_id INT UNSIGNED NOT NULL,                               -- PK + FK -> comments
  user_id    INT UNSIGNED NOT NULL,                               -- PK + FK -> users
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (comment_id, user_id),
  FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
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

-- ===== Người 5: products, post_products (Dụng cụ & Dụng cụ chuyến đi) =====
CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,                     -- PK
  category_id INT UNSIGNED NULL,                                  -- FK -> categories
  name        VARCHAR(150) NOT NULL,
  price       DECIMAL(10, 0) NOT NULL DEFAULT 0,
  image_url   VARCHAR(255) NULL,
  description TEXT NULL,
  link        TEXT NULL,
  is_shared   TINYINT(1) NOT NULL DEFAULT 1,                      -- 1: Vào kho cộng đồng, 0: Chỉ dùng riêng cho bài viết
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE post_products (
  post_id    INT UNSIGNED NOT NULL,                               -- PK + FK -> posts
  product_id INT NOT NULL,                                        -- PK + FK -> products
  PRIMARY KEY (post_id, product_id),
  FOREIGN KEY (post_id)    REFERENCES posts(id)    ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Tính năng nâng cao: bookmarks, follows, notifications, reports =====
CREATE TABLE IF NOT EXISTS post_bookmarks (
  user_id    INT UNSIGNED NOT NULL,                               -- PK + FK -> users
  post_id    INT UNSIGNED NOT NULL,                               -- PK + FK -> posts
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, post_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_follows (
  follower_id  INT UNSIGNED NOT NULL,                             -- PK + FK -> users
  following_id INT UNSIGNED NOT NULL,                             -- PK + FK -> users
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (follower_id, following_id),
  FOREIGN KEY (follower_id)  REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS category_follows (
  user_id     INT UNSIGNED NOT NULL,                              -- PK + FK -> users
  category_id INT UNSIGNED NOT NULL,                              -- PK + FK -> categories
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, category_id),
  FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,                               -- Người nhận thông báo
  actor_id   INT UNSIGNED NOT NULL,                               -- Người tương tác
  type       VARCHAR(50) NOT NULL,                                -- like_post, like_comment, comment_post, reply_comment, follow_user
  target_id  INT UNSIGNED NOT NULL,                               -- post_id, comment_id, user_id
  content    VARCHAR(255) NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reports (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT UNSIGNED NOT NULL,                              -- Người báo cáo
  target_type ENUM('post', 'comment', 'user') NOT NULL,
  target_id   INT UNSIGNED NOT NULL,
  reason      VARCHAR(100) NOT NULL,
  details     TEXT NULL,
  status      ENUM('pending', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
  resolved_by INT UNSIGNED NULL,                                  -- Mod / Admin xử lý
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NULL,
  FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
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
-- FORUM DÃ NGOẠI - CÁC SQL VIEWS CHO TRUY VẤN HAY DÙNG (VIEWS)
-- Tương thích: MariaDB / MySQL (XAMPP)
-- ==============================================================================

USE outdoor_forum;

-- 1. View chi tiết bài viết tổng hợp (tác giả, chuyên mục, ảnh bìa, điểm vote, số bình luận, điểm hot)
CREATE OR REPLACE VIEW vw_post_details AS
SELECT
    p.id,
    p.user_id,
    p.category_id,
    p.title,
    p.content,
    p.location,
    p.route,
    p.trip_duration,
    p.estimated_cost,
    p.gear_needed,
    p.difficulty,
    p.region,
    p.activity_type,
    p.lat,
    p.lng,
    p.status,
    p.created_at,
    u.username,
    u.avatar_path,
    r.name AS role_name,
    c.name AS category_name,
    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS vote_score,
    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = 1) AS upvotes,
    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = -1) AS downvotes,
    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS likes,
    (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count,
    (COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) * 2
     + (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) * 3) AS hot_score
FROM posts p
INNER JOIN users u ON u.id = p.user_id
INNER JOIN roles r ON r.id = u.role_id
INNER JOIN categories c ON c.id = p.category_id;

-- 2. View thống kê nhanh chuyên mục kèm tổng số bài, bài đã duyệt và bài chờ duyệt
CREATE OR REPLACE VIEW vw_category_summary AS
SELECT
    c.id,
    c.name,
    c.description,
    COUNT(p.id) AS post_count,
    SUM(CASE WHEN p.status = 'published' THEN 1 ELSE 0 END) AS published_count,
    SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
    MAX(p.created_at) AS latest_post_at
FROM categories c
LEFT JOIN posts p ON p.category_id = c.id
GROUP BY c.id, c.name, c.description;

-- 3. View tổng hợp thông tin thành viên kèm vai trò, số bài đăng, bình luận và điểm uy tín (karma)
CREATE OR REPLACE VIEW vw_user_summary AS
SELECT
    u.id,
    u.username,
    u.email,
    u.role_id,
    r.name AS role_name,
    u.bio,
    u.avatar_path,
    u.created_at,
    (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS total_posts,
    (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id AND p.status = 'published') AS published_posts,
    (SELECT COUNT(*) FROM comments cm WHERE cm.user_id = u.id) AS total_comments,
    COALESCE((
        SELECT SUM(pl.vote)
        FROM post_likes pl
        INNER JOIN posts up ON up.id = pl.post_id
        WHERE up.user_id = u.id
    ), 0) AS total_karma
FROM users u
INNER JOIN roles r ON r.id = u.role_id;

-- 4. View danh sách thẻ (tags) phổ biến kèm số lượng bài viết
CREATE OR REPLACE VIEW vw_popular_tags AS
SELECT
    t.id,
    t.name,
    COUNT(pt.post_id) AS post_count
FROM tags t
INNER JOIN post_tags pt ON pt.tag_id = t.id
GROUP BY t.id, t.name
ORDER BY post_count DESC, t.name ASC;


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
    SELECT id, name, description, post_count, published_count, pending_count
    FROM vw_category_summary
    ORDER BY id ASC;
END$$

DROP PROCEDURE IF EXISTS sp_category_get$$
CREATE PROCEDURE sp_category_get(IN p_id INT)
BEGIN
    SELECT id, name, description, post_count, published_count, pending_count
    FROM vw_category_summary
    WHERE id = p_id
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

DROP PROCEDURE IF EXISTS sp_user_update_role$$
CREATE PROCEDURE sp_user_update_role(
    IN p_user_id INT,
    IN p_role_id INT
)
BEGIN
    UPDATE users SET role_id = p_role_id WHERE id = p_user_id;
    SELECT ROW_COUNT() AS affected_rows;
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
    SELECT id, username, email, role_id, role_name, created_at,
           total_posts, published_posts, total_comments, total_karma
    FROM vw_user_summary
    ORDER BY id ASC;
END$$


-- ==============================================================================
-- 3. BÀI VIẾT (POSTS)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_post_list$$
CREATE PROCEDURE sp_post_list(IN p_category_id INT)
BEGIN
    SELECT *
    FROM vw_post_details
    WHERE (p_category_id IS NULL OR category_id = p_category_id)
    ORDER BY created_at DESC;
END$$

DROP PROCEDURE IF EXISTS sp_post_get$$
CREATE PROCEDURE sp_post_get(IN p_id INT)
BEGIN
    SELECT *
    FROM vw_post_details
    WHERE id = p_id
    LIMIT 1;
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
    VALUES (p_user_id, p_category_id, p_title, p_content, p_location, p_lat, p_lng, IFNULL(p_status, 'pending'), NOW());
    
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
    SELECT c.id, c.post_id, c.user_id, c.parent_id, c.content, c.created_at,
           u.username, r.name AS role_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN roles r ON r.id = u.role_id
    WHERE c.post_id = p_post_id
    ORDER BY c.created_at ASC, c.id ASC;
END$$

DROP PROCEDURE IF EXISTS sp_comment_create$$
CREATE PROCEDURE sp_comment_create(
    IN p_post_id INT,
    IN p_user_id INT,
    IN p_content TEXT,
    IN p_parent_id INT
)
BEGIN
    INSERT INTO comments (post_id, user_id, parent_id, content, created_at)
    VALUES (p_post_id, p_user_id, p_parent_id, p_content, NOW());
    
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
-- 5. LƯỢT THÍCH & VOTE (POST LIKES / UPVOTE / DOWNVOTE)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_post_vote$$
CREATE PROCEDURE sp_post_vote(IN p_post_id INT, IN p_user_id INT, IN p_vote TINYINT)
BEGIN
    DECLARE v_existing TINYINT DEFAULT NULL;
    DECLARE v_user_vote TINYINT DEFAULT 0;

    SELECT vote INTO v_existing
    FROM post_likes
    WHERE post_id = p_post_id AND user_id = p_user_id
    LIMIT 1;

    IF v_existing IS NOT NULL THEN
        IF v_existing = p_vote THEN
            DELETE FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id;
            SET v_user_vote = 0;
        ELSE
            UPDATE post_likes SET vote = p_vote WHERE post_id = p_post_id AND user_id = p_user_id;
            SET v_user_vote = p_vote;
        END IF;
    ELSE
        INSERT INTO post_likes (post_id, user_id, vote) VALUES (p_post_id, p_user_id, p_vote);
        SET v_user_vote = p_vote;
    END IF;

    SELECT
        COALESCE(SUM(vote), 0) AS score,
        COUNT(CASE WHEN vote = 1 THEN 1 END) AS upvotes,
        COUNT(CASE WHEN vote = -1 THEN 1 END) AS downvotes,
        v_user_vote AS user_vote
    FROM post_likes
    WHERE post_id = p_post_id;
END$$

DROP PROCEDURE IF EXISTS sp_toggle_like$$
DROP PROCEDURE IF EXISTS sp_post_toggle_like$$
CREATE PROCEDURE sp_post_toggle_like(IN p_post_id INT, IN p_user_id INT)
BEGIN
    DECLARE v_liked INT DEFAULT 0;
    
    IF EXISTS (SELECT 1 FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id AND vote = 1) THEN
        DELETE FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id;
        SET v_liked = 0;
    ELSE
        INSERT INTO post_likes (post_id, user_id, vote) VALUES (p_post_id, p_user_id, 1)
        ON DUPLICATE KEY UPDATE vote = 1;
        SET v_liked = 1;
    END IF;
    
    SELECT COALESCE(SUM(vote), 0) AS likes, v_liked AS is_liked 
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
        SELECT 1 FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id AND vote = 1
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
-- 7. THỐNG KÊ & DASHBOARD (STATISTICS & REPORTS - STORED PROCEDURES)
-- ==============================================================================

-- 7.1 Thống kê tổng quan toàn hệ thống cho Admin Dashboard & Trang chủ
DROP PROCEDURE IF EXISTS sp_dashboard_stats$$
CREATE PROCEDURE sp_dashboard_stats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM posts) AS total_posts,
        (SELECT COUNT(*) FROM posts WHERE status = 'published') AS published_posts,
        (SELECT COUNT(*) FROM posts WHERE status = 'pending') AS pending_posts,
        (SELECT COUNT(*) FROM categories) AS total_categories,
        (SELECT COUNT(*) FROM comments) AS total_comments,
        (SELECT COUNT(*) FROM products WHERE is_shared = 1) AS total_products;
END$$

-- 7.2 Thống kê Top bài viết nổi bật nhất (theo điểm tương tác hot_score, vote_score, số bình luận)
DROP PROCEDURE IF EXISTS sp_stats_top_posts$$
CREATE PROCEDURE sp_stats_top_posts(IN p_limit INT)
BEGIN
    DECLARE v_limit INT DEFAULT 5;
    SET v_limit = IF(p_limit IS NULL OR p_limit <= 0, 5, p_limit);

    SELECT
        id,
        user_id,
        category_id,
        title,
        location,
        status,
        created_at,
        username,
        category_name,
        image_url,
        vote_score,
        upvotes,
        downvotes,
        likes,
        comments_count,
        hot_score
    FROM vw_post_details
    WHERE status = 'published'
    ORDER BY hot_score DESC, vote_score DESC, comments_count DESC, created_at DESC
    LIMIT v_limit;
END$$

-- 7.3 Thống kê số lượng bài viết theo từng danh mục (kèm số bài đã duyệt & chờ duyệt)
DROP PROCEDURE IF EXISTS sp_stats_posts_by_category$$
CREATE PROCEDURE sp_stats_posts_by_category()
BEGIN
    SELECT
        id,
        name,
        name AS category,
        description,
        post_count AS total_posts,
        post_count,
        published_count,
        pending_count,
        latest_post_at
    FROM vw_category_summary
    ORDER BY post_count DESC, id ASC;
END$$

-- Giữ lk_posts_incategory để tương thích ngược
DROP PROCEDURE IF EXISTS lk_posts_incategory$$
CREATE PROCEDURE lk_posts_incategory()
BEGIN
    CALL sp_stats_posts_by_category();
END$$

-- 7.4 Thống kê Top thành viên đóng góp nhiều bài viết & tương tác nhất
DROP PROCEDURE IF EXISTS sp_stats_top_users$$
CREATE PROCEDURE sp_stats_top_users(IN p_limit INT)
BEGIN
    DECLARE v_limit INT DEFAULT 5;
    SET v_limit = IF(p_limit IS NULL OR p_limit <= 0, 5, p_limit);

    SELECT
        id,
        username,
        email,
        role_name,
        avatar_path,
        created_at,
        total_posts,
        published_posts,
        total_comments,
        total_karma
    FROM vw_user_summary
    ORDER BY total_posts DESC, total_karma DESC, total_comments DESC, id ASC
    LIMIT v_limit;
END$$

-- Giữ lk_posts_byuser để tương thích ngược
DROP PROCEDURE IF EXISTS lk_posts_byuser$$
CREATE PROCEDURE lk_posts_byuser()
BEGIN
    CALL sp_stats_top_users(10);
END$$

-- 7.5 Thống kê số bài viết theo từng trạng thái kiểm duyệt (published, pending, rejected, hidden)
DROP PROCEDURE IF EXISTS sp_stats_posts_by_status$$
CREATE PROCEDURE sp_stats_posts_by_status()
BEGIN
    SELECT
        COUNT(*) AS total_all,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS total_published,
        SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS total_pending,
        SUM(CASE WHEN status = 'rejected'  THEN 1 ELSE 0 END) AS total_rejected,
        SUM(CASE WHEN status = 'hidden'    THEN 1 ELSE 0 END) AS total_hidden
    FROM posts;
END$$

DROP PROCEDURE IF EXISTS sp_stats_daily_activity$$
CREATE PROCEDURE sp_stats_daily_activity(IN p_days INT)
BEGIN
    DECLARE v_days INT DEFAULT 7;
    SET v_days = IF(p_days IS NULL OR p_days <= 0, 7, IF(p_days > 30, 30, p_days));

    SELECT
        d.stat_date,
        DATE_FORMAT(d.stat_date, '%d/%m') AS date_label,
        COALESCE(u.new_users, 0) AS new_users,
        COALESCE(p.new_posts, 0) AS new_posts,
        COALESCE(c.new_comments, 0) AS new_comments
    FROM (
        SELECT DATE_SUB(CURDATE(), INTERVAL seq.n DAY) AS stat_date
        FROM (
            SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
            UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
            UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
            UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
            UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
        ) AS seq
        WHERE seq.n < v_days
    ) AS d
    LEFT JOIN (
        SELECT DATE(created_at) AS reg_date, COUNT(*) AS new_users
        FROM users
        GROUP BY DATE(created_at)
    ) AS u ON u.reg_date = d.stat_date
    LEFT JOIN (
        SELECT DATE(created_at) AS post_date, COUNT(*) AS new_posts
        FROM posts
        GROUP BY DATE(created_at)
    ) AS p ON p.post_date = d.stat_date
    LEFT JOIN (
        SELECT DATE(created_at) AS comment_date, COUNT(*) AS new_comments
        FROM comments
        GROUP BY DATE(created_at)
    ) AS c ON c.comment_date = d.stat_date
    ORDER BY d.stat_date ASC;
END$$

-- ==============================================================================
-- 8. DỤNG CỤ & THIẾT BỊ CHUYẾN ĐI (PRODUCTS & POST TOOLS)
-- ==============================================================================

DROP PROCEDURE IF EXISTS sp_product_list_by_category$$
CREATE PROCEDURE sp_product_list_by_category(IN p_category_id INT)
BEGIN
    SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link, p.is_shared, p.created_at,
           c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE (p_category_id IS NULL OR p.category_id = p_category_id)
      AND p.is_shared = 1
    ORDER BY p.id DESC;
END$$

DROP PROCEDURE IF EXISTS sp_product_get$$
CREATE PROCEDURE sp_product_get(IN p_id INT)
BEGIN
    SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link, p.created_at,
           c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = p_id
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_product_create$$
CREATE PROCEDURE sp_product_create(
    IN p_category_id INT,
    IN p_name VARCHAR(150),
    IN p_price DECIMAL(10,0),
    IN p_image_url VARCHAR(255),
    IN p_description TEXT,
    IN p_link TEXT,
    IN p_is_shared TINYINT
)
BEGIN
    INSERT INTO products (category_id, name, price, image_url, description, link, is_shared)
    VALUES (p_category_id, p_name, p_price, p_image_url, p_description, p_link, COALESCE(p_is_shared, 1));

    SELECT LAST_INSERT_ID() AS product_id;
END$$

DROP PROCEDURE IF EXISTS sp_product_update$$
CREATE PROCEDURE sp_product_update(
    IN p_id INT,
    IN p_category_id INT,
    IN p_name VARCHAR(150),
    IN p_price DECIMAL(10,0),
    IN p_image_url VARCHAR(255),
    IN p_description TEXT,
    IN p_link TEXT,
    IN p_is_shared TINYINT
)
BEGIN
    UPDATE products
    SET category_id = p_category_id,
        name = p_name,
        price = p_price,
        image_url = COALESCE(p_image_url, image_url),
        description = p_description,
        link = p_link,
        is_shared = COALESCE(p_is_shared, is_shared)
    WHERE id = p_id;

    SELECT ROW_COUNT() AS affected_rows;
END$$

DROP PROCEDURE IF EXISTS sp_product_delete$$
CREATE PROCEDURE sp_product_delete(IN p_id INT)
BEGIN
    DELETE FROM products WHERE id = p_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$

DROP PROCEDURE IF EXISTS sp_post_products_get$$
CREATE PROCEDURE sp_post_products_get(IN p_post_id INT)
BEGIN
    SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link,
           c.name AS category_name
    FROM post_products pp
    JOIN products p ON p.id = pp.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE pp.post_id = p_post_id
    ORDER BY p.name ASC;
END$$

DROP PROCEDURE IF EXISTS sp_post_product_add$$
CREATE PROCEDURE sp_post_product_add(IN p_post_id INT, IN p_product_id INT)
BEGIN
    INSERT IGNORE INTO post_products (post_id, product_id)
    VALUES (p_post_id, p_product_id);
END$$

DROP PROCEDURE IF EXISTS sp_post_products_clear$$
CREATE PROCEDURE sp_post_products_clear(IN p_post_id INT)
BEGIN
    DELETE FROM post_products WHERE post_id = p_post_id;
END$$

DELIMITER ;


-- ===== (Tuỳ chọn) Cho máy khác trong LAN kết nối DB =====
-- CREATE USER 'team'@'%' IDENTIFIED BY 'doi_mat_khau';
-- GRANT ALL PRIVILEGES ON outdoor_forum.* TO 'team'@'%' ;
-- FLUSH PRIVILEGES;
