<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Comment.php';
require_once __DIR__ . '/../app/helpers/Session.php';

Session::init();

$method = $_SERVER['REQUEST_METHOD'];
$commentModel = new Comment();

// 1. LẤY DANH SÁCH BÌNH LUẬN (GET)
if ($method === 'GET') {
    $postId = 0;
    if (isset($_GET['post_id'])) {
        $postId = intval($_GET['post_id']);
    }

    if ($postId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Bài viết không hợp lệ']);
        exit;
    }

    $comments = $commentModel->getByPostId($postId);
    echo json_encode([
        'success' => true,
        'data' => $comments
    ]);
    exit;
}

// 2. THÊM BÌNH LUẬN MỚI (POST)
if ($method === 'POST') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để bình luận']);
        exit;
    }

    $rawInput = json_decode(file_get_contents('php://input'), true);
    
    $postId = 0;
    if (isset($rawInput['post_id'])) {
        $postId = intval($rawInput['post_id']);
    } elseif (isset($_POST['post_id'])) {
        $postId = intval($_POST['post_id']);
    }

    $content = '';
    if (isset($rawInput['content'])) {
        $content = trim($rawInput['content']);
    } elseif (isset($_POST['content'])) {
        $content = trim($_POST['content']);
    }

    $userId = $_SESSION['user']['id'];

    if ($postId <= 0 || $content === '') {
        echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được để trống']);
        exit;
    }

    $created = $commentModel->create($postId, $userId, $content);

    if ($created) {
        echo json_encode([
            'success' => true, 
            'message' => 'Đã thêm bình luận',
            'data' => [
                'username' => $_SESSION['user']['username'],
                'content' => htmlspecialchars($content),
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể lưu bình luận']);
    }
    exit;
}