-- Routine lấy danh sách người dùng cho Quản trị viên (Khang - Tuần 4)
-- Trả về: danh sách người dùng kèm vai trò và tổng số bài viết đã đăng

DROP PROCEDURE IF EXISTS sp_user_list;

DELIMITER $$

CREATE PROCEDURE sp_user_list()
BEGIN
    SELECT u.id, u.username, u.email, u.role_id, r.name AS role_name, u.bio, u.avatar_path, u.created_at,
           COUNT(p.id) AS total_posts
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    LEFT JOIN posts p  ON p.user_id = u.id
    GROUP BY u.id, u.username, u.email, u.role_id, r.name, u.bio, u.avatar_path, u.created_at
    ORDER BY u.id ASC;
END$$

DELIMITER ;
