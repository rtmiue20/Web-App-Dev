-- Routine lấy chi tiết bài viết theo ID (Tuần 2 - Phúc)
-- Nhận vào: post_id
-- Trả về: thông tin bài viết kèm username tác giả, tên chuyên mục, ảnh bìa đầu tiên, số lượt like

DROP PROCEDURE IF EXISTS sp_post_get;

DELIMITER $$

CREATE PROCEDURE sp_post_get(
    IN p_id INT
)
BEGIN
    SELECT p.*, 
           u.username, 
           c.name AS category_name,
           (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
           (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = p_id;
END$$

DELIMITER ;
