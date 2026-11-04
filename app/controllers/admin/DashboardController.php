<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../repositories/CategoryRepository.php';
require_once __DIR__ . '/../../repositories/PostRepository.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';

/**
 * DashboardController - Bảng điều khiển Quản trị viên (/admin).
 * Sử dụng tầng Repository / DAO (CategoryRepository, PostRepository, UserRepository).
 */
class DashboardController extends Controller
{
    private CategoryRepository $categoryRepository;
    private PostRepository $postRepository;
    private UserRepository $userRepository;

    public function __construct(
        ?CategoryRepository $categoryRepository = null,
        ?PostRepository $postRepository = null,
        ?UserRepository $userRepository = null
    ) {
        $this->categoryRepository = $categoryRepository ?? new CategoryRepository();
        $this->postRepository = $postRepository ?? new PostRepository();
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function index(): void
    {
        $categoryCount = $this->categoryRepository->count();
        $postCount = $this->postRepository->count();
        $userCount = $this->userRepository->count();
        $recentCategories = array_slice($this->categoryRepository->findAll(), 0, 5);

        $this->render('admin/dashboard', [
            'title'            => 'Bảng điều khiển Quản trị',
            'user'             => Session::get('user'),
            'categoryCount'    => $categoryCount,
            'postCount'        => $postCount,
            'userCount'        => $userCount,
            'recentCategories' => $recentCategories,
        ], 'admin');
    }
}
