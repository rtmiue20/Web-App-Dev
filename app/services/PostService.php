<?php
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../repositories/PostRepository.php';
require_once __DIR__ . '/../repositories/CategoryRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/StatsRepository.php';
require_once __DIR__ . '/../models/Post.php';

/**
 * PostService - Tầng nghiệp vụ Bài viết, Tìm kiếm, Phân trang & Kiểm duyệt (Service Layer).
 * Điều phối giữa PostRepository, CategoryRepository, UserRepository và StatsRepository.
 */
class PostService extends BaseService
{
    public const VALID_STATUSES = ['published', 'pending', 'rejected', 'hidden'];

    private PostRepository $postRepository;
    private CategoryRepository $categoryRepository;
    private UserRepository $userRepository;
    private StatsRepository $statsRepository;

    public function __construct(
        ?PostRepository $postRepository = null,
        ?CategoryRepository $categoryRepository = null,
        ?UserRepository $userRepository = null,
        ?StatsRepository $statsRepository = null
    ) {
        $this->postRepository = $postRepository ?? new PostRepository();
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();
        $this->userRepository = $this->userRepository ?? new UserRepository();
        $this->statsRepository = $statsRepository ?? new StatsRepository();
    }

    /**
     * Lấy dữ liệu tổng hợp cho Trang chủ (Thống kê qua sp_dashboard_stats + Bài viết nổi bật/mới/top từ View vw_post_details).
     */
    public function getHomeOverview(string $sort = 'hot', int $limit = 4): array
    {
        $normalizedSort = Post::normalizeSort($sort);
        $summary = $this->statsRepository->getDashboardStats();
        $postCount = (int)($summary['total_posts'] ?? 0) ?: $this->postRepository->count();
        $userCount = (int)($summary['total_users'] ?? 0) ?: $this->userRepository->count();

        $stats = [
            [
                'icon'  => 'bi-signpost-split',
                'label' => 'Bài chia sẻ',
                'value' => $postCount > 0 ? (string)$postCount : '128',
            ],
            [
                'icon'  => 'bi-people',
                'label' => 'Thành viên',
                'value' => $userCount > 0 ? (string)$userCount : '342',
            ],
        ];

        $topics = [
            ['label' => 'Chủ đề nổi bật', 'title' => 'Cắm trại mùa mưa', 'tag' => 'camp'],
            ['label' => 'Bài mới nhất',   'title' => 'Sau cơn mưa trên đỉnh Tà Xùa', 'tag' => 'trek'],
        ];

        if ($normalizedSort === 'hot') {
            $recentPosts = $this->statsRepository->getTopPosts(max(1, $limit));
            if (empty($recentPosts)) {
                $recentPosts = array_slice($this->postRepository->findAll(null, $normalizedSort), 0, max(1, $limit));
            }
        } else {
            $recentPosts = array_slice($this->postRepository->findAll(null, $normalizedSort), 0, max(1, $limit));
        }

        return [
            'stats'        => $stats,
            'topics'       => $topics,
            'recentPosts'  => $recentPosts,
            'selectedSort' => $normalizedSort,
        ];
    }

    /**
     * Lấy danh sách bài viết có lọc, tìm kiếm nâng cao và phân trang cho người dùng.
     */
    public function getPublicFeed(array $queryParams, int $limit = 6): array
    {
        $categoryId = isset($queryParams['category']) && $queryParams['category'] !== ''
            ? max(0, (int)$queryParams['category'])
            : null;
        if ($categoryId === 0) {
            $categoryId = null;
        }

        $page = isset($queryParams['page']) ? max(1, (int)$queryParams['page']) : 1;
        $selectedSort = Post::normalizeSort($queryParams['sort'] ?? 'hot');

        $keyword      = $this->cleanString($queryParams['q'] ?? $queryParams['keyword'] ?? '');
        $difficulty   = $this->cleanString($queryParams['difficulty'] ?? '');
        $region       = $this->cleanString($queryParams['region'] ?? '');
        $activityType = $this->cleanString($queryParams['activity_type'] ?? '');
        $tagName      = $this->cleanString($queryParams['tag'] ?? '');

        $filters = [
            'keyword'       => $keyword,
            'category_id'   => $categoryId,
            'difficulty'    => $difficulty,
            'region'        => $region,
            'activity_type' => $activityType,
            'tag_name'      => $tagName,
            'sort'          => $selectedSort,
        ];

        $hasAdvancedFilter = ($keyword !== '' || $difficulty !== '' || $region !== '' || $activityType !== '' || $tagName !== '');

        if ($hasAdvancedFilter) {
            $totalPosts = $this->postRepository->countAdvanced($filters);
            $totalPages = $totalPosts > 0 ? (int)ceil($totalPosts / $limit) : 1;
            $page = min($page, $totalPages);
            $postsList = $this->postRepository->searchAdvanced($filters, $page, $limit);
        } else {
            $totalPosts = $this->postRepository->countFiltered($categoryId, 'published');
            $totalPages = $totalPosts > 0 ? (int)ceil($totalPosts / $limit) : 1;
            $page = min($page, $totalPages);
            $postsList = $this->postRepository->findPaginated($categoryId, $page, $limit, 'published', $selectedSort);
        }

        return [
            'posts'              => $postsList,
            'categories'         => $this->categoryRepository->findAll(),
            'popularTags'        => $this->postRepository->findPopularTags(12),
            'selectedCategoryId' => $categoryId,
            'selectedSort'       => $selectedSort,
            'filters'            => $filters,
            'currentPage'        => $page,
            'totalPages'         => $totalPages,
            'totalPosts'         => $totalPosts,
            'limit'              => $limit,
        ];
    }

    /**
     * Lấy dữ liệu danh sách bài viết kèm bộ đếm trạng thái cho trang Quản trị (/admin/posts).
     */
    public function getAdminPostListing(array $queryParams, int $limit = 10): array
    {
        $status = null;
        if (isset($queryParams['status']) && $queryParams['status'] !== '' && $queryParams['status'] !== 'all') {
            $status = (string)$queryParams['status'];
        }

        $categoryId = isset($queryParams['category']) && $queryParams['category'] !== ''
            ? max(0, (int)$queryParams['category'])
            : null;
        if ($categoryId === 0) {
            $categoryId = null;
        }

        $page = isset($queryParams['page']) ? max(1, (int)$queryParams['page']) : 1;

        $totalPosts = $this->postRepository->countForAdmin($status, $categoryId);
        $totalPages = $totalPosts > 0 ? (int)ceil($totalPosts / $limit) : 1;
        $page = min($page, $totalPages);

        $counts = $this->getStatusCounts();

        return [
            'posts'              => $this->postRepository->findAllForAdmin($status, $categoryId, $page, $limit),
            'categories'         => $this->categoryRepository->findAll(),
            'selectedStatus'     => $status ?? 'all',
            'selectedCategoryId' => $categoryId,
            'currentPage'        => $page,
            'totalPages'         => $totalPages,
            'totalPosts'         => $totalPosts,
            'countAll'           => $counts['all'],
            'countPublished'     => $counts['published'],
            'countPending'       => $counts['pending'],
            'countRejected'      => $counts['rejected'],
            'countHidden'        => $counts['hidden'],
        ];
    }

    /**
     * Nghiệp vụ chuyển trạng thái kiểm duyệt bài viết (pending / published / rejected / hidden).
     */
    public function changeStatus(int $postId, string $newStatus): array
    {
        $post = $this->postRepository->findById($postId);
        if (!$post) {
            return $this->failure('Bài viết không tồn tại.', [], 404);
        }

        $newStatus = $this->cleanString($newStatus);
        if (!in_array($newStatus, self::VALID_STATUSES, true)) {
            return $this->failure('Trạng thái bài viết không hợp lệ.', [], 400);
        }

        $updated = $this->postRepository->updateStatus($postId, $newStatus);
        if (!$updated) {
            return $this->failure('Không thể cập nhật trạng thái bài viết. Vui lòng thử lại.', [], 500);
        }

        $statusLabels = [
            'published' => 'Đã duyệt (Approved)',
            'pending'   => 'Chờ duyệt (Pending)',
            'rejected'  => 'Từ chối (Reject)',
            'hidden'    => 'Đã ẩn (Hidden)',
        ];
        $label = $statusLabels[$newStatus] ?? $newStatus;
        $msg = 'Đã chuyển trạng thái bài viết "' . $post['title'] . '" sang "' . $label . '" thành công.';

        return $this->success([
            'post'       => $post,
            'post_id'    => $postId,
            'new_status' => $newStatus,
            'new_label'  => $label,
            'counts'     => $this->getStatusCounts(),
        ], $msg);
    }

    /**
     * Đếm số bài viết theo từng trạng thái kiểm duyệt (ưu tiên gọi Stored Procedure `sp_stats_posts_by_status`).
     */
    public function getStatusCounts(): array
    {
        $statusStats = $this->statsRepository->getPostsByStatus();
        if (($statusStats['all'] ?? 0) > 0) {
            return $statusStats;
        }

        return [
            'all'       => $this->postRepository->count(),
            'published' => $this->postRepository->countFiltered(null, 'published'),
            'pending'   => $this->postRepository->countFiltered(null, 'pending'),
            'rejected'  => $this->postRepository->countFiltered(null, 'rejected'),
            'hidden'    => $this->postRepository->countFiltered(null, 'hidden'),
        ];
    }
}

