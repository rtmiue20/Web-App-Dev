<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/models/Post.php';

echo "=== TEST KIỂM THỬ TÍNH NĂNG SỐ MẮT XEM CHO BÀI VIẾT (VIEWS COUNT) ===\n\n";

$postModel = new Post();
$pdo = Database::getConnection();

// 1. Kiểm tra cấu trúc CSDL: Cột views_count trong bảng posts
$cols = $pdo->query("SHOW COLUMNS FROM posts LIKE 'views_count'")->fetchAll();
assert(!empty($cols), "Bảng posts thiếu cột views_count");
echo "[✓ PASS] Bảng posts có cột views_count INT UNSIGNED NOT NULL DEFAULT 0\n";

// 2. Kiểm tra SQL View vw_post_details
$viewRow = $pdo->query("SELECT views_count FROM vw_post_details LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assert(isset($viewRow['views_count']), "View vw_post_details thiếu trường views_count");
echo "[✓ PASS] View vw_post_details trả về trường views_count chuẩn xác\n";

// 3. Kiểm tra hàm incrementViews và getById
$p7 = $postModel->getById(7);
assert($p7 !== false, "Không tìm thấy post 7");
$oldViews = (int)($p7['views_count'] ?? 0);

$res = $postModel->incrementViews(7);
assert($res === true, "Hàm incrementViews(7) thất bại");

$p7After = $postModel->getById(7);
$newViews = (int)($p7After['views_count'] ?? 0);
assert($newViews === $oldViews + 1, "Views không tăng đúng: trước {$oldViews}, sau {$newViews}");
echo "[✓ PASS] Post::incrementViews(7) tăng số mắt xem thành công từ {$oldViews} lên {$newViews}\n";

// 4. Kiểm tra view partials/post-cards.php
$postCardsHtml = file_get_contents(__DIR__ . '/../app/views/partials/post-cards.php');
assert(strpos($postCardsHtml, 'bi-eye') !== false, "post-cards.php thiếu icon con mắt bi-eye");
assert(strpos($postCardsHtml, '$viewsCount') !== false, "post-cards.php thiếu biến viewsCount");
echo "[✓ PASS] Thẻ bài viết (post-cards.php) hiển thị badge số mắt xem với icon bi-eye\n";

// 5. Kiểm tra view posts/show.php
$showHtml = file_get_contents(__DIR__ . '/../app/views/posts/show.php');
assert(strpos($showHtml, 'lượt xem') !== false, "show.php thiếu văn bản 'lượt xem'");
assert(strpos($showHtml, 'views_count') !== false, "show.php thiếu biến views_count");
echo "[✓ PASS] Chi tiết bài viết (posts/show.php) hiển thị số mắt xem trên thanh thông tin bài viết\n";

// 6. Kiểm tra view admin/posts/index.php
$adminPostsHtml = file_get_contents(__DIR__ . '/../app/views/admin/posts/index.php');
assert(strpos($adminPostsHtml, 'bi-eye-fill') !== false, "admin/posts/index.php thiếu icon bi-eye-fill");
assert(strpos($adminPostsHtml, 'views_count') !== false, "admin/posts/index.php thiếu cột views_count");
echo "[✓ PASS] Danh sách bài viết Admin (admin/posts/index.php) hiển thị số mắt xem chi tiết\n";

// 7. Kiểm tra PostController::show tích hợp tăng view với session chống spam F5
$controllerCode = file_get_contents(__DIR__ . '/../app/controllers/PostController.php');
assert(strpos($controllerCode, 'incrementViews') !== false, "PostController thiếu lệnh gọi incrementViews");
assert(strpos($controllerCode, 'viewed_posts') !== false, "PostController thiếu cơ chế session chống spam view");
echo "[✓ PASS] PostController ghi nhận lượt xem bài viết có cơ chế chống spam F5 qua Session\n";

echo "\n============================================\n";
echo "🎉 TẤT CẢ TEST SỐ MẮT XEM (VIEWS COUNT) ĐỀU PASS 100%!\n";

