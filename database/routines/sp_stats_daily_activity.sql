-- Routine thống kê số người dùng đăng ký mới, bài viết mới và bình luận mới theo từng ngày
-- Phục vụ biểu đồ Chart.js trên Admin Dashboard.
-- Tham số p_days: số ngày gần nhất cần thống kê (mặc định 7, tối đa 30).
-- Các ngày không có dữ liệu vẫn được trả về với giá trị 0 để biểu đồ liên tục.

DROP PROCEDURE IF EXISTS sp_stats_daily_activity;

DELIMITER $$

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

DELIMITER ;

