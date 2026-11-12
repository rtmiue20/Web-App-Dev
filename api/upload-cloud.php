<?php
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/Csrf.php';
require_once __DIR__ . '/../app/helpers/Response.php';
require_once __DIR__ . '/../app/helpers/Upload.php';
require_once __DIR__ . '/../app/services/CloudUploadService.php';

Session::init();

if (!Session::has('user')) {
    Response::json(['success' => false, 'message' => 'Vui lòng đăng nhập để tải ảnh lên.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
}

if (!Csrf::verify()) {
    Response::json(['success' => false, 'message' => 'CSRF token không hợp lệ. Vui lòng tải lại trang.'], 403);
}

$service = new CloudUploadService();
$uploaded = [];

try {
    if (!empty($_FILES['image']['name'])) {
        $uploaded[] = $service->uploadImage($_FILES['image'], 'posts');
    } elseif (!empty($_FILES['images']['name']) && is_array($_FILES['images']['name'])) {
        $count = count($_FILES['images']['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($_FILES['images']['error'][$i] ?? null) === UPLOAD_ERR_OK) {
                $single = [
                    'name'     => $_FILES['images']['name'][$i],
                    'type'     => $_FILES['images']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i] ?? 0,
                ];
                $uploaded[] = $service->uploadImage($single, 'posts');
            }
        }
    }

    if (empty($uploaded)) {
        Response::json(['success' => false, 'message' => 'Không có file ảnh hợp lệ được chọn.'], 400);
    }

    Response::json([
        'success' => true,
        'message' => 'Tải ảnh thành công!',
        'data'    => $uploaded
    ]);
} catch (Throwable $e) {
    Response::json(['success' => false, 'message' => $e->getMessage()], 400);
}

