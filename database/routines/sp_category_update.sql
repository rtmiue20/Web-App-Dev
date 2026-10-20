-- Routine cập nhật thông tin chuyên mục (Leader - Tuần 3)
-- Nhận vào: p_id, p_name, p_description
-- Trả về: số dòng bị ảnh hưởng (affected_rows)

DROP PROCEDURE IF EXISTS sp_category_update;

DELIMITER $$

CREATE PROCEDURE sp_category_update(
    IN p_id INT,
    IN p_name VARCHAR(100),
    IN p_description VARCHAR(255)
)
BEGIN
    UPDATE categories
    SET name = p_name,
        description = p_description
    WHERE id = p_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

DELIMITER ;
