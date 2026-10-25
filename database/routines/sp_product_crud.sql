-- ==============================================================================
-- FILE SQL TỔNG HỢP PHẦN VIỆC CỦA VƯƠNG (TUẦN 2, 3, 4)
-- Bao gồm: Bảng products, dữ liệu mẫu và các Stored Procedures cho Product, Comment, Like
-- ==============================================================================

-- 1. Tạo bảng products (FR09 & FR12: thiết bị / sản phẩm dã ngoại theo danh mục)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10,0) NOT NULL DEFAULT 0,
    image_url VARCHAR(255) NULL,
    description VARCHAR(255) NULL,
    link VARCHAR(255) NULL,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Đảm bảo bảng cũ nếu đã tạo trước đó vẫn có cột link
ALTER TABLE products ADD COLUMN IF NOT EXISTS link VARCHAR(255) NULL;

-- Cập nhật các bản ghi mẫu cũ nếu image_url đang lưu tên icon Bootstrap (bi-*)
UPDATE products SET image_url = 'assets/img/tent-forest.png' WHERE image_url LIKE 'bi-%';

-- 2. Thêm dữ liệu mẫu (chỉ thêm nếu bảng đang trống)
INSERT INTO products (category_id, name, price, description, image_url, link)
SELECT * FROM (
    SELECT 1 AS category_id, 'Lều cắm trại chống mưa 4 người' AS name, 1850000 AS price,
           'Lều 2 lớp chống nước PU3000mm, khung nhôm nhẹ, dựng nhanh trong 5 phút.' AS description,
           'assets/img/tent-forest.png' AS image_url,
           'https://example.com/leu-cam-trai-4-nguoi' AS link
    UNION ALL
    SELECT 1, 'Túi ngủ giữ nhiệt mùa đông', 490000,
           'Giữ ấm комфортно tới 5°C, chất liệu chống ẩm, nhẹ và dễ gấp gọn vào balo.',
           'assets/img/moss-form.png',
           'https://example.com/tui-ngu-giu-nhiet'
    UNION ALL
    SELECT 2, 'Balo Trekking trợ lực 50L', 950000,
           'Đệm lưng thoáng khí, đai trợ lực hông, kèm áo trùm mưa chuyên dụng leo núi.',
           'assets/img/ta-xua-trek.png',
           'https://example.com/balo-trekking-50l'
    UNION ALL
    SELECT 3, 'Bếp gas mini dã ngoại gấp gọn', 320000,
           'Đầu đốt hợp kim siêu nhẹ, chắn gió tốt, sử dụng bình gas lon tiện lợi.',
           'assets/img/tent-forest.png',
           'https://example.com/bep-gas-mini-da-ngoai'
) AS seed_data
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);

DELIMITER $$

-- ==========================================
-- THỦ TỤC QUẢN LÝ SẢN PHẨM (FR09, FR12 - Tuần 2 & 3)
-- ==========================================

-- 3. Thủ tục lấy danh sách sản phẩm (có lọc theo danh mục)
DROP PROCEDURE IF EXISTS sp_product_list_by_category$$
CREATE PROCEDURE sp_product_list_by_category(
    IN p_category_id INT
)
BEGIN
    SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link,
           c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p_category_id IS NULL OR p.category_id = p_category_id
    ORDER BY p.id DESC;
END$$

-- 4. Thủ tục lấy chi tiết 1 sản phẩm theo ID
DROP PROCEDURE IF EXISTS sp_product_get$$
CREATE PROCEDURE sp_product_get(
    IN p_id INT
)
BEGIN
    SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link,
           c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = p_id
    LIMIT 1;
END$$

-- 5. Thủ tục thêm sản phẩm mới
DROP PROCEDURE IF EXISTS sp_product_create$$
CREATE PROCEDURE sp_product_create(
    IN p_category_id INT,
    IN p_name VARCHAR(150),
    IN p_price DECIMAL(10,0),
    IN p_image_url VARCHAR(255),
    IN p_description VARCHAR(255),
    IN p_link VARCHAR(255)
)
BEGIN
    INSERT INTO products (category_id, name, price, image_url, description, link)
    VALUES (p_category_id, p_name, p_price, p_image_url, p_description, p_link);

    SELECT LAST_INSERT_ID() AS product_id;
END$$

-- 6. Thủ tục cập nhật sản phẩm
DROP PROCEDURE IF EXISTS sp_product_update$$
CREATE PROCEDURE sp_product_update(
    IN p_id INT,
    IN p_category_id INT,
    IN p_name VARCHAR(150),
    IN p_price DECIMAL(10,0),
    IN p_image_url VARCHAR(255),
    IN p_description VARCHAR(255),
    IN p_link VARCHAR(255)
)
BEGIN
    UPDATE products
    SET category_id = p_category_id,
        name = p_name,
        price = p_price,
        image_url = COALESCE(p_image_url, image_url),
        description = p_description,
        link = p_link
    WHERE id = p_id;

    SELECT ROW_COUNT() AS affected_rows;
END$$

-- 7. Thủ tục xóa sản phẩm
DROP PROCEDURE IF EXISTS sp_product_delete$$
CREATE PROCEDURE sp_product_delete(
    IN p_id INT
)
BEGIN
    DELETE FROM products WHERE id = p_id;

    SELECT ROW_COUNT() AS affected_rows;
END$$

-- ==========================================
-- THỦ TỤC TƯƠNG TÁC LIKE & COMMENT (FR07, FR08 - Tuần 2 & 4)
-- ==========================================

-- 8. Đếm số lượt thích của bài viết
DROP PROCEDURE IF EXISTS sp_post_like_count$$
CREATE PROCEDURE sp_post_like_count(
    IN p_post_id INT
)
BEGIN
    SELECT COUNT(*) AS total FROM post_likes WHERE post_id = p_post_id;
END$$

-- 9. Danh sách bình luận có phân trang (Load More AJAX)
DROP PROCEDURE IF EXISTS sp_comment_list_paged$$
CREATE PROCEDURE sp_comment_list_paged(
    IN p_post_id INT,
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.post_id = p_post_id
    ORDER BY c.created_at DESC, c.id DESC
    LIMIT p_limit OFFSET p_offset;
END$$

-- 10. Lấy 1 bình luận theo ID (kiểm tra quyền sở hữu trước khi xóa)
DROP PROCEDURE IF EXISTS sp_comment_get$$
CREATE PROCEDURE sp_comment_get(
    IN p_id INT
)
BEGIN
    SELECT c.id, c.post_id, c.user_id, c.content, c.created_at, u.username
    FROM comments c
    LEFT JOIN users u ON u.id = c.user_id
    WHERE c.id = p_id
    LIMIT 1;
END$$

-- 11. Alias sp_comment_add (theo tên trong kế hoạch Tuần 2)
DROP PROCEDURE IF EXISTS sp_comment_add$$
CREATE PROCEDURE sp_comment_add(
    IN p_post_id INT,
    IN p_user_id INT,
    IN p_content TEXT
)
BEGIN
    CALL sp_comment_create(p_post_id, p_user_id, p_content);
END$$

DELIMITER ;
