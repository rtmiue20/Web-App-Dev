-- Routine thống kê tổng quan hệ thống cho Admin Dashboard (Leader - Tuần 3)
-- Trả về: total_users, total_posts, total_categories, total_comments

DROP PROCEDURE IF EXISTS sp_dashboard_stats;

DELIMITER $$

CREATE PROCEDURE sp_dashboard_stats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM posts) AS total_posts,
        (SELECT COUNT(*) FROM categories) AS total_categories,
        (SELECT COUNT(*) FROM comments) AS total_comments;
END$$

DELIMITER ;
