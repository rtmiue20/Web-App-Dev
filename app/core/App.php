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
        $router->get('/posts/{id}/edit', function ($id) use ($posts) {
            $posts->edit((int)$id);
        }, [AuthMiddleware::class]);
        $router->post('/posts/{id}/update', function ($id) use ($posts) {
            $posts->update((int)$id);
        }, [AuthMiddleware::class]);
        $router->post('/posts/{id}/delete', function ($id) use ($posts) {
            $posts->delete((int)$id);
        }, [AuthMiddleware::class]);
        $router->post('/posts/{id}/like', function ($id) use ($posts) {
            $posts->like((int)$id);
        });

        // 5. Categories (Public)
        $router->get('/categories', [$categories, 'index']);

        // Legacy category creation compatibility
        $router->get('/categories/create', function () use ($categories) {
            $categories->create();
        }, [AdminMiddleware::class]);
        $router->post('/categories/store', [$categories, 'store'], [AdminMiddleware::class]);

        // 6. Admin Area (All protected by AdminMiddleware)
        $router->get('/admin', [$dashboard, 'index'], [AdminMiddleware::class]);
        
        // FR11: Admin Category Management
        $router->get('/admin/categories', [$categories, 'adminIndex'], [AdminMiddleware::class]);
        $router->get('/admin/categories/create', [$categories, 'create'], [AdminMiddleware::class]);
        $router->post('/admin/categories/store', [$categories, 'store'], [AdminMiddleware::class]);
        $router->get('/admin/categories/edit/{id}', function ($id) use ($categories) {
            $categories->edit((int)$id);
        }, [AdminMiddleware::class]);
        $router->post('/admin/categories/update/{id}', function ($id) use ($categories) {
            $categories->update((int)$id);
        }, [AdminMiddleware::class]);
        $router->post('/admin/categories/delete/{id}', function ($id) use ($categories) {
            $categories->delete((int)$id);
        }, [AdminMiddleware::class]);

        // URL parsing
        $url = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!isset($_GET['url']) && BASE_URL !== '' && str_starts_with((string) $url, BASE_URL)) {
            $url = substr((string) $url, strlen(BASE_URL));
        }

        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', (string) $url);
    }
}
