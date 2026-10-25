<?php
header('Content-Type: application/json; charset=utf-8');

$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT) ?: 21.28;
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT) ?: 104.31;

$url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&current_weather=true";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $data = json_decode($response, true);
    if (!empty($data['current_weather'])) {
        $cw = $data['current_weather'];
        $weatherCode = $cw['weathercode'] ?? 0;
        
        $condition = match(true) {
            $weatherCode === 0 => 'Nắng đẹp',
            $weatherCode >= 1 && $weatherCode <= 3 => 'Có mây nhẹ',
            $weatherCode >= 45 && $weatherCode <= 48 => 'Có sương mù',
            $weatherCode >= 51 && $weatherCode <= 67 => 'Có mưa phùn',
            $weatherCode >= 80 && $weatherCode <= 82 => 'Mưa rào',
            default => 'Mát mẻ'
        };

        echo json_encode([
            'success' => true,
            'data' => [
                'temp' => round($cw['temperature'] ?? 20),
                'windspeed' => $cw['windspeed'] ?? 0,
                'condition' => $condition
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Fallback demo weather
echo json_encode([
    'success' => true,
    'data' => [
        'temp' => 22,
        'windspeed' => 5,
        'condition' => 'Trời trong xanh'
    ]
], JSON_UNESCAPED_UNICODE);
exit;
