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
    define('BASE_URL', ($baseUrl === '.' || $baseUrl === '/') ? '' : $baseUrl);
}

require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Auth.php';
require_once __DIR__ . '/../app/helpers/Csrf.php';
require_once __DIR__ . '/../app/helpers/Validator.php';
require_once __DIR__ . '/../app/helpers/Response.php';
require_once __DIR__ . '/../app/helpers/Upload.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/helpers/DemoSeeder.php';

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';

require_once __DIR__ . '/../app/models/Role.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Post.php';
require_once __DIR__ . '/../app/models/Comment.php';
require_once __DIR__ . '/../app/models/Product.php';

require_once __DIR__ . '/../app/services/WeatherService.php';
require_once __DIR__ . '/../app/services/VietnamAddressService.php';

require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/ModeratorMiddleware.php';
require_once __DIR__ . '/../app/middleware/AdminMiddleware.php';

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/PostController.php';
require_once __DIR__ . '/../app/controllers/CategoryController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';
require_once __DIR__ . '/../app/controllers/admin/DashboardController.php';
require_once __DIR__ . '/../app/controllers/admin/PostAdminController.php';
require_once __DIR__ . '/../app/controllers/admin/UserAdminController.php';
require_once __DIR__ . '/../app/controllers/admin/ProductAdminController.php';

require_once __DIR__ . '/../app/core/App.php';

DemoSeeder::ensureSeeded();
(new App())->run();
