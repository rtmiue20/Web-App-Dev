-- Routine lấy danh sách bài viết (Tuần 2 - Phúc)
-- Nhận vào: p_category_id (INT, có thể NULL để lấy tất cả bài)
-- Trả về: danh sách bài viết kèm username, tên chuyên mục, ảnh bìa, số lượt like, số lượt bình luận

DROP PROCEDURE IF EXISTS sp_post_list;

DELIMITER $$

CREATE PROCEDURE sp_post_list(
    IN p_category_id INT
)
BEGIN
    SELECT p.*, 
           u.username, 
           c.name AS category_name,
           (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
           (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes,
           (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE (p_category_id IS NULL OR p.category_id = p_category_id)
    ORDER BY p.created_at DESC;
END$$

DELIMITER ;
