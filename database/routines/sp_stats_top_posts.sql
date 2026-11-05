-- Routine thống kê Top bài viết nổi bật nhất (theo điểm tương tác hot_score, vote_score, số bình luận)
-- Truy vấn từ SQL View: vw_post_details

DROP PROCEDURE IF EXISTS sp_stats_top_posts;

DELIMITER $$

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

DELIMITER ;

