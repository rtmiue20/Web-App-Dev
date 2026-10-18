<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Không tìm thấy trang</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css" rel="stylesheet">
</head>
<body style="background: #383b34; color: #ffffff; display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="nature-card text-center frame-in" style="max-width: 500px; padding: 3rem 2rem;">
        <h1 class="display-3 fw-light mb-2" style="color: var(--color-ink);">404</h1>
        <p class="nature-label mb-3">Lạc đường giữa rừng sâu</p>
        <p class="text-muted small mb-4">Trang bạn đang tìm kiếm không tồn tại hoặc đã được di chuyển.</p>
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" class="nature-btn-primary px-4 py-2 d-inline-flex" style="width: auto; height: 46px;">
            Trở về trang chủ
        </a>
    </div>
</body>
</html>