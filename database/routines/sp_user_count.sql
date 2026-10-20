-- Routine đếm tổng số người dùng trong hệ thống (Leader & Khang - Tuần 2)
-- Trả về: total (số lượng người dùng)

DROP PROCEDURE IF EXISTS sp_user_count;

DELIMITER $$

CREATE PROCEDURE sp_user_count()
BEGIN
    SELECT COUNT(*) AS total FROM users;
END$$

DELIMITER ;
