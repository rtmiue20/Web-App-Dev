<?php
require_once __DIR__ . '/../core/Database.php';

/**
 * Helper DemoSeeder - Tự động kiểm tra, sửa bảng thiếu và nạp dữ liệu mẫu chuẩn
 * Đảm bảo 100% các bảng (post_tags, products, post_products), danh mục, bài viết (published + lat/lng),
 * dụng cụ dã ngoại và bình luận mẫu luôn sẵn sàng mà không làm mất tài khoản người dùng hiện tại.
 */
class DemoSeeder
{
    public static function ensureSeeded(): void
    {
        try {
            // 1. Đảm bảo các bảng thứ cấp tồn tại (phòng trường hợp schema.sql cũ bị ngắt ở dòng 88)
            Database::execute("
                CREATE TABLE IF NOT EXISTS post_tags (
                  post_id INT UNSIGNED NOT NULL,
                  tag_id  INT UNSIGNED NOT NULL,
                  PRIMARY KEY (post_id, tag_id),
                  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                  FOREIGN KEY (tag_id)  REFERENCES tags(id)  ON DELETE CASCADE
                ) ENGINE=InnoDB
            ");

            Database::execute("
                CREATE TABLE IF NOT EXISTS products (
                  id          INT AUTO_INCREMENT PRIMARY KEY,
                  category_id INT UNSIGNED NULL,
                  name        VARCHAR(150) NOT NULL,
                  price       DECIMAL(10, 0) NOT NULL DEFAULT 0,
                  image_url   VARCHAR(255) NULL,
                  description TEXT NULL,
                  link        VARCHAR(255) NULL,
                  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
                ) ENGINE=InnoDB
            ");

            Database::execute("
                CREATE TABLE IF NOT EXISTS post_products (
                  post_id    INT UNSIGNED NOT NULL,
                  product_id INT NOT NULL,
                  PRIMARY KEY (post_id, product_id),
                  FOREIGN KEY (post_id)    REFERENCES posts(id)    ON DELETE CASCADE,
                  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ");

            // 2. Đảm bảo bảng roles và users có ít nhất 1 tài khoản hợp lệ
            $roleRows = Database::query("SELECT COUNT(*) AS total FROM roles");
            if ((int)($roleRows[0]['total'] ?? 0) === 0) {
                Database::execute("INSERT IGNORE INTO roles (id, name) VALUES (1, 'member'), (2, 'admin')");
            }

            $userRows = Database::query("SELECT id FROM users ORDER BY id ASC");
            if (empty($userRows)) {
                $defaultHash = password_hash('123456', PASSWORD_DEFAULT);
                Database::execute(
                    "INSERT IGNORE INTO users (id, username, email, password_hash, role_id) VALUES
                     (1, 'admin', 'admin@forum.local', :h1, 2),
                     (2, 'minh_trekking', 'minh@forum.local', :h2, 1),
                     (3, 'lan_camping', 'lan@forum.local', :h3, 1)",
                    ['h1' => $defaultHash, 'h2' => $defaultHash, 'h3' => $defaultHash]
                );
                $authorId = 2;
                $secondAuthorId = 3;
            } else {
                $authorId = (int)$userRows[0]['id'];
                $secondAuthorId = isset($userRows[1]['id']) ? (int)$userRows[1]['id'] : $authorId;
            }

            // 3. Đảm bảo bảng categories có đủ 3 chuyên mục
            $catRows = Database::query("SELECT id FROM categories ORDER BY id ASC");
            if (empty($catRows)) {
                Database::execute(
                    "INSERT IGNORE INTO categories (id, name, description) VALUES
                     (1, 'Cắm trại', 'Kinh nghiệm cắm trại, hạ trại qua đêm và chọn bãi cắm đẹp'),
                     (2, 'Trekking', 'Lịch trình leo núi, đi rừng, săn mây và kỹ năng sinh tồn'),
                     (3, 'Dụng cụ', 'Đánh giá & tư vấn lều trại, balo, bếp dã ngoại, trang bị')"
                );
                $cat1 = 1;
                $cat2 = 2;
                $cat3 = 3;
            } else {
                $cat1 = (int)($catRows[0]['id'] ?? 1);
                $cat2 = (int)($catRows[1]['id'] ?? $cat1);
                $cat3 = (int)($catRows[2]['id'] ?? $cat1);
            }

                        // Đảm bảo khóa ngoại user_id và category_id của mọi bài viết luôn hợp lệ
            Database::execute("UPDATE posts SET user_id = :uid WHERE user_id NOT IN (SELECT id FROM users)", ['uid' => $authorId]);
            Database::execute("UPDATE posts SET category_id = :cid WHERE category_id NOT IN (SELECT id FROM categories)", ['cid' => $cat1]);

            // 4. Đảm bảo có bài viết mẫu ở trạng thái 'published' và có đầy đủ tọa độ lat/lng
            $pubPosts = Database::query("SELECT COUNT(*) AS total FROM posts WHERE status = 'published'");
            if ((int)($pubPosts[0]['total'] ?? 0) === 0) {
                $allPosts = Database::query("SELECT id FROM posts ORDER BY id ASC");
                if (empty($allPosts)) {
                    Database::execute(
                        "INSERT INTO posts (id, user_id, category_id, title, content, location, lat, lng, status) VALUES
                         (1, :u1, :c1, 'Kinh nghiệm cắm trại săn mây Đồi Đa Phú – Đà Lạt tự túc', :cnt1, 'TP. Đà Lạt, Lâm Đồng', 11.9404000, 108.4583000, 'published'),
                         (2, :u2, :c2, 'Lịch trình Trekking Tà Năng – Phan Dũng 2 ngày 1 đêm mùa cỏ xanh', :cnt2, 'Huyện Đức Trọng (Tà Năng), Lâm Đồng', 11.5333000, 108.4667000, 'published'),
                         (3, :u3, :c3, 'Hướng dẫn chọn lều 2 lớp chống mưa và săn mây Tà Xùa cho người mới', :cnt3, 'Huyện Bắc Yên (Tà Xùa), Sơn La', 21.2833000, 104.3167000, 'published')",
                        [
                            'u1'   => $authorId,
                            'c1'   => $cat1,
                            'cnt1' => "Đồi Đa Phú cách trung tâm Đà Lạt khoảng 10km, là địa điểm lý tưởng để dựng lều ngắm biển mây vào sáng sớm và hoàng hôn thông reo.\n\nLịch trình gợi ý:\n- 15h00: Di chuyển lên đồi, chọn vị trí bằng phẳng khuất gió để hạ trại.\n- 18h00: Nướng BBQ, pha cà phê nóng ngắm toàn cảnh thành phố lên đèn.\n- 05h00 sáng hôm sau: Đón bình minh và săn biển mây tuyệt đẹp.\n\nLưu ý: Nhiệt độ ban đêm xuống khoảng 12-15 độ C, cần chuẩn bị lều 2 lớp chống sương và túi ngủ giữ nhiệt.",
                            'u2'   => $authorId,
                            'c2'   => $cat2,
                            'cnt2' => "Cung đường trekking đẹp nhất Việt Nam nối liền Lâm Đồng và Bình Thuận với những đồi cỏ trải dài bất tận.\n\nChuẩn bị thể lực và trang bị:\n- Giày trekking cổ lửng có độ bám cao.\n- Balo 40L-50L có đai trợ lực hông, mang tối thiểu 3 lít nước/người.\n- Hạ trại tại khu vực đồi Lính để đón bình minh toàn cảnh núi rừng.",
                            'u3'   => $secondAuthorId,
                            'c3'   => $cat3,
                            'cnt3' => "Khi đi cắm trại vùng núi cao như Tà Xùa (Sơn La) hay Sa Pa, độ ẩm ban đêm và sương mù rất dày.\n\nTiêu chí chọn lều:\n1. Chọn lều 2 lớp có chỉ số chống nước từ PU3000mm trở lên.\n2. Khung nhôm hợp kim nhẹ và chịu gió tốt trên sống lưng khủng long.\n3. Đi kèm tấm lót đáy (footprint) để cách ẩm từ mặt đất."
                        ]
                    );
                } else {
                    Database::execute("UPDATE posts SET status = 'published' WHERE status = 'pending'");
                    $p1 = (int)$allPosts[0]['id'];
                    Database::execute(
                        "UPDATE posts SET title = 'Kinh nghiệm cắm trại săn mây Đồi Đa Phú – Đà Lạt tự túc', location = 'TP. Đà Lạt, Lâm Đồng', lat = 11.9404000, lng = 108.4583000, content = :c WHERE id = :id",
                        [
                            'id' => $p1,
                            'c'  => "Đồi Đa Phú cách trung tâm Đà Lạt khoảng 10km, là địa điểm lý tưởng để dựng lều ngắm biển mây vào sáng sớm và hoàng hôn thông reo.\n\nLịch trình gợi ý:\n- 15h00: Di chuyển lên đồi, chọn vị trí bằng phẳng khuất gió để hạ trại.\n- 18h00: Nướng BBQ, pha cà phê nóng ngắm toàn cảnh thành phố lên đèn.\n- 05h00 sáng hôm sau: Đón bình minh và săn biển mây tuyệt đẹp.\n\nLưu ý: Nhiệt độ ban đêm xuống khoảng 12-15 độ C, cần chuẩn bị lều 2 lớp chống sương và túi ngủ giữ nhiệt."
                        ]
                    );
                    if (isset($allPosts[1]['id'])) {
                        $p2 = (int)$allPosts[1]['id'];
                        Database::execute(
                            "UPDATE posts SET title = 'Lịch trình Trekking Tà Năng – Phan Dũng 2 ngày 1 đêm mùa cỏ xanh', location = 'Huyện Đức Trọng (Tà Năng), Lâm Đồng', lat = 11.5333000, lng = 108.4667000, content = :c WHERE id = :id",
                            [
                                'id' => $p2,
                                'c'  => "Cung đường trekking đẹp nhất Việt Nam nối liền Lâm Đồng và Bình Thuận với những đồi cỏ trải dài bất tận.\n\nChuẩn bị thể lực và trang bị:\n- Giày trekking cổ lửng có độ bám cao.\n- Balo 40L-50L có đai trợ lực hông, mang tối thiểu 3 lít nước/người.\n- Hạ trại tại khu vực đồi Lính để đón bình minh toàn cảnh núi rừng."
                            ]
                        );
                    }
                    if (isset($allPosts[2]['id'])) {
                        $p3 = (int)$allPosts[2]['id'];
                        Database::execute(
                            "UPDATE posts SET category_id = :cat, title = 'Hướng dẫn chọn lều 2 lớp chống mưa và săn mây Tà Xùa cho người mới', location = 'Huyện Bắc Yên (Tà Xùa), Sơn La', lat = 21.2833000, lng = 104.3167000, content = :c WHERE id = :id",
                            [
                                'id'  => $p3,
                                'cat' => $cat3,
                                'c'   => "Khi đi cắm trại vùng núi cao như Tà Xùa (Sơn La) hay Sa Pa, độ ẩm ban đêm và sương mù rất dày.\n\nTiêu chí chọn lều:\n1. Chọn lều 2 lớp có chỉ số chống nước từ PU3000mm trở lên.\n2. Khung nhôm hợp kim nhẹ và chịu gió tốt trên sống lưng khủng long.\n3. Đi kèm tấm lót đáy (footprint) để cách ẩm từ mặt đất."
                            ]
                        );
                    }
                }
            }

            // 5. Đảm bảo bảng products và post_products có dữ liệu mẫu
            $prodCountRows = Database::query("SELECT COUNT(*) AS total FROM products");
            if ((int)($prodCountRows[0]['total'] ?? 0) === 0) {
                $sampleProducts = [
                    [$cat1, 'Lều cắm trại 2 lớp Naturehike Cloud Up 2 chống mưa PU4000mm', 2150000, 'assets/img/tent-forest.png', 'Lều siêu nhẹ khung nhôm 7001, chống mưa lớn và gió mạnh, phù hợp cho 2 người đi săn mây.', 'https://naturehike.vn'],
                    [$cat2, 'Balo Trekking leo núi 50L khung trợ lực chống thấm nước', 1450000, 'assets/img/ta-xua-trek.png', 'Balo dung tích lớn 50L có đệm lưng thoáng khí, đai hông trợ lực và áo mưa balo đi kèm.', 'https://decathlon.vn'],
                    [$cat3, 'Đèn pin đội đầu dã ngoại siêu sáng sạc USB chống nước IPX6', 380000, 'assets/img/moss-form.png', 'Đèn đội đầu rảnh tay khi dựng lều ban đêm hoặc đi rừng sớm, thời lượng pin lên đến 12 giờ.', 'https://decathlon.vn'],
                    [$cat1, 'Túi ngủ lông vũ giữ nhiệt cắm trại vùng cao (5°C - 15°C)', 890000, 'assets/img/tent-forest.png', 'Túi ngủ gấp gọn siêu nhẹ giúp giữ ấm cơ thể khi cắm trại đêm tại Đà Lạt, Tà Xùa, Sa Pa.', 'https://naturehike.vn'],
                    [$cat2, 'Gậy leo núi Trekking hợp kim Carbon khóa bật nhanh', 450000, 'assets/img/ta-xua-trek.png', 'Hỗ trợ giảm tải lực lên khớp gối khi leo dốc và xuống núi trên các cung đường dài.', 'https://decathlon.vn'],
                    [$cat3, 'Bếp gas dã ngoại gấp gọn kèm bộ nồi nấu ăn cắm trại', 620000, 'assets/img/moss-form.png', 'Đầu đốt chắn gió công suất cao cùng bộ nồi hợp kim nhôm anod thu gọn trong balo.', 'https://naturehike.vn']
                ];

                foreach ($sampleProducts as $sp) {
                    Database::execute(
                        'INSERT INTO products (category_id, name, price, image_url, description, link)
                         VALUES (:cat, :name, :price, :img, :desc, :link)',
                        [
                            'cat'   => $sp[0],
                            'name'  => $sp[1],
                            'price' => $sp[2],
                            'img'   => $sp[3],
                            'desc'  => $sp[4],
                            'link'  => $sp[5]
                        ]
                    );
                }
            }

            // 6. Đảm bảo post_products có liên kết dụng cụ cho các bài viết mẫu
            $ppCountRows = Database::query("SELECT COUNT(*) AS total FROM post_products");
            if ((int)($ppCountRows[0]['total'] ?? 0) === 0) {
                $postRows = Database::query("SELECT id FROM posts ORDER BY id ASC LIMIT 3");
                $prodRows = Database::query("SELECT id FROM products ORDER BY id ASC LIMIT 6");
                if (!empty($postRows) && count($prodRows) >= 6) {
                    $p1 = (int)$postRows[0]['id'];
                    Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p1, 'pr' => (int)$prodRows[0]['id']]);
                    Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p1, 'pr' => (int)$prodRows[2]['id']]);
                    Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p1, 'pr' => (int)$prodRows[3]['id']]);

                    if (isset($postRows[1]['id'])) {
                        $p2 = (int)$postRows[1]['id'];
                        Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p2, 'pr' => (int)$prodRows[1]['id']]);
                        Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p2, 'pr' => (int)$prodRows[2]['id']]);
                        Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p2, 'pr' => (int)$prodRows[4]['id']]);
                    }
                    if (isset($postRows[2]['id'])) {
                        $p3 = (int)$postRows[2]['id'];
                        Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p3, 'pr' => (int)$prodRows[0]['id']]);
                        Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p3, 'pr' => (int)$prodRows[3]['id']]);
                        Database::execute("INSERT IGNORE INTO post_products (post_id, product_id) VALUES (:p, :pr)", ['p' => $p3, 'pr' => (int)$prodRows[5]['id']]);
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('DemoSeeder error: ' . $e->getMessage());
        }
    }
}
