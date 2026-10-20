-- Routine gợi ý từ khóa tìm kiếm khi người dùng đang gõ AJAX (Phúc - Tuần 5)
-- Nhận vào: p_keyword (VARCHAR)
-- Trả về: danh sách 8 gợi ý nhanh gồm id, title, location

DROP PROCEDURE IF EXISTS sp_post_search_suggest;

DELIMITER $$

CREATE PROCEDURE sp_post_search_suggest(
    IN p_keyword VARCHAR(100)
)
BEGIN
    SET @pattern = CONCAT('%', TRIM(p_keyword), '%');
    
    SELECT id, title, location
    FROM posts
    WHERE title LIKE @pattern OR location LIKE @pattern
    ORDER BY created_at DESC
    LIMIT 8;
END$$

DELIMITER ;
