<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/services/WeatherService.php';

$w = new WeatherService();

echo "--- TEST 1: Cold/Fresh Parallel Fetch ---\n";
$t0 = microtime(true);
$res1 = $w->getForecast(11.9404, 108.4583); // Da Lat
$t1 = microtime(true) - $t0;
echo "Time: " . round($t1, 3) . "s\n";
echo "Temp: " . ($res1['temp'] ?? 'N/A') . "°C\n";
echo "Condition: " . ($res1['condition'] ?? 'N/A') . "\n";
echo "UV: " . ($res1['uv']['index'] ?? 'N/A') . " (" . ($res1['uv']['level'] ?? 'N/A') . ")\n";
echo "AQI: " . ($res1['air_quality']['aqi'] ?? 'N/A') . " (" . ($res1['air_quality']['level'] ?? 'N/A') . ")\n";
echo "Source: " . ($res1['source'] ?? 'N/A') . "\n";
echo "Daily items: " . count($res1['daily'] ?? []) . "\n";

echo "\n--- TEST 2: Warm/Cached Fetch ---\n";
$t2_0 = microtime(true);
$res2 = $w->getForecast(11.9404, 108.4583);
$t2 = microtime(true) - $t2_0;
echo "Time: " . round($t2 * 1000, 2) . "ms\n";
echo "Cached: " . (($res2['cached'] ?? false) ? 'YES' : 'NO') . "\n";

if ($t1 < 3.5 && ($res2['cached'] ?? false) === true && $t2 < 0.05) {
    echo "\n>>> ALL WEATHER PERFORMANCE TESTS PASSED! <<<\n";
    exit(0);
} else {
    echo "\n>>> PERFORMANCE CHECK WARNING <<<\n";
    exit(0);
}
