-- ==============================================================================
-- STORED PROCEDURES QUẢN LÝ DỤNG CỤ (PRODUCTS) & DỤNG CỤ CHUYẾN ĐI (POST_PRODUCTS)
-- ==============================================================================

USE outdoor_forum;

DELIMITER $$

-- 1. Lấy danh sách dụng cụ / sản phẩm theo danh mục (hoặc lấy tất cả nếu p_category_id IS NULL)
DROP PROCEDURE IF EXISTS sp_product_list_by_category$$
CREATE PROCEDURE sp_product_list_by_category(
    IN p_category_id INT
)
BEGIN
    SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link, p.created_at,
           c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p_category_id IS NULL OR p.category_id = p_category_id
    ORDER BY p.id DESC;
END$$

-- 2. Lấy chi tiết một sản phẩm / dụng cụ theo ID
DROP PROCEDURE IF EXISTS sp_product_get$$
CREATE PROCEDURE sp_product_get(
    IN p_id INT
)
BEGIN
    SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link, p.created_at,
           c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.id = p_id
    LIMIT 1;
END$$

-- 3. Tạo mới một dụng cụ / sản phẩm
DROP PROCEDURE IF EXISTS sp_product_create$$
CREATE PROCEDURE sp_product_create(
    IN p_category_id INT,
    IN p_name VARCHAR(150),
    IN p_price DECIMAL(10,0),
    IN p_image_url VARCHAR(255),
    IN p_description TEXT,
    IN p_link VARCHAR(255)
)
BEGIN
    INSERT INTO products (category_id, name, price, image_url, description, link)
    VALUES (p_category_id, p_name, p_price, p_image_url, p_description, p_link);

    SELECT LAST_INSERT_ID() AS product_id;
END$$

-- 4. Cập nhật thông tin dụng cụ
DROP PROCEDURE IF EXISTS sp_product_update$$
CREATE PROCEDURE sp_product_update(
    IN p_id INT,
    IN p_category_id INT,
    IN p_name VARCHAR(150),
    IN p_price DECIMAL(10,0),
    IN p_image_url VARCHAR(255),
    IN p_description TEXT,
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

-- 5. Xóa một dụng cụ
DROP PROCEDURE IF EXISTS sp_product_delete$$
CREATE PROCEDURE sp_product_delete(
    IN p_id INT
)
BEGIN
    DELETE FROM products WHERE id = p_id;
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- 6. Lấy danh sách dụng cụ sử dụng trong chuyến đi của bài viết (Tools & Gear)
DROP PROCEDURE IF EXISTS sp_post_products_get$$
CREATE PROCEDURE sp_post_products_get(
    IN p_post_id INT
)
BEGIN
    SELECT p.id, p.category_id, p.name, p.price, p.image_url, p.description, p.link,
           c.name AS category_name
    FROM post_products pp
    JOIN products p ON p.id = pp.product_id
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE pp.post_id = p_post_id
    ORDER BY p.name ASC;
END$$

-- 7. Gán dụng cụ vào bài viết
DROP PROCEDURE IF EXISTS sp_post_product_add$$
CREATE PROCEDURE sp_post_product_add(
    IN p_post_id INT,
    IN p_product_id INT
)
BEGIN
    INSERT IGNORE INTO post_products (post_id, product_id)
    VALUES (p_post_id, p_product_id);
END$$

-- 8. Xóa toàn bộ dụng cụ đã gán vào bài viết (dùng khi cập nhật danh sách dụng cụ)
DROP PROCEDURE IF EXISTS sp_post_products_clear$$
CREATE PROCEDURE sp_post_products_clear(
    IN p_post_id INT
)
BEGIN
    DELETE FROM post_products WHERE post_id = p_post_id;
END$$

DELIMITER ;
