<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Csrf.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Comment.php';

Session::init();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!Session::has('user')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để gửi bình luận.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawInput = file_get_contents('php://input');
$json = json_decode((string)$rawInput, true);

$postId = (int)($json['post_id'] ?? $_POST['post_id'] ?? 0);
$content = trim((string)($json['content'] ?? $_POST['content'] ?? ''));
$csrf = $json['_csrf'] ?? $_POST['_csrf'] ?? null;

if (!Csrf::verify($csrf)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($postId <= 0 || mb_strlen($content) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nội dung bình luận phải từ 2 ký tự trở lên.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$user = Session::get('user');
$userId = (int)($user['id'] ?? 0);

$commentModel = new Comment();
$commentId = $commentModel->create($postId, $userId, $content);

if ($commentId > 0) {
    $totalComments = $commentModel->countByPost($postId);
    echo json_encode([
        'success' => true,
        'message' => 'Đã gửi bình luận thành công.',
        'data' => [
            'id' => $commentId,
            'post_id' => $postId,
            'content' => $content,
            'username' => (string)($user['username'] ?? 'Thành viên'),
            'created_at' => date('d/m/Y H:i'),
            'can_delete' => true,
            'total_comments' => $totalComments
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể lưu bình luận vào cơ sở dữ liệu.'], JSON_UNESCAPED_UNICODE);
}
exit;
