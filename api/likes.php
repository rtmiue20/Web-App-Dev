<?php
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Auth.php';
require_once __DIR__ . '/../app/helpers/Csrf.php';
require_once __DIR__ . '/../app/helpers/Response.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Post.php';

Session::init();

if (!Auth::isMember()) {
    Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập tài khoản Thành viên để thích bài viết.'], 401);
}

$rawInput = file_get_contents('php://input');
$json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
if (!is_array($json)) {
    $json = [];
}

$postId = (int)($json['post_id'] ?? $_POST['post_id'] ?? $_GET['post_id'] ?? 0);
if ($postId <= 0) {
    Response::json(['success' => false, 'message' => 'Bài viết không hợp lệ.'], 400);
}

$user = Session::get('user');
$userId = (int)($user['id'] ?? 0);

$postModel = new Post();
$newLikes = $postModel->toggleLike($postId, $userId);
$isLiked = $postModel->isLiked($postId, $userId);

Response::json([
    'success' => true,
    'data' => [
        'likes' => $newLikes,
        'liked' => $isLiked
    ]
]);

