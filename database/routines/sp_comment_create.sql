-- Routine thêm bình luận mới vào bài viết (Vương - Tuần 2 & 4)
-- Nhận vào: p_post_id, p_user_id, p_content
-- Trả về: comment_id vừa tạo

DROP PROCEDURE IF EXISTS sp_comment_create;

DELIMITER $$

CREATE PROCEDURE sp_comment_create(
    IN p_post_id INT,
    IN p_user_id INT,
    IN p_content TEXT
)
BEGIN
    INSERT INTO comments (post_id, user_id, content, created_at)
    VALUES (p_post_id, p_user_id, p_content, NOW());
    
    SELECT LAST_INSERT_ID() AS comment_id;
END$$

DELIMITER ;
