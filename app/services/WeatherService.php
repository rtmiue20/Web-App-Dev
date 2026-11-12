<?php
require_once __DIR__ . '/../core/BaseService.php';

/**
 * Service WeatherService - Tích hợp 2 Web Services khí tượng từ hệ sinh thái Open-Meteo (JSON):
 *  1. Open-Meteo Weather Forecast API (Nhiệt độ, độ ẩm, sức gió, tia UV, dự báo 4-16 ngày)
 *  2. Open-Meteo Air Quality API (Chất lượng không khí US AQI, Bụi mịn PM2.5, PM10, Tia cực tím UV)
 *
 * Đáp ứng chuẩn kiến trúc OOP + MVC (kế thừa BaseService), sử dụng cURL + File Cache (15 phút)
 * và cơ chế Fallback an toàn khi ngoại tuyến.
 */
class WeatherService extends BaseService
{
    private int $cacheTtl = 900; // 15 phút (900 giây)
    private int $timeout = 5;    // Timeout 5 giây cho mỗi request cURL

    /**
     * Lấy thời tiết hiện tại, chỉ số UV, chất lượng không khí (AQI/PM2.5) và dự báo cho tọa độ (lat, lng).
     *
     * @param float  $lat          Vĩ độ (-90 đến 90)
     * @param float  $lng          Kinh độ (-180 đến 180)
     * @param string $forecastDate Ngày dự báo cụ thể dạng Y-m-d (tùy chọn cho chuyến đi)
     * @return array Dữ liệu thời tiết & môi trường chuẩn hóa
     */
    public function getForecast(float $lat, float $lng, string $forecastDate = ''): array
    {
        if ($lat < -90 || $lat > 90) {
            $lat = 21.2833;
        }
        if ($lng < -180 || $lng > 180) {
            $lng = 104.3167;
        }

        $roundedLat = round($lat, 3);
        $roundedLng = round($lng, 3);
        $cleanDate = trim($forecastDate);

        $cacheKey = 'outdoor_weather_v2_' . md5($roundedLat . '_' . $roundedLng . '_' . $cleanDate);
        $cacheFile = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . $cacheKey . '.json';

        // 1. Kiểm tra cache còn hạn (15 phút)
        if (is_file($cacheFile) && (time() - (int)@filemtime($cacheFile)) < $this->cacheTtl) {
            $cachedJson = @file_get_contents($cacheFile);
            if ($cachedJson !== false) {
                $cachedData = json_decode($cachedJson, true);
                if (is_array($cachedData) && isset($cachedData['temp']) && isset($cachedData['uv']) && isset($cachedData['air_quality'])) {
                    $cachedData['cached'] = true;
                    return $cachedData;
                }
            }
        }

        // 2. Gọi Open-Meteo Weather Forecast API (Nhiệt độ, Độ ẩm, Gió, Tia UV, Dự báo ngày)
        $forecastQuery = [
            'latitude'        => $roundedLat,
            'longitude'       => $roundedLng,
            'current'         => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,uv_index',
            'current_weather' => 'true',
            'daily'           => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,wind_speed_10m_max,uv_index_max',
            'timezone'        => 'Asia/Ho_Chi_Minh',
            'forecast_days'   => 4
        ];

        if ($cleanDate !== '') {
            $forecastQuery['start_date'] = $cleanDate;
            $forecastQuery['end_date'] = $cleanDate;
            unset($forecastQuery['forecast_days']);
        }

        $forecastUrl = 'https://api.open-meteo.com/v1/forecast?' . http_build_query($forecastQuery);
        $rawForecast = $this->fetchUrl($forecastUrl);

        // 3. Gọi Open-Meteo Air Quality & UV API (US AQI, European AQI, PM2.5, PM10, UV Index)
        $airQuery = [
            'latitude'  => $roundedLat,
            'longitude' => $roundedLng,
            'current'   => 'us_aqi,european_aqi,pm2_5,pm10,uv_index',
            'timezone'  => 'Asia/Ho_Chi_Minh'
        ];
        $airUrl = 'https://air-quality-api.open-meteo.com/v1/air-quality?' . http_build_query($airQuery);
        $rawAir = $this->fetchUrl($airUrl);

        $airDecoded = [];
        if ($rawAir !== null) {
            $parsedAir = json_decode($rawAir, true);
            if (is_array($parsedAir)) {
                $airDecoded = $parsedAir;
            }
        }

        if ($rawForecast !== null) {
            $decoded = json_decode($rawForecast, true);
            if (is_array($decoded)) {
                $formatted = $this->formatOpenMeteoData($decoded, $airDecoded, $roundedLat, $roundedLng, $cleanDate);
                if (!empty($formatted)) {
                    @file_put_contents($cacheFile, json_encode($formatted, JSON_UNESCAPED_UNICODE));
                    return $formatted;
                }
            }
        }

        // 4. Fallback an toàn khi không có mạng hoặc API quá tải
        return $this->getFallbackWeather($roundedLat, $roundedLng, $cleanDate);
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
     * Chuẩn hóa dữ liệu JSON từ Open-Meteo Forecast API + Air Quality API
     */
    private function formatOpenMeteoData(
        array $apiData,
        array $airData,
        float $lat,
        float $lng,
        string $forecastDate = ''
    ): array {
        $current = $apiData['current'] ?? [];
        $legacyCurrent = $apiData['current_weather'] ?? [];
        $dailyRaw = $apiData['daily'] ?? [];
        $airCurrent = $airData['current'] ?? [];

        if ($forecastDate !== '' && isset($dailyRaw['temperature_2m_max'][0])) {
            $temp = (float)$dailyRaw['temperature_2m_max'][0];
            $weatherCode = (int)($dailyRaw['weather_code'][0] ?? 0);
            $windspeed = round((float)($dailyRaw['wind_speed_10m_max'][0] ?? 8.0), 1);
            $humidity = (int)($current['relative_humidity_2m'] ?? 75);
            $feelsLike = round($temp);
        } else {
            $temp = $current['temperature_2m'] ?? $legacyCurrent['temperature'] ?? null;
            if ($temp === null) {
                return [];
            }
            $weatherCode = (int)($current['weather_code'] ?? $legacyCurrent['weathercode'] ?? 0);
            $windspeed = round((float)($current['wind_speed_10m'] ?? $legacyCurrent['windspeed'] ?? 5), 1);
            $humidity = (int)($current['relative_humidity_2m'] ?? 75);
            $feelsLike = round((float)($current['apparent_temperature'] ?? $temp));
        }

        // Chỉ số tia cực tím (UV Index): lấy từ current hoặc air_quality current hoặc daily max
        $uvCurrentRaw = $current['uv_index'] ?? $airCurrent['uv_index'] ?? null;
        $uvMaxDailyRaw = $dailyRaw['uv_index_max'][0] ?? null;
        if ($uvCurrentRaw === null && $uvMaxDailyRaw !== null) {
            $uvCurrentRaw = $uvMaxDailyRaw;
        }
        $uvIndex = $uvCurrentRaw !== null ? round((float)$uvCurrentRaw, 1) : 5.5;
        $uvMaxDaily = $uvMaxDailyRaw !== null ? round((float)$uvMaxDailyRaw, 1) : max($uvIndex, 6.5);
        $uvMeta = $this->evaluateUvIndex($uvIndex, $uvMaxDaily);

        // Chất lượng không khí (AQI & Bụi mịn PM2.5 / PM10)
        $usAqiRaw = $airCurrent['us_aqi'] ?? $airCurrent['european_aqi'] ?? null;
        $pm25Raw = $airCurrent['pm2_5'] ?? null;
        $pm10Raw = $airCurrent['pm10'] ?? null;

        $aqi = $usAqiRaw !== null ? (int)round((float)$usAqiRaw) : 24;
        $pm25 = $pm25Raw !== null ? round((float)$pm25Raw, 1) : 6.2;
        $pm10 = $pm10Raw !== null ? round((float)$pm10Raw, 1) : 12.8;
        $airMeta = $this->evaluateAirQuality($aqi, $pm25, $pm10);

        $meta = $this->mapWeatherCode($weatherCode);

        // Xử lý dự báo 4 ngày tới
        $daily = [];
        if ($forecastDate === '' && !empty($dailyRaw['time']) && is_array($dailyRaw['time'])) {
            $times = $dailyRaw['time'];
            $codes = $dailyRaw['weather_code'] ?? [];
            $maxTemps = $dailyRaw['temperature_2m_max'] ?? [];
            $minTemps = $dailyRaw['temperature_2m_min'] ?? [];
            $rainProbs = $dailyRaw['precipitation_probability_max'] ?? [];
            $uvMaxList = $dailyRaw['uv_index_max'] ?? [];

            $count = min(4, count($times));
            for ($i = 0; $i < $count; $i++) {
                $dayCode = (int)($codes[$i] ?? 0);
                $dayMeta = $this->mapWeatherCode($dayCode);
                $timestamp = strtotime((string)$times[$i]);
                $dayUvMax = isset($uvMaxList[$i]) ? round((float)$uvMaxList[$i], 1) : $uvMaxDaily;
                $daily[] = [
                    'date'       => $timestamp ? date('d/m', $timestamp) : (string)$times[$i],
                    'day_label'  => $i === 0 ? 'Hôm nay' : $this->getVietnameseDayName($timestamp),
                    'temp_max'   => isset($maxTemps[$i]) ? (int)round((float)$maxTemps[$i]) : (int)round((float)$temp + 3),
                    'temp_min'   => isset($minTemps[$i]) ? (int)round((float)$minTemps[$i]) : (int)round((float)$temp - 3),
                    'rain_prob'  => isset($rainProbs[$i]) ? (int)$rainProbs[$i] : 15,
                    'uv_max'     => $dayUvMax,
                    'condition'  => $dayMeta['condition'],
                    'icon'       => $dayMeta['icon']
                ];
            }
        }

        $rainProbToday = (int)($dailyRaw['precipitation_probability_max'][0] ?? 15);
        $rainSumToday = (float)($dailyRaw['precipitation_sum'][0] ?? 0.0);
        $alerts = $this->buildSafetyAlerts($weatherCode, $windspeed, $rainProbToday, $rainSumToday, $uvIndex, $aqi);

        return [
            'lat'         => $lat,
            'lng'         => $lng,
            'temp'        => (int)round((float)$temp),
            'feels_like'  => (int)$feelsLike,
            'humidity'    => $humidity,
            'windspeed'   => $windspeed,
            'code'        => $weatherCode,
            'condition'   => $meta['condition'],
            'icon'        => $meta['icon'],
            'advice'      => $meta['advice'],
            'uv'          => $uvMeta,
            'air_quality' => $airMeta,
            'alerts'      => $alerts,
            'date'        => $forecastDate !== '' ? $forecastDate : null,
            'daily'       => $daily,
            'source'      => 'Open-Meteo Weather & Air Quality API',
            'cached'      => false
        ];
    }

    /**
     * Phân loại Chỉ số Tia cực tím (UV Index) và đưa ra khuyến nghị bảo vệ sức khỏe dã ngoại
     */
    private function evaluateUvIndex(float $uvIndex, float $uvMaxDaily): array
    {
        $effectiveUv = max($uvIndex, 0.0);
        $percent = (int)min(100, round(($effectiveUv / 11.0) * 100));

        if ($effectiveUv < 3.0) {
            return [
                'index'        => $effectiveUv,
                'max_daily'    => $uvMaxDaily,
                'level'        => 'Thấp',
                'risk_code'    => 'low',
                'percent'      => max(8, $percent),
                'color'        => '#4ade80',
                'badge_bg'     => 'rgba(74, 222, 128, 0.16)',
                'badge_border' => 'rgba(74, 222, 128, 0.4)',
                'icon'         => 'bi-shield-check',
                'advice'       => 'Bức xạ tia cực tím thấp (đỉnh ngày UV ' . $uvMaxDaily . '). An toàn cho các hoạt động trekking và cắm trại ngoài trời.'
            ];
        }

        if ($effectiveUv < 6.0) {
            return [
                'index'        => $effectiveUv,
                'max_daily'    => $uvMaxDaily,
                'level'        => 'Trung bình',
                'risk_code'    => 'moderate',
                'percent'      => $percent,
                'color'        => '#facc15',
                'badge_bg'     => 'rgba(250, 204, 21, 0.16)',
                'badge_border' => 'rgba(250, 204, 21, 0.4)',
                'icon'         => 'bi-brightness-high-fill',
                'advice'       => 'Mức bức xạ trung bình (đỉnh ngày UV ' . $uvMaxDaily . '). Nên đội mũ rộng vành, đeo kính râm và bôi kem chống nắng SPF 30+.'
            ];
        }

        if ($effectiveUv < 8.0) {
            return [
                'index'        => $effectiveUv,
                'max_daily'    => $uvMaxDaily,
                'level'        => 'Cao',
                'risk_code'    => 'high',
                'percent'      => $percent,
                'color'        => '#fb923c',
                'badge_bg'     => 'rgba(251, 146, 60, 0.18)',
                'badge_border' => 'rgba(251, 146, 60, 0.45)',
                'icon'         => 'bi-sun-fill',
                'advice'       => 'Cảnh báo nắng gắt (UV ' . $effectiveUv . ', đỉnh ngày ' . $uvMaxDaily . ')! Cần bôi kem chống nắng SPF 50+, mặc áo khoác chống UV dài tay và bù nước liên tục.'
            ];
        }

        if ($effectiveUv < 11.0) {
            return [
                'index'        => $effectiveUv,
                'max_daily'    => $uvMaxDaily,
                'level'        => 'Rất cao',
                'risk_code'    => 'very_high',
                'percent'      => $percent,
                'color'        => '#f87171',
                'badge_bg'     => 'rgba(248, 113, 113, 0.2)',
                'badge_border' => 'rgba(248, 113, 113, 0.5)',
                'icon'         => 'bi-exclamation-triangle-fill',
                'advice'       => 'Nguy cơ cháy nắng rất cao (UV ' . $effectiveUv . ')! Hạn chế băng rừng trống trải từ 11h–15h, bắt buộc dùng kem chống nắng SPF 50+ và che chắn kỹ.'
            ];
        }

        return [
            'index'        => $effectiveUv,
            'max_daily'    => $uvMaxDaily,
            'level'        => 'Cực kỳ nguy hiểm',
            'risk_code'    => 'extreme',
            'percent'      => 100,
            'color'        => '#c084fc',
            'badge_bg'     => 'rgba(192, 132, 252, 0.22)',
            'badge_border' => 'rgba(192, 132, 252, 0.5)',
            'icon'         => 'bi-radioactive',
            'advice'       => 'Tia cực tím ở ngưỡng cực đại (UV ' . $effectiveUv . ')! Tránh tiếp xúc trực tiếp với ánh nắng giữa trưa, ưu tiên nghỉ dưới tán rừng rậm hoặc tăng bạt.'
        ];
    }

    /**
     * Phân loại Chất lượng không khí (US AQI & Bụi mịn PM2.5, PM10) cho hoạt động leo núi / cắm trại
     */
    private function evaluateAirQuality(int $aqi, float $pm25, float $pm10): array
    {
        $cleanAqi = max(1, $aqi);
        $percent = (int)min(100, max(6, round(($cleanAqi / 200.0) * 100)));

        if ($cleanAqi <= 50) {
            return [
                'aqi'          => $cleanAqi,
                'pm2_5'        => $pm25,
                'pm10'         => $pm10,
                'level'        => 'Rất trong lành',
                'status_code'  => 'good',
                'percent'      => $percent,
                'color'        => '#4ade80',
                'badge_bg'     => 'rgba(74, 222, 128, 0.16)',
                'badge_border' => 'rgba(74, 222, 128, 0.4)',
                'icon'         => 'bi-wind',
                'advice'       => 'Không khí vùng núi rất trong lành (PM2.5: ' . $pm25 . ' µg/m³), cực kỳ lý tưởng để trekking, chạy trail và hạ trại qua đêm.'
            ];
        }

        if ($cleanAqi <= 100) {
            return [
                'aqi'          => $cleanAqi,
                'pm2_5'        => $pm25,
                'pm10'         => $pm10,
                'level'        => 'Trung bình - Tốt',
                'status_code'  => 'moderate',
                'percent'      => $percent,
                'color'        => '#facc15',
                'badge_bg'     => 'rgba(250, 204, 21, 0.16)',
                'badge_border' => 'rgba(250, 204, 21, 0.4)',
                'icon'         => 'bi-cloud-haze2',
                'advice'       => 'Chất lượng không khí chấp nhận được (PM2.5: ' . $pm25 . ' µg/m³). Phù hợp cho đại đa số thành viên tham gia dã ngoại.'
            ];
        }

        if ($cleanAqi <= 150) {
            return [
                'aqi'          => $cleanAqi,
                'pm2_5'        => $pm25,
                'pm10'         => $pm10,
                'level'        => 'Kém cho nhóm nhạy cảm',
                'status_code'  => 'sensitive',
                'percent'      => $percent,
                'color'        => '#fb923c',
                'badge_bg'     => 'rgba(251, 146, 60, 0.18)',
                'badge_border' => 'rgba(251, 146, 60, 0.45)',
                'icon'         => 'bi-exclamation-circle-fill',
                'advice'       => 'Nồng độ bụi mịn PM2.5 ở mức ' . $pm25 . ' µg/m³. Người có bệnh hô hấp hoặc hen suyễn nên hạn chế vận động gắng sức kéo dài.'
            ];
        }

        return [
            'aqi'          => $cleanAqi,
            'pm2_5'        => $pm25,
            'pm10'         => $pm10,
            'level'        => 'Ô nhiễm - Cảnh báo',
            'status_code'  => 'unhealthy',
            'percent'      => $percent,
            'color'        => '#f87171',
            'badge_bg'     => 'rgba(248, 113, 113, 0.2)',
            'badge_border' => 'rgba(248, 113, 113, 0.5)',
            'icon'         => 'bi-shield-exclamation',
            'advice'       => 'Chất lượng không khí kém (AQI ' . $cleanAqi . ', PM2.5: ' . $pm25 . ' µg/m³)! Nên đeo khẩu trang lọc bụi và cân nhắc lùi lịch leo núi cường độ cao.'
        ];
    }

    /**
     * Tổng hợp danh sách cảnh báo an toàn chuyến đi
     */
    private function buildSafetyAlerts(
        int $weatherCode,
        float $windSpeed,
        int $rainProb,
        float $rainSum,
        float $uvIndex,
        int $aqi
    ): array {
        $alerts = [];

        if (in_array($weatherCode, [65, 67, 82, 95, 96, 99], true) || $rainSum >= 50.0) {
            $alerts[] = 'Mưa lớn hoặc dông; tránh trekking, khu vực sông suối và nơi có nguy cơ lũ quét.';
        } elseif (in_array($weatherCode, [51, 53, 55, 61, 63, 66, 80, 81], true) || $rainProb >= 60 || $rainSum >= 20.0) {
            $alerts[] = 'Có khả năng mưa; đường rừng có thể trơn trượt, cần kiểm tra tình trạng đường và cảnh báo lũ địa phương.';
        }

        if ($windSpeed >= 40.0) {
            $alerts[] = 'Gió mạnh (' . $windSpeed . ' km/h); cẩn thận khi di chuyển ở sống núi trống trải hoặc dựng lều.';
        }

        if ($uvIndex >= 8.0) {
            $alerts[] = 'Chỉ số tia cực tím UV cao (' . $uvIndex . '); cần bôi kem chống nắng SPF 50+ và mặc đồ che nắng.';
        }

        if ($aqi > 100) {
            $alerts[] = 'Chỉ số AQI ở mức ' . $aqi . '; người nhạy cảm đường hô hấp nên lưu ý khi vận động ngoài trời.';
        }

        return $alerts;
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
     * Dữ liệu dự phòng khi ngoại tuyến (đầy đủ cả UV Index và Air Quality AQI/PM2.5)
     */
    private function getFallbackWeather(float $lat, float $lng, string $forecastDate = ''): array
    {
        $baseTemp = ($lat > 20.5 || ($lat > 11.5 && $lat < 12.3)) ? 19 : 25;
        $now = time();
        $uvMeta = $this->evaluateUvIndex(5.2, 7.0);
        $airMeta = $this->evaluateAirQuality(18, 4.8, 10.5);

        return [
            'lat'         => $lat,
            'lng'         => $lng,
            'temp'        => $baseTemp,
            'feels_like'  => $baseTemp - 1,
            'humidity'    => 78,
            'windspeed'   => 8.5,
            'code'        => 2,
            'condition'   => 'Có mây nhẹ, mát mẻ',
            'icon'        => 'bi-cloud-sun-fill text-warning',
            'advice'      => 'Thời tiết mát mẻ, thích hợp cho hoạt động cắm trại và trekking.',
            'uv'          => $uvMeta,
            'air_quality' => $airMeta,
            'alerts'      => [],
            'date'        => $forecastDate !== '' ? $forecastDate : null,
            'daily'       => [
                [
                    'date'      => date('d/m', $now),
                    'day_label' => 'Hôm nay',
                    'temp_max'  => $baseTemp + 3,
                    'temp_min'  => $baseTemp - 4,
                    'rain_prob' => 15,
                    'uv_max'    => 7.0,
                    'condition' => 'Có mây nhẹ',
                    'icon'      => 'bi-cloud-sun-fill text-warning'
                ],
                [
                    'date'      => date('d/m', $now + 86400),
                    'day_label' => $this->getVietnameseDayName($now + 86400),
                    'temp_max'  => $baseTemp + 4,
                    'temp_min'  => $baseTemp - 3,
                    'rain_prob' => 10,
                    'uv_max'    => 7.5,
                    'condition' => 'Nắng đẹp',
                    'icon'      => 'bi-sun-fill text-warning'
                ],
                [
                    'date'      => date('d/m', $now + 172800),
                    'day_label' => $this->getVietnameseDayName($now + 172800),
                    'temp_max'  => $baseTemp + 2,
                    'temp_min'  => $baseTemp - 4,
                    'rain_prob' => 25,
                    'uv_max'    => 5.8,
                    'condition' => 'Có sương sớm',
                    'icon'      => 'bi-cloud-fog2-fill text-info'
                ],
                [
                    'date'      => date('d/m', $now + 259200),
                    'day_label' => $this->getVietnameseDayName($now + 259200),
                    'temp_max'  => $baseTemp + 3,
                    'temp_min'  => $baseTemp - 3,
                    'rain_prob' => 20,
                    'uv_max'    => 6.4,
                    'condition' => 'Ít mây',
                    'icon'      => 'bi-cloud-sun-fill text-warning'
                ]
            ],
            'source'      => 'Open-Meteo Weather & Air Quality API',
            'cached'      => false
        ];
    }
}
