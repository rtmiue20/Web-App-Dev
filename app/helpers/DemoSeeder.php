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

            // 1b. Đảm bảo bảng comments có cột parent_id để hỗ trợ bình luận lồng nhau (reply nhiều cấp)
            $parentCol = Database::query("SHOW COLUMNS FROM comments LIKE 'parent_id'");
            if (empty($parentCol)) {
                Database::execute("ALTER TABLE comments ADD COLUMN parent_id INT UNSIGNED NULL DEFAULT NULL AFTER user_id");
                try {
                    Database::execute("ALTER TABLE comments ADD CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE");
                } catch (Throwable $fkEx) {
                    // Bỏ qua nếu constraint đã tồn tại
                }
            }

            // 1c. Đảm bảo bảng post_likes có cột vote (1 = Upvote, -1 = Downvote) cho hệ thống Vote
            $voteCol = Database::query("SHOW COLUMNS FROM post_likes LIKE 'vote'");
            if (empty($voteCol)) {
                Database::execute("ALTER TABLE post_likes ADD COLUMN vote TINYINT NOT NULL DEFAULT 1");
            }

            // 2. Đảm bảo bảng roles có đầy đủ 4 vai trò RBAC (member, admin, moderator, guest)
            Database::execute("INSERT IGNORE INTO roles (id, name) VALUES (1, 'member'), (2, 'admin'), (3, 'moderator'), (4, 'guest')");

            $userRows = Database::query("SELECT id FROM users ORDER BY id ASC");
            $defaultHash = password_hash('123456', PASSWORD_DEFAULT);
            if (empty($userRows)) {
                Database::execute(
                    "INSERT IGNORE INTO users (id, username, email, password_hash, role_id) VALUES
                     (1, 'admin', 'admin@forum.local', :h1, 2),
                     (2, 'minh_trekking', 'minh@forum.local', :h2, 1),
                     (3, 'lan_camping', 'lan@forum.local', :h3, 1),
                     (4, 'mod_outdoor', 'mod@forum.local', :h4, 3)",
                    ['h1' => $defaultHash, 'h2' => $defaultHash, 'h3' => $defaultHash, 'h4' => $defaultHash]
                );
                $authorId = 2;
                $secondAuthorId = 3;
            } else {
                $authorId = (int)$userRows[0]['id'];
                $secondAuthorId = isset($userRows[1]['id']) ? (int)$userRows[1]['id'] : $authorId;

                // Đảm bảo có tài khoản mẫu Điều hành viên (moderator) và Quản trị viên (admin) để test RBAC
                $modCheck = Database::query("SELECT id FROM users WHERE role_id = 3 LIMIT 1");
                if (empty($modCheck)) {
                    Database::execute(
                        "INSERT IGNORE INTO users (username, email, password_hash, role_id, bio)
                         VALUES ('mod_outdoor', 'mod@forum.local', :hash, 3, 'Điều hành viên kiểm duyệt nội dung diễn đàn dã ngoại')",
                        ['hash' => $defaultHash]
                    );
                }
                // Cập nhật mật khẩu hợp lệ nếu tài khoản mẫu từ schema.sql đang lưu chuỗi 'hash_demo'
                Database::execute(
                    "UPDATE users SET password_hash = :hash WHERE password_hash = 'hash_demo'",
                    ['hash' => $defaultHash]
                );
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
                    [$cat1, $u1, 'Lều cắm trại 2 lớp Naturehike Cloud Up 2 chống mưa PU4000mm', 2150000, 'assets/img/products/naturehike-cloud-up-2.jpg', 'Lều siêu nhẹ khung nhôm 7001, chống mưa lớn và gió mạnh, phù hợp cho 2 người đi săn mây.', 'https://shopee.vn/search?keyword=leu%20naturehike%20cloud%20up%202'],
                    [$cat2, $u2, 'Balo Trekking leo núi 50L khung trợ lực chống thấm nước', 1450000, 'assets/img/products/balo-trekking-50l.jpg', 'Balo dung tích lớn 50L có đệm lưng thoáng khí, đai hông trợ lực và áo mưa balo đi kèm.', 'https://shopee.vn/search?keyword=balo%20trekking%2050l%20chong%20nuoc'],
                    [$cat3, $u1, 'Đèn pin đội đầu dã ngoại siêu sáng sạc USB chống nước IPX6', 380000, 'assets/img/products/den-pin-doi-dau.jpg', 'Đèn đội đầu rảnh tay khi dựng lều ban đêm hoặc đi rừng sớm, thời lượng pin lên đến 12 giờ.', 'https://shopee.vn/search?keyword=den%20pin%20doi%20dau%20da%20ngoai'],
                    [$cat1, $u2, 'Túi ngủ lông vũ giữ nhiệt cắm trại vùng cao (5°C - 15°C)', 890000, 'assets/img/products/tui-ngu-long-vu.jpg', 'Túi ngủ gấp gọn siêu nhẹ giúp giữ ấm cơ thể khi cắm trại đêm tại Đà Lạt, Tà Xùa, Sa Pa.', 'https://shopee.vn/search?keyword=tui%20ngu%20long%20vu%20cam%20trai'],
                    [$cat2, $u1, 'Gậy leo núi Trekking hợp kim Carbon khóa bật nhanh', 450000, 'assets/img/products/gay-leo-nui-trekking.jpg', 'Hỗ trợ giảm tải lực lên khớp gối khi leo dốc và xuống núi trên các cung đường dài.', 'https://shopee.vn/search?keyword=gay%20leo%20nui%20trekking%20carbon'],
                    [$cat3, $u2, 'Bếp gas dã ngoại gấp gọn kèm bộ nồi nấu ăn cắm trại', 620000, 'assets/img/products/bep-gas-da-ngoai.png', 'Đầu đốt chắn gió công suất cao cùng bộ nồi hợp kim nhôm anod thu gọn trong balo.', 'https://shopee.vn/search?keyword=bep%20ga%20da%20ngoai%20fire%20maple']
                ];

                foreach ($sampleProducts as $sp) {
                    Database::execute(
                        'INSERT INTO products (category_id, user_id, name, price, image_url, description, link)
                         VALUES (:cat, :uid, :name, :price, :img, :desc, :link)',
                        [
                            'cat'   => $sp[0],
                            'uid'   => $sp[1],
                            'name'  => $sp[2],
                            'price' => $sp[3],
                            'img'   => $sp[4],
                            'desc'  => $sp[5],
                            'link'  => $sp[6]
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

            // 7. Đảm bảo có bình luận lồng nhau (reply nhiều cấp) mẫu trên bài viết đầu tiên (chỉ tạo 1 lần đầu)
            $seedLockFile = __DIR__ . '/../../storage/cache/nested_comments_seeded.lock';
            $nestedCheck = Database::query("SELECT COUNT(*) AS total FROM comments WHERE parent_id IS NOT NULL");
            if (!is_file($seedLockFile) && (int)($nestedCheck[0]['total'] ?? 0) === 0) {
                $firstPost = Database::query("SELECT id FROM posts WHERE status = 'published' ORDER BY id ASC LIMIT 1");
                if (!empty($firstPost)) {
                    $firstPostId = (int)$firstPost[0]['id'];
                    $rootComments = Database::query("SELECT id, user_id FROM comments WHERE post_id = :pid AND parent_id IS NULL ORDER BY id ASC LIMIT 1", ['pid' => $firstPostId]);
                    if (empty($rootComments)) {
                        Database::execute(
                            "INSERT INTO comments (post_id, user_id, parent_id, content, created_at)
                             VALUES (:pid, :uid, NULL, :cnt, NOW())",
                            [
                                'pid' => $firstPostId,
                                'uid' => $secondAuthorId,
                                'cnt' => 'Cảm ơn bài chia sẻ rất chi tiết! Cho mình hỏi đường lên Đồi Đa Phú ban đêm xe tay ga có dễ đi không bạn?'
                            ]
                        );
                        $rootComments = Database::query("SELECT id, user_id FROM comments WHERE post_id = :pid AND parent_id IS NULL ORDER BY id ASC LIMIT 1", ['pid' => $firstPostId]);
                    }
                    if (!empty($rootComments)) {
                        $rootId = (int)$rootComments[0]['id'];
                        Database::execute(
                            "INSERT INTO comments (post_id, user_id, parent_id, content, created_at)
                             VALUES (:pid, :uid, :parent_id, :cnt, NOW())",
                            [
                                'pid'       => $firstPostId,
                                'uid'       => $authorId,
                                'parent_id' => $rootId,
                                'cnt'       => 'Đường nhựa dễ đi khoảng 90%, chỉ có đoạn dốc đất cuối khoảng 300m hơi trơn nếu trời mưa nên bạn đi xe số hoặc đi trước 16h chiều là an toàn nhất nhé!'
                            ]
                        );
                        $level1Rows = Database::query("SELECT id FROM comments WHERE parent_id = :pid ORDER BY id DESC LIMIT 1", ['pid' => $rootId]);
                        if (!empty($level1Rows)) {
                            $level1Id = (int)$level1Rows[0]['id'];
                            Database::execute(
                                "INSERT INTO comments (post_id, user_id, parent_id, content, created_at)
                                 VALUES (:pid, :uid, :parent_id, :cnt, NOW())",
                                [
                                    'pid'       => $firstPostId,
                                    'uid'       => $secondAuthorId,
                                    'parent_id' => $level1Id,
                                    'cnt'       => 'Tuyệt vời quá, cuối tuần này nhóm mình sẽ mang lều 2 lớp lên săn mây theo đúng lịch trình của bạn luôn!'
                                ]
                            );
                        }
                    }
                }
                $cacheDir = dirname($seedLockFile);
                if (!is_dir($cacheDir)) {
                    @mkdir($cacheDir, 0777, true);
                }
                @file_put_contents($seedLockFile, date('c'));
            }

            // 8. Đảm bảo có dữ liệu vote mẫu để kiểm thử sắp xếp Hot / Mới / Top ngay lập tức (chỉ tạo 1 lần đầu)
            $voteLockFile = __DIR__ . '/../../storage/cache/post_votes_seeded.lock';
            $voteCountRows = Database::query("SELECT COUNT(*) AS total FROM post_likes");
            if (!is_file($voteLockFile) && (int)($voteCountRows[0]['total'] ?? 0) === 0) {
                $pubPostRows = Database::query("SELECT id FROM posts WHERE status = 'published' ORDER BY id ASC LIMIT 3");
                $allUserRows = Database::query("SELECT id FROM users ORDER BY id ASC LIMIT 4");
                if (count($pubPostRows) >= 2 && count($allUserRows) >= 2) {
                    $p1 = (int)$pubPostRows[0]['id'];
                    $p2 = (int)$pubPostRows[1]['id'];
                    $p3 = isset($pubPostRows[2]['id']) ? (int)$pubPostRows[2]['id'] : 0;

                    // Bài 2 có nhiều Upvote nhất (Top 1 về điểm vote), Bài 1 có nhiều bình luận + vote (Hot)
                    foreach ($allUserRows as $idx => $uRow) {
                        $uid = (int)$uRow['id'];
                        Database::execute("INSERT IGNORE INTO post_likes (post_id, user_id, vote) VALUES (:p, :u, 1)", ['p' => $p2, 'u' => $uid]);
                        if ($idx < 2) {
                            Database::execute("INSERT IGNORE INTO post_likes (post_id, user_id, vote) VALUES (:p, :u, 1)", ['p' => $p1, 'u' => $uid]);
                        }
                        if ($p3 > 0 && $idx === 0) {
                            Database::execute("INSERT IGNORE INTO post_likes (post_id, user_id, vote) VALUES (:p, :u, 1)", ['p' => $p3, 'u' => $uid]);
                        }
                    }
                }
                $cacheDir = dirname($voteLockFile);
                if (!is_dir($cacheDir)) {
                    @mkdir($cacheDir, 0777, true);
                }
                @file_put_contents($voteLockFile, date('c'));
            }

            // 9. Đảm bảo 5 bảng tính năng dã ngoại (Checklist, Đi chung, Đánh giá địa điểm) luôn tồn tại
            Database::execute("
                CREATE TABLE IF NOT EXISTS trip_checklists (
                  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  user_id    INT UNSIGNED NOT NULL,
                  title      VARCHAR(120) NOT NULL,
                  trip_type  VARCHAR(60) NOT NULL,
                  is_public  TINYINT(1) NOT NULL DEFAULT 0,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ");

            Database::execute("
                CREATE TABLE IF NOT EXISTS checklist_items (
                  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  checklist_id INT UNSIGNED NOT NULL,
                  item         VARCHAR(120) NOT NULL,
                  sort_order   INT UNSIGNED NOT NULL DEFAULT 0,
                  FOREIGN KEY (checklist_id) REFERENCES trip_checklists(id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ");

            Database::execute("
                CREATE TABLE IF NOT EXISTS trips (
                  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  user_id           INT UNSIGNED NOT NULL,
                  title             VARCHAR(150) NOT NULL,
                  description       TEXT NOT NULL,
                  location          VARCHAR(150) NOT NULL,
                  lat               DECIMAL(10, 7) NULL,
                  lng               DECIMAL(10, 7) NULL,
                  starts_at         DATETIME NOT NULL,
                  max_participants  INT UNSIGNED NOT NULL,
                  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ");

            Database::execute("
                CREATE TABLE IF NOT EXISTS trip_participants (
                  trip_id   INT UNSIGNED NOT NULL,
                  user_id   INT UNSIGNED NOT NULL,
                  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (trip_id, user_id),
                  FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
                  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ");

            Database::execute("
                CREATE TABLE IF NOT EXISTS place_reviews (
                  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  post_id    INT UNSIGNED NOT NULL,
                  user_id    INT UNSIGNED NOT NULL,
                  rating     TINYINT UNSIGNED NOT NULL,
                  review     VARCHAR(1000) NOT NULL,
                  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  UNIQUE KEY uq_place_review_user (post_id, user_id),
                  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ");

            // 10. Nạp dữ liệu mẫu cho Checklist nếu bảng trống
            $chkCount = (int) (Database::query("SELECT COUNT(*) AS total FROM trip_checklists")[0]['total'] ?? 0);
            if ($chkCount === 0) {
                Database::execute(
                    "INSERT INTO trip_checklists (id, user_id, title, trip_type, is_public, created_at)
                     VALUES (1, :uid, 'Checklist Trekking Tà Năng – Phan Dũng 2 ngày 1 đêm', 'Trekking', 1, NOW())",
                    ['uid' => $authorId]
                );
                $items1 = [
                    'Balo dã ngoại trợ lực 40L - 50L',
                    'Giày trekking chuyên dụng cổ lửng bám đá chống trơn',
                    'Túi ngủ giữ nhiệt mỏng 15°C & lều 2 lớp chịu gió',
                    'Nước uống bù khoáng (tối thiểu 3.5 lít/người)',
                    'Bộ đồ sơ cứu y tế cá nhân & thuốc sát trùng, băng gạc',
                    'Đèn pin đội đầu siêu sáng + 2 bộ pin sạc dự phòng',
                    'Thực phẩm gọn nhẹ (thanh protein, chocolate, lương khô)',
                    'Áo khoác gió chống nước Gore-Tex & áo mưa bộ',
                    'Còi cứu hộ sinh tồn, dao đa năng & bật lửa chống nước'
                ];
                foreach ($items1 as $idx => $it) {
                    Database::execute(
                        "INSERT INTO checklist_items (checklist_id, item, sort_order) VALUES (1, :item, :order)",
                        ['item' => $it, 'order' => $idx]
                    );
                }

                Database::execute(
                    "INSERT INTO trip_checklists (id, user_id, title, trip_type, is_public, created_at)
                     VALUES (2, :uid, 'Checklist Cắm trại gia đình & bạn bè cuối tuần bên hồ', 'Cắm trại', 1, NOW())",
                    ['uid' => $secondAuthorId]
                );
                $items2 = [
                    'Lều cắm trại 4 người tự bung chống thấm nước',
                    'Tấm bạt tăng che nắng mưa (Tarp 3x4m)',
                    'Đệm hơi cách nhiệt & gối hơi tự bơm',
                    'Bếp nướng BBQ dã ngoại & than không khói',
                    'Bộ bàn ghế xếp gọn du lịch (4 ghế + 1 bàn)',
                    'Thùng giữ nhiệt 24L trữ thịt và đồ uống lạnh',
                    'Đèn bão LED treo lều & dây đèn trang trí ấm cúng',
                    'Bình xịt chống muỗi & côn trùng dã ngoại',
                    'Túi rác tự phân hủy (Leave No Trace - Giữ sạch thiên nhiên)'
                ];
                foreach ($items2 as $idx => $it) {
                    Database::execute(
                        "INSERT INTO checklist_items (checklist_id, item, sort_order) VALUES (2, :item, :order)",
                        ['item' => $it, 'order' => $idx]
                    );
                }

                Database::execute(
                    "INSERT INTO trip_checklists (id, user_id, title, trip_type, is_public, created_at)
                     VALUES (3, :uid, 'Checklist Săn mây Đồi Đa Phú – Đà Lạt ấm áp', 'Săn mây', 1, NOW())",
                    ['uid' => $authorId]
                );
                $items3 = [
                    'Áo phao giữ nhiệt hoặc áo lông vũ siêu nhẹ',
                    'Bình giữ nhiệt 1L chứa nước sôi pha trà/cà phê',
                    'Bộ ấm phin pha cà phê dã ngoại',
                    'Miếng dán giữ nhiệt bàn chân',
                    'Máy ảnh / Tripod chắc chắn chống rung gió sớm',
                    'Tấm bạt lót đất chống sương đọng đáy lều'
                ];
                foreach ($items3 as $idx => $it) {
                    Database::execute(
                        "INSERT INTO checklist_items (checklist_id, item, sort_order) VALUES (3, :item, :order)",
                        ['item' => $it, 'order' => $idx]
                    );
                }
            }

            // 11. Nạp dữ liệu mẫu cho Chuyến đi nhóm ("Đi chung") nếu bảng trống
            $tripCount = (int) (Database::query("SELECT COUNT(*) AS total FROM trips")[0]['total'] ?? 0);
            if ($tripCount === 0) {
                $t1Starts = date('Y-m-d 06:00:00', strtotime('+3 days'));
                Database::execute(
                    "INSERT INTO trips (id, user_id, title, description, location, lat, lng, starts_at, max_participants, created_at)
                     VALUES (1, :uid, 'Săn mây Sống Lưng Khủng Long Tà Xùa 2 ngày 1 đêm', :desc, 'Xã Tà Xùa, Huyện Bắc Yên, Sơn La', 21.2833000, 104.3167000, :starts, 8, NOW())",
                    [
                        'uid' => $authorId,
                        'desc' => "Nhóm mình hiện đã có 4 người (2 nam 2 nữ), cần tìm thêm 4 bạn nữa ghép xe Limousine từ Hà Nội lên Bắc Yên rồi thuê xe máy lên bản Tà Xùa săn mây.\n\nLịch trình tóm tắt:\n- Sáng thứ 7: Tập trung 6h00 tại bến xe Mỹ Đình, di chuyển lên Tà Xùa.\n- Chiều: Check-in homestay, đón hoàng hôn đỉnh Gió.\n- Sáng CN: 5h00 đón bình minh và săn biển mây tại Sống lưng khủng long Háng Đồng.\n\nChi phí dự kiến: ~1.200.000đ/người (chia đều tiền xe + homestay + ăn uống).",
                        'starts' => $t1Starts
                    ]
                );
                Database::execute("INSERT IGNORE INTO trip_participants (trip_id, user_id, joined_at) VALUES (1, :u1, NOW())", ['u1' => $authorId]);
                Database::execute("INSERT IGNORE INTO trip_participants (trip_id, user_id, joined_at) VALUES (1, :u2, NOW())", ['u2' => $secondAuthorId]);

                $t2Starts = date('Y-m-d 14:30:00', strtotime('+5 days'));
                Database::execute(
                    "INSERT INTO trips (id, user_id, title, description, location, lat, lng, starts_at, max_participants, created_at)
                     VALUES (2, :uid, 'Cắm trại ngắm hoàng hôn Hồ Trị An cuối tuần thư giãn', :desc, 'Hồ Trị An, Huyện Vĩnh Cửu, Đồng Nai', 11.0825000, 107.0375000, :starts, 10, NOW())",
                    [
                        'uid' => $secondAuthorId,
                        'desc' => "Rủ rê anh chị em đam mê chill dã ngoại cuối tuần tại bãi cỏ Mã Đà bên hồ Trị An. Tụi mình đã chuẩn bị sẵn lều to, bếp nướng BBQ và loa mini nghe nhạc.\n\nDi chuyển bằng xe máy từ TP.HCM mất khoảng 2 tiếng. Đi chung xe hoặc tự túc đều được, ăn uống nướng thịt buổi tối chia đều.\n\nLưu ý: Tuân thủ nguyên tắc Leave No Trace, dọn sạch rác trước khi về.",
                        'starts' => $t2Starts
                    ]
                );
                Database::execute("INSERT IGNORE INTO trip_participants (trip_id, user_id, joined_at) VALUES (2, :u, NOW())", ['u' => $secondAuthorId]);

                $t3Starts = date('Y-m-d 05:30:00', strtotime('+7 days'));
                Database::execute(
                    "INSERT INTO trips (id, user_id, title, description, location, lat, lng, starts_at, max_participants, created_at)
                     VALUES (3, :uid, 'Leo núi Bà Đen – Cung đường Cột Điện rèn luyện thể lực', :desc, 'Núi Bà Đen, TP. Tây Ninh', 11.3823834, 106.1702210, :starts, 6, NOW())",
                    [
                        'uid' => $authorId,
                        'desc' => "Chuyến leo núi về trong ngày dành cho các bạn muốn thử sức bền và ngắm mây Tây Ninh. Cung Cột Điện có bóng mát nhưng dốc đứng và nhiều đá tảng.\n\nYêu cầu trang bị: Giày bám tốt, gậy leo núi, tối thiểu 2 lít nước và điện giải. Tập trung lúc 5h30 sáng tại chân núi.",
                        'starts' => $t3Starts
                    ]
                );
                Database::execute("INSERT IGNORE INTO trip_participants (trip_id, user_id, joined_at) VALUES (3, :u, NOW())", ['u' => $authorId]);
            }

            // 12. Nạp dữ liệu mẫu cho Đánh giá địa điểm (Place Reviews) để bản đồ có điểm sao và review
            $revCount = (int) (Database::query("SELECT COUNT(*) AS total FROM place_reviews")[0]['total'] ?? 0);
            if ($revCount === 0) {
                $pubPosts = Database::query("SELECT id FROM posts WHERE status = 'published' AND lat IS NOT NULL AND lng IS NOT NULL ORDER BY id ASC");
                $users = Database::query("SELECT id FROM users ORDER BY id ASC LIMIT 3");
                if (!empty($pubPosts) && count($users) >= 2) {
                    $u1 = (int)$users[0]['id'];
                    $u2 = (int)$users[1]['id'];
                    $u3 = isset($users[2]['id']) ? (int)$users[2]['id'] : $u1;

                    foreach ($pubPosts as $pIdx => $pRow) {
                        $pid = (int)$pRow['id'];
                        if ($pIdx === 0) {
                            Database::execute(
                                "INSERT IGNORE INTO place_reviews (post_id, user_id, rating, review, created_at)
                                 VALUES (:pid, :uid, 5, 'Địa điểm săn mây Đà Lạt tuyệt vời nhất mình từng đến! Không gian thoáng đãng, buổi sớm mây luồn qua thung lũng đẹp như tranh vẽ.', NOW())",
                                ['pid' => $pid, 'uid' => $u2]
                            );
                            if ($u3 !== $u2) {
                                Database::execute(
                                    "INSERT IGNORE INTO place_reviews (post_id, user_id, rating, review, created_at)
                                     VALUES (:pid, :uid, 5, 'Đường lên dốc đất hơi khó đi một chút nếu trời mưa, nhưng bù lại cảnh hoàng hôn và bình minh cực kỳ xứng đáng.', NOW())",
                                    ['pid' => $pid, 'uid' => $u3]
                                );
                            }
                        } elseif ($pIdx === 1) {
                            Database::execute(
                                "INSERT IGNORE INTO place_reviews (post_id, user_id, rating, review, created_at)
                                 VALUES (:pid, :uid, 5, 'Cung đường trekking huyền thoại. Cảnh sắc đồi cỏ mênh mông bạt ngàn, gió mát lộng. Rất khuyến khích trải nghiệm!', NOW())",
                                ['pid' => $pid, 'uid' => $u1]
                            );
                        } else {
                            Database::execute(
                                "INSERT IGNORE INTO place_reviews (post_id, user_id, rating, review, created_at)
                                 VALUES (:pid, :uid, 4, 'Khung cảnh thiên nhiên đẹp và trong lành, địa điểm cắm trại lý tưởng cho những ai thích sự yên tĩnh.', NOW())",
                                ['pid' => $pid, 'uid' => $u2]
                            );
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('DemoSeeder error: ' . $e->getMessage());
        }
    }
}
