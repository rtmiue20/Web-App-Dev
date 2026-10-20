-- Routine tạo bài viết mới (Tuần 2 & 3 - Phúc)
-- Nhận vào: user_id, category_id, title, content, location, lat, lng, status
-- Trả về: post_id của bài viết vừa được tạo

DROP PROCEDURE IF EXISTS sp_post_create;

DELIMITER $$

CREATE PROCEDURE sp_post_create(
    IN p_user_id INT,
    IN p_category_id INT,
    IN p_title VARCHAR(200),
    IN p_content TEXT,
    IN p_location VARCHAR(150),
    IN p_lat DECIMAL(10, 7),
    IN p_lng DECIMAL(10, 7),
    IN p_status VARCHAR(20)
)
BEGIN
    INSERT INTO posts (user_id, category_id, title, content, location, lat, lng, status, created_at)
    VALUES (p_user_id, p_category_id, p_title, p_content, p_location, p_lat, p_lng, IFNULL(p_status, 'published'), NOW());
    
    SELECT LAST_INSERT_ID() AS post_id;
END$$

DELIMITER ;
