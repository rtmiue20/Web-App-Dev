<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/PostService.php';

/**
 * HomeController - Điều khiển Trang chủ diễn đàn.
 * Sử dụng PostService (Service Layer) -> PostRepository & UserRepository (DAO Layer).
 */
class HomeController extends Controller
{
    private PostService $postService;

    public function __construct(?PostService $postService = null)
    {
        $this->postService = $postService ?? new PostService();
    }

    public function index(): void
    {
        $overview = $this->postService->getHomeOverview($_GET['sort'] ?? 'hot', 4);

        $this->render('home/index', [
            'title'        => 'Trang chủ',
            'stats'        => $overview['stats'],
            'topics'       => $overview['topics'],
            'recentPosts'  => $overview['recentPosts'],
            'selectedSort' => $overview['selectedSort'],
            'active'       => 'home',
        ]);
    }
}
