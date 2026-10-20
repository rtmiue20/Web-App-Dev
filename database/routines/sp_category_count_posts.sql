-- Routine đếm số bài viết thuộc chuyên mục (Leader - Tuần 3)
-- Nhận vào: p_id
-- Trả về: total (số lượng bài viết)

DROP PROCEDURE IF EXISTS sp_category_count_posts;

DELIMITER $$

CREATE PROCEDURE sp_category_count_posts(
    IN p_id INT
)
BEGIN
    SELECT COUNT(*) AS total FROM posts WHERE category_id = p_id;
END$$

DELIMITER ;
