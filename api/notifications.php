<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';

$api = new ApiController();
$action = $_GET['action'] ?? '';
if ($action === 'read') {
    $api->readNotification();
} else {
    $api->notifications();
}
