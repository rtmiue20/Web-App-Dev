-- Routine tìm thẻ theo tên hoặc tạo mới nếu chưa tồn tại (Người 4 / Tag - Tuần 2)
-- Nhận vào: p_name (VARCHAR)
-- Trả về: tag_id

DROP PROCEDURE IF EXISTS sp_tag_find_or_create;

DELIMITER $$

CREATE PROCEDURE sp_tag_find_or_create(
    IN p_name VARCHAR(50)
)
BEGIN
    DECLARE v_id INT DEFAULT NULL;
    DECLARE v_clean_name VARCHAR(50);
    SET v_clean_name = LOWER(TRIM(p_name));
    
    SELECT id INTO v_id FROM tags WHERE LOWER(name) = v_clean_name LIMIT 1;
    
    IF v_id IS NULL THEN
        INSERT INTO tags (name) VALUES (v_clean_name);
        SET v_id = LAST_INSERT_ID();
    END IF;
    
    SELECT v_id AS tag_id;
END$$

DELIMITER ;
