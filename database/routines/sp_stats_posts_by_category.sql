-- Routine thống kê số lượng bài viết theo từng danh mục (kèm số bài đã duyệt & chờ duyệt)
-- Truy vấn từ SQL View: vw_category_summary

DROP PROCEDURE IF EXISTS sp_stats_posts_by_category;

DELIMITER $$

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

DELIMITER ;

