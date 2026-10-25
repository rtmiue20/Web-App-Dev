<?php

class App
{
    public function run(): void
    {
        Session::init();

        $router = new Router();

        // Khởi tạo tất cả các Controller
        $home = new HomeController();
        $auth = new AuthController();
        $posts = new PostController();
        $categories = new CategoryController();
        $users = new UserController();
        $dashboard = new DashboardController();
        $postAdmin = new PostAdminController();
        $userAdmin = new UserAdminController();

        // Controller Sản phẩm (FR09 & FR12 - Vương)
        $products = new ProductController();
        $productAdmin = new ProductAdminController();

        // Controller API tương tác (FR07 & FR08 - Vương)
        $likeApi = new LikeApi();
        $commentApi = new CommentApi();

        // 1. Home
        $router->get('/', [$home, 'index']);

        // 2. Auth (FR01)
        $router->get('/login', [$auth, 'showLogin']);
        $router->post('/login', [$auth, 'login']);
        $router->get('/register', [$auth, 'showRegister']);
        $router->post('/register', [$auth, 'register']);
        $router->post('/logout', [$auth, 'logout'], [AuthMiddleware::class]);

        // 3. Profile (FR02)
        $router->get('/profile', [$users, 'profile'], [AuthMiddleware::class]);
        $router->post('/profile', [$users, 'updateProfile'], [AuthMiddleware::class]);
        $router->post('/profile/password', [$users, 'changePassword'], [AuthMiddleware::class]);

        // 4. Posts (FR03, FR05)
        $router->get('/posts', [$posts, 'index']);
        $router->get('/posts/create', [$posts, 'create'], [AuthMiddleware::class]);
        $router->post('/posts', [$posts, 'store'], [AuthMiddleware::class]);
        $router->get('/posts/{id}', [$posts, 'show']);
        $router->get('/posts/{id}/edit', [$posts, 'edit'], [AuthMiddleware::class]);
        $router->post('/posts/{id}/update', [$posts, 'update'], [AuthMiddleware::class]);
        $router->post('/posts/{id}/delete', [$posts, 'delete'], [AuthMiddleware::class]);
        $router->post('/posts/{id}/like', [$posts, 'like']);

        // 5. Categories (Public)
        $router->get('/categories', [$categories, 'index']);
        $router->get('/categories/create', [$categories, 'create'], [AdminMiddleware::class]);
        $router->post('/categories/store', [$categories, 'store'], [AdminMiddleware::class]);

        // 6. Products / Thiết bị dã ngoại (FR09 - Public)
        $router->get('/products', [$products, 'index']);

        // 7. Admin Area (Được bảo vệ bởi AdminMiddleware)
        $router->get('/admin', [$dashboard, 'index'], [AdminMiddleware::class]);

        // FR11: Admin Category Management
        $router->get('/admin/categories', [$categories, 'adminIndex'], [AdminMiddleware::class]);
        $router->get('/admin/categories/create', [$categories, 'create'], [AdminMiddleware::class]);
        $router->post('/admin/categories/store', [$categories, 'store'], [AdminMiddleware::class]);
        $router->get('/admin/categories/edit/{id}', [$categories, 'edit'], [AdminMiddleware::class]);
        $router->post('/admin/categories/update/{id}', [$categories, 'update'], [AdminMiddleware::class]);
        $router->post('/admin/categories/delete/{id}', [$categories, 'delete'], [AdminMiddleware::class]);

        // FR13: Admin Post Management (Duyệt, Ẩn, Xóa bài)
        $router->get('/admin/posts', [$postAdmin, 'index'], [AdminMiddleware::class]);
        $router->post('/admin/posts/{id}/approve', [$postAdmin, 'approve'], [AdminMiddleware::class]);
        $router->post('/admin/posts/{id}/hide', [$postAdmin, 'hide'], [AdminMiddleware::class]);
        $router->post('/admin/posts/{id}/delete', [$postAdmin, 'delete'], [AdminMiddleware::class]);

        // FR12: Admin Product Management (Thêm, Sửa, Xóa sản phẩm)
        $router->get('/admin/products', [$productAdmin, 'index'], [AdminMiddleware::class]);
        $router->get('/admin/products/create', [$productAdmin, 'create'], [AdminMiddleware::class]);
        $router->post('/admin/products/store', [$productAdmin, 'store'], [AdminMiddleware::class]);
        $router->get('/admin/products/{id}/edit', [$productAdmin, 'edit'], [AdminMiddleware::class]);
        $router->post('/admin/products/{id}/update', [$productAdmin, 'update'], [AdminMiddleware::class]);
        $router->post('/admin/products/{id}/delete', [$productAdmin, 'delete'], [AdminMiddleware::class]);

        // 8. API (AJAX / JSON - FR07 & FR08)
        // FR08: Thích / Bỏ thích bài viết
        $router->post('/api/posts/{id}/like', [$likeApi, 'toggle']);

        // FR07: Bình luận bằng AJAX (Danh sách Load More, Thêm bình luận mới, Xóa bình luận của mình)
        $router->get('/api/posts/{id}/comments', [$commentApi, 'list']);
        $router->post('/api/posts/{id}/comments', [$commentApi, 'store']);
        $router->post('/api/comments/{id}/delete', [$commentApi, 'delete']);

        // FR14: Admin User Management & Password Reset (Xem thông tin & Cấp lại mật khẩu)
        $router->get('/admin/users', [$userAdmin, 'index'], [AdminMiddleware::class]);
        $router->get('/admin/users/{id}', function ($id) use ($userAdmin) {
            $userAdmin->show((int)$id);
        }, [AdminMiddleware::class]);
        $router->post('/admin/users/{id}/reset-password', function ($id) use ($userAdmin) {
            $userAdmin->resetPassword((int)$id);
        }, [AdminMiddleware::class]);

        // URL parsing
        $url = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!isset($_GET['url']) && BASE_URL !== '' && str_starts_with((string) $url, BASE_URL)) {
            $url = substr((string) $url, strlen(BASE_URL));
        }

        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', (string) $url);
    }
}
