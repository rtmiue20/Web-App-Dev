-- Routine đăng ký người dùng mới (Khang - Tuần 2)
-- Nhận vào: p_username, p_email, p_password_hash
-- Mặc định gán role 'member' (role_id lấy từ bảng roles)
-- Trả về: user_id vừa tạo

DROP PROCEDURE IF EXISTS sp_user_register;

DELIMITER $$

CREATE PROCEDURE sp_user_register(
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(120),
    IN p_password_hash VARCHAR(255)
)
BEGIN
    DECLARE v_role_id INT;
    SELECT id INTO v_role_id FROM roles WHERE name = 'member' LIMIT 1;
    IF v_role_id IS NULL THEN
        SET v_role_id = 1;
    END IF;
    
    INSERT INTO users (username, email, password_hash, role_id, created_at)
    VALUES (p_username, p_email, p_password_hash, v_role_id, NOW());
    
    SELECT LAST_INSERT_ID() AS user_id;
END$$

DELIMITER ;
