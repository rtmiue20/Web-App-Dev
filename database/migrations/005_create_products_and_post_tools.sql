-- Migration 005: Tạo bảng products và bảng liên kết post_products (dụng cụ chuyến đi)
-- Phục vụ cho tính năng danh mục sản phẩm (Dụng cụ) và chia sẻ dụng cụ trong bài viết dã ngoại

USE outdoor_forum;

-- 1. Bảng danh mục sản phẩm / dụng cụ dã ngoại
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10,0) NOT NULL DEFAULT 0,
    image_url VARCHAR(255) NULL,
    description TEXT NULL,
    link VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Bảng liên kết bài viết và dụng cụ đã sử dụng trong chuyến đi (Tools & Gear)
CREATE TABLE IF NOT EXISTS post_products (
    post_id INT UNSIGNED NOT NULL,
    product_id INT NOT NULL,
    PRIMARY KEY (post_id, product_id),
    CONSTRAINT fk_post_products_post
        FOREIGN KEY (post_id) REFERENCES posts(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_post_products_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. Chèn dữ liệu mẫu cho bảng products nếu bảng chưa có dữ liệu
INSERT INTO products (category_id, name, price, description, image_url, link)
SELECT * FROM (
    SELECT 1 AS category_id, 'Lều cắm trại chống mưa 4 người Naturehike' AS name, 1850000 AS price,
           'Lều 2 lớp chống nước PU3000mm, khung hợp kim nhôm nhẹ, chống gió tốt, dựng nhanh trong 5 phút.' AS description,
           'assets/img/tent-forest.png' AS image_url,
           'https://naturehike.vn/leu-cam-trai-4-nguoi' AS link
    UNION ALL
    SELECT 1, 'Túi ngủ giữ nhiệt du lịch mùa đông (-5°C)', 520000,
           'Lớp lót nỉ ấm áp, bông định hình siêu nhẹ, chống thấm sương đêm, phù hợp thời tiết vùng cao.',
           'assets/img/moss-form.png',
           'https://example.com/tui-ngu-giu-nhiet'
    UNION ALL
    SELECT 2, 'Balo Trekking trợ lực Osprey 50L', 1250000,
           'Hệ thống đệm lưng AirScape thoáng khí, đai hông trợ lực chịu tải nặng, có kèm áo trùm mưa.',
           'assets/img/ta-xua-trek.png',
           'https://example.com/balo-trekking-50l'
    UNION ALL
    SELECT 2, 'Gậy leo núi dã ngoại carbon siêu nhẹ', 380000,
           'Chất liệu sợi carbon bền bỉ, khóa gập 3 khúc gọn gàng, tay nắm xốp EVA chống trượt mồ hôi.',
           'assets/img/ta-xua-trek.png',
           'https://example.com/gay-leo-nui'
    UNION ALL
    SELECT 3, 'Bếp gas mini dã ngoại xếp gọn kèm đầu khò', 320000,
           'Đầu đốt hợp kim đồng chịu nhiệt, công suất 3500W sôi nước trong 3 phút, có chắn gió thông minh.',
           'assets/img/tent-forest.png',
           'https://example.com/bep-gas-mini'
    UNION ALL
    SELECT 3, 'Đèn pin đội đầu siêu sáng chống nước IPX6', 290000,
           'Độ sáng 500 lumens, pin sạc USB-C 2000mAh dùng liên tục 8 tiếng, hỗ trợ cảm biến bật tắt không chạm.',
           'assets/img/moss-form.png',
           'https://example.com/den-pin-doi-dau'
    UNION ALL
    SELECT 3, 'Bộ nồi chảo dã ngoại nhôm Anodized 3 món', 450000,
           'Lớp phủ nhôm chống dính, tay cầm gấp gọn bọc cách nhiệt, xếp lồng vào nhau siêu tiết kiệm thể tích.',
           'assets/img/tent-forest.png',
           'https://example.com/bo-noi-da-ngoai'
    UNION ALL
    SELECT 3, 'Bộ dụng cụ sinh tồn đa năng 14 trong 1', 280000,
           'Gồm kìm, dao cắt, khui nắp, cưa mini, còi cứu hộ, đá đánh lửa sinh tồn trong rừng sâu.',
           'assets/img/moss-form.png',
           'https://example.com/dung-cu-sinh-ton'
) AS seed_data
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);
