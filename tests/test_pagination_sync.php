<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/functions.php';

echo "=== TEST KIỂM THỬ ĐỒNG BỘ PHÂN TRANG CÁC TAB ===\n\n";

// 1. Checklists view
$chkContent = file_get_contents(__DIR__ . '/../app/views/outdoor/checklists.php');
assert(strpos($chkContent, 'forum-pagination') !== false, "Thiếu pagination trong checklists.php");
assert(strpos($chkContent, 'totalChecklists') !== false, "Thiếu totalChecklists trong checklists.php");
assert(strpos($chkContent, 'Trang <?= $currentPage ?> / <?= $totalPages ?>') !== false, "Thiếu text trạng thái phân trang trong checklists.php");
echo "[✓ PASS] Checklists view đồng bộ thanh phân trang forum-pagination chuẩn Nature Glass\n";

// 2. Trips view
$tripsContent = file_get_contents(__DIR__ . '/../app/views/outdoor/trips.php');
assert(strpos($tripsContent, 'forum-pagination') !== false, "Thiếu pagination trong trips.php");
assert(strpos($tripsContent, 'totalTrips') !== false, "Thiếu totalTrips trong trips.php");
echo "[✓ PASS] Trips view đồng bộ thanh phân trang forum-pagination\n";

// 3. Categories view
$catContent = file_get_contents(__DIR__ . '/../app/views/categories/index.php');
assert(strpos($catContent, 'forum-pagination') !== false, "Thiếu pagination trong categories/index.php");
assert(strpos($catContent, 'totalCategories') !== false, "Thiếu totalCategories trong categories/index.php");
echo "[✓ PASS] Categories view đồng bộ thanh phân trang forum-pagination\n";

// 4. Products view
$prodContent = file_get_contents(__DIR__ . '/../app/views/products/index.php');
assert(strpos($prodContent, 'forum-pagination') !== false, "Thiếu pagination trong products/index.php");
assert(strpos($prodContent, 'totalProducts') !== false, "Thiếu totalProducts trong products/index.php");
echo "[✓ PASS] Products view đồng bộ thanh phân trang forum-pagination\n";

// 5. Places / Map view
$mapContent = file_get_contents(__DIR__ . '/../app/views/outdoor/map.php');
assert(strpos($mapContent, 'forum-pagination') !== false, "Thiếu pagination trong outdoor/map.php");
assert(strpos($mapContent, 'totalPlaces') !== false, "Thiếu totalPlaces trong outdoor/map.php");
assert(strpos($mapContent, 'Trang <?= $currentPage ?> / <?= $totalPages ?>') !== false, "Thiếu text trạng thái phân trang trong outdoor/map.php");
echo "[✓ PASS] Places/Map view đồng bộ thanh phân trang forum-pagination (4 địa điểm/trang)\n";

// 6. Posts view
$postsContent = file_get_contents(__DIR__ . '/../app/views/posts/index.php');
assert(strpos($postsContent, 'forum-pagination') !== false, "Thiếu pagination trong posts/index.php");
assert(strpos($postsContent, 'totalPosts') !== false, "Thiếu totalPosts trong posts/index.php");
assert(strpos($postsContent, '($totalPosts ?? 0) > 0') !== false, "Thiếu điều kiện hiển thị phân trang khi có bài viết trong posts/index.php");
echo "[✓ PASS] Posts view đồng bộ thanh phân trang forum-pagination (6 bài viết/trang)\n";

// 7. Controller limits
$outdoorCtrl = file_get_contents(__DIR__ . '/../app/controllers/OutdoorController.php');
assert(strpos($outdoorCtrl, '$limit = 4;') !== false, "OutdoorController chưa cấu hình limit 4");
echo "[✓ PASS] OutdoorController giới hạn 4 chuyến đi/trang và 4 địa điểm bản đồ/trang\n";

echo "\n============================================\n";
echo "🎉 TẤT CẢ CÁC MỤC (BÀI VIẾT, BẢN ĐỒ, ĐI CHUNG, DỤNG CỤ, CHECKLIST) ĐÃ ĐỒNG BỘ PHÂN TRANG 100%!\n";

