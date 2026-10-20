-- Routine đếm số bình luận của một bài viết (Vương - Tuần 4)
-- Nhận vào: p_post_id (INT)
-- Trả về: total (số lượng bình luận)

DROP PROCEDURE IF EXISTS sp_comment_count_by_post;

DELIMITER $$

CREATE PROCEDURE sp_comment_count_by_post(
    IN p_post_id INT
)
BEGIN
    SELECT COUNT(*) AS total FROM comments WHERE post_id = p_post_id;
END$$

DELIMITER ;
