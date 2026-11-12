<?php
require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../ApiController.php';

/**
 * WeatherApi - RESTful API Controller tích hợp Web Service Thời tiết Open-Meteo & Địa giới hành chính (FR06).
 *
 * Endpoints:
 *  - GET /api/weather?lat=...&lng=...
 *  - GET /api/provinces
 */
class WeatherApi extends BaseApiController
{
    private ApiController $api;

    public function __construct(?ApiController $api = null)
    {
        $this->api = $api ?? new ApiController();
    }

    /**
     * GET /api/weather?lat=...&lng=...
     */
    public function forecast(): void
    {
        $this->api->weather();
    }

    /**
     * GET /api/provinces
     */
    public function provinces(): void
    {
        $this->api->provinces();
    }
}

