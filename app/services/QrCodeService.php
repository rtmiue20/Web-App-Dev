
    <?php
require_once __DIR__ . '/../core/BaseService.php';

/**
 * Service QrCodeService - Tích hợp Web Service QuickChart QR API & GoQR (api.qrserver.com)
 * Phục vụ tính năng "Mã QR chia sẻ" chuyến đi dã ngoại (/trips/{id}), bài viết (/posts/{id}) và checklist.
 *
 * Đáp ứng chuẩn kiến trúc OOP + MVC (kế thừa BaseService):
 *  - Đọc và xác thực khóa API Key từ `config/config.php`.
 *  - Tương tác với Web Service bên ngoài qua cURL, truyền tải dữ liệu JSON cho AJAX client.
 *  - Tự động phát hiện địa chỉ IP mạng LAN nội bộ để hỗ trợ quét mã bằng điện thoại khi demo trên localhost.
 */
class QrCodeService extends BaseService
{
    private string $apiKey = '';
    private int $timeout = 2;
    private int $cacheTtl = 86400; // Cache 24 giờ

    public function __construct()
    {
        $envKey = getenv('QR_API_KEY') ?: '';
        if ($envKey !== '') {
            $this->apiKey = $envKey;
            return;
        }

        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        if (is_file($configPath)) {
            $cfg = @include $configPath;
            if (is_array($cfg)) {
                if (!empty($cfg['qr_api_key'])) {
                    $this->apiKey = (string)$cfg['qr_api_key'];
                } elseif (!empty($cfg['quickchart_api_key'])) {
                    $this->apiKey = (string)$cfg['quickchart_api_key'];
                }
            }
        }
    }

    /**
     * Sinh thông tin mã QR hoàn chỉnh trả về qua JSON cho AJAX Modal.
     *
     * @param string $targetUrl Đường dẫn cần mã hóa thành QR Code
     * @param string $title     Tiêu đề chuyến đi / bài viết
     * @param string $type      Loại nội dung (trip | post | checklist)
     * @param int    $size      Kích thước ảnh QR (px)
     * @return array Dữ liệu JSON chuẩn hóa
     */
    public function generateQrData(
        string $targetUrl,
        string $title = '',
        string $type = 'trip',
        int $size = 260
    ): array {
        $cleanSize = max(150, min(500, $size));
        $normalizedUrl = $this->normalizeShareUrl($targetUrl);
        $isLocalhost = $this->isLocalhostUrl($normalizedUrl);
        $lanIp = $isLocalhost ? $this->detectLocalLanIp() : null;
        $lanUrl = ($isLocalhost && $lanIp && $lanIp !== '127.0.0.1') ? $this->buildLanUrl($normalizedUrl) : $normalizedUrl;
        $isLanApplicable = $isLocalhost && ($lanUrl !== $normalizedUrl);

        // Khi chạy trên localhost / máy tính phát triển:
        // ĐIỆN THOẠI KHÔNG THỂ TRUY CẬP LOCALHOST (sẽ trỏ ngược vào 127.0.0.1 của điện thoại).
        // Do đó, URL chính mặc định để mã hóa QR phải là $lanUrl (IP Wi-Fi nội bộ) để điện thoại cùng Wi-Fi quét mở được.
        // Nhưng khi chạy trên Hosting / Domain công khai thực tế (vd outdoor-forum.rf.gd):
        // URL chính LUÔN LÀ $normalizedUrl công khai để bất kỳ ai dù ở bất kỳ mạng nào (4G, 5G, Wi-Fi) quét cũng mở được ngay!
        $primaryTargetUrl = $isLanApplicable ? $lanUrl : $normalizedUrl;

        // 1. Xây dựng URL gọi REST API QuickChart QR (kèm API Key) mã hóa $primaryTargetUrl
        $quickChartParams = [
            'text'    => $primaryTargetUrl,
            'size'    => $cleanSize,
            'dark'    => '1e2b18',
            'light'   => 'ffffff',
            'margin'  => 2,
            'ecLevel' => 'M',
            'format'  => 'png'
        ];
        if ($this->apiKey !== '') {
            $quickChartParams['key'] = $this->apiKey;
        }
        $quickChartUrl = 'https://quickchart.io/qr?' . http_build_query($quickChartParams);

        // 2. Xây dựng URL dự phòng từ GoQR / QRServer REST API
        $goQrParams = [
            'size'    => $cleanSize . 'x' . $cleanSize,
            'data'    => $primaryTargetUrl,
            'color'   => '30-43-24',
            'bgcolor' => '255-255-255',
            'qzone'   => 2,
            'format'  => 'png'
        ];
        $goQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query($goQrParams);

        // 3. Xây dựng URL QR cho localhost (phục vụ nút chuyển đổi "Link gốc")
        $localQuickChartParams = $quickChartParams;
        $localQuickChartParams['text'] = $normalizedUrl;
        $localQrUrl = 'https://quickchart.io/qr?' . http_build_query($localQuickChartParams);

        $localGoQrParams = $goQrParams;
        $localGoQrParams['data'] = $normalizedUrl;
        $localFallbackQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query($localGoQrParams);

        // 4. URL QR cho mạng LAN
        $lanQuickChartParams = $quickChartParams;
        $lanQuickChartParams['text'] = $lanUrl;
        $lanQrUrl = 'https://quickchart.io/qr?' . http_build_query($lanQuickChartParams);

        $lanGoQrParams = $goQrParams;
        $lanGoQrParams['data'] = $lanUrl;
        $lanFallbackQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query($lanGoQrParams);

        // 5. Tải ảnh QR qua cURL (có cache) để trả về Base64 Data URI ngay trong JSON response
        $dataUri = $this->fetchQrDataUri($quickChartUrl, $goQrUrl, $primaryTargetUrl, $cleanSize);

        return [
            'title'              => $title !== '' ? $title : 'Chia sẻ chuyến dã ngoại',
            'type'               => $type,
            'type_label'         => $this->mapTypeLabel($type),
            'target_url'         => $primaryTargetUrl,
            'original_url'       => $normalizedUrl,
            'share_url'          => $primaryTargetUrl,
            'lan_url'            => $lanUrl,
            'lan_ip'             => $isLanApplicable ? $lanIp : null,
            'is_localhost'       => $isLocalhost,
            'size'               => $cleanSize,
            'qr_image_url'       => $quickChartUrl,
            'fallback_qr_url'    => $goQrUrl,
            'local_qr_url'       => $localQrUrl,
            'local_fallback_url' => $localFallbackQrUrl,
            'lan_qr_url'         => $lanQrUrl,
            'lan_fallback_url'   => $lanFallbackQrUrl,
            'qr_data_uri'        => $dataUri,
            'provider'           => 'QuickChart & GoQR REST API',
            'has_api_key'        => $this->apiKey !== '',
            'api_key_configured' => $this->apiKey !== '',
            'api_key_masked'     => $this->getMaskedApiKey(),
        ];
    }

    /**
     * Tải dữ liệu nhị phân ảnh PNG từ Web Service qua cURL và mã hóa Base64 Data URI (có Cache)
     */
    private function fetchQrDataUri(string $primaryUrl, string $fallbackUrl, string $rawText, int $size): ?string
    {
        $cacheKey = 'outdoor_qr_' . md5($rawText . '_' . $size . '_' . $this->apiKey);
        $cacheDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $cacheKey . '.txt';

        if (is_file($cacheFile) && (time() - (int)@filemtime($cacheFile)) < $this->cacheTtl) {
            $cached = @file_get_contents($cacheFile);
            if (is_string($cached) && str_starts_with($cached, 'data:image/png;base64,')) {
                return $cached;
            }
        }

        $binary = $this->fetchBinaryUrl($primaryUrl);
        if ($binary === null) {
            $binary = $this->fetchBinaryUrl($fallbackUrl);
        }

        if ($binary !== null && strlen($binary) > 64) {
            $dataUri = 'data:image/png;base64,' . base64_encode($binary);
            @file_put_contents($cacheFile, $dataUri);
            return $dataUri;
        }

        return null;
    }

    /**
     * Gọi HTTP GET lấy dữ liệu ảnh PNG từ Web Service
     */
    private function fetchBinaryUrl(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            $ctx = stream_context_create([
                'http' => ['timeout' => $this->timeout, 'ignore_errors' => true],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
            ]);
            $res = @file_get_contents($url, false, $ctx);
            return ($res !== false && strlen($res) > 64) ? $res : null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OutdoorForum-QrCodeService/1.0');
        if ($this->apiKey !== '') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-API-Key: ' . $this->apiKey
            ]);
        }

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpCode >= 200 && $httpCode < 300 && strlen((string)$response) > 64) {
            return (string)$response;
        }
        return null;
    }

    /**
     * Chuẩn hóa đường dẫn tương đối thành URL tuyệt đối đầy đủ
     */
    private function normalizeShareUrl(string $inputUrl): string
    {
        $trimmed = trim($inputUrl);
        if ($trimmed === '') {
            $trimmed = '/';
        }

        if (preg_match('/^https?:\/\//i', $trimmed)) {
            return $trimmed;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $path = str_starts_with($trimmed, '/') ? $trimmed : ('/' . $trimmed);

        return $scheme . '://' . $host . $path;
    }

    /**
     * Kiểm tra URL có phải là localhost / 127.0.0.1 hay không
     */
    private function isLocalhostUrl(string $url): bool
    {
        $host = (string)parse_url($url, PHP_URL_HOST);
        return in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true);
    }

    /**
     * Tự động phát hiện IP mạng LAN nội bộ của máy tính để điện thoại cùng Wi-Fi có thể quét và truy cập
     */
    private function buildLanUrl(string $normalizedUrl): string
    {
        if (!$this->isLocalhostUrl($normalizedUrl)) {
            return $normalizedUrl;
        }

        $lanIp = $this->detectLocalLanIp();
        if ($lanIp === '' || $lanIp === '127.0.0.1') {
            return $normalizedUrl;
        }

        // Tách và tái cấu trúc URL chính xác tuyệt đối, tránh bẫy backreference regex với ký tự số
        $parsed = parse_url($normalizedUrl);
        $scheme = $parsed['scheme'] ?? 'http';
        $port = isset($parsed['port']) ? (':' . $parsed['port']) : '';
        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? ('?' . $parsed['query']) : '';
        $fragment = isset($parsed['fragment']) ? ('#' . $parsed['fragment']) : '';

        return $scheme . '://' . $lanIp . $port . $path . $query . $fragment;
    }

    /**
     * Tìm địa chỉ IPv4 LAN của máy chủ hiện tại
     * Ưu tiên dải IP Wi-Fi nội bộ thật (192.168.x.x, 10.x.x.x) trước các card mạng ảo VPN (26.x, 100.x)
     */
    private function detectLocalLanIp(): string
    {
        $hostName = @gethostname();
        if (is_string($hostName) && $hostName !== '') {
            $ipList = @gethostbynamel($hostName);
            if (is_array($ipList)) {
                // Ưu tiên 1: Dải Wi-Fi/Ethernet gia đình và trường học phổ biến nhất (192.168.x.x)
                foreach ($ipList as $ip) {
                    if (is_string($ip) && str_starts_with($ip, '192.168.')) {
                        return $ip;
                    }
                }
                // Ưu tiên 2: Dải IP mạng nội bộ cơ quan, trường đại học (10.x.x.x hoặc 172.16.x.x - 172.31.x.x)
                foreach ($ipList as $ip) {
                    if (is_string($ip) && (str_starts_with($ip, '10.') || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip))) {
                        return $ip;
                    }
                }
                // Ưu tiên 3: Các IPv4 hợp lệ khác (loại bỏ localhost, APIPA 169.254, và card ảo Radmin VPN 26.x)
                foreach ($ipList as $ip) {
                    if (is_string($ip)
                        && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                        && !str_starts_with($ip, '127.')
                        && !str_starts_with($ip, '169.254.')
                        && !str_starts_with($ip, '26.')) {
                        return $ip;
                    }
                }
            }
        }
        return '127.0.0.1';
    }

    private function mapTypeLabel(string $type): string
    {
        if ($type === 'trip') {
            return 'Chuyến đi dã ngoại';
        }
        if ($type === 'post') {
            return 'Bài viết chia sẻ kinh nghiệm';
        }
        if ($type === 'checklist') {
            return 'Checklist hành trang dã ngoại';
        }
        return 'Liên kết diễn đàn Outdoor Forum';
    }

    public function getMaskedApiKey(): string
    {
        $len = strlen($this->apiKey);
        if ($len <= 8) {
            return 'Đã cấu hình';
        }
        return substr($this->apiKey, 0, 6) . '••••' . substr($this->apiKey, -4);
    }
}

