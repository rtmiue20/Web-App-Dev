<?php
require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../ApiController.php';

/**
 * SearchApi - RESTful API Controller xử lý Tìm kiếm gợi ý (Autocomplete FR04) & Danh sách bài viết (FR03).
 *
 * Endpoints:
 *  - GET /api/search/suggest?q=... : Gợi ý nhanh tiêu đề & địa điểm bài viết khi gõ
 *  - GET /api/posts                : Danh sách bài viết có lọc, tìm kiếm, phân trang JSON
 *  - GET /api/posts/{id}           : Chi tiết 1 bài viết dạng JSON
 */
class SearchApi extends BaseApiController
{
    private ApiController $api;

    public function __construct(?ApiController $api = null)
    {
        $this->api = $api ?? new ApiController();
    }

    /**
     * GET /api/search/suggest?q=...
     */
    public function suggest(): void
    {
        $_GET['suggest'] = '1';
        $this->api->posts();
    }

    /**
     * GET /api/posts
     */
    public function posts(): void
    {
        $this->api->posts();
    }

    /**
     * GET /api/posts/{id}
     */
    public function show(int $id): void
    {
        $this->api->showPost($id);
    }
}

