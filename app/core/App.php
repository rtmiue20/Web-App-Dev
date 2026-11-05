<?php



/**

 * App - Lớp khởi chạy ứng dụng từ Front Controller (public/index.php).

 * Áp dụng HTTP Security Headers, khởi tạo Session an toàn và đăng ký bảng định tuyến (Router).

 */

class App

{

    public function run(): void

    {

        Session::init();

        $this->sendSecurityHeaders();



        $router = new Router();



        // 1. Trang chủ

        $router->get('/', [HomeController::class, 'index']);



        // 2. Xác thực & Bảo mật tài khoản (FR01)

        $router->get('/login', [AuthController::class, 'showLogin']);

        $router->post('/login', [AuthController::class, 'login']);

        $router->get('/register', [AuthController::class, 'showRegister']);

        $router->post('/register', [AuthController::class, 'register']);

        $router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);



        // 3. Hồ sơ người dùng (FR02)

        $router->get('/profile', [UserController::class, 'profile'], [AuthMiddleware::class]);

        $router->post('/profile', [UserController::class, 'updateProfile'], [AuthMiddleware::class]);

        $router->post('/profile/password', [UserController::class, 'changePassword'], [AuthMiddleware::class]);

        $router->get('/users/{id}', [UserController::class, 'show']);



        // 4. Bài viết diễn đàn (FR03, FR04, FR05, FR08)

        $router->get('/posts', [PostController::class, 'index']);

        $router->get('/posts/create', [PostController::class, 'create'], [AuthMiddleware::class]);

        $router->post('/posts', [PostController::class, 'store'], [AuthMiddleware::class]);

        $router->get('/posts/{id}', [PostController::class, 'show']);

        $router->get('/posts/{id}/edit', [PostController::class, 'edit'], [AuthMiddleware::class]);

        $router->post('/posts/{id}/update', [PostController::class, 'update'], [AuthMiddleware::class]);

        $router->post('/posts/{id}/delete', [PostController::class, 'delete'], [AuthMiddleware::class]);

        $router->post('/posts/{id}/like', [PostController::class, 'like']);

        $router->post('/posts/{id}/vote', [PostController::class, 'like']);

        $router->post('/posts/{id}/reviews', [OutdoorController::class, 'storeReview'], [AuthMiddleware::class]);



        // 4b. Tính năng dã ngoại (Checklist, Chuyến đi nhóm, Bản đồ địa điểm)

        $router->get('/checklists', [OutdoorController::class, 'checklists']);

        $router->post('/checklists', [OutdoorController::class, 'storeChecklist'], [AuthMiddleware::class]);

        $router->post('/checklists/{id}/delete', [OutdoorController::class, 'deleteChecklist'], [AuthMiddleware::class]);

        $router->get('/trips', [OutdoorController::class, 'trips']);

        $router->post('/trips', [OutdoorController::class, 'storeTrip'], [AuthMiddleware::class]);

        $router->get('/trips/{id}', [OutdoorController::class, 'showTrip']);

        $router->post('/trips/{id}/join', [OutdoorController::class, 'joinTrip'], [AuthMiddleware::class]);

        $router->post('/trips/{id}/leave', [OutdoorController::class, 'leaveTrip'], [AuthMiddleware::class]);

        $router->post('/trips/{id}/delete', [OutdoorController::class, 'deleteTrip'], [AuthMiddleware::class]);

        $router->get('/places', [OutdoorController::class, 'map']);

        $router->get('/map', [OutdoorController::class, 'map']);



        // 5. Chuyên mục & Thiết bị dã ngoại công khai (FR09)

        $router->get('/categories', [CategoryController::class, 'index']);

        $router->get('/categories/create', [CategoryController::class, 'create'], [AdminMiddleware::class]);

        $router->post('/categories/store', [CategoryController::class, 'store'], [AdminMiddleware::class]);



        $router->get('/products', [ProductController::class, 'index']);

        $router->post('/products/store', [ProductController::class, 'store'], [AuthMiddleware::class]);

        $router->post('/products/update', [ProductController::class, 'update'], [AuthMiddleware::class]);

        $router->post('/products/delete', [ProductController::class, 'destroy'], [AuthMiddleware::class]);

        $router->get('/products/{id}', [ProductController::class, 'show']);



        // 6. Nhóm Route AJAX & Web Service Endpoints (/api/*)

        $router->group('/api', [], function (Router $apiRouter) {

            $api = new ApiController();



            $apiRouter->post('/posts/{id}/bookmark', function ($id) use ($api) {

                $_POST['post_id'] = $id;

                $api->toggleBookmark();

            });

            $apiRouter->post('/bookmarks', [$api, 'toggleBookmark']);

            $apiRouter->post('/users/{id}/follow', function ($id) use ($api) {

                $_POST['user_id'] = $id;

                $api->toggleUserFollow();

            });

            $apiRouter->post('/follows/user', [$api, 'toggleUserFollow']);

            $apiRouter->post('/categories/{id}/follow', function ($id) use ($api) {

                $_POST['category_id'] = $id;

                $api->toggleCategoryFollow();

            });

            $apiRouter->post('/follows/category', [$api, 'toggleCategoryFollow']);

            $apiRouter->get('/notifications', [$api, 'notifications']);

            $apiRouter->post('/notifications/read', [$api, 'readNotification']);

            $apiRouter->post('/reports', [$api, 'report']);



            $apiRouter->get('/weather', [$api, 'weather']);

            $apiRouter->get('/weather.php', [$api, 'weather']);

            $apiRouter->get('/provinces', [$api, 'provinces']);

            $apiRouter->get('/provinces.php', [$api, 'provinces']);



            $apiRouter->get('/comments', [$api, 'listComments']);

            $apiRouter->get('/comments.php', [$api, 'listComments']);



            $commentActionDispatch = function () use ($api) {

                $act = $_GET['action'] ?? '';

                if ($act === 'delete') {

                    $api->deleteComment();

                } elseif ($act === 'like') {

                    $api->likeComment();

                } else {

                    $api->createComment();

                }

            };

            $apiRouter->post('/comments', $commentActionDispatch);

            $apiRouter->post('/comments.php', $commentActionDispatch);

            $apiRouter->post('/comments/delete', [$api, 'deleteComment']);

            $apiRouter->post('/comments/like', [$api, 'likeComment']);



            $apiRouter->get('/check-user', [$api, 'checkUserAvailability']);

            $apiRouter->get('/check-user.php', [$api, 'checkUserAvailability']);

            $apiRouter->get('/check-email', [$api, 'checkUserAvailability']);



            $apiRouter->post('/likes', [$api, 'votePost']);

            $apiRouter->post('/likes.php', [$api, 'votePost']);

            $apiRouter->post('/votes', [$api, 'votePost']);



            $apiRouter->get('/posts', [$api, 'posts']);

            $apiRouter->get('/posts.php', [$api, 'posts']);

            $apiRouter->get('/search/suggest', [$api, 'posts']);



            // --- RESTful API nội bộ trả JSON cho AJAX (resource-oriented) ---

            // Bài viết

            $apiRouter->get('/posts/{id}', [SearchApi::class, 'show']);

            $apiRouter->post('/posts/{id}/like', [LikeApi::class, 'togglePostLike']);

            $apiRouter->post('/posts/{id}/vote', [LikeApi::class, 'togglePostLike']);

            // Bình luận

            $apiRouter->get('/posts/{id}/comments', [CommentApi::class, 'index']);

            $apiRouter->post('/posts/{id}/comments', [CommentApi::class, 'store']);

            $apiRouter->delete('/comments/{id}', [CommentApi::class, 'destroy']);

            $apiRouter->post('/comments/{id}/like', [LikeApi::class, 'toggleCommentLike']);

            // Tin tức RSS (XML -> JSON)

            $apiRouter->get('/news', [RssApi::class, 'index']);

            // Thống kê quản trị cho Dashboard (chỉ Moderator/Admin, kiểm tra quyền trong controller trả JSON 401/403)

            $apiRouter->get('/stats', [$api, 'stats']);

            $apiRouter->get('/stats/daily', [$api, 'dailyStats']);

        });



        // 7. Nhóm Route Quản trị & Kiểm duyệt (/admin/* - Bảo vệ theo RBAC Middleware)

        $router->group('/admin', [ModeratorMiddleware::class], function (Router $adminRouter) {

            $adminRouter->get('/', [DashboardController::class, 'index']);



            // FR13: Kiểm duyệt bài viết & Báo cáo vi phạm (Moderator & Admin)

            $adminRouter->get('/posts', [PostAdminController::class, 'index']);

            $adminRouter->post('/posts/{id}/status', [PostAdminController::class, 'changeStatus']);

            $adminRouter->post('/posts/{id}/approve', [PostAdminController::class, 'approve']);

            $adminRouter->post('/posts/{id}/hide', [PostAdminController::class, 'hide']);

            $adminRouter->post('/posts/{id}/delete', [PostAdminController::class, 'delete']);



            $adminRouter->get('/moderation', [ModerationController::class, 'index']);

            $adminRouter->post('/moderation/{id}/resolve', [ModerationController::class, 'resolve']);



            // FR11: Quản lý Chuyên mục (Chỉ Admin)

            $adminRouter->get('/categories', [CategoryController::class, 'adminIndex'], [AdminMiddleware::class]);

            $adminRouter->get('/categories/create', [CategoryController::class, 'create'], [AdminMiddleware::class]);

            $adminRouter->post('/categories/store', [CategoryController::class, 'store'], [AdminMiddleware::class]);

            $adminRouter->get('/categories/edit/{id}', [CategoryController::class, 'edit'], [AdminMiddleware::class]);

            $adminRouter->post('/categories/update/{id}', [CategoryController::class, 'update'], [AdminMiddleware::class]);

            $adminRouter->post('/categories/delete/{id}', [CategoryController::class, 'delete'], [AdminMiddleware::class]);



            // FR12: Quản lý Dụng cụ & Sản phẩm (Chỉ Admin)

            $adminRouter->get('/products', [ProductAdminController::class, 'index'], [AdminMiddleware::class]);

            $adminRouter->get('/products/create', [ProductAdminController::class, 'create'], [AdminMiddleware::class]);

            $adminRouter->post('/products/store', [ProductAdminController::class, 'store'], [AdminMiddleware::class]);

            $adminRouter->get('/products/edit/{id}', [ProductAdminController::class, 'edit'], [AdminMiddleware::class]);

            $adminRouter->post('/products/update/{id}', [ProductAdminController::class, 'update'], [AdminMiddleware::class]);

            $adminRouter->post('/products/delete/{id}', [ProductAdminController::class, 'delete'], [AdminMiddleware::class]);



            // FR14: Quản lý Thành viên & Phân quyền RBAC (Chỉ Admin)

            $adminRouter->get('/users', [UserAdminController::class, 'index'], [AdminMiddleware::class]);

            $adminRouter->get('/users/{id}', [UserAdminController::class, 'show'], [AdminMiddleware::class]);

            $adminRouter->post('/users/{id}/reset-password', [UserAdminController::class, 'resetPassword'], [AdminMiddleware::class]);

            $adminRouter->post('/users/{id}/role', [UserAdminController::class, 'changeRole'], [AdminMiddleware::class]);

        });



        // Phân tích URL và điều phối qua Router

        $url = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        if (!isset($_GET['url']) && BASE_URL !== '' && str_starts_with((string)$url, BASE_URL)) {

            $url = substr((string)$url, strlen(BASE_URL));

        }



        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', (string)$url);

    }



    /**

     * Thiết lập các HTTP Security Headers chống Clickjacking, MIME-Sniffing và XSS.

     */

    private function sendSecurityHeaders(): void

    {

        if (!headers_sent()) {

            header('X-Content-Type-Options: nosniff');

            header('X-Frame-Options: SAMEORIGIN');

            header('Referrer-Policy: strict-origin-when-cross-origin');

            header('X-XSS-Protection: 1; mode=block');

        }

    }

}

