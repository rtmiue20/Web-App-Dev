<?php
/**
 * Test FR14: Quản lý thành viên & Cấp lại mật khẩu (UserAdminController)
 */
define('PROJECT_ROOT', 'C:/Users/MinhTrieu/UEH/WebDev/Project');
define('PHP_BIN', 'D:/xampp/php/php.exe');
define('HOST', '127.0.0.1');
define('PORT', '7656');
define('BASE', 'http://' . HOST . ':' . PORT);

$pass = 0; $fail = 0;

$devLogFile = sys_get_temp_dir() . '/php_user_admin_test.log';
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['file', $devLogFile, 'w'],
    2 => ['file', $devLogFile, 'w'],
];
$proc = proc_open('"' . PHP_BIN . '" -S ' . HOST . ':' . PORT . ' -t "' . PROJECT_ROOT . '/public"', $descriptors, $pipes, PROJECT_ROOT);
sleep(2);

function httpGet(string $url, bool $followRedirects = true, ?string $cookieFile = null): array {
    usleep(120000);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => $followRedirects,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_FORBID_REUSE => true,
        CURLOPT_HTTPHEADER => ['Connection: close'],
        CURLOPT_COOKIEFILE => $cookieFile ?? '',
        CURLOPT_COOKIEJAR => $cookieFile ?? '',
    ]);
    $response = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr((string)$response, $headerSize);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
}

function httpPost(string $url, array $data, ?string $cookieFile = null): array {
    usleep(120000);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Connection: close'],
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_FORBID_REUSE => true,
        CURLOPT_COOKIEFILE => $cookieFile ?? '',
        CURLOPT_COOKIEJAR => $cookieFile ?? '',
        CURLOPT_HEADER => true,
    ]);
    $response = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr((string)$response, $headerSize);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
}

function getCsrfToken(string $body): string {
    preg_match('/name=["\']_csrf["\']\s+value=["\'](.*?)["\']/', $body, $m);
    return $m[1] ?? '';
}

function assert_test(string $label, bool $ok, string $details = ''): void {
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "[✓ PASS] $label\n";
    } else {
        $fail++;
        echo "[✗ FAIL] $label" . ($details !== '' ? " ($details)" : '') . "\n";
    }
}

echo "=== KIỂM THỬ TÍCH HỢP QUẢN LÝ THÀNH VIÊN & CẤP LẠI MẬT KHẨU (FR14) ===\n\n";

// 1. Khách chưa đăng nhập truy cập /admin/users
$guestGet = httpGet(BASE . '/admin/users', false);
assert_test("Chưa đăng nhập truy cập /admin/users -> Redirect 303", $guestGet['code'] === 303);

// 2. Member thường truy cập /admin/users -> 403 Forbidden
$memberCookie = tempnam(sys_get_temp_dir(), 'mem_cookie_');
$loginPage = httpGet(BASE . '/login', true, $memberCookie);
$csrf = getCsrfToken($loginPage['body']);
$memberLogin = httpPost(BASE . '/login', [
    'email' => 'member@forum.local',
    'password' => 'member123',
    '_csrf' => $csrf
], $memberCookie);
assert_test("Đăng nhập tài khoản member", $memberLogin['code'] === 303);

$memberAccess = httpGet(BASE . '/admin/users', false, $memberCookie);
assert_test("Tài khoản member truy cập /admin/users -> Bị chặn 403 Forbidden", $memberAccess['code'] === 403);

// 3. Admin truy cập /admin/users
$adminCookie = tempnam(sys_get_temp_dir(), 'adm_cookie_');
$loginPage2 = httpGet(BASE . '/login', true, $adminCookie);
$csrf2 = getCsrfToken($loginPage2['body']);
$adminLogin = httpPost(BASE . '/login', [
    'email' => 'admin@forum.local',
    'password' => 'admin123',
    '_csrf' => $csrf2
], $adminCookie);
assert_test("Đăng nhập tài khoản admin", $adminLogin['code'] === 303);

$adminUsersPage = httpGet(BASE . '/admin/users', true, $adminCookie);
assert_test("Admin truy cập /admin/users -> HTTP 200 OK", $adminUsersPage['code'] === 200);
assert_test("Trang chứa tiêu đề 'Quản lý Thành viên'", str_contains($adminUsersPage['body'], 'Quản lý Thành viên'));
assert_test("Trang hiển thị thông tin thành viên (member@forum.local)", str_contains($adminUsersPage['body'], 'member@forum.local'));
assert_test("Trang có nút 'Cấp lại mật khẩu' cho thành viên", str_contains($adminUsersPage['body'], 'btn-reset-password'));

// 4. Admin cấp lại mật khẩu cho thành viên ID = 2 (member)
$csrfAdmin = getCsrfToken($adminUsersPage['body']);
$resetPass = 'NewSecretPass@123';
$resetReq = httpPost(BASE . '/admin/users/2/reset-password', [
    'new_password' => $resetPass,
    '_csrf' => $csrfAdmin,
], $adminCookie);
assert_test("Admin POST /admin/users/2/reset-password -> Redirect 303", $resetReq['code'] === 303);

// 5. Kiểm tra thành viên đăng nhập bằng mật khẩu MỚI
$memberCookieNew = tempnam(sys_get_temp_dir(), 'mem_new_');
$loginPage3 = httpGet(BASE . '/login', true, $memberCookieNew);
$csrf3 = getCsrfToken($loginPage3['body']);
$loginWithNewPass = httpPost(BASE . '/login', [
    'email' => 'member@forum.local',
    'password' => $resetPass,
    '_csrf' => $csrf3,
], $memberCookieNew);
assert_test("Thành viên đăng nhập THÀNH CÔNG với mật khẩu mới vừa cấp lại", $loginWithNewPass['code'] === 303);

// 6. Trả lại mật khẩu ban đầu 'member123' để hệ thống ổn định
$adminUsersPage2 = httpGet(BASE . '/admin/users', true, $adminCookie);
$csrfAdmin2 = getCsrfToken($adminUsersPage2['body']);
$restoreReq = httpPost(BASE . '/admin/users/2/reset-password', [
    'new_password' => 'member123',
    '_csrf' => $csrfAdmin2,
], $adminCookie);
assert_test("Khôi phục lại mật khẩu mặc định 'member123'", $restoreReq['code'] === 303);

// 7. Test kiểm tra thẻ Thành viên trên Dashboard
$dashPage = httpGet(BASE . '/admin', true, $adminCookie);
assert_test("Dashboard có liên kết '/admin/users'", str_contains($dashPage['body'], '/admin/users'));
assert_test("Dashboard có nhãn 'Quản lý thành viên'", str_contains($dashPage['body'], 'Quản lý thành viên'));

echo "\n" . str_repeat("═", 60) . "\n";
echo " KẾT QUẢ: $pass PASS  /  $fail FAIL\n";
echo str_repeat("═", 60) . "\n";

@unlink($memberCookie);
@unlink($memberCookieNew);
@unlink($adminCookie);
proc_terminate($proc);
