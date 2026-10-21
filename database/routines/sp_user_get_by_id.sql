-- Routine lấy thông tin người dùng theo ID để hiển thị hồ sơ cá nhân (Khang - Tuần 2 & 3)
-- Nhận vào: p_id (INT)
-- Trả về: hồ sơ người dùng và password_hash dùng để xác minh mật khẩu hiện tại

DROP PROCEDURE IF EXISTS sp_user_get_by_id;

DELIMITER $$

CREATE PROCEDURE sp_user_get_by_id(
    IN p_id INT
)
BEGIN
    SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
           u.bio, u.avatar_path, u.created_at
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    WHERE u.id = p_id
    LIMIT 1;
END$$

DELIMITER ;
