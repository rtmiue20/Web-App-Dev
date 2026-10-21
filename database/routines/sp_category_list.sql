-- Routine lấy danh sách tất cả chuyên mục kèm số lượng bài viết (Leader - Tuần 2 & 3)
-- Trả về: id, name, description, post_count

DROP PROCEDURE IF EXISTS sp_category_list;

DELIMITER $$

CREATE PROCEDURE sp_category_list()
BEGIN
    SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    GROUP BY c.id, c.name, c.description
    ORDER BY c.id ASC;
END$$

DELIMITER ;
