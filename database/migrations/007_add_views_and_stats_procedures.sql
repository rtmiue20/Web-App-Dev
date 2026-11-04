-- ==============================================================================
-- MIGRATION 007: SQL VIEWS CHO TRUY VẤN HAY DÙNG & STORED PROCEDURES THỐNG KÊ
-- Tương thích: MariaDB / MySQL (XAMPP)
-- ==============================================================================

USE outdoor_forum;

-- ------------------------------------------------------------------------------
-- PHẦN 1: SQL VIEWS CHO CÁC TRUY VẤN THƯỜNG XUYÊN SỬ DỤNG
-- ------------------------------------------------------------------------------

-- 1. View tổng hợp chi tiết bài viết (tác giả, chuyên mục, ảnh đại diện, vote, bình luận, điểm hot)
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

-- 2. View thống kê danh mục kèm tổng số bài, số bài đã duyệt, chờ duyệt
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

-- 3. View tổng hợp thành viên kèm vai trò, số bài viết, bình luận và điểm uy tín (karma)
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

-- ------------------------------------------------------------------------------
-- PHẦN 2: STORED PROCEDURES CHO THỐNG KÊ (TOP BÀI VIẾT, SỐ BÀI THEO DANH MỤC...)
-- ------------------------------------------------------------------------------

DELIMITER $$

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

DELIMITER ;

