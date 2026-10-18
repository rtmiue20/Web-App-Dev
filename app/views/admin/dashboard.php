<?php
$title = 'Quản trị hệ thống';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 960px;">
    <div class="page-header">
        <h1 class="page-title">Bảng điều khiển quản trị</h1>
        <p class="page-subtitle">Xin chào quản trị viên <strong><?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="nature-card" style="border-radius: 28px; padding: 1.75rem;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="nature-label mb-0">Chuyên mục</span>
                    <i class="bi bi-tags-fill fs-4 text-success"></i>
                </div>
                <div class="h2 fw-normal mb-3" style="color: var(--color-ink);">Quản lý</div>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories" class="nature-btn-secondary d-inline-flex text-dark border-dark-subtle" style="font-size: 13px;">
                    Xem danh sách &rarr;
                </a>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="nature-card" style="border-radius: 28px; padding: 1.75rem;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="nature-label mb-0">Bài viết</span>
                    <i class="bi bi-file-earmark-text-fill fs-4 text-primary"></i>
                </div>
                <div class="h2 fw-normal mb-3" style="color: var(--color-ink);">Bài đăng</div>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="nature-btn-secondary d-inline-flex text-dark border-dark-subtle" style="font-size: 13px;">
                    Duyệt bài &rarr;
                </a>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="nature-card" style="border-radius: 28px; padding: 1.75rem;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="nature-label mb-0">Cộng đồng</span>
                    <i class="bi bi-people-fill fs-4 text-warning"></i>
                </div>
                <div class="h2 fw-normal mb-3" style="color: var(--color-ink);">Người dùng</div>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile" class="nature-btn-secondary d-inline-flex text-dark border-dark-subtle" style="font-size: 13px;">
                    Xem hồ sơ &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Actions Panel -->
    <div class="nature-card" style="border-radius: 36px; padding: 2rem;">
        <h2 class="h5 fw-normal mb-3" style="color: var(--color-ink);">Thao tác nhanh</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                <i class="bi bi-plus-circle-fill me-1"></i> Thêm chuyên mục mới
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-secondary px-4 py-2 text-dark border-dark-subtle" style="height: 44px;">
                <i class="bi bi-pencil-fill me-1"></i> Đăng thông báo / bài viết mới
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" class="nature-btn-secondary px-4 py-2 text-dark border-dark-subtle" style="height: 44px;">
                <i class="bi bi-house-door-fill me-1"></i> Về trang chủ
            </a>
        </div>
    </div>
</div>