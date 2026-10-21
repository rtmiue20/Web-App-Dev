-- Routine tìm chuyên mục theo tên để kiểm tra trùng lặp (Leader - Tuần 3)
-- Nhận vào: p_name (VARCHAR), p_exclude_id (INT, có thể NULL nếu kiểm tra khi tạo mới)
-- Trả về: id, name, description

DROP PROCEDURE IF EXISTS sp_category_get_by_name;

DELIMITER $$

CREATE PROCEDURE sp_category_get_by_name(
    IN p_name VARCHAR(100),
    IN p_exclude_id INT
)
BEGIN
    IF p_exclude_id IS NOT NULL THEN
        SELECT id, name, description
        FROM categories
        WHERE LOWER(TRIM(name)) = LOWER(TRIM(p_name)) AND id != p_exclude_id
        LIMIT 1;
    ELSE
        SELECT id, name, description
        FROM categories
        WHERE LOWER(TRIM(name)) = LOWER(TRIM(p_name))
        LIMIT 1;
    END IF;
END$$

DELIMITER ;
