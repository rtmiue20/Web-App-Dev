<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';

$api = new ApiController();
$type = $_GET['type'] ?? 'user';
if ($type === 'category') {
    $api->toggleCategoryFollow();
} else {
    $api->toggleUserFollow();
}
