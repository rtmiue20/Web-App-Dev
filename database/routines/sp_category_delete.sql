-- Routine xóa chuyên mục (Leader - Tuần 3)
-- Nhận vào: p_id
-- Trả về: số dòng bị ảnh hưởng (affected_rows)

DROP PROCEDURE IF EXISTS sp_category_delete;

DELIMITER $$

CREATE PROCEDURE sp_category_delete(
    IN p_id INT
)
BEGIN
    DELETE FROM categories WHERE id = p_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$

DELIMITER ;
