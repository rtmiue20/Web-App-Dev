<?php

if (PHP_SAPI === 'cli-server') {
    $filePath = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (is_file($filePath)) {
        return false;
    }
    define('BASE_URL', '');
} else {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $baseUrl = str_ends_with($scriptName, '/index.php')
        ? rtrim( substr($scriptName, 0, -strlen('/index.php')), '/' )
        : '';
    define('BASE_URL', ($baseUrl === '.' || $baseUrl === '') ? '' : $baseUrl);
}

require_once __DIR__ . '/../app/bootstrap.php';

require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/ModeratorMiddleware.php';
require_once __DIR__ . '/../app/middleware/AdminMiddleware.php';

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/PostController.php';
require_once __DIR__ . '/../app/controllers/CategoryController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/OutdoorController.php';
require_once __DIR__ . '/../app/controllers/ModerationController.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';
require_once __DIR__ . '/../app/controllers/admin/DashboardController.php';
require_once __DIR__ . '/../app/controllers/admin/PostAdminController.php';
require_once __DIR__ . '/../app/controllers/admin/UserAdminController.php';
require_once __DIR__ . '/../app/controllers/admin/ProductAdminController.php';

require_once __DIR__ . '/../app/core/App.php';

DemoSeeder::ensureSeeded();
(new App())->run();
