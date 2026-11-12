<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../repositories/CategoryRepository.php';
require_once __DIR__ . '/../../repositories/PostRepository.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';
require_once __DIR__ . '/../../repositories/StatsRepository.php';

/**
 * DashboardController - Bảng điều khiển Quản trị viên (/admin).
 * Sử dụng StatsRepository (Stored Procedures thống kê: sp_dashboard_stats, sp_stats_posts_by_category,
 * sp_stats_top_posts, sp_stats_top_users) và các SQL Views (vw_post_details, vw_category_summary, vw_user_summary).
 */
class DashboardController extends Controller
{
    private CategoryRepository $categoryRepository;
    private PostRepository $postRepository;
    private UserRepository $userRepository;
    private StatsRepository $statsRepository;

    public function __construct(
        ?CategoryRepository $categoryRepository = null,
        ?PostRepository $postRepository = null,
        ?UserRepository $userRepository = null,
        ?StatsRepository $statsRepository = null
    ) {
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();
        $this->postRepository = $postRepository ?? new PostRepository();
        $this->userRepository = $userRepository ?? new UserRepository();
        $this->statsRepository = $statsRepository ?? new StatsRepository();
    }

    public function index(): void
    {
        $summary = $this->statsRepository->getDashboardStats();

        $categoryCount = (int)($summary['total_categories'] ?? 0) ?: $this->categoryRepository->count();
        $postCount     = (int)($summary['total_posts'] ?? 0) ?: $this->postRepository->count();
        $userCount     = (int)($summary['total_users'] ?? 0) ?: $this->userRepository->count();
        $commentCount  = (int)($summary['total_comments'] ?? 0);
        $publishedPosts = (int)($summary['published_posts'] ?? 0);
        $pendingPosts   = (int)($summary['pending_posts'] ?? 0);

        $categoryStats = $this->statsRepository->getPostsByCategory();
        if (empty($categoryStats)) {
            $categoryStats = $this->categoryRepository->findAll();
        }

        $topPosts = $this->statsRepository->getTopPosts(5);
        $topUsers = $this->statsRepository->getTopUsers(5);

        // Dữ liệu ban đầu cho biểu đồ Chart.js (7 ngày gần nhất) qua sp_stats_daily_activity
        $dailyActivity = $this->statsRepository->getDailyActivity(7);

        $this->render('admin/dashboard', [
            'title'            => 'Bảng điều khiển Quản trị',
            'user'             => Session::get('user'),
            'categoryCount'    => $categoryCount,
            'postCount'        => $postCount,
            'publishedPosts'   => $publishedPosts,
            'pendingPosts'     => $pendingPosts,
            'userCount'        => $userCount,
            'commentCount'     => $commentCount,
            'recentCategories' => $categoryStats,
            'categoryStats'    => $categoryStats,
            'topPosts'         => $topPosts,
            'topUsers'         => $topUsers,
            'dailyActivity'    => $dailyActivity,
        ], 'admin');
    }
}
