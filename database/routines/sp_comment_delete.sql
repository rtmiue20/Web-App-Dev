-- Routine xóa bình luận (Vương & Khang - Tuần 4)
-- Nhận vào: p_id, p_user_id, p_is_admin (1 nếu admin, 0 nếu thành viên thường)
-- Chỉ xóa nếu là chính tác giả hoặc admin
-- Trả về: affected_rows

DROP PROCEDURE IF EXISTS sp_comment_delete;

DELIMITER $$

CREATE PROCEDURE sp_comment_delete(
    IN p_id INT,
    IN p_user_id INT,
    IN p_is_admin TINYINT
)
BEGIN
    DELETE FROM comments 
    WHERE id = p_id AND (p_is_admin = 1 OR user_id = p_user_id);
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

DELIMITER ;
