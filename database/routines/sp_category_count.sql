-- Routine đếm tổng số chuyên mục trong hệ thống (Leader - Tuần 3)
-- Trả về: total (số lượng chuyên mục)

DROP PROCEDURE IF EXISTS sp_category_count;

DELIMITER $$

CREATE PROCEDURE sp_category_count()
BEGIN
    SELECT COUNT(*) AS total FROM categories;
END$$

DELIMITER ;
