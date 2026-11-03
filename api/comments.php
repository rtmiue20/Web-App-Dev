<?php
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Csrf.php';
require_once __DIR__ . '/../app/helpers/Response.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';

Session::init();
$api = new ApiController();

$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $api->listComments();
} elseif ($action === 'delete') {
    $api->deleteComment();
} elseif ($action === 'like') {
    $api->likeComment();
} else {
    $api->createComment();
}
