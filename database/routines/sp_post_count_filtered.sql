-- Routine đếm số lượng bài viết theo chuyên mục và trạng thái (Tuần 4 - Phúc)
-- Nhận vào: category_id (có thể NULL), status (có thể NULL)
-- Trả về: total (tổng số bài viết)

DROP PROCEDURE IF EXISTS sp_post_count_filtered;

DELIMITER $$

CREATE PROCEDURE sp_post_count_filtered(
    IN p_category_id INT,
    IN p_status VARCHAR(20)
)
BEGIN
    SELECT COUNT(*) AS total
    FROM posts
    WHERE (p_category_id IS NULL OR category_id = p_category_id)
      AND (p_status IS NULL OR status = p_status);
END$$

DELIMITER ;
