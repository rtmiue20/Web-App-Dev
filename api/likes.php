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
    Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập tài khoản Thành viên để bình chọn (Upvote / Downvote) bài viết.'], 401);
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

$voteRaw = $json['vote'] ?? $_POST['vote'] ?? $_GET['vote'] ?? 1;
$voteType = ((int)$voteRaw < 0) ? -1 : 1;

$user = Session::get('user');
$userId = (int)($user['id'] ?? 0);

$postModel = new Post();
$voteData = $postModel->vote($postId, $userId, $voteType);

Response::json([
    'success' => true,
    'data'    => $voteData
]);
