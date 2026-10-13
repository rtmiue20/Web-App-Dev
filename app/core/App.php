<?php

class App
{
    public function run(): void
    {
        Session::init();

        $router = new Router();
        $auth = new AuthController();
        $users = new UserController();
        $dashboard = new DashboardController();

        $router->get('/', static function (): void {
            $path = Session::has('user') ? '/profile' : '/login';
            header('Location: ' . BASE_URL . $path, true, 302);
        });
        $router->get('/login', [$auth, 'showLogin']);
        $router->post('/login', [$auth, 'login']);
        $router->get('/register', [$auth, 'showRegister']);
        $router->post('/register', [$auth, 'register']);
        $router->post('/logout', [$auth, 'logout'], [AuthMiddleware::class]);
        $router->get('/profile', [$users, 'profile'], [AuthMiddleware::class]);
        $router->get('/admin', [$dashboard, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);

        $url = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!isset($_GET['url']) && BASE_URL !== '' && str_starts_with((string) $url, BASE_URL)) {
            $url = substr((string) $url, strlen(BASE_URL));
        }

        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', (string) $url);
    }
}