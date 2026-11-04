-- Routine thống kê tổng quan hệ thống cho Admin Dashboard & Trang chủ
-- Trả về: total_users, total_posts, published_posts, pending_posts, total_categories, total_comments, total_products

DROP PROCEDURE IF EXISTS sp_dashboard_stats;

DELIMITER $$

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

DELIMITER ;
