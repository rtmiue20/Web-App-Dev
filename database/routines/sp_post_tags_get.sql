-- Routine lấy danh sách thẻ của bài viết
-- Nhận vào: p_post_id (INT)
-- Trả về: danh sách tag id và name

DROP PROCEDURE IF EXISTS sp_post_tags_get;

DELIMITER $$

CREATE PROCEDURE sp_post_tags_get(
    IN p_post_id INT
)
BEGIN
    SELECT t.id, t.name
    FROM post_tags pt
    JOIN tags t ON t.id = pt.tag_id
    WHERE pt.post_id = p_post_id
    ORDER BY t.name;
END$$

DELIMITER ;
