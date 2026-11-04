-- Routine thống kê số bài viết theo từng trạng thái kiểm duyệt (published, pending, rejected, hidden)

DROP PROCEDURE IF EXISTS sp_stats_posts_by_status;

DELIMITER $$

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

