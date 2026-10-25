<?php
header('Content-Type: application/json; charset=utf-8');

$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
if ($lat === false || $lat === null) {
    $lat = 21.28;
}

$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
if ($lng === false || $lng === null) {
    $lng = 104.31;
}

$url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&current_weather=true";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $data = json_decode((string)$response, true);
    if (!empty($data['current_weather'])) {
        $cw = $data['current_weather'];
        $weatherCode = (int)($cw['weathercode'] ?? 0);

        if ($weatherCode === 0) {
            $condition = 'Nắng đẹp';
        } elseif ($weatherCode >= 1 && $weatherCode <= 3) {
            $condition = 'Có mây nhẹ';
        } elseif ($weatherCode >= 45 && $weatherCode <= 48) {
            $condition = 'Có sương mù';
        } elseif ($weatherCode >= 51 && $weatherCode <= 67) {
            $condition = 'Có mưa phùn';
        } elseif ($weatherCode >= 80 && $weatherCode <= 82) {
            $condition = 'Mưa rào';
        } else {
            $condition = 'Mát mẻ';
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'temp' => round((float)($cw['temperature'] ?? 20)),
                'windspeed' => $cw['windspeed'] ?? 0,
                'condition' => $condition
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode([
    'success' => true,
    'data' => [
        'temp' => 22,
        'windspeed' => 5,
        'condition' => 'Trời trong xanh'
    ]
], JSON_UNESCAPED_UNICODE);
exit;
