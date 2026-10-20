-- Routine kiểm tra xem người dùng đã thích bài viết này chưa (Vương - Tuần 2 & 4)
-- Nhận vào: p_post_id, p_user_id
-- Trả về: is_liked (1 nếu đã thích, 0 nếu chưa)

DROP PROCEDURE IF EXISTS sp_post_is_liked;

DELIMITER $$

CREATE PROCEDURE sp_post_is_liked(
    IN p_post_id INT,
    IN p_user_id INT
)
BEGIN
    SELECT EXISTS (
        SELECT 1 FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id
    ) AS is_liked;
END$$

DELIMITER ;
