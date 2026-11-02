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
        $api = new ApiController();
        $dashboard = new DashboardController();
        $postAdmin = new PostAdminController();
        $userAdmin = new UserAdminController();
        $productAdmin = new ProductAdminController();

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
        $router->post('/profile', [$users, 'updateProfile'], [AuthMiddleware::class]);
        $router->post('/profile/password', [$users, 'changePassword'], [AuthMiddleware::class]);

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

        // 5b. Products / Tools (Public - FR09)
        $products = new ProductController();
        $router->get('/products', [$products, 'index']);
        $router->get('/products/{id}', function ($id) use ($products) {
            $products->show((int)$id);
        });

        // 5c. AJAX & Web Service Endpoints (FR06, FR07, FR08 - JSON API)
        $router->get('/api/weather', [$api, 'weather']);
        $router->get('/api/weather.php', [$api, 'weather']);
        $router->get('/api/provinces', [$api, 'provinces']);
        $router->get('/api/provinces.php', [$api, 'provinces']);
        $router->get('/api/comments', [$api, 'listComments']);
        $router->get('/api/comments.php', [$api, 'listComments']);
        $router->post('/api/comments', function () use ($api) {
            if (($_GET['action'] ?? '') === 'delete') {
                $api->deleteComment();
            } else {
                $api->createComment();
            }
        });
        $router->post('/api/comments.php', function () use ($api) {
            if (($_GET['action'] ?? '') === 'delete') {
                $api->deleteComment();
            } else {
                $api->createComment();
            }
        });
        $router->post('/api/comments/delete', [$api, 'deleteComment']);
        $router->get('/api/posts', [$api, 'posts']);
        $router->get('/api/posts.php', [$api, 'posts']);

        // 6. Admin & Moderator Area (RBAC: moderator & admin)
        $router->get('/admin', [$dashboard, 'index'], [ModeratorMiddleware::class]);
        
        // FR11: Admin Category Management (Chỉ Admin)
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

        // FR12: Admin Product / Gear Management (Chỉ Admin)
        $router->get('/admin/products', [$productAdmin, 'index'], [AdminMiddleware::class]);
        $router->get('/admin/products/create', [$productAdmin, 'create'], [AdminMiddleware::class]);
        $router->post('/admin/products/store', [$productAdmin, 'store'], [AdminMiddleware::class]);
        $router->get('/admin/products/edit/{id}', function ($id) use ($productAdmin) {
            $productAdmin->edit((int)$id);
        }, [AdminMiddleware::class]);
        $router->post('/admin/products/update/{id}', function ($id) use ($productAdmin) {
            $productAdmin->update((int)$id);
        }, [AdminMiddleware::class]);
        $router->post('/admin/products/delete/{id}', function ($id) use ($productAdmin) {
            $productAdmin->delete((int)$id);
        }, [AdminMiddleware::class]);

        // FR13: Post Moderation (Cho phép cả Moderator & Admin kiểm duyệt bài viết)
        $router->get('/admin/posts', [$postAdmin, 'index'], [ModeratorMiddleware::class]);
        $router->post('/admin/posts/{id}/status', [$postAdmin, 'changeStatus'], [ModeratorMiddleware::class]);
        $router->post('/admin/posts/{id}/approve', [$postAdmin, 'approve'], [ModeratorMiddleware::class]);
        $router->post('/admin/posts/{id}/hide', [$postAdmin, 'hide'], [ModeratorMiddleware::class]);
        $router->post('/admin/posts/{id}/delete', [$postAdmin, 'delete'], [ModeratorMiddleware::class]);

        // FR14: Admin User Management, Password Reset & RBAC Role Assignment (Chỉ Admin)
        $router->get('/admin/users', [$userAdmin, 'index'], [AdminMiddleware::class]);
        $router->get('/admin/users/{id}', function ($id) use ($userAdmin) {
            $userAdmin->show((int)$id);
        }, [AdminMiddleware::class]);
        $router->post('/admin/users/{id}/reset-password', function ($id) use ($userAdmin) {
            $userAdmin->resetPassword((int)$id);
        }, [AdminMiddleware::class]);
        $router->post('/admin/users/{id}/role', function ($id) use ($userAdmin) {
            $userAdmin->changeRole((int)$id);
        }, [AdminMiddleware::class]);

        // URL parsing
        $url = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!isset($_GET['url']) && BASE_URL !== '' && str_starts_with((string) $url, BASE_URL)) {
            $url = substr((string) $url, strlen(BASE_URL));
        }

        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', (string) $url);
    }
}
