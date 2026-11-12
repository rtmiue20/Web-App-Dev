<?php
require_once __DIR__ . '/../core/BaseService.php';

/**
 * RssService - Dịch vụ đọc Tin tức Du lịch & Dã ngoại từ nguồn RSS (XML) ngoài
 * và chuyển đổi sang cấu trúc mảng/JSON phục vụ RESTful API `/api/news` & Sidebar (FR10).
 *
 * Đáp ứng tiêu chí đề bài:
 *  - Tích hợp Webservice ngoài ở mức sử dụng service (RSS XML qua SimpleXML + cURL).
 *  - Truyền tải dữ liệu qua cả XML (đọc từ RSS feed) và JSON (trả về cho AJAX client).
 *  - Có bộ nhớ đệm (File Cache 15 phút) tại `storage/cache/` và dữ liệu dự phòng khi ngoại tuyến.
 */
class RssService extends BaseService
{
    private const DEFAULT_FEED_URL = 'https://vnexpress.net/rss/du-lich.rss';
    private const CACHE_TTL_SECONDS = 900; // 15 phút

    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? (dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache');
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Lấy danh sách bản tin dã ngoại / du lịch từ RSS XML (có cache).
     */
    public function getOutdoorNews(int $limit = 6, ?string $feedUrl = null): array
    {
        $limit = max(1, min(20, $limit));
        $targetUrl = $feedUrl ?: self::DEFAULT_FEED_URL;
        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'rss_' . md5($targetUrl) . '.json';

        // 1. Đọc từ cache nếu còn hạn (15 phút)
        if (is_file($cacheFile) && (time() - (int)@filemtime($cacheFile)) < self::CACHE_TTL_SECONDS) {
            $cachedJson = @file_get_contents($cacheFile);
            if (is_string($cachedJson) && $cachedJson !== '') {
                $decoded = json_decode($cachedJson, true);
                if (is_array($decoded) && !empty($decoded['items'])) {
                    $decoded['items'] = array_slice($decoded['items'], 0, $limit);
                    $decoded['cached'] = true;
                    return $decoded;
                }
            }
        }

        // 2. Tải XML từ RSS Feed qua cURL và phân tích bằng SimpleXML
        $xmlString = $this->fetchRawXml($targetUrl);
        if ($xmlString !== null) {
            $parsed = $this->parseRssXml($xmlString, $limit);
            if (!empty($parsed['items'])) {
                @file_put_contents(
                    $cacheFile,
                    json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
                $parsed['cached'] = false;
                return $parsed;
            }
        }

        // 3. Fallback dữ liệu RSS mẫu khi máy chạy ở môi trường offline / không có internet
        $fallback = $this->getFallbackNews($limit);
        return $fallback;
    }

    /**
     * Phân tích chuỗi XML chuẩn RSS 2.0 thành mảng PHP sạch (chống XXE & XSS).
     */
    public function parseRssXml(string $xmlContent, int $limit = 6): array
    {
        $prevUseErrors = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseErrors);

        if ($xml === false || !isset($xml->channel)) {
            return ['source' => 'RSS XML', 'format' => 'XML->JSON', 'items' => []];
        }

        $channelTitle = trim((string)($xml->channel->title ?? 'Tin Du lịch & Dã ngoại'));
        $items = [];
        $count = 0;

        foreach ($xml->channel->item as $item) {
            if ($count >= $limit) {
                break;
            }

            $rawDescription = (string)($item->description ?? '');
            $imageUrl = null;
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $rawDescription, $matches)) {
                $imageUrl = trim($matches[1]);
            }

            $cleanSummary = trim(strip_tags(html_entity_decode($rawDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            $pubDateRaw = (string)($item->pubDate ?? '');
            $timestamp = $pubDateRaw !== '' ? strtotime($pubDateRaw) : false;

            $items[] = [
                'title'        => trim((string)($item->title ?? '')),
                'link'         => trim((string)($item->link ?? '#')),
                'summary'      => mb_strimwidth($cleanSummary, 0, 140, '...'),
                'image_url'    => $imageUrl,
                'published_at' => $timestamp ? date('d/m/Y H:i', $timestamp) : $pubDateRaw,
            ];
            $count++;
        }

        return [
            'source' => $channelTitle,
            'format' => 'RSS 2.0 (XML -> JSON)',
            'items'  => $items,
        ];
    }

    /**
     * Tải nội dung XML từ URL qua cURL.
     */
    private function fetchRawXml(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_USERAGENT      => 'OutdoorForum-RssReader/1.0',
            CURLOPT_HTTPHEADER     => ['Accept: application/rss+xml, application/xml, text/xml'],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($response) || $response === '' || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        return $response;
    }

    /**
     * Dữ liệu RSS XML mẫu phục vụ môi trường Local/Offline.
     */
    private function getFallbackNews(int $limit = 6): array
    {
        $sampleXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>Tin Du lịch &amp; Dã ngoại Việt Nam (RSS XML)</title>
    <item>
      <title>Kinh nghiệm săn mây Tà Xùa mùa đẹp nhất năm cho người mới</title>
      <link>https://vnexpress.net/du-lich</link>
      <description><![CDATA[<img src="/assets/img/ta-xua-trek.png" />Hành trình 2 ngày 1 đêm chinh phục sống lưng khủng long Tà Xùa (Sơn La) với lưu ý thời tiết và trang bị giữ nhiệt.]]></description>
      <pubDate>Wed, 04 Nov 2026 07:30:00 +0700</pubDate>
    </item>
    <item>
      <title>5 bãi cắm trại ven hồ gần TP.HCM lý tưởng cho dịp cuối tuần</title>
      <link>https://vnexpress.net/du-lich</link>
      <description><![CDATA[<img src="/assets/img/tent-forest.png" />Hồ Trị An, Hồ Dầu Tiếng và Hồ Cốc là những tọa độ cắm trại tự túc thoáng đãng, dễ di chuyển trong 2-3 giờ.]]></description>
      <pubDate>Wed, 04 Nov 2026 06:15:00 +0700</pubDate>
    </item>
    <item>
      <title>Nguyên tắc Leave No Trace: Cắm trại văn minh không để lại rác rừng</title>
      <link>https://vnexpress.net/du-lich</link>
      <description><![CDATA[7 nguyên tắc vàng giúp bảo vệ hệ sinh thái tự nhiên khi đi trekking và cắm trại tự túc tại các vườn quốc gia.]]></description>
      <pubDate>Tue, 03 Nov 2026 18:00:00 +0700</pubDate>
    </item>
    <item>
      <title>Checklist chuẩn bị túi sơ cứu y tế và dụng cụ sinh tồn khi leo núi</title>
      <link>https://vnexpress.net/du-lich</link>
      <description><![CDATA[Những vật dụng nhỏ gọn nhưng quyết định sự an toàn của cả đoàn khi gặp mưa rừng hoặc sương mù dày đặc.]]></description>
      <pubDate>Tue, 03 Nov 2026 14:20:00 +0700</pubDate>
    </item>
  </channel>
</rss>
XML;

        $parsed = $this->parseRssXml($sampleXml, $limit);
        $parsed['cached'] = true;
        return $parsed;
    }
}

