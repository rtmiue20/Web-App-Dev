-- Routine thống kê Top thành viên đóng góp nhiều bài viết & tương tác nhất
-- Truy vấn từ SQL View: vw_user_summary

DROP PROCEDURE IF EXISTS sp_stats_top_users;

DELIMITER $$

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

DELIMITER ;

