-- Routine gỡ thẻ khỏi bài viết
-- Nhận vào: p_post_id (INT), p_tag_id (INT)
-- Trả về: affected_rows

DROP PROCEDURE IF EXISTS sp_post_tag_remove;

DELIMITER $$

CREATE PROCEDURE sp_post_tag_remove(
    IN p_post_id INT,
    IN p_tag_id INT
)
BEGIN
    DELETE FROM post_tags WHERE post_id = p_post_id AND tag_id = p_tag_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$

DELIMITER ;
