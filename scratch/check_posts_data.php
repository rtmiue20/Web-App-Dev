<?php
require_once __DIR__ . '/../app/bootstrap.php';
$posts = Database::query("SELECT id, title, activity_type, difficulty, region, status FROM posts LIMIT 15");
echo json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
