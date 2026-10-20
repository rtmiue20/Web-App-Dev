-- Routine xóa bài viết (Tuần 3 - Phúc)
-- Nhận vào: post_id
-- Trả về: số dòng bị xóa (affected_rows)

DROP PROCEDURE IF EXISTS sp_post_delete;

DELIMITER $$

CREATE PROCEDURE sp_post_delete(
    IN p_id INT
)
BEGIN
    DELETE FROM posts WHERE id = p_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$

DELIMITER ;
