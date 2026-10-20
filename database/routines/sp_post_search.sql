-- Routine tìm kiếm bài viết theo từ khóa và chuyên mục (Phúc - Tuần 5)
-- Nhận vào: p_keyword (VARCHAR), p_category_id (INT, có thể NULL)
-- Tìm kiếm trong tiêu đề, nội dung, địa điểm
-- Trả về: danh sách bài viết khớp từ khóa

DROP PROCEDURE IF EXISTS sp_post_search;

DELIMITER $$

CREATE PROCEDURE sp_post_search(
    IN p_keyword VARCHAR(255),
    IN p_category_id INT
)
BEGIN
    SET @pattern = CONCAT('%', TRIM(p_keyword), '%');
    
    SELECT p.*,
           u.username,
           c.name AS category_name,
           (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
           (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id) AS likes,
           (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count
    FROM posts p
    JOIN users u ON u.id = p.user_id
    JOIN categories c ON c.id = p.category_id
    WHERE (p_category_id IS NULL OR p.category_id = p_category_id)
      AND (p.title LIKE @pattern OR p.content LIKE @pattern OR p.location LIKE @pattern)
    ORDER BY p.created_at DESC;
END$$

DELIMITER ;
