<?php
require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../../services/RssService.php';

/**
 * RssApi - RESTful API Controller đọc tin tức Du lịch / Dã ngoại từ RSS (XML) chuyển sang JSON (FR10).
 *
 * Endpoint:
 *  - GET /api/news?limit=6
 */
class RssApi extends BaseApiController
{
    private RssService $rssService;

    public function __construct(?RssService $rssService = null)
    {
        $this->rssService = $rssService ?? new RssService();
    }

    /**
     * GET /api/news
     */
    public function index(): void
    {
        $limit = isset($_GET['limit']) ? max(1, min(20, (int)$_GET['limit'])) : 6;
        $newsData = $this->rssService->getOutdoorNews($limit);

        Response::ok($newsData, 'Tải tin tức dã ngoại từ RSS (XML) thành công.');
    }
}

