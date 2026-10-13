<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Post.php';
require_once __DIR__ . '/../app/helpers/Session.php';

Session::init();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thích bài viết!']);
        exit;
    }

    $rawInput = json_decode(file_get_contents('php://input'), true);
    
    $action = '';
    if (isset($rawInput['action'])) {
        $action = $rawInput['action'];
    } elseif (isset($_POST['action'])) {
        $action = $_POST['action'];
    }

    $postId = 0;
    if (isset($rawInput['post_id'])) {
        $postId = intval($rawInput['post_id']);
    } elseif (isset($_POST['post_id'])) {
        $postId = intval($_POST['post_id']);
    }

    $userId = $_SESSION['user']['id'];

    if ($action === 'like' && $postId > 0) {
        $postModel = new Post();
        $totalLikes = $postModel->toggleLike($postId, $userId);

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật lượt thích thành công',
            'data' => [
                'likes' => $totalLikes
            ]
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);