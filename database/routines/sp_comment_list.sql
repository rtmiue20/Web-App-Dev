-- Routine lấy danh sách bình luận của bài viết (Vương - Tuần 2 & 4)
-- Nhận vào: p_post_id (INT)
-- Trả về: id, post_id, user_id, parent_id, content, created_at, username, avatar_path, role_name

DROP PROCEDURE IF EXISTS sp_comment_list;

DELIMITER $$

CREATE PROCEDURE sp_comment_list(
    IN p_post_id INT
)
BEGIN
    SELECT c.id, c.post_id, c.user_id, c.parent_id, c.content, c.created_at, u.username, u.avatar_path, r.name AS role_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN roles r ON r.id = u.role_id
    WHERE c.post_id = p_post_id
    ORDER BY c.created_at ASC, c.id ASC;
END$$

DELIMITER ;
