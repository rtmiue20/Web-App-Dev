<?php
/**
 * Self-contained HTTP integration test for Week 3 Leader
 * Starts PHP's built-in dev server, runs tests, then stops it.
 */
define('PROJECT_ROOT', 'C:/Users/MinhTrieu/UEH/WebDev/Project');
define('PHP_BIN', 'D:/xampp/php/php.exe');
define('HOST', '127.0.0.1');
define('PORT', '7654');
define('BASE', 'http://' . HOST . ':' . PORT);

$pass = 0; $fail = 0; $results = [];

// ─── Start PHP dev server ────────────────────────────────────────────────────
$devLogFile = sys_get_temp_dir() . '/php_server_test_' . time() . '.log';
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['file', $devLogFile, 'w'],
    2 => ['file', $devLogFile, 'w'],
];
$pipes = [];
$proc = proc_open(
    '"' . PHP_BIN . '" -S ' . HOST . ':' . PORT . ' -t "' . PROJECT_ROOT . '/public"',
    $descriptors,
    $pipes,
    PROJECT_ROOT
);

if (!is_resource($proc)) {
    die("FATAL: Cannot start PHP dev server\n");
}

sleep(2);

$conn = @fsockopen(HOST, (int)PORT, $errno, $errstr, 2);
if (!$conn) {
    proc_terminate($proc);
    die("FATAL: Server not listening on " . PORT . " - $errstr\n");
}
fclose($conn);
echo "PHP Dev Server started on http://" . HOST . ":" . PORT . "\n";
echo str_repeat("─", 60) . "\n\n";

function httpGet(string $url, bool $followRedirects = true, ?string $cookieFile = null): array {
    usleep(150000);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => $followRedirects,
        CURLOPT_MAXREDIRS => $followRedirects ? 3 : 0,
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
    $location = '';
    if (preg_match('/^Location:\s*(.+)$/im', substr((string)$response, 0, $headerSize), $m)) {
        $location = trim($m[1]);
    }
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'location' => $location];
}

function httpPost(string $url, array $data, ?string $cookieFile = null): array {
    usleep(150000);
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
    $location = '';
    if (preg_match('/^Location:\s*(.+)$/im', substr((string)$response, 0, $headerSize), $m)) {
        $location = trim($m[1]);
    }
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'location' => $location];
}

function getCsrfToken(string $body): string {
    preg_match('/name=["\']_csrf["\']\s+value=["\'](.*?)["\']/', $body, $m);
    return $m[1] ?? '';
}

function assert_http(string $label, int $actualCode, array $expectedCodes): void {
    global $pass, $fail, $results;
    $ok = in_array($actualCode, $expectedCodes);
    $icon = $ok ? '✓ PASS' : '✗ FAIL';
    if ($ok) { $pass++; } else { $fail++; }
    $expectStr = implode(' or ', $expectedCodes);
    $results[] = [$ok, $label, $actualCode, $expectStr];
    echo sprintf("[%s] %-50s HTTP %-3d (expect: %s)\n", $icon, $label, $actualCode, $expectStr);
}

function assert_contains(string $label, string $body, string $needle): void {
    global $pass, $fail, $results;
    $ok = str_contains($body, $needle);
    $icon = $ok ? '✓ PASS' : '✗ FAIL';
    if ($ok) { $pass++; } else { $fail++; }
    $results[] = [$ok, $label, 'contains: '.$needle, 'body contains '.$needle];
    echo sprintf("[%s] %-50s %s\n", $icon, $label, $ok ? 'found' : "NOT FOUND in body");
    if (!$ok) {
        preg_match('/nature-alert.*?<\/div>/s', $body, $m);
        echo "   [DEBUG Alert]: " . ($m[0] ?? substr(strip_tags($body), 0, 150)) . "\n";
    }
}

// ─── Test Suite ──────────────────────────────────────────────────────────────

echo "1. ROUTE CÔNG KHAI (không cần đăng nhập)\n";
echo str_repeat("─", 60) . "\n";

$r = httpGet(BASE . '/');
assert_http("GET /  (Trang chủ)", $r['code'], [200]);
assert_contains("Trang chủ chứa nội dung HTML", $r['body'], '<html');

$r = httpGet(BASE . '/categories');
assert_http("GET /categories", $r['code'], [200]);

$r = httpGet(BASE . '/posts');
assert_http("GET /posts", $r['code'], [200]);

$r = httpGet(BASE . '/login');
assert_http("GET /login", $r['code'], [200]);
assert_contains("Form đăng nhập có CSRF token", $r['body'], '_csrf');

$r = httpGet(BASE . '/register');
assert_http("GET /register", $r['code'], [200]);

$r = httpGet(BASE . '/randomroute_xyz123', false);
assert_http("GET /randomroute_xyz123 (404)", $r['code'], [404]);

echo "\n2. KIỂM TRA MIDDLEWARE: CHƯA ĐĂNG NHẬP VÀO /admin\n";
echo str_repeat("─", 60) . "\n";

$r = httpGet(BASE . '/admin', false);
assert_http("GET /admin (chưa login → redirect 303)", $r['code'], [303]);
$r2 = httpGet(BASE . '/admin/categories', false);
assert_http("GET /admin/categories (chưa login → 303)", $r2['code'], [303]);
$r3 = httpGet(BASE . '/admin/categories/create', false);
assert_http("GET /admin/categories/create (chưa login → 303)", $r3['code'], [303]);

echo "\n3. ĐĂNG NHẬP ADMIN\n";
echo str_repeat("─", 60) . "\n";

$adminCookie = tempnam(sys_get_temp_dir(), 'admin_session_');
$loginPage = httpGet(BASE . '/login', true, $adminCookie);
$csrf = getCsrfToken($loginPage['body']);
$loginResult = httpPost(BASE . '/login', [
    'email' => 'admin@forum.local',
    'password' => 'admin123',
    '_csrf' => $csrf,
], $adminCookie);
assert_http("POST /login admin@forum.local/admin123", $loginResult['code'], [303]);
$adminOk = $loginResult['code'] === 303;

echo "\n4. ADMIN ROUTES (đã đăng nhập là admin)\n";
echo str_repeat("─", 60) . "\n";

if ($adminOk) {
    $r = httpGet(BASE . '/admin', true, $adminCookie);
    assert_http("GET /admin Dashboard", $r['code'], [200]);
    assert_contains("Dashboard chứa 'Bảng điều khiển'", $r['body'], 'Bảng điều khiển');
    assert_contains("Dashboard dùng layout admin (Admin badge)", $r['body'], 'ADMIN');

    $r = httpGet(BASE . '/admin/categories', true, $adminCookie);
    assert_http("GET /admin/categories", $r['code'], [200]);
    assert_contains("Trang admin categories có title 'Quản lý Chuyên mục'", $r['body'], 'Quản lý Chuyên mục');
    
    $r = httpGet(BASE . '/admin/categories/create', true, $adminCookie);
    assert_http("GET /admin/categories/create", $r['code'], [200]);
    assert_contains("Form thêm có CSRF token", $r['body'], '_csrf');
    assert_contains("Form thêm có trường 'name'", $r['body'], 'name="name"');

    $r = httpGet(BASE . '/admin/categories/edit/1', true, $adminCookie);
    assert_http("GET /admin/categories/edit/1", $r['code'], [200]);

    // Truy cập id không tồn tại -> mong đợi redirect 303
    $r = httpGet(BASE . '/admin/categories/edit/99999', false, $adminCookie);
    assert_http("GET /admin/categories/edit/99999 (không tồn tại → redirect)", $r['code'], [303]);
}

echo "\n5. FR11: THÊM DANH MỤC (Validation + Success)\n";
echo str_repeat("─", 60) . "\n";

if ($adminOk) {
    // 5.1 Thêm danh mục mới thành công
    $createPage = httpGet(BASE . '/admin/categories/create', true, $adminCookie);
    $csrf = getCsrfToken($createPage['body']);
    
    $testCatName = 'Test Cat ' . time();
    $storeResult = httpPost(BASE . '/admin/categories/store', [
        'name' => $testCatName,
        'description' => 'Danh muc test tu dong',
        '_csrf' => $csrf,
    ], $adminCookie);
    assert_http("POST /admin/categories/store (tạo mới → redirect 303)", $storeResult['code'], [303]);

    // 5.2 Thêm danh mục trùng tên -> mong đợi trả về 200 kèm form báo lỗi
    $createPage2 = httpGet(BASE . '/admin/categories/create', true, $adminCookie);
    $csrf2 = getCsrfToken($createPage2['body']);
    $dupResult = httpPost(BASE . '/admin/categories/store', [
        'name' => $testCatName,
        'description' => 'Trùng tên',
        '_csrf' => $csrf2,
    ], $adminCookie);
    assert_http("POST /admin/categories/store trùng tên → 200 (hiện lỗi)", $dupResult['code'], [200]);
    assert_contains("Thông báo lỗi khi trùng tên danh mục", $dupResult['body'], 'đã tồn tại');

    // 5.3 Thêm danh mục tên quá ngắn (< 2 ký tự) -> mong đợi trả về 200 kèm form báo lỗi
    $createPage3 = httpGet(BASE . '/admin/categories/create', true, $adminCookie);
    $csrf3 = getCsrfToken($createPage3['body']);
    $shortResult = httpPost(BASE . '/admin/categories/store', [
        'name' => 'X',
        'description' => '',
        '_csrf' => $csrf3,
    ], $adminCookie);
    assert_http("POST /admin/categories/store tên 1 ký tự → 200 (hiện lỗi)", $shortResult['code'], [200]);
    assert_contains("Thông báo lỗi khi tên quá ngắn", $shortResult['body'], '2 ký tự');
}

echo "\n6. KIỂM TRA 403 CHO THÀNH VIÊN THƯỜNG\n";
echo str_repeat("─", 60) . "\n";

$memberCookie = tempnam(sys_get_temp_dir(), 'member_session_');
$loginPage2 = httpGet(BASE . '/login', true, $memberCookie);
$csrf4 = getCsrfToken($loginPage2['body']);
$memberLogin = httpPost(BASE . '/login', [
    'email' => 'member@forum.local',
    'password' => 'member123',
    '_csrf' => $csrf4,
], $memberCookie);
assert_http("POST /login member@forum.local/member123", $memberLogin['code'], [303]);

if ($memberLogin['code'] === 303) {
    $r = httpGet(BASE . '/admin', false, $memberCookie);
    assert_http("GET /admin với member → 403", $r['code'], [403]);
    assert_contains("Trang 403 có nội dung lỗi phù hợp", $r['body'], '403');
    
    $r2 = httpGet(BASE . '/admin/categories', false, $memberCookie);
    assert_http("GET /admin/categories với member → 403", $r2['code'], [403]);
}

// ─── Summary ─────────────────────────────────────────────────────────────────
echo "\n" . str_repeat("═", 60) . "\n";
echo " KẾT QUẢ: $pass PASS  /  $fail FAIL\n";
echo str_repeat("═", 60) . "\n";

if ($fail === 0) {
    echo "🎉 TẤT CẢ CÁC TEST ĐÃ PASS - Tuần 3 Leader hoàn chỉnh 100%!\n";
} else {
    echo "⚠  Có $fail test thất bại.\n";
}

@unlink($adminCookie);
@unlink($memberCookie);
proc_terminate($proc);
echo "\nServer đã dừng an toàn.\n";
