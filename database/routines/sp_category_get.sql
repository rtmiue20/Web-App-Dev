-- Routine lấy chi tiết chuyên mục theo ID kèm số lượng bài viết (Leader - Tuần 3)
-- Nhận vào: p_id (INT)
-- Trả về: id, name, description, post_count

DROP PROCEDURE IF EXISTS sp_category_get;

DELIMITER $$

CREATE PROCEDURE sp_category_get(
    IN p_id INT
)
BEGIN
    SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    WHERE c.id = p_id
    GROUP BY c.id, c.name, c.description
    LIMIT 1;
END$$

DELIMITER ;
