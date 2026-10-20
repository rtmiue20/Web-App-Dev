-- Routine cập nhật bài viết (Tuần 3 - Phúc)
-- Nhận vào: post_id, category_id, title, content, location, lat, lng, status
-- Trả về: số dòng bị ảnh hưởng (affected_rows)

DROP PROCEDURE IF EXISTS sp_post_update;

DELIMITER $$

CREATE PROCEDURE sp_post_update(
    IN p_id INT,
    IN p_category_id INT,
    IN p_title VARCHAR(200),
    IN p_content TEXT,
    IN p_location VARCHAR(150),
    IN p_lat DECIMAL(10, 7),
    IN p_lng DECIMAL(10, 7),
    IN p_status VARCHAR(20)
)
BEGIN
    UPDATE posts
    SET category_id = p_category_id,
        title = p_title,
        content = p_content,
        location = p_location,
        lat = p_lat,
        lng = p_lng,
        status = IFNULL(p_status, status)
    WHERE id = p_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

DELIMITER ;
