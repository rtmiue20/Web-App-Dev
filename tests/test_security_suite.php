<?php
/**
 * Kiểm thử Tự động 5 Trụ cột Bảo mật (Security Hardening Test Suite)
 * 1. PDO + Prepared Statements (Chống SQL Injection)
 * 2. password_hash & password_verify (Mã hóa mật khẩu một chiều Bcrypt)
 * 3. CSRF Token (Chống giả mạo yêu cầu liên trang trên Form & AJAX)
 * 4. Escape Output & Safe URL (Chống XSS trên View & JSON API)
 * 5. Validate Upload & .htaccess (Chống tải lên Webshell / Polyglot Image / RCE)
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$passed = 0;
$failed = 0;

function assertSecurity(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo " KIỂM THỬ 5 TRỤ CỘT BẢO MẬT (PDO, PASSWORD_HASH, CSRF, XSS, UPLOAD)\n";
echo "=================================================================\n\n";

// -------------------------------------------------------------------------
// 1. PDO + Prepared Statements (Chống SQL Injection)
// -------------------------------------------------------------------------
echo "1. Kiểm tra PDO + Prepared Statements (Chống SQL Injection):\n";
$dbSource = (string)file_get_contents(__DIR__ . '/../app/core/Database.php');

assertSecurity(
    str_contains($dbSource, 'PDO::ATTR_EMULATE_PREPARES') && str_contains($dbSource, 'false'),
    'Database::getConnection() cấu hình PDO::ATTR_EMULATE_PREPARES => false (Native Prepared Statements)'
);
assertSecurity(
    str_contains($dbSource, 'PDO::ATTR_ERRMODE') && str_contains($dbSource, 'PDO::ERRMODE_EXCEPTION'),
    'Database::getConnection() cấu hình PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION'
);

// Khởi tạo PDO chuẩn với cùng cấu hình bảo mật để kiểm thử Prepared Statement chống SQL Injection
$testPdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);
$testPdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, email TEXT, password_hash TEXT)');
$stmtInsert = $testPdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:u, :e, :p)');
$stmtInsert->execute([
    ':u' => 'admin',
    ':e' => 'admin@outdoor.vn',
    ':p' => Validator::hashPassword('Admin@123456'),
]);

$sqliPayload = "' OR '1'='1' -- ";
$stmtSelect = $testPdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
$stmtSelect->execute([':email' => $sqliPayload]);
$sqliResult = $stmtSelect->fetch();

assertSecurity(
    $sqliResult === false,
    "Prepared Statement vô hiệu hóa hoàn toàn payload SQL Injection kinh điển: {$sqliPayload}"
);

$spInjectionBlocked = false;
try {
    Database::call('sp_user_get_by_email; DROP TABLE users--');
} catch (InvalidArgumentException $e) {
    $spInjectionBlocked = true;
}
assertSecurity(
    $spInjectionBlocked,
    'Database::call() chặn SQL Injection qua tên Stored Procedure bằng Whitelist Regex (/\\A[a-zA-Z0-9_]+\\z/)'
);

// -------------------------------------------------------------------------
// 2. password_hash & password_verify
// -------------------------------------------------------------------------
echo "\n2. Kiểm tra password_hash & password_verify (Bcrypt):\n";
$rawPass = 'Outdoor@2026!Strong';
$hash = Validator::hashPassword($rawPass);

assertSecurity(
    str_starts_with($hash, '$2y$') && strlen($hash) === 60,
    'Validator::hashPassword() sinh chuỗi băm Bcrypt chuẩn ($2y$, 60 ký tự) kèm salt ngẫu nhiên'
);
assertSecurity(
    Validator::verifyPassword($rawPass, $hash) === true,
    'Validator::verifyPassword() xác thực chính xác mật khẩu khớp với chuỗi băm'
);
assertSecurity(
    Validator::verifyPassword('WrongPassword123', $hash) === false,
    'Validator::verifyPassword() từ chối chính xác mật khẩu sai'
);
assertSecurity(
    password_needs_rehash($hash, PASSWORD_DEFAULT) === false,
    'password_needs_rehash() kiểm tra thuật toán băm đạt chuẩn hiện hành'
);

// -------------------------------------------------------------------------
// 3. CSRF Token (Form POST & AJAX Header X-CSRF-Token)
// -------------------------------------------------------------------------
echo "\n3. Kiểm tra CSRF Token (hash_equals, Form _csrf, Header X-CSRF-Token, Rotate):\n";
Session::init();
$token = Csrf::token();

assertSecurity(
    strlen($token) === 64 && ctype_xdigit($token),
    'Csrf::token() sinh mã 256-bit (64 ký tự hex) bằng CSPRNG random_bytes(32)'
);
assertSecurity(
    Csrf::verify($token) === true,
    'Csrf::verify() chấp nhận token hợp lệ truyền trực tiếp'
);

$_POST['_csrf'] = $token;
assertSecurity(
    Csrf::verify() === true,
    'Csrf::verify() tự động đọc và xác thực token từ $_POST[\'_csrf\']'
);
unset($_POST['_csrf']);

$_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
assertSecurity(
    Csrf::verify() === true,
    'Csrf::verify() tự động đọc và xác thực token từ HTTP Header X-CSRF-TOKEN (cho AJAX)'
);
unset($_SERVER['HTTP_X_CSRF_TOKEN']);

assertSecurity(
    Csrf::verify('invalid_tampered_csrf_token_1234567890') === false && Csrf::verify('') === false,
    'Csrf::verify() từ chối token giả mạo hoặc rỗng bằng hàm chống Timing Attack hash_equals()'
);

$rotatedToken = Csrf::rotate();
assertSecurity(
    strlen($rotatedToken) === 64 && $rotatedToken !== $token,
    'Csrf::rotate() làm mới CSRF token sau khi đăng nhập / đăng xuất (chống Session Fixation & Replay)'
);

// -------------------------------------------------------------------------
// 4. Escape Output (Chống XSS trên View & URL)
// -------------------------------------------------------------------------
echo "\n4. Kiểm tra Escape Output & Safe URL (Chống XSS):\n";
$xssVector = '<script>alert("XSS");</script><img src=x onerror=\'steal()\'>';
$escapedHtml = e($xssVector);

assertSecurity(
    !str_contains($escapedHtml, '<script>') && str_contains($escapedHtml, '&lt;script&gt;') && str_contains($escapedHtml, '&quot;XSS&quot;') && str_contains($escapedHtml, '&#039;steal()&#039;'),
    'Hàm e() mã hóa toàn bộ thẻ HTML và dấu nháy đơn/kép (ENT_QUOTES | ENT_SUBSTITUTE, UTF-8)'
);
assertSecurity(
    safe_url('javascript:alert(document.cookie)') === '#' && safe_url("java\tscript:alert(1)") === '#' && safe_url('data:text/html;base64,PHNjcmlwdD4=') === '#',
    'Hàm safe_url() chặn triệt để các lược đồ XSS nguy hiểm (javascript:, data:, vbscript:, control-char bypass)'
);
assertSecurity(
    safe_url('https://shopee.vn/leu-cam-trai') === 'https://shopee.vn/leu-cam-trai' && safe_url('/posts/12') === '/posts/12',
    'Hàm safe_url() giữ nguyên các đường dẫn https:// và đường dẫn nội bộ hợp lệ'
);

// -------------------------------------------------------------------------
// 5. Validate Upload (Chống Webshell / Double Extension / Polyglot Image)
// -------------------------------------------------------------------------
echo "\n5. Kiểm tra Validate Upload & .htaccess:\n";
$tmpDir = sys_get_temp_dir();

// 5a. Tạo 1 ảnh PNG 1x1 hợp lệ
$validPngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$validPngPath = $tmpDir . DIRECTORY_SEPARATOR . 'test_valid_' . uniqid() . '.png';
file_put_contents($validPngPath, $validPngBytes);

$ext = Upload::validateImageFile([
    'name'     => 'nature_photo.png',
    'type'     => 'image/png',
    'tmp_name' => $validPngPath,
    'error'    => UPLOAD_ERR_OK,
    'size'     => strlen($validPngBytes),
], false);
assertSecurity(
    $ext === 'png',
    'Upload::validateImageFile() chấp nhận file ảnh PNG thật có Magic Bytes và kích thước hợp lệ'
);

// 5b. Chặn Double Extension (ví dụ: shell.php.png)
$doubleExtBlocked = false;
try {
    Upload::validateImageFile([
        'name'     => 'webshell.php.png',
        'type'     => 'image/png',
        'tmp_name' => $validPngPath,
        'error'    => UPLOAD_ERR_OK,
        'size'     => strlen($validPngBytes),
    ], false);
} catch (Exception $e) {
    $doubleExtBlocked = true;
}
assertSecurity(
    $doubleExtBlocked,
    'Upload::validateImageFile() chặn file có Double Extension nguy hiểm (webshell.php.png)'
);

// 5c. Chặn Polyglot Image (ảnh thật nhưng nhúng mã <?php ở phần đuôi)
$polyglotPath = $tmpDir . DIRECTORY_SEPARATOR . 'test_polyglot_' . uniqid() . '.png';
file_put_contents($polyglotPath, $validPngBytes . "\n<?php system(\$_GET['cmd']); ?>");
$polyglotBlocked = false;
try {
    Upload::validateImageFile([
        'name'     => 'avatar.png',
        'type'     => 'image/png',
        'tmp_name' => $polyglotPath,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($polyglotPath),
    ], false);
} catch (Exception $e) {
    $polyglotBlocked = true;
}
assertSecurity(
    $polyglotBlocked,
    'Upload::validateImageFile() phát hiện và chặn Polyglot Image chứa mã độc <?php bên trong file ảnh'
);

// 5d. Chặn file giả mạo MIME (file text đổi đuôi .jpg)
$fakeJpgPath = $tmpDir . DIRECTORY_SEPARATOR . 'test_fake_' . uniqid() . '.jpg';
file_put_contents($fakeJpgPath, 'This is plain text pretending to be an image');
$fakeMimeBlocked = false;
try {
    Upload::validateImageFile([
        'name'     => 'fake.jpg',
        'type'     => 'image/jpeg',
        'tmp_name' => $fakeJpgPath,
        'error'    => UPLOAD_ERR_OK,
        'size'     => filesize($fakeJpgPath),
    ], false);
} catch (Exception $e) {
    $fakeMimeBlocked = true;
}
assertSecurity(
    $fakeMimeBlocked,
    'Upload::validateImageFile() chặn file giả mạo đuôi .jpg bằng finfo(FILEINFO_MIME_TYPE) & getimagesize()'
);

@unlink($validPngPath);
@unlink($polyglotPath);
@unlink($fakeJpgPath);

// 5e. Kiểm tra file .htaccess trong public/uploads/
$htaccessPath = __DIR__ . '/../public/uploads/.htaccess';
$htaccessContent = is_file($htaccessPath) ? (string)file_get_contents($htaccessPath) : '';
assertSecurity(
    is_file($htaccessPath) && str_contains($htaccessContent, 'php_flag engine off') && str_contains($htaccessContent, 'FilesMatch'),
    'Thư mục public/uploads/.htaccess tồn tại và tắt thực thi PHP (php_flag engine off + FilesMatch deny)'
);

echo "\n=================================================================\n";
echo " KẾT QUẢ KIỂM THỬ BẢO MẬT: {$passed} PASSED / {$failed} FAILED\n";
echo "=================================================================\n";

exit($failed > 0 ? 1 : 0);
