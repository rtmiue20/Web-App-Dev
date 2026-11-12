<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/bootstrap.php';

$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
$forecastDate = trim((string) ($_GET['date'] ?? ''));
if ($lat === false || $lat === null || $lat < -90 || $lat > 90
    || $lng === false || $lng === null || $lng < -180 || $lng > 180) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tọa độ không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$query = [
    'latitude' => $lat,
    'longitude' => $lng,
];
if ($forecastDate !== '') {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $forecastDate);
    $today = new DateTimeImmutable('today');
    if (!$date || $date->format('Y-m-d') !== $forecastDate) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ngày dự báo không hợp lệ.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($date < $today || $date > $today->modify('+15 days')) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Chỉ có thể xem dự báo trong 16 ngày tới. Hãy kiểm tra lại gần ngày đi.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $query += [
        'daily' => 'weather_code,temperature_2m_max,precipitation_probability_max,precipitation_sum,wind_speed_10m_max',
        'timezone' => 'auto',
        'start_date' => $forecastDate,
        'end_date' => $forecastDate,
    ];
} else {
    $query['current_weather'] = 'true';
    $query['daily'] = 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max';
    $query['timezone'] = 'auto';
    $query['forecast_days'] = 4;
}
$url = 'https://api.open-meteo.com/v1/forecast?' . http_build_query($query);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $httpStatus < 200 || $httpStatus >= 300) {
    error_log('Open-Meteo request failed: ' . ($curlError !== '' ? $curlError : 'HTTP ' . $httpStatus));
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Dịch vụ thời tiết hiện không khả dụng.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($response, true);
if (!is_array($data)) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu thời tiết trả về không hợp lệ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$airQuality = null;
if ($forecastDate === '') {
    $airQualityQuery = http_build_query([
        'latitude' => $lat,
        'longitude' => $lng,
        'current' => 'us_aqi,pm2_5,uv_index',
        'timezone' => 'auto',
    ]);
    $airQualityUrl = 'https://air-quality-api.open-meteo.com/v1/air-quality?' . $airQualityQuery;
    $airQualityHandle = curl_init();
    curl_setopt($airQualityHandle, CURLOPT_URL, $airQualityUrl);
    curl_setopt($airQualityHandle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($airQualityHandle, CURLOPT_TIMEOUT, 3);
    curl_setopt($airQualityHandle, CURLOPT_SSL_VERIFYPEER, true);
    $airQualityResponse = curl_exec($airQualityHandle);
    $airQualityStatus = (int) curl_getinfo($airQualityHandle, CURLINFO_HTTP_CODE);
    $airQualityError = curl_error($airQualityHandle);
    curl_close($airQualityHandle);

    if ($airQualityResponse === false || $airQualityStatus < 200 || $airQualityStatus >= 300) {
        error_log('Open-Meteo air quality request failed: ' . ($airQualityError !== '' ? $airQualityError : 'HTTP ' . $airQualityStatus));
    } else {
        $airQualityData = json_decode($airQualityResponse, true);
        $airCurrent = is_array($airQualityData) ? ($airQualityData['current'] ?? null) : null;
        if (is_array($airCurrent)) {
            $uvIndex = isset($airCurrent['uv_index']) && is_numeric($airCurrent['uv_index'])
                ? (float) $airCurrent['uv_index']
                : null;
            $usAqi = isset($airCurrent['us_aqi']) && is_numeric($airCurrent['us_aqi'])
                ? (int) round((float) $airCurrent['us_aqi'])
                : null;
            $pm25 = isset($airCurrent['pm2_5']) && is_numeric($airCurrent['pm2_5'])
                ? (float) $airCurrent['pm2_5']
                : null;

            if ($uvIndex !== null || $usAqi !== null || $pm25 !== null) {
                $airQuality = [
                    'uv_index' => $uvIndex !== null ? round($uvIndex, 1) : null,
                    'uv_category' => $uvIndex === null ? null : match (true) {
                        $uvIndex <= 2 => 'Thấp',
                        $uvIndex <= 5 => 'Trung bình',
                        $uvIndex <= 7 => 'Cao',
                        $uvIndex <= 10 => 'Rất cao',
                        default => 'Cực cao',
                    },
                    'uv_advice' => $uvIndex === null ? null : match (true) {
                        $uvIndex <= 2 => 'Có thể hoạt động ngoài trời bình thường.',
                        $uvIndex <= 5 => 'Nên che nắng và dùng kem chống nắng khi ở ngoài lâu.',
                        $uvIndex <= 7 => 'Cần che chắn, dùng kem chống nắng và hạn chế nắng gắt giữa trưa.',
                        $uvIndex <= 10 => 'Nguy cơ cao; tránh nắng giữa trưa, mặc áo dài tay và dùng kem chống nắng.',
                        default => 'Nguy cơ rất cao; hạn chế ra ngoài và che chắn kỹ nếu phải di chuyển.',
                    },
                    'us_aqi' => $usAqi,
                    'aqi_category' => $usAqi === null ? null : match (true) {
                        $usAqi <= 50 => 'Tốt',
                        $usAqi <= 100 => 'Trung bình',
                        $usAqi <= 150 => 'Không tốt cho nhóm nhạy cảm',
                        $usAqi <= 200 => 'Không tốt',
                        $usAqi <= 300 => 'Rất không tốt',
                        default => 'Nguy hại',
                    },
                    'aqi_advice' => $usAqi === null ? null : match (true) {
                        $usAqi <= 50 => 'Không khí tốt cho các hoạt động ngoài trời.',
                        $usAqi <= 100 => 'Nhóm nhạy cảm nên theo dõi sức khỏe khi hoạt động lâu ngoài trời.',
                        $usAqi <= 150 => 'Người nhạy cảm nên hạn chế hoạt động gắng sức ngoài trời.',
                        $usAqi <= 200 => 'Nên giảm hoạt động ngoài trời kéo dài, đặc biệt với người nhạy cảm.',
                        $usAqi <= 300 => 'Hạn chế hoạt động ngoài trời; nhóm nhạy cảm nên ở trong nhà.',
                        default => 'Tránh hoạt động ngoài trời và tìm nơi có không khí trong lành.',
                    },
                    'pm2_5' => $pm25 !== null ? round($pm25, 1) : null,
                ];
            } else {
                error_log('Open-Meteo air quality response did not contain current UV, AQI, or PM2.5 values.');
            }
        } else {
            error_log('Open-Meteo air quality response did not contain current conditions.');
        }
    }
}

$alerts = [];
if ($forecastDate !== '') {
    $daily = $data['daily'] ?? null;
    if (!is_array($daily) || !isset($daily['weather_code'][0], $daily['temperature_2m_max'][0])) {
        http_response_code(502);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu dự báo trả về không hợp lệ.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $weatherCode = (int) $daily['weather_code'][0];
    $temperature = (float) $daily['temperature_2m_max'][0];
    $precipitationProbability = (int) ($daily['precipitation_probability_max'][0] ?? 0);
    $precipitation = (float) ($daily['precipitation_sum'][0] ?? 0);
    $windSpeed = (float) ($daily['wind_speed_10m_max'][0] ?? 0);
} else {
    $current = $data['current_weather'] ?? null;
    if (!is_array($current) || !isset($current['weathercode'], $current['temperature'])) {
        http_response_code(502);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu thời tiết trả về không hợp lệ.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $weatherCode = (int) $current['weathercode'];
    $temperature = (float) $current['temperature'];
    $windSpeed = (float) ($current['windspeed'] ?? 0);
}

$condition = match (true) {
    $weatherCode === 0 => 'Nắng đẹp',
    $weatherCode >= 1 && $weatherCode <= 3 => 'Có mây',
    $weatherCode >= 45 && $weatherCode <= 48 => 'Có sương mù',
    $weatherCode >= 51 && $weatherCode <= 67 => 'Có mưa',
    $weatherCode >= 71 && $weatherCode <= 77 => 'Có tuyết',
    $weatherCode >= 80 && $weatherCode <= 82 => 'Mưa rào',
    $weatherCode >= 95 && $weatherCode <= 99 => 'Dông',
    default => 'Thời tiết thay đổi'
};

$dailyForecast = [];
if ($forecastDate === '' && isset($data['daily']['time']) && is_array($data['daily']['time'])) {
    foreach (array_slice($data['daily']['time'], 0, 4) as $index => $day) {
        $dayCode = (int) ($data['daily']['weather_code'][$index] ?? 0);
        $dailyForecast[] = [
            'date' => date('d/m', strtotime((string) $day)),
            'day_label' => $index === 0 ? 'Hôm nay' : date('D', strtotime((string) $day)),
            'temp_max' => (int) round((float) ($data['daily']['temperature_2m_max'][$index] ?? $temperature)),
            'temp_min' => (int) round((float) ($data['daily']['temperature_2m_min'][$index] ?? $temperature)),
            'rain_prob' => (int) ($data['daily']['precipitation_probability_max'][$index] ?? 0),
            'condition' => match (true) {
                $dayCode === 0 => 'Nắng đẹp',
                $dayCode >= 1 && $dayCode <= 3 => 'Có mây',
                $dayCode >= 45 && $dayCode <= 48 => 'Có sương mù',
                $dayCode >= 51 && $dayCode <= 67 => 'Có mưa',
                $dayCode >= 80 && $dayCode <= 82 => 'Mưa rào',
                $dayCode >= 95 && $dayCode <= 99 => 'Dông',
                default => 'Thời tiết thay đổi',
            },
            'icon' => 'bi-cloud-sun',
        ];
    }
}

if (in_array($weatherCode, [65, 67, 82, 95, 96, 99], true)
    || ($forecastDate !== '' && $precipitation >= 50)) {
    $alerts[] = 'Mưa lớn hoặc dông; tránh trekking, khu vực sông suối và nơi có nguy cơ lũ quét.';
} elseif (in_array($weatherCode, [51, 53, 55, 61, 63, 66, 80, 81], true)
    || ($forecastDate !== '' && ($precipitationProbability >= 60 || $precipitation >= 20))) {
    $alerts[] = 'Có khả năng mưa; đường có thể trơn, kiểm tra tình trạng đường và cảnh báo lũ địa phương.';
}
if ($windSpeed >= 40) {
    $alerts[] = 'Gió mạnh; cẩn thận khi ở khu vực trống hoặc dựng lều.';
}

echo json_encode([
    'success' => true,
    'data' => [
        'temp' => round($temperature),
        'windspeed' => $windSpeed,
        'condition' => $condition,
        'alerts' => $alerts,
        'air_quality' => $airQuality,
        'date' => $forecastDate !== '' ? $forecastDate : null,
        'daily' => $dailyForecast,
    ]
], JSON_UNESCAPED_UNICODE);
exit;
