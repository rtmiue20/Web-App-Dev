-- Routine lấy danh sách bài viết theo chuyên mục, trạng thái và phân trang (Tuần 4 - Phúc)
-- Nhận vào: category_id (có thể NULL), status (có thể NULL), limit, offset
-- Trả về: danh sách bài viết kèm username, tên chuyên mục, ảnh bìa, số lượt like, số bình luận

DROP PROCEDURE IF EXISTS sp_post_list_paginated;

DELIMITER $$

CREATE PROCEDURE sp_post_list_paginated(
    IN p_category_id INT,
    IN p_status VARCHAR(20),
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    SELECT p.*, 
           u.username, 
           c.name AS category_name,
           (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
           (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes,
           (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
    FROM posts p
    JOIN users u      ON u.id = p.user_id
    JOIN categories c ON c.id = p.category_id
    WHERE (p_category_id IS NULL OR p.category_id = p_category_id)
      AND (p_status IS NULL OR p.status = p_status)
    ORDER BY p.created_at DESC
    LIMIT p_limit OFFSET p_offset;
END$$

DELIMITER ;
