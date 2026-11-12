<?php
chdir(dirname(__DIR__));
require_once 'app/bootstrap.php';
require_once 'app/controllers/OutdoorController.php';

Session::init();

echo "=== TEST: KIỂM THỬ TÍNH NĂNG LIÊN HỆ / GỬI THƯ CHO TRƯỞNG ĐOÀN (TRIP CONTACT) ===\n\n";

$controller = new OutdoorController();

// 1. Kiểm tra UI Modal liên hệ trong view trip-show (/trips/1)
echo "[1] Kiểm tra cấu trúc UI/UX Modal liên hệ trưởng đoàn...\n";
$_SESSION['user'] = ['id' => 2, 'username' => 'hoang_trekker', 'role_id' => 1, 'role_name' => 'member', 'email' => 'hoang@example.com'];
ob_start();
$controller->showTrip(1);
$html = ob_get_clean();

assert(str_contains($html, 'id="contactOrganizerModal"'), "Thiếu modal #contactOrganizerModal");
assert(str_contains($html, 'id="contactFormBox"'), "Thiếu khung form soạn thư #contactFormBox");
assert(str_contains($html, 'id="contactMessageInput"'), "Thiếu textarea #contactMessageInput");
assert(str_contains($html, 'id="btnSubmitOrganizerMessage"'), "Thiếu nút bấm gửi thư #btnSubmitOrganizerMessage");
assert(str_contains($html, 'id="contactSuccessBox"'), "Thiếu hộp phản hồi thành công #contactSuccessBox");
assert(str_contains($html, 'onclick="copyOrganizerEmail()"'), "Thiếu tính năng sao chép email");
assert(str_contains($html, 'onclick="focusContactMessageForm()"'), "Thiếu trigger focus soạn thư khi bấm Gửi thư");
assert(str_contains($html, 'submitOrganizerMessage('), "Thiếu hàm submitOrganizerMessage");
assert(str_contains($html, 'mail.google.com'), "Thiếu liên kết mở Gmail trực tiếp");

echo "-> OK! Modal có đầy đủ giao diện soạn thư, sao chép email, mở Gmail và phản hồi trực quan.\n";

// Helper thực thi API contact qua isolated process
function callContactApi(int $tripId, array $postData): array {
    $token = Csrf::token();
    $postData['_csrf'] = $token;
    $tmpFile = __DIR__ . '/_tmp_contact_test.php';
    $code = '<?php
chdir(' . var_export(dirname(__DIR__), true) . ');
require_once "app/bootstrap.php";
require_once "app/controllers/OutdoorController.php";
Session::init();
$_SESSION["_csrf_token"] = ' . var_export($token, true) . ';
$_SERVER["HTTP_X_REQUESTED_WITH"] = "xmlhttprequest";
$_SESSION["user"] = ["id" => 2, "username" => "hoang_trekker", "role_id" => 1, "role_name" => "member", "email" => "hoang@example.com"];
$_POST = ' . var_export($postData, true) . ';
(new OutdoorController())->contactOrganizer(' . (int)$tripId . ');
';
    file_put_contents($tmpFile, $code);
    $output = shell_exec('php ' . escapeshellarg($tmpFile));
    if (file_exists($tmpFile)) {
        unlink($tmpFile);
    }
    return [
        'raw' => (string)$output,
        'json' => json_decode((string)$output, true)
    ];
}

// 2. Kiểm tra validation khi gửi nội dung quá ngắn (< 5 ký tự)
echo "\n[2] Kiểm tra validation khi nội dung tin nhắn < 5 ký tự...\n";
$res2 = callContactApi(1, [
    'sender_name' => 'Hoàng Trekker',
    'sender_contact' => '0901234567',
    'message' => 'Alo'
]);

$json2 = $res2['json'];
assert(is_array($json2) && $json2['success'] === false, "Phải trả về success = false khi tin nhắn quá ngắn. Raw: " . $res2['raw']);
assert(str_contains($json2['message'] ?? '', '5 ký tự'), "Thông báo lỗi phải nhắc nhở tối thiểu 5 ký tự");
echo "-> OK! Hệ thống chặn hợp lệ và phản hồi lỗi 422 JSON khi nội dung quá ngắn: " . ($json2['message'] ?? '') . "\n";

// 3. Kiểm tra gửi thư thành công qua AJAX & tạo thông báo cho Trưởng đoàn
echo "\n[3] Kiểm tra gửi thư hợp lệ, trả về 200 JSON và gửi Notification tới trưởng đoàn...\n";
$db = Database::getInstance();
$countBefore = (int) $db->query("SELECT COUNT(*) FROM notifications WHERE type = 'trip_contact'")->fetchColumn();

$res3 = callContactApi(1, [
    'sender_name' => 'Hoàng Trekker',
    'sender_contact' => '0901234567',
    'message' => 'Chào bạn, cho mình hỏi chuyến Tà Xùa này còn chỗ không và có cần chuẩn bị lều riêng không ạ?'
]);

$json3 = $res3['json'];
assert(is_array($json3) && $json3['success'] === true, "Phải trả về success = true khi dữ liệu hợp lệ. Raw: " . $res3['raw']);

$countAfter = (int) $db->query("SELECT COUNT(*) FROM notifications WHERE type = 'trip_contact'")->fetchColumn();
assert($countAfter > $countBefore, "Chưa tạo được bản ghi thông báo trong bảng notifications");

$latestNotif = $db->query("SELECT * FROM notifications WHERE type = 'trip_contact' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assert(str_contains($latestNotif['content'], 'Hoàng Trekker'), "Nội dung thông báo phải chứa tên người gửi");
assert(str_contains($latestNotif['content'], '0901234567'), "Nội dung thông báo phải chứa số liên hệ");
echo "-> OK! Đã tạo thông báo in-app thành công cho Trưởng đoàn với đầy đủ thông tin người gửi: " . $latestNotif['content'] . "\n";

// Dọn dẹp test notification vừa tạo
$db->query("DELETE FROM notifications WHERE id = " . (int)$latestNotif['id']);

echo "\n======================================================\n";
echo "🎉 TẤT CẢ TEST LIÊN HỆ TRƯỞNG ĐOÀN ĐỀU PASS 100%!\n";
echo "======================================================\n";
