<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Post.php';

$postModel = new Post();
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$posts = $postModel->getAll($categoryId);

echo json_encode([
    'success' => true,
    'data' => $posts
], JSON_UNESCAPED_UNICODE);
exit;