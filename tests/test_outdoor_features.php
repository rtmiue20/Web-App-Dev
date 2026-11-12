<?php
chdir(dirname(__DIR__));
require_once 'app/bootstrap.php';
require_once 'app/controllers/OutdoorController.php';

echo "=== KIỂM THỬ TOÀN DIỆN 3 TÍNH NĂNG: CHECKLIST, ĐI CHUNG, BẢN ĐỒ ===\n\n";

$controller = new OutdoorController();
$checklistModel = new Checklist();
$tripModel = new Trip();
$placeReviewModel = new PlaceReview();

// Test 1: Checklist index
echo "[1] Kiểm tra tải trang Checklist (/checklists)...\n";
ob_start();
$controller->checklists();
$out = ob_get_clean();
assert(strlen($out) > 0, "Không render được view checklists");
assert(str_contains($out, 'Checklist đồ dùng'), "Thiếu tiêu đề Checklist");
assert(str_contains($out, 'Tà Năng'), "Thiếu dữ liệu checklist mẫu Tà Năng");
echo "-> OK! Render " . strlen($out) . " bytes\n";

// Test 2: Checklist filter
echo "\n[2] Kiểm tra lọc Checklist theo loại (?type=Trekking)...\n";
$_GET['type'] = 'Trekking';
ob_start();
$controller->checklists();
$out = ob_get_clean();
assert(str_contains($out, 'Trekking'), "Lọc theo Trekking thất bại");
$_GET = [];
echo "-> OK!\n";

// Test 3: Model Checklist CRUD
echo "\n[3] Kiểm tra Model Checklist (Tạo & Xóa)...\n";
$newChkId = $checklistModel->create(
    1,
    'Checklist Test Unit',
    'Leo núi',
    true,
    ['Găng tay bảo hộ', 'Gậy trekking gấp gọn']
);
assert($newChkId > 0, "Tạo checklist thất bại");
$chkData = $checklistModel->getById($newChkId);
assert(!empty($chkData) && count($chkData['items']) === 2, "Dữ liệu items checklist không khớp");
$delRes = $checklistModel->delete($newChkId, 1, false);
assert($delRes === true, "Xóa checklist thất bại");
echo "-> OK! Checklist ID #$newChkId tạo và xóa thành công.\n";

// Test 4: Trips index
echo "\n[4] Kiểm tra tải trang Đi chung (/trips)...\n";
ob_start();
$controller->trips();
$out = ob_get_clean();
assert(strlen($out) > 0, "Không render được view trips");
assert(str_contains($out, 'Nhóm đi chung'), "Thiếu tiêu đề Nhóm đi chung");
assert(str_contains($out, 'Tà Xùa'), "Thiếu dữ liệu chuyến đi mẫu Tà Xùa");
echo "-> OK! Render " . strlen($out) . " bytes\n";

// Test 5: Trip detail & participants
echo "\n[5] Kiểm tra chi tiết chuyến đi (/trips/1) và danh sách thành viên...\n";
ob_start();
$controller->showTrip(1);
$out = ob_get_clean();
assert(str_contains($out, 'Săn mây Sống Lưng Khủng Long Tà Xùa'), "Tiêu đề chi tiết chuyến 1 không khớp");
assert(str_contains($out, 'Thành viên tham gia'), "Thiếu danh sách thành viên");
echo "-> OK! Render " . strlen($out) . " bytes\n";

// Test 6: Model Trip Join & Leave
echo "\n[6] Kiểm tra Tham gia và Rời nhóm đi chung...\n";
$tripParticipants = $tripModel->getParticipants(1);
echo "  Số thành viên hiện tại chuyến 1: " . count($tripParticipants) . "\n";
// Giả lập user 3 tham gia chuyến 1
$joinRes = $tripModel->join(1, 3);
echo "  Kết quả tham gia (User 3): $joinRes\n";
assert(in_array($joinRes, ['joined', 'already']), "Không thể join chuyến đi");
$hasJoined = $tripModel->hasJoined(1, 3);
assert($hasJoined === true, "hasJoined phải là true");
// User 3 rời nhóm
$leaveRes = $tripModel->leave(1, 3);
echo "  Kết quả rời nhóm (User 3): " . ($leaveRes ? 'Thành công' : 'Thất bại') . "\n";
assert($leaveRes === true, "Rời nhóm thất bại");
echo "-> OK!\n";

// Test 7: Map / Places
echo "\n[7] Kiểm tra Bản đồ địa điểm dã ngoại (/places & /map)...\n";
ob_start();
$controller->map();
$out = ob_get_clean();
assert(strlen($out) > 0, "Không render được view map");
assert(str_contains($out, 'Bản đồ địa điểm dã ngoại'), "Thiếu tiêu đề Bản đồ");
assert(str_contains($out, 'places-map'), "Thiếu container bản đồ places-map");
assert(str_contains($out, 'leaflet.js'), "Thiếu thư viện Leaflet");
assert(str_contains($out, 'Đà Lạt') || str_contains($out, 'Tà Năng') || str_contains($out, 'Tà Xùa') || str_contains($out, 'Núi Chứa Chan') || str_contains($out, 'Núi Bà Đen'), "Thiếu địa điểm có tọa độ");
echo "-> OK! Render " . strlen($out) . " bytes\n";

// Test 8: Place Reviews
echo "\n[8] Kiểm tra dữ liệu Đánh giá địa điểm (PlaceReview)...\n";
$places = $placeReviewModel->getPopularPlaces();
echo "  Tìm thấy " . count($places) . " địa điểm có tọa độ trên bản đồ.\n";
assert(count($places) >= 2, "Phải có ít nhất 2 địa điểm");
$p1 = $places[0];
echo "  Địa điểm hàng đầu: " . $p1['title'] . " | Rating: " . $p1['average_rating'] . " (" . $p1['review_count'] . " đánh giá)\n";
assert((float) $p1['average_rating'] > 0, "Điểm rating phải lớn hơn 0");
echo "-> OK!\n";

echo "\n======================================================\n";
echo " TẤT CẢ CÁC BÀI TEST CHO 3 MỤC ĐÃ VƯỢT QUA 100% THÀNH CÔNG!\n";
echo "======================================================\n";
