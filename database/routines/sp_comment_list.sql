-- Routine lấy danh sách bình luận của bài viết (Vương - Tuần 2 & 4)
-- Nhận vào: p_post_id (INT)
-- Trả về: id, post_id, user_id, content, created_at, username

DROP PROCEDURE IF EXISTS sp_comment_list;

DELIMITER $$

CREATE PROCEDURE sp_comment_list(
    IN p_post_id INT
)
BEGIN
    SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = p_post_id
    ORDER BY c.created_at DESC;
END$$

DELIMITER ;
