<?php
chdir(dirname(__DIR__));
require_once 'app/bootstrap.php';
require_once 'app/controllers/OutdoorController.php';

Session::init();

echo "=== TEST: KIỂM THỬ KHỞI TẠO VÀ XÁC THỰC THỜI GIAN KHỞI HÀNH (TRIPS) ===\n\n";

$controller = new OutdoorController();

// 1. Kiểm tra render view /trips ban đầu:
echo "[1] Kiểm tra trường thời gian khởi hành trong form trips (để trống ban đầu, có icon lịch bên phải, không hardcode preset)...\n";
$_SESSION['user'] = ['id' => 1, 'username' => 'admin', 'role_id' => 2, 'role_name' => 'admin'];
ob_start();
$controller->trips();
$html = ob_get_clean();

assert(str_contains($html, 'name="starts_at"'), "Form thiếu trường starts_at");
// Check value is empty by default so user picks their own date/time:
preg_match('/name="starts_at"[^>]*value="([^"]*)"/', $html, $matches);
assert(empty($matches[1]), "Trường starts_at không được ép giá trị mặc định, phải để trống cho người dùng chọn");
assert(str_contains($html, 'bi-calendar-event'), "Thiếu icon lịch phía tay phải ô nhập");
assert(str_contains($html, '#trip-date::-webkit-calendar-picker-indicator'), "Thiếu style picker indicator cho ô lịch");
assert(!str_contains($html, 'setTripDate'), "Vẫn còn hàm JS setTripDate hardcode");
assert(!str_contains($html, 'Sáng mai (07:00)'), "Vẫn còn nút chọn nhanh sáng mai");
assert(!str_contains($html, 'T7 tới (06:00)'), "Vẫn còn nút chọn nhanh T7");
echo "-> OK! Ô nhập để trống sạch sẽ, có icon lịch phía tay phải, đã xóa hoàn toàn các nút hardcode phía dưới.\n";

// 2. Kiểm tra validation khi có lỗi (ví dụ tên chuyến đi quá ngắn):
echo "\n[2] Kiểm tra re-render form trips khi có lỗi xác thực...\n";
$_SESSION['user'] = ['id' => 1, 'username' => 'admin', 'role_id' => 2, 'role_name' => 'admin'];
$_POST = [
    '_csrf' => Csrf::token(),
    'title' => 'abc', // quá ngắn (< 5)
    'description' => 'Mô tả ngắn',
    'location' => 'Hà Nội',
    'starts_at' => date('Y-m-d\T07:00', strtotime('-1 day')), // ngày trong quá khứ
    'max_participants' => 5,
];

ob_start();
$controller->storeTrip();
$errorHtml = ob_get_clean();

assert(str_contains($errorHtml, 'Tên chuyến đi cần từ 5 đến 150 ký tự.'), "Không hiển thị lỗi tên chuyến đi");
assert(str_contains($errorHtml, 'Thời gian khởi hành phải ở trong tương lai'), "Không hiển thị lỗi ngày khởi hành");
assert(str_contains($errorHtml, 'Điều hướng phân trang nhóm đi chung'), "Thanh phân trang bị mất khi form có lỗi");
echo "-> OK! Re-render form trips khi có lỗi bảo toàn đầy đủ biến phân trang và thông báo lỗi rõ ràng.\n";

// 3. Kiểm tra validation storeChecklist khi có lỗi:
echo "\n[3] Kiểm tra re-render form checklists khi có lỗi xác thực...\n";
$_POST = [
    '_csrf' => Csrf::token(),
    'title' => 'a', // quá ngắn
    'trip_type' => 'Cắm trại',
    'items' => '',
];

ob_start();
$controller->storeChecklist();
$chkErrorHtml = ob_get_clean();

assert(str_contains($chkErrorHtml, 'Tên checklist cần từ 3 đến 120 ký tự.'), "Không hiển thị lỗi tên checklist");
assert(str_contains($chkErrorHtml, 'Điều hướng phân trang'), "Thanh phân trang checklist bị mất khi form có lỗi");
echo "-> OK! Re-render form checklists bảo toàn phân trang.\n";

// 4. Kiểm tra nút Gửi thư và các nút trong modal Liên hệ người tổ chức:
echo "\n[4] Kiểm tra UI nút 'Gửi thư' trong modal Liên hệ người tổ chức (/trips/1)...\n";
ob_start();
$controller->showTrip(1);
$tripDetailHtml = ob_get_clean();

assert(str_contains($tripDetailHtml, 'btn-outline-success rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1'), "Nút Gửi thư thiếu class outline success cân đối");
assert(str_contains($tripDetailHtml, 'bi-envelope-fill'), "Nút Gửi thư thiếu icon envelope");
assert(!str_contains($tripDetailHtml, 'btn-primary rounded-pill px-2.5 py-1'), "Nút Gửi thư vẫn còn dùng class btn-primary gây lỗi đen to");
echo "-> OK! Nút Gửi thư và modal liên hệ đạt chuẩn cân đối, tinh tế.\n";

echo "\n======================================================\n";
echo " TẤT CẢ CÁC BÀI TEST THỜI GIAN KHỞI HÀNH ĐÃ ĐẠT 100%!\n";
echo "======================================================\n";
