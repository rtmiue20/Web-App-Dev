<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/functions.php';

Session::init();

echo "=== TEST PROFILE TABS & PENDING POST DELETION ===\n\n";

// 1. Kiểm tra cấu trúc view profile.php
$profileContent = file_get_contents(__DIR__ . '/../app/views/users/profile.php');
assert(strpos($profileContent, 'profile-nav-dock') !== false, "Thiếu class profile-nav-dock");
assert(strpos($profileContent, 'badge-pending-pill') !== false, "Thiếu class badge-pending-pill");
assert(strpos($profileContent, 'overflow-x: auto') !== false, "Thiếu overflow-x: auto để scroll mượt mà không vỡ dòng");
assert(strpos($profileContent, 'bi-trash3') !== false, "Thiếu icon thùng rác xóa bài viết trong profile");
assert(strpos($profileContent, 'name="redirect" value="profile"') !== false, "Thiếu hidden input redirect profile");
echo "[✓ PASS] Profile view chứa dock tab và nút xóa bài viết với redirect=profile\n";

// 2. Kiểm tra post show view
$showContent = file_get_contents(__DIR__ . '/../app/views/posts/show.php');
assert(strpos($showContent, 'Hủy / Xóa bài viết') !== false, "Thiếu nút Hủy / Xóa bài viết trong banner chờ duyệt");
assert(strpos($showContent, 'name="redirect" value="profile"') !== false, "Thiếu hidden input redirect profile trong show banner");
echo "[✓ PASS] Post show view chứa nút Hủy / Xóa bài viết trong banner chờ duyệt\n";

// 3. Kiểm tra PostController::delete logic redirect
$controllerContent = file_get_contents(__DIR__ . '/../app/controllers/PostController.php');
assert(strpos($controllerContent, "\$_POST['redirect'] === 'profile'") !== false, "PostController chưa hỗ trợ redirect về profile?tab=posts");
echo "[✓ PASS] PostController hỗ trợ redirect về /profile?tab=posts khi xóa thành công\n";

echo "\n============================================\n";
echo "🎉 TẤT CẢ TEST PROFILE & DELETE ĐỀU PASS!\n";
