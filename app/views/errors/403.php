<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Không có quyền truy cập</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css" rel="stylesheet">
</head>
<body style="background: var(--color-scene-edge, #383b34); color: #ffffff; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem;">
    <div class="nature-card text-center frame-in" style="max-width: 520px; padding: 3rem 2rem; border-radius: 36px; box-shadow: 0 24px 60px rgba(0,0,0,0.4);">
        <div class="mb-3">
            <span class="d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(220, 53, 69, 0.12); color: #ff6b6b; font-size: 2.25rem;">
                <i class="bi bi-shield-lock-fill"></i>
            </span>
        </div>
        <h1 class="display-4 fw-light mb-2" style="color: var(--color-ink, #23261f);">403</h1>
        <p class="nature-label mb-3" style="color: #dc3545;">Khu vực giới hạn — Quyền truy cập bị từ chối</p>
        <p class="text-muted small mb-4" style="line-height: 1.6;">
            Bạn không có quyền quản trị viên (Admin) để truy cập vào khu vực này. Nếu bạn cho rằng đây là một sự nhầm lẫn, vui lòng liên hệ ban quản trị hệ thống.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" class="nature-btn-primary px-4 py-2 d-inline-flex" style="width: auto; height: 46px;">
                <i class="bi bi-house-door me-1"></i> Về trang chủ
            </a>
            <?php if (Session::has('user')): ?>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile" class="nature-btn-secondary px-4 py-2 text-dark border-dark-subtle d-inline-flex" style="height: 46px;">
                    <i class="bi bi-person me-1"></i> Trang cá nhân
                </a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-secondary px-4 py-2 text-dark border-dark-subtle d-inline-flex" style="height: 46px;">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
