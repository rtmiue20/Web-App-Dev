-- Routine thích hoặc bỏ thích bài viết (Vương - Tuần 2 & 4)
-- Nhận vào: p_post_id, p_user_id
-- Trả về: likes (tổng lượt thích sau thao tác), is_liked (1: vừa thích, 0: vừa bỏ thích)

DROP PROCEDURE IF EXISTS sp_post_toggle_like;

DELIMITER $$

CREATE PROCEDURE sp_post_toggle_like(
    IN p_post_id INT,
    IN p_user_id INT
)
BEGIN
    DECLARE v_liked INT DEFAULT 0;
    
    IF EXISTS (SELECT 1 FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id) THEN
        DELETE FROM post_likes WHERE post_id = p_post_id AND user_id = p_user_id;
        SET v_liked = 0;
    ELSE
        INSERT INTO post_likes (post_id, user_id) VALUES (p_post_id, p_user_id);
        SET v_liked = 1;
    END IF;
    
    SELECT COUNT(*) AS likes, v_liked AS is_liked 
    FROM post_likes 
    WHERE post_id = p_post_id;
END$$

DELIMITER ;
