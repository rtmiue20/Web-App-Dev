<?php

// Thiet lap mui gio Viet Nam (UTC+7) dong bo toan he thong
date_default_timezone_set('Asia/Ho_Chi_Minh');

if (!defined('BASE_URL')) {
    if (PHP_SAPI === 'cli-server') {
        define('BASE_URL', '');
    } else {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $baseUrl = str_ends_with($scriptName, '/index.php')
            ? rtrim(substr($scriptName, 0, -strlen('/index.php')), '/')
            : '';
        define('BASE_URL', ($baseUrl === '.' || $baseUrl === '') ? '' : $baseUrl);
    }
}

// Universal Class Autoloader (Core, Repositories/DAO, Services, Models, Controllers, Middleware, Helpers)
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/core/',
        __DIR__ . '/repositories/',
        __DIR__ . '/services/',
        __DIR__ . '/helpers/',
        __DIR__ . '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/controllers/admin/',
        __DIR__ . '/controllers/api/',
        __DIR__ . '/middleware/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Pre-load essential helpers and core architecture
require_once __DIR__ . '/helpers/Session.php';
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/helpers/Csrf.php';
require_once __DIR__ . '/helpers/Validator.php';
require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/helpers/Upload.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/DemoSeeder.php';
require_once __DIR__ . '/helpers/Gamification.php';

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/RepositoryInterface.php';
require_once __DIR__ . '/core/BaseRepository.php';
require_once __DIR__ . '/core/BaseService.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Router.php';

// Pre-load all Models
require_once __DIR__ . '/models/Role.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/Comment.php';
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Bookmark.php';
require_once __DIR__ . '/models/Follow.php';
require_once __DIR__ . '/models/Notification.php';
require_once __DIR__ . '/models/Report.php';

// Pre-load Repositories (DAO Layer)
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/repositories/CategoryRepository.php';
require_once __DIR__ . '/repositories/PostRepository.php';
require_once __DIR__ . '/repositories/CommentRepository.php';
require_once __DIR__ . '/repositories/ProductRepository.php';
require_once __DIR__ . '/repositories/StatsRepository.php';

// Pre-load Services (Service Layer)
require_once __DIR__ . '/services/WeatherService.php';
require_once __DIR__ . '/services/VietnamAddressService.php';
require_once __DIR__ . '/services/RssService.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/CategoryService.php';
require_once __DIR__ . '/services/PostService.php';
require_once __DIR__ . '/services/UserService.php';

Session::init();
