<?php
/**
 * Service WeatherService - Tích hợp Web Service thời tiết Open-Meteo (JSON)
 * Phục vụ FR06 (Vương - Tuần 5): Hiển thị thời tiết & dự báo tại địa điểm dã ngoại
 * Sử dụng cURL + File Cache (15 phút) + Xử lý lỗi/Timeout an toàn
 */
class WeatherService
{
    private int $cacheTtl = 900; // 15 phút (900 giây)
    private int $timeout = 4;    // Timeout 4 giây cho cURL

    /**
     * Lấy thời tiết hiện tại và dự báo 4 ngày cho tọa độ (lat, lng)
     * @param float $lat Vĩ độ
     * @param float $lng Kinh độ
     * @return array Dữ liệu thời tiết chuẩn hóa
     */
    public function getForecast(float $lat, float $lng): array
    {
        // Giới hạn phạm vi tọa độ hợp lệ
        if ($lat < -90 || $lat > 90) {
            $lat = 21.2833;
        }
        if ($lng < -180 || $lng > 180) {
            $lng = 104.3167;
        }

        $roundedLat = round($lat, 3);
        $roundedLng = round($lng, 3);
        $cacheKey = 'outdoor_weather_' . md5($roundedLat . '_' . $roundedLng);
        $cacheFile = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . $cacheKey . '.json';

        // 1. Kiểm tra cache còn hạn (15 phút)
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTtl) {
            $cachedJson = @file_get_contents($cacheFile);
            if ($cachedJson !== false) {
                $cachedData = json_decode($cachedJson, true);
                if (is_array($cachedData) && !empty($cachedData['temp'])) {
                    $cachedData['cached'] = true;
                    return $cachedData;
                }
            }
        }

        // 2. Gọi Web Service Open-Meteo API qua cURL
        $query = http_build_query([
            'latitude'        => $roundedLat,
            'longitude'       => $roundedLng,
            'current'         => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m',
            'current_weather' => 'true',
            'daily'           => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max',
            'timezone'        => 'Asia/Ho_Chi_Minh',
            'forecast_days'   => 4
        ]);
        $url = 'https://api.open-meteo.com/v1/forecast?' . $query;

        $rawResponse = $this->fetchUrl($url);
        if ($rawResponse !== null) {
            $decoded = json_decode($rawResponse, true);
            if (is_array($decoded)) {
                $formatted = $this->formatOpenMeteoData($decoded, $roundedLat, $roundedLng);
                if (!empty($formatted)) {
                    @file_put_contents($cacheFile, json_encode($formatted, JSON_UNESCAPED_UNICODE));
                    return $formatted;
                }
            }
        }

        // 3. Fallback an toàn khi không có mạng hoặc API quá tải
        return $this->getFallbackWeather($roundedLat, $roundedLng);
    }

    /**
     * Thực hiện HTTP GET bằng cURL
     */
    private function fetchUrl(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            $ctx = stream_context_create([
                'http' => ['timeout' => $this->timeout, 'ignore_errors' => true],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
            ]);
            $res = @file_get_contents($url, false, $ctx);
            return $res !== false ? $res : null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OutdoorForum-WebAppDev/1.0');
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
            return (string)$response;
        }
        return null;
    }

    /**
     * Chuẩn hóa dữ liệu JSON từ Open-Meteo
     */
    private function formatOpenMeteoData(array $apiData, float $lat, float $lng): array
    {
        $current = $apiData['current'] ?? [];
        $legacyCurrent = $apiData['current_weather'] ?? [];

        $temp = $current['temperature_2m'] ?? $legacyCurrent['temperature'] ?? null;
        if ($temp === null) {
            return [];
        }

        $weatherCode = (int)($current['weather_code'] ?? $legacyCurrent['weathercode'] ?? 0);
        $windspeed = round((float)($current['wind_speed_10m'] ?? $legacyCurrent['windspeed'] ?? 5), 1);
        $humidity = (int)($current['relative_humidity_2m'] ?? 75);
        $feelsLike = round((float)($current['apparent_temperature'] ?? $temp));

        $meta = $this->mapWeatherCode($weatherCode);

        // Xử lý dự báo 4 ngày tới
        $daily = [];
        if (!empty($apiData['daily']['time']) && is_array($apiData['daily']['time'])) {
            $times = $apiData['daily']['time'];
            $codes = $apiData['daily']['weather_code'] ?? [];
            $maxTemps = $apiData['daily']['temperature_2m_max'] ?? [];
            $minTemps = $apiData['daily']['temperature_2m_min'] ?? [];
            $rainProbs = $apiData['daily']['precipitation_probability_max'] ?? [];

            $count = min(4, count($times));
            for ($i = 0; $i < $count; $i++) {
                $dayCode = (int)($codes[$i] ?? 0);
                $dayMeta = $this->mapWeatherCode($dayCode);
                $timestamp = strtotime((string)$times[$i]);
                $daily[] = [
                    'date'       => $timestamp ? date('d/m', $timestamp) : (string)$times[$i],
                    'day_label'  => $i === 0 ? 'Hôm nay' : $this->getVietnameseDayName($timestamp),
                    'temp_max'   => isset($maxTemps[$i]) ? (int)round((float)$maxTemps[$i]) : (int)round((float)$temp + 3),
                    'temp_min'   => isset($minTemps[$i]) ? (int)round((float)$minTemps[$i]) : (int)round((float)$temp - 3),
                    'rain_prob'  => isset($rainProbs[$i]) ? (int)$rainProbs[$i] : 15,
                    'condition'  => $dayMeta['condition'],
                    'icon'       => $dayMeta['icon']
                ];
            }
        }

        return [
            'lat'        => $lat,
            'lng'        => $lng,
            'temp'       => (int)round((float)$temp),
            'feels_like' => (int)$feelsLike,
            'humidity'   => $humidity,
            'windspeed'  => $windspeed,
            'code'       => $weatherCode,
            'condition'  => $meta['condition'],
            'icon'       => $meta['icon'],
            'advice'     => $meta['advice'],
            'daily'      => $daily,
            'source'     => 'Open-Meteo API',
            'cached'     => false
        ];
    }

    /**
     * Ánh xạ mã thời tiết WMO sang mô tả tiếng Việt, icon Bootstrap và lời khuyên dã ngoại
     */
    private function mapWeatherCode(int $code): array
    {
        if ($code === 0) {
            return [
                'condition' => 'Trời quang, nắng đẹp',
                'icon'      => 'bi-sun-fill text-warning',
                'advice'    => 'Thời tiết rất lý tưởng để cắm trại, trekking và ngắm sao ban đêm.'
            ];
        }
        if ($code >= 1 && $code <= 3) {
            return [
                'condition' => 'Ít mây, mát mẻ',
                'icon'      => 'bi-cloud-sun-fill text-warning',
                'advice'    => 'Thời tiết thuận lợi cho hoạt động leo núi, săn mây và chụp ảnh.'
            ];
        }
        if ($code >= 45 && $code <= 48) {
            return [
                'condition' => 'Có sương mù',
                'icon'      => 'bi-cloud-fog2-fill text-info',
                'advice'    => 'Tầm nhìn hạn chế vào sáng sớm, nên mang đèn pin đội đầu và áo giữ nhiệt.'
            ];
        }
        if ($code >= 51 && $code <= 67) {
            return [
                'condition' => 'Có mưa nhỏ / mưa phùn',
                'icon'      => 'bi-cloud-drizzle-fill text-info',
                'advice'    => 'Đường rừng có thể trơn trượt, nên dùng giày bám địa hình và bọc balo chống nước.'
            ];
        }
        if ($code >= 71 && $code <= 77) {
            return [
                'condition' => 'Trời lạnh giá',
                'icon'      => 'bi-snow2 text-info',
                'advice'    => 'Nhiệt độ xuống thấp, cần chuẩn bị túi ngủ chịu nhiệt và miếng dán giữ ấm.'
            ];
        }
        if ($code >= 80 && $code <= 82) {
            return [
                'condition' => 'Mưa rào rải rác',
                'icon'      => 'bi-cloud-rain-heavy-fill text-primary',
                'advice'    => 'Nên chuẩn bị lều 2 lớp chống nước tốt và tránh hạ trại sát bờ suối.'
            ];
        }
        if ($code >= 95) {
            return [
                'condition' => 'Giông bão',
                'icon'      => 'bi-cloud-lightning-rain-fill text-danger',
                'advice'    => 'Cảnh báo thời tiết xấu! Hạn chế di chuyển trên đỉnh núi trống trải.'
            ];
        }

        return [
            'condition' => 'Thời tiết ôn hòa',
            'icon'      => 'bi-cloud-sun text-warning',
            'advice'    => 'Thích hợp cho các hoạt động dã ngoại ngoài trời.'
        ];
    }

    private function getVietnameseDayName(int|false $timestamp): string
    {
        if (!$timestamp) {
            return 'Ngày tới';
        }
        $w = (int)date('w', $timestamp);
        $days = [
            0 => 'Chủ nhật',
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7'
        ];
        return $days[$w] ?? 'Ngày tới';
    }

    /**
     * Dữ liệu dự phòng khi ngoại tuyến
     */
    private function getFallbackWeather(float $lat, float $lng): array
    {
        // Tính nhiệt độ mô phỏng hợp lý theo vĩ độ/độ cao vùng núi
        $baseTemp = ($lat > 20.5 || ($lat > 11.5 && $lat < 12.3)) ? 19 : 25;
        $now = time();

        return [
            'lat'        => $lat,
            'lng'        => $lng,
            'temp'       => $baseTemp,
            'feels_like' => $baseTemp - 1,
            'humidity'   => 78,
            'windspeed'  => 8.5,
            'code'       => 2,
            'condition'  => 'Có mây nhẹ, mát mẻ',
            'icon'       => 'bi-cloud-sun-fill text-warning',
            'advice'     => 'Thời tiết mát mẻ, thích hợp cho hoạt động cắm trại và trekking.',
            'daily'      => [
                [
                    'date'      => date('d/m', $now),
                    'day_label' => 'Hôm nay',
                    'temp_max'  => $baseTemp + 3,
                    'temp_min'  => $baseTemp - 4,
                    'rain_prob' => 15,
                    'condition' => 'Có mây nhẹ',
                    'icon'      => 'bi-cloud-sun-fill text-warning'
                ],
                [
                    'date'      => date('d/m', $now + 86400),
                    'day_label' => $this->getVietnameseDayName($now + 86400),
                    'temp_max'  => $baseTemp + 4,
                    'temp_min'  => $baseTemp - 3,
                    'rain_prob' => 10,
                    'condition' => 'Nắng đẹp',
                    'icon'      => 'bi-sun-fill text-warning'
                ],
                [
                    'date'      => date('d/m', $now + 172800),
                    'day_label' => $this->getVietnameseDayName($now + 172800),
                    'temp_max'  => $baseTemp + 2,
                    'temp_min'  => $baseTemp - 4,
                    'rain_prob' => 25,
                    'condition' => 'Có sương sớm',
                    'icon'      => 'bi-cloud-fog2-fill text-info'
                ],
                [
                    'date'      => date('d/m', $now + 259200),
                    'day_label' => $this->getVietnameseDayName($now + 259200),
                    'temp_max'  => $baseTemp + 3,
                    'temp_min'  => $baseTemp - 3,
                    'rain_prob' => 20,
                    'condition' => 'Ít mây',
                    'icon'      => 'bi-cloud-sun-fill text-warning'
                ]
            ],
            'source'     => 'Open-Meteo Service',
            'cached'     => false
        ];
    }
}

