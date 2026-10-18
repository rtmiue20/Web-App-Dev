<?php

class App
{
    public function run(): void
    {
        Session::init();

        $router = new Router();
        $home = new HomeController();
        $auth = new AuthController();
        $posts = new PostController();
        $categories = new CategoryController();
        $users = new UserController();
        $dashboard = new DashboardController();

        // 1. Home
        $router->get('/', [$home, 'index']);

        // 2. Auth
        $router->get('/login', [$auth, 'showLogin']);
        $router->post('/login', [$auth, 'login']);
        $router->get('/register', [$auth, 'showRegister']);
        $router->post('/register', [$auth, 'register']);
        $router->post('/logout', [$auth, 'logout'], [AuthMiddleware::class]);

        // 3. Profile
        $router->get('/profile', [$users, 'profile'], [AuthMiddleware::class]);

        // 4. Posts
        $router->get('/posts', [$posts, 'index']);
        $router->get('/posts/create', [$posts, 'create'], [AuthMiddleware::class]);
        $router->post('/posts', [$posts, 'store'], [AuthMiddleware::class]);
        $router->get('/posts/{id}', function ($id) use ($posts) {
            $posts->show((int)$id);
        });
        $router->post('/posts/{id}/like', function ($id) use ($posts) {
            $posts->like((int)$id);
        });

        // 5. Categories
        $router->get('/categories', [$categories, 'index']);
        $router->get('/categories/create', [$categories, 'create'], [AuthMiddleware::class]);
        $router->post('/categories/store', [$categories, 'store'], [AuthMiddleware::class]);

        // 6. Admin
        $router->get('/admin', [$dashboard, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);

        // URL parsing
        $url = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!isset($_GET['url']) && BASE_URL !== '' && str_starts_with((string) $url, BASE_URL)) {
            $url = substr((string) $url, strlen(BASE_URL));
        }

        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', (string) $url);
    }
}