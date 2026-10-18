<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Category.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $postModel = new Post();
        $userModel = new User();
        $categoryModel = new Category();

        $postCount = $postModel->count();
        $userCount = $userModel->count();

        $stats = [
            [
                'icon' => 'bi-signpost-split', 
                'label' => 'Bài chia sẻ',  
                'value' => $postCount > 0 ? (string)$postCount : '128'
            ],
            [
                'icon' => 'bi-people',         
                'label' => 'Thành viên',   
                'value' => $userCount > 0 ? (string)$userCount : '342'
            ],
        ];

        $topics = [
            ['label' => 'Chủ đề nổi bật', 'title' => 'Cắm trại mùa mưa', 'tag' => 'camp'],
            ['label' => 'Bài mới nhất',   'title' => 'Sau cơn mưa trên đỉnh Tà Xùa', 'tag' => 'trek'],
        ];

        $recentPosts = $postModel->getAll();

        $this->render('home/index', [
            'title'       => 'Trang chủ',
            'stats'       => $stats,
            'topics'      => $topics,
            'recentPosts' => array_slice($recentPosts, 0, 4),
            'active'      => 'home',
        ]);
    }
}
