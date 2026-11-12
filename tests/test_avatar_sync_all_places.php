<?php
chdir(dirname(__DIR__));
require_once 'app/bootstrap.php';
require_once 'app/controllers/OutdoorController.php';
require_once 'app/controllers/PostController.php';
require_once 'app/repositories/CommentRepository.php';

Session::init();

echo "=== TEST: KIỂM THỬ ĐỒNG BỘ ẢNH ĐẠI DIỆN TRÊN TOÀN BỘ HỆ THỐNG ===\n\n";

$db = Database::getInstance();

// 1. Kiểm tra Auth::user() tự động đồng bộ avatar_path từ DB vào Session
echo "[1] Kiểm tra Auth::user() đồng bộ avatar_path từ CSDL vào Session...\n";
$db->query("UPDATE users SET avatar_path = 'uploads/avatars/test_avatar.jpg' WHERE id = 1");
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'admin',
    'role_id' => 2,
    'role_name' => 'admin',
    'avatar_path' => null // Giả lập session cũ chưa có avatar
];

// Gọi Auth::user()
$syncedUser = Auth::user();
assert($syncedUser !== null, "Auth::user() trả về null");
assert($syncedUser['avatar_path'] === 'uploads/avatars/test_avatar.jpg', "Auth::user() không đồng bộ avatar_path từ CSDL vào session");
assert($_SESSION['user']['avatar_path'] === 'uploads/avatars/test_avatar.jpg', "Session['user']['avatar_path'] không được cập nhật");
echo "-> OK! Auth::user() tự động đồng bộ avatar mới nhất từ DB vào Session.\n";

// 2. Kiểm tra Navbar Dock Header hiển thị avatar của user đăng nhập
echo "\n[2] Kiểm tra Navbar Dock Header hiển thị avatar tròn khi đã có ảnh đại diện...\n";
ob_start();
require 'app/views/partials/navbar.php';
$navHtml = ob_get_clean();
assert(str_contains($navHtml, 'test_avatar.jpg'), "Navbar dock không hiển thị ảnh đại diện của người dùng");
assert(str_contains($navHtml, 'dock-user-badge'), "Navbar thiếu dock-user-badge");
echo "-> OK! Navbar Dock hiển thị đúng ảnh đại diện tròn của người dùng đang đăng nhập.\n";

// 3. Kiểm tra danh sách Thành viên tham gia chuyến đi (/trips/{id}) hiển thị avatar
echo "\n[3] Kiểm tra Danh sách thành viên tham gia chuyến đi (/trips/1) hiển thị avatar...\n";
$outdoorController = new OutdoorController();
ob_start();
$outdoorController->showTrip(1);
$tripHtml = ob_get_clean();

assert(str_contains($tripHtml, 'test_avatar.jpg'), "Card thành viên tham gia chuyến đi thiếu ảnh đại diện");
assert(str_contains($tripHtml, 'rounded-circle object-fit-cover shadow-sm border flex-shrink-0'), "Ảnh đại diện thành viên thiếu style chuẩn UI");
echo "-> OK! Card thành viên tham gia trong Chuyến đi nhóm hiển thị avatar đồng bộ.\n";

// 4. Kiểm tra Card chuyến đi ở danh sách (/trips) hiển thị avatar người tổ chức
echo "\n[4] Kiểm tra Danh sách nhóm đi chung (/trips) hiển thị avatar người tổ chức...\n";
ob_start();
$outdoorController->trips();
$tripsListHtml = ob_get_clean();
assert(str_contains($tripsListHtml, 'test_avatar.jpg'), "Card chuyến đi thiếu avatar của người tổ chức");
echo "-> OK! Danh sách chuyến đi hiển thị avatar của người khởi xướng/tổ chức.\n";

// 5. Kiểm tra Card Checklist (/checklists) hiển thị avatar tác giả
echo "\n[5] Kiểm tra Danh sách Checklist (/checklists) hiển thị avatar tác giả...\n";
ob_start();
$outdoorController->checklists();
$checklistsHtml = ob_get_clean();
assert(str_contains($checklistsHtml, 'test_avatar.jpg'), "Card checklist thiếu avatar của người tạo");
echo "-> OK! Danh sách Checklist hiển thị avatar của tác giả checklist.\n";

// 6. Kiểm tra CommentRepository và Stored Procedure sp_comment_list trả về avatar_path
echo "\n[6] Kiểm tra CommentRepository::findByPostId & sp_comment_list trả về avatar_path...\n";
$commentRepo = new CommentRepository();
$comments = $commentRepo->findByPostId(1);
if (!empty($comments)) {
    assert(array_key_exists('avatar_path', $comments[0]), "CommentRepository không trả về trường avatar_path");
    echo "-> OK! CommentRepository trả về đầy đủ avatar_path của người bình luận.\n";
} else {
    echo "-> (Bài viết 1 chưa có comment để test danh sách)\n";
}

// 7. Kiểm tra Card bài viết (/posts) và view bài viết chi tiết
echo "\n[7] Kiểm tra Thẻ bài viết hiển thị avatar tác giả...\n";
ob_start();
(new PostController())->index();
$postsHtml = ob_get_clean();
assert(str_contains($postsHtml, 'test_avatar.jpg'), "Card bài viết thiếu avatar của tác giả");
echo "-> OK! Thẻ bài viết hiển thị avatar tác giả đồng bộ.\n";

// Dọn dẹp lại avatar_path của user 1 về rỗng
$db->query("UPDATE users SET avatar_path = NULL WHERE id = 1");

echo "\n======================================================\n";
echo "🎉 TẤT CẢ TEST ĐỒNG BỘ ẢNH ĐẠI DIỆN ĐỀU PASS 100%!\n";
echo "======================================================\n";
