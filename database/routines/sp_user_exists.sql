-- Routine kiểm tra email hoặc username đã tồn tại trong hệ thống chưa (Khang - Tuần 2 & 4)
-- Nhận vào: p_email, p_username
-- Trả về: is_exists (1 nếu đã tồn tại, 0 nếu chưa)

DROP PROCEDURE IF EXISTS sp_user_exists;

DELIMITER $$

CREATE PROCEDURE sp_user_exists(
    IN p_email VARCHAR(120),
    IN p_username VARCHAR(50)
)
BEGIN
    SELECT EXISTS (
        SELECT 1 FROM users WHERE email = p_email OR username = p_username
    ) AS is_exists;
END$$

DELIMITER ;
