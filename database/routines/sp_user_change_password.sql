-- Cập nhật mật khẩu đã được băm ở tầng ứng dụng

DROP PROCEDURE IF EXISTS sp_user_change_password;

DELIMITER $$

CREATE PROCEDURE sp_user_change_password(
    IN p_user_id INT,
    IN p_password_hash VARCHAR(255)
)
BEGIN
    UPDATE users SET password_hash = p_password_hash WHERE id = p_user_id;
END$$

DELIMITER ;
