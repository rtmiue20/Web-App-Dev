<?php

class DashboardController extends Controller
{
    private Category $categories;
    private Post $posts;
    private User $users;

    public function __construct()
    {
        $this->categories = new Category();
        $this->posts = new Post();
        $this->users = new User();
    }

    public function index(): void
    {
        $categoryCount = $this->categories->count();
        $postCount = $this->posts->count();
        $userCount = $this->users->count();
        $recentCategories = array_slice($this->categories->getAll(), 0, 5);

        $this->render('admin/dashboard', [
            'title' => 'Bảng điều khiển Quản trị',
            'user' => Session::get('user'),
            'categoryCount' => $categoryCount,
            'postCount' => $postCount,
            'userCount' => $userCount,
            'recentCategories' => $recentCategories,
        ], 'admin');
    }
}
