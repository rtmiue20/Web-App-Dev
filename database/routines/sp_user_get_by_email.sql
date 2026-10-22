-- Routine lấy thông tin người dùng theo email để xác thực đăng nhập (Khang - Tuần 2)
-- Nhận vào: p_email (VARCHAR)
-- Trả về: thông tin đăng nhập và hồ sơ người dùng

DROP PROCEDURE IF EXISTS sp_user_get_by_email;

DELIMITER $$

CREATE PROCEDURE sp_user_get_by_email(
    IN p_email VARCHAR(120)
)
BEGIN
    SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
           u.bio, u.avatar_path, u.created_at
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    WHERE u.email = p_email
    LIMIT 1;
END$$

DELIMITER ;
