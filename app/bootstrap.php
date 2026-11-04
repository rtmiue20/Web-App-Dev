<?php

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

// Universal Class Autoloader
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/core/',
        __DIR__ . '/helpers/',
        __DIR__ . '/models/',
        __DIR__ . '/services/',
        __DIR__ . '/controllers/',
        __DIR__ . '/controllers/admin/',
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

// Pre-load essential helpers and core
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

// Services
require_once __DIR__ . '/services/WeatherService.php';
require_once __DIR__ . '/services/VietnamAddressService.php';

Session::init();
