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
        'date' => $forecastDate !== '' ? $forecastDate : null,
        'daily' => $dailyForecast,
    ]
], JSON_UNESCAPED_UNICODE);
exit;
