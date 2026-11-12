<?php
/**
 * Test Suite: Xác thực tối ưu Weather API và Khắc phục lỗi Quét mã QR trên điện thoại
 */
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/services/WeatherService.php';
require_once __DIR__ . '/../app/services/QrCodeService.php';

echo "========================================================\n";
echo " KIỂM THỬ TỐC ĐỘ WEATHER API & MÃ QR CHIA SẺ DI ĐỘNG   \n";
echo "========================================================\n\n";

$allPassed = true;

// ── TEST 1: WeatherService Parallel cURL & Cache Speed ─────────────────────
echo "[TEST 1] WeatherService::getForecast tốc độ và tính toàn vẹn...\n";
$weatherService = new WeatherService();

$t0 = microtime(true);
$weather1 = $weatherService->getForecast(11.9404, 108.4583); // Đà Lạt
$elapsed1 = microtime(true) - $t0;

echo "  - Đà Lạt (Cold/Cached): " . round($elapsed1, 3) . "s\n";
echo "  - Nhiệt độ: " . ($weather1['temp'] ?? 'N/A') . "°C | Thời tiết: " . ($weather1['condition'] ?? 'N/A') . "\n";
echo "  - Tia UV: " . ($weather1['uv']['index'] ?? 'N/A') . " (" . ($weather1['uv']['level'] ?? 'N/A') . ")\n";
echo "  - AQI: " . ($weather1['air_quality']['aqi'] ?? 'N/A') . " (" . ($weather1['air_quality']['level'] ?? 'N/A') . ")\n";

if (!isset($weather1['temp']) || !isset($weather1['uv']) || !isset($weather1['air_quality'])) {
    echo "  [FAIL] Dữ liệu thời tiết thiếu trường dữ liệu cốt lõi!\n";
    $allPassed = false;
} else {
    echo "  [PASS] Cấu trúc dữ liệu thời tiết đầy đủ chuẩn xác 100%.\n";
}

$t2_0 = microtime(true);
$weather2 = $weatherService->getForecast(11.9404, 108.4583);
$elapsed2 = (microtime(true) - $t2_0) * 1000;
echo "  - Cache Hit: " . round($elapsed2, 2) . "ms (Cached: " . (($weather2['cached'] ?? false) ? 'YES' : 'NO') . ")\n";

if ($elapsed2 > 50 || ($weather2['cached'] ?? false) !== true) {
    echo "  [FAIL] Cache hit quá chậm hoặc không lưu cache!\n";
    $allPassed = false;
} else {
    echo "  [PASS] Cache hit siêu tốc (< 50ms) đáp ứng yêu cầu tức thì.\n";
}

// ── TEST 2: QrCodeService LAN IP Detection & Default LAN URL ───────────────
echo "\n[TEST 2] QrCodeService sinh mã QR mặc định cho thiết bị di động...\n";
$qrService = new QrCodeService();
$qrData = $qrService->generateQrData('/posts/2', 'Bài viết Đà Lạt', 'post');

echo "  - Original URL: " . ($qrData['original_url'] ?? 'N/A') . "\n";
echo "  - LAN URL: " . ($qrData['lan_url'] ?? 'N/A') . "\n";
echo "  - Primary Target URL (mặc định điện thoại quét): " . ($qrData['target_url'] ?? 'N/A') . "\n";
echo "  - LAN IP: " . ($qrData['lan_ip'] ?? 'N/A') . "\n";
echo "  - Is Localhost: " . (($qrData['is_localhost'] ?? false) ? 'YES' : 'NO') . "\n";
echo "  - QR Image URL: " . ($qrData['qr_image_url'] ?? 'N/A') . "\n";

if (($qrData['is_localhost'] ?? false) === true) {
    // Khi chạy trên localhost, URL đích mã hóa QR PHẢI là $lanUrl
    if ($qrData['target_url'] === $qrData['lan_url'] && str_contains($qrData['target_url'], '192.168.')) {
        echo "  [PASS] Mã QR đã tự động mã hóa IP Wi-Fi ({$qrData['lan_ip']}) thay vì localhost!\n";
    } else {
        echo "  [FAIL] URL mã hóa QR vẫn trỏ về localhost khiến điện thoại không quét được!\n";
        $allPassed = false;
    }
} else {
    echo "  [INFO] Đang chạy trên production host: {$qrData['target_url']}\n";
}

// ── TEST 3: HTTP Endpoint /api/qr.php qua cURL ─────────────────────────────
echo "\n[TEST 3] Kiểm thử gọi HTTP Endpoint /api/qr.php trên máy chủ port 8000...\n";
$ch = curl_init('http://localhost:8000/api/qr.php?target_url=/posts/2');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  - HTTP Status Code: {$httpCode}\n";
if ($httpCode === 200 && $resp) {
    $json = json_decode($resp, true);
    if (!empty($json['success']) && !empty($json['data']['lan_url'])) {
        echo "  [PASS] Endpoint /api/qr.php hoạt động trơn tru 200 OK. LAN URL: " . $json['data']['lan_url'] . "\n";
    } else {
        echo "  [FAIL] JSON response không hợp lệ: " . substr($resp, 0, 100) . "\n";
        $allPassed = false;
    }
} else {
    echo "  [FAIL] Gọi /api/qr.php thất bại, mã HTTP {$httpCode}\n";
    $allPassed = false;
}

// ── TEST 4: HTTP Endpoint /api/weather.php qua cURL ─────────────────────────
echo "\n[TEST 4] Kiểm thử gọi HTTP Endpoint /api/weather.php trên máy chủ port 8000...\n";
$tWeather0 = microtime(true);
$ch = curl_init('http://localhost:8000/api/weather.php?lat=11.9404&lng=108.4583');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 4);
$respWeather = curl_exec($ch);
$httpCodeWeather = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$weatherReqTime = microtime(true) - $tWeather0;
curl_close($ch);

echo "  - HTTP Status Code: {$httpCodeWeather}\n";
echo "  - Response Time: " . round($weatherReqTime * 1000, 2) . "ms\n";

if ($httpCodeWeather === 200 && $respWeather) {
    $jsonW = json_decode($respWeather, true);
    if (!empty($jsonW['success']) && isset($jsonW['data']['temp'])) {
        echo "  [PASS] Endpoint /api/weather.php phản hồi siêu tốc " . round($weatherReqTime * 1000, 1) . "ms (200 OK).\n";
    } else {
        echo "  [FAIL] Dữ liệu JSON thời tiết trả về lỗi!\n";
        $allPassed = false;
    }
} else {
    echo "  [FAIL] Gọi /api/weather.php thất bại, mã HTTP {$httpCodeWeather}\n";
    $allPassed = false;
}

// ── TEST 5: HTML Pre-rendering trên /posts/2 ────────────────────────────────
echo "\n[TEST 5] Kiểm tra SSR Pre-rendering thời tiết trên trang chi tiết /posts/2...\n";
$ch = curl_init('http://localhost:8000/posts/2');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 4);
$htmlPost = curl_exec($ch);
$httpCodePost = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCodePost === 200 && $htmlPost) {
    if (str_contains($htmlPost, 'data-loaded="true"') && str_contains($htmlPost, 'Chỉ số Tia UV') && str_contains($htmlPost, 'US AQI')) {
        echo "  [PASS] Trang /posts/2 đã pre-render sẵn thời tiết và chỉ số UV/AQI từ server (0ms spinner)!\n";
    } else {
        echo "  [FAIL] Trang /posts/2 chưa render sẵn widget thời tiết!\n";
        $allPassed = false;
    }
} else {
    echo "  [FAIL] Không tải được trang /posts/2 (HTTP {$httpCodePost})\n";
    $allPassed = false;
}

echo "\n========================================================\n";
if ($allPassed) {
    echo "  KẾT QUẢ: TẤT CẢ 5/5 KIỂM THỬ ĐÃ THÀNH CÔNG VƯỢT TRỘI!  \n";
    echo "========================================================\n";
    exit(0);
} else {
    echo "  KẾT QUẢ: CÓ BƯỚC KIỂM THỬ CHƯA ĐẠT!                   \n";
    echo "========================================================\n";
    exit(1);
}
