-- Routine cập nhật trạng thái bài viết (Tuần 4 - Phúc)
-- Nhận vào: post_id, status ('published', 'pending', 'hidden')
-- Trả về: số dòng bị ảnh hưởng (affected_rows)

DROP PROCEDURE IF EXISTS sp_post_update_status;

DELIMITER $$

CREATE PROCEDURE sp_post_update_status(
    IN p_id INT,
    IN p_status VARCHAR(20)
)
BEGIN
    UPDATE posts
    SET status = p_status
    WHERE id = p_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

DELIMITER ;
