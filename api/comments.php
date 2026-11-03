<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';

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
