-- Routine gán thẻ vào bài viết (nếu tag chưa có sẽ tự tạo)
-- Nhận vào: p_post_id (INT), p_tag_name (VARCHAR)
-- Trả về: tag_id

DROP PROCEDURE IF EXISTS sp_post_tag_add;

DELIMITER $$

CREATE PROCEDURE sp_post_tag_add(
    IN p_post_id INT,
    IN p_tag_name VARCHAR(50)
)
BEGIN
    DECLARE v_tag_id INT DEFAULT NULL;
    DECLARE v_clean_name VARCHAR(50);
    SET v_clean_name = LOWER(TRIM(p_tag_name));
    
    SELECT id INTO v_tag_id FROM tags WHERE LOWER(name) = v_clean_name LIMIT 1;
    IF v_tag_id IS NULL THEN
        INSERT INTO tags (name) VALUES (v_clean_name);
        SET v_tag_id = LAST_INSERT_ID();
    END IF;
    
    INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (p_post_id, v_tag_id);
    SELECT v_tag_id AS tag_id;
END$$

DELIMITER ;
