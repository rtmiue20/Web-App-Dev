<?php

$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
define('BASE_URL', $baseUrl === '.' ? '' : $baseUrl);

require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Csrf.php';
require_once __DIR__ . '/../app/helpers/Validator.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/models/Role.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/AdminMiddleware.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/admin/DashboardController.php';
require_once __DIR__ . '/../app/core/App.php';

(new App())->run();