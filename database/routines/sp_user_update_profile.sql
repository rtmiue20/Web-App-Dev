-- Cập nhật tên người dùng, giới thiệu và đường dẫn ảnh đại diện

DROP PROCEDURE IF EXISTS sp_user_update_profile;

DELIMITER $$

CREATE PROCEDURE sp_user_update_profile(
    IN p_user_id INT,
    IN p_username VARCHAR(50),
    IN p_bio VARCHAR(500),
    IN p_avatar_path VARCHAR(255)
)
BEGIN
    UPDATE users
    SET username = p_username, bio = p_bio, avatar_path = p_avatar_path
    WHERE id = p_user_id;
END$$

DELIMITER ;
