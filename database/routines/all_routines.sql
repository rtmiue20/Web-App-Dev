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
    SELECT u.id, u.username, u.email, u.role_id, r.name AS role_name, u.bio, u.avatar_path, u.created_at,
           COUNT(p.id) AS total_posts
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    LEFT JOIN posts p  ON p.user_id = u.id
    GROUP BY u.id, u.username, u.email, u.role_id, r.name, u.bio, u.avatar_path, u.created_at
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

DROP PROCEDURE IF EXISTS sp_post_update_status$$
CREATE PROCEDURE sp_post_update_status(IN p_id INT, IN p_status VARCHAR(20))
BEGIN
    UPDATE posts
    SET status = p_status
    WHERE id = p_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

DROP PROCEDURE IF EXISTS sp_post_list_paginated$$
CREATE PROCEDURE sp_post_list_paginated(
    IN p_category_id INT,
    IN p_status VARCHAR(20),
    IN p_limit INT,
    IN p_offset INT
)
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
      AND (p_status IS NULL OR p.status = p_status)
    ORDER BY p.created_at DESC
    LIMIT p_limit OFFSET p_offset;
END$$

DROP PROCEDURE IF EXISTS sp_post_count_filtered$$
CREATE PROCEDURE sp_post_count_filtered(IN p_category_id INT, IN p_status VARCHAR(20))
BEGIN
    SELECT COUNT(*) AS total
    FROM posts
    WHERE (p_category_id IS NULL OR category_id = p_category_id)
      AND (p_status IS NULL OR status = p_status);
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
-- 7. THỐNG KÊ & DASHBOARD (STATISTICS & REPORTS - STORED PROCEDURES)
-- ==============================================================================

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
        (SELECT COUNT(*) FROM products) AS total_products;
END$$

DROP PROCEDURE IF EXISTS sp_stats_top_posts$$
CREATE PROCEDURE sp_stats_top_posts(IN p_limit INT)
BEGIN
    DECLARE v_limit INT DEFAULT 5;
    SET v_limit = IF(p_limit IS NULL OR p_limit <= 0, 5, p_limit);

    SELECT
        id, user_id, category_id, title, location, status, created_at,
        username, category_name, image_url, vote_score, upvotes, downvotes, likes, comments_count, hot_score
    FROM vw_post_details
    WHERE status = 'published'
    ORDER BY hot_score DESC, vote_score DESC, comments_count DESC, created_at DESC
    LIMIT v_limit;
END$$

DROP PROCEDURE IF EXISTS sp_stats_posts_by_category$$
CREATE PROCEDURE sp_stats_posts_by_category()
BEGIN
    SELECT
        id, name, name AS category, description,
        post_count AS total_posts, post_count, published_count, pending_count, latest_post_at
    FROM vw_category_summary
    ORDER BY post_count DESC, id ASC;
END$$

DROP PROCEDURE IF EXISTS lk_posts_incategory$$
CREATE PROCEDURE lk_posts_incategory()
BEGIN
    CALL sp_stats_posts_by_category();
END$$

DROP PROCEDURE IF EXISTS sp_stats_top_users$$
CREATE PROCEDURE sp_stats_top_users(IN p_limit INT)
BEGIN
    DECLARE v_limit INT DEFAULT 5;
    SET v_limit = IF(p_limit IS NULL OR p_limit <= 0, 5, p_limit);

    SELECT
        id, username, email, role_name, avatar_path, created_at,
        total_posts, published_posts, total_comments, total_karma
    FROM vw_user_summary
    ORDER BY total_posts DESC, total_karma DESC, total_comments DESC, id ASC
    LIMIT v_limit;
END$$

DROP PROCEDURE IF EXISTS lk_posts_byuser$$
CREATE PROCEDURE lk_posts_byuser()
BEGIN
    CALL sp_stats_top_users(10);
END$$

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
    SELECT p.id, p.category_id, p.user_id, p.name, p.price, p.image_url, p.description, p.link, p.is_shared, p.created_at,
           c.name AS category_name,
           u.username AS author_username, u.avatar_path AS author_avatar, r.name AS author_role
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.user_id
    LEFT JOIN roles r ON r.id = u.role_id
    WHERE (p_category_id IS NULL OR p.category_id = p_category_id)
      AND p.is_shared = 1
      AND (
          r.name IN ('admin', 'moderator')
          OR p.user_id IS NULL
          OR EXISTS (
              SELECT 1
              FROM post_products pp
              INNER JOIN posts po ON po.id = pp.post_id
              WHERE pp.product_id = p.id
                AND po.status = 'published'
          )
      )
    ORDER BY p.id DESC;
END$$

DROP PROCEDURE IF EXISTS sp_product_get$$
CREATE PROCEDURE sp_product_get(IN p_id INT)
BEGIN
    SELECT p.id, p.category_id, p.user_id, p.name, p.price, p.image_url, p.description, p.link, p.is_shared, p.created_at,
           c.name AS category_name,
           u.username AS author_username, u.avatar_path AS author_avatar, r.name AS author_role
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN users u ON u.id = p.user_id
    LEFT JOIN roles r ON r.id = u.role_id
    WHERE p.id = p_id
    LIMIT 1;
END$$

DROP PROCEDURE IF EXISTS sp_product_create$$
CREATE PROCEDURE sp_product_create(
    IN p_category_id INT,
    IN p_user_id INT,
    IN p_name VARCHAR(150),
    IN p_price DECIMAL(10,0),
    IN p_image_url VARCHAR(255),
    IN p_description TEXT,
    IN p_link TEXT,
    IN p_is_shared TINYINT
)
BEGIN
    INSERT INTO products (category_id, user_id, name, price, image_url, description, link, is_shared)
    VALUES (p_category_id, p_user_id, p_name, p_price, p_image_url, p_description, p_link, COALESCE(p_is_shared, 1));

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

