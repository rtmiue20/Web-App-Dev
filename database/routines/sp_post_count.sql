-- Routine đếm tổng số bài viết trong hệ thống (Leader & Phúc - Tuần 2)
-- Trả về: total (số lượng bài viết)

DROP PROCEDURE IF EXISTS sp_post_count;

DELIMITER $$

CREATE PROCEDURE sp_post_count()
BEGIN
    SELECT COUNT(*) AS total FROM posts;
END$$

DELIMITER ;
