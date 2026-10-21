-- Routine thêm chuyên mục mới (Leader - Tuần 3)
-- Nhận vào: p_name, p_description
-- Trả về: category_id vừa tạo

DROP PROCEDURE IF EXISTS sp_category_create;

DELIMITER $$

CREATE PROCEDURE sp_category_create(
    IN p_name VARCHAR(100),
    IN p_description VARCHAR(255)
)
BEGIN
    INSERT INTO categories (name, description) VALUES (p_name, p_description);
    SELECT LAST_INSERT_ID() AS category_id;
END$$

DELIMITER ;
