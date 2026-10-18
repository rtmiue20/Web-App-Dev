<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Csrf.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Comment.php';

Session::init();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

if (!Session::has('user')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi bình luận.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);

$postId = (int)($json['post_id'] ?? $_POST['post_id'] ?? 0);
$content = trim((string)($json['content'] ?? $_POST['content'] ?? ''));
$csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? null;

if (!Csrf::verify($csrf)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.']);
    exit;
}

if ($postId <= 0 || mb_strlen($content) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nội dung bình luận phải từ 2 ký tự trở lên.']);
    exit;
}

$user = Session::get('user');
$userId = (int)($user['id'] ?? 0);

$commentModel = new Comment();
$result = $commentModel->create($postId, $userId, $content);

if ($result) {
    echo json_encode([
        'success' => true,
        'data' => [
            'post_id' => $postId,
            'content' => htmlspecialchars($content, ENT_QUOTES, 'UTF-8'),
            'username' => htmlspecialchars($user['username'] ?? 'Bạn', ENT_QUOTES, 'UTF-8'),
            'created_at' => date('d/m/Y H:i')
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể lưu bình luận vào cơ sở dữ liệu.']);
}
exit;