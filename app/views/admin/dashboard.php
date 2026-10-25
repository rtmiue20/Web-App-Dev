<?php
$title = 'Bảng điều khiển Quản trị';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 1040px;">
    <!-- Welcome Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="nature-badge mb-2" style="background: rgba(155, 189, 85, 0.15); color: #c4e482; border-color: rgba(155, 189, 85, 0.3);">
                <i class="bi bi-shield-check me-1"></i>Khu vực Quản trị
            </span>
            <h1 class="page-title">Bảng điều khiển</h1>
            <p class="page-subtitle">Xin chào <strong><?= htmlspecialchars($user['username'] ?? 'Quản trị viên', ENT_QUOTES, 'UTF-8') ?></strong>! Chúc bạn một ngày làm việc hiệu quả.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                <i class="bi bi-plus-circle me-1"></i> Thêm chuyên mục
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" class="nature-btn-secondary px-3 py-2 text-white border-white-20" style="height: 44px;" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Xem diễn đàn
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Chuyên mục -->
        <div class="col-12 col-md-4">
            <div class="nature-card h-100" style="border-radius: 28px; padding: 1.75rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="nature-label mb-0">Chuyên mục</span>
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 50%; background: rgba(155, 189, 85, 0.15); color: var(--color-moss);">
                        <i class="bi bi-tags-fill fs-5"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="display-6 fw-normal" style="color: var(--color-ink);"><?= (int)($categoryCount ?? 0) ?></span>
                    <span class="text-muted small">danh mục đang hoạt động</span>
                </div>
                <div class="pt-2 border-top">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="text-decoration-none small fw-medium" style="color: var(--color-ink);">
                        Quản lý chuyên mục &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Bài viết -->
        <div class="col-12 col-md-4">
            <div class="nature-card h-100" style="border-radius: 28px; padding: 1.75rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="nature-label mb-0">Bài viết</span>
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 50%; background: rgba(13, 110, 253, 0.12); color: #0d6efd;">
                        <i class="bi bi-file-earmark-text-fill fs-5"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="display-6 fw-normal" style="color: var(--color-ink);"><?= (int)($postCount ?? 0) ?></span>
                    <span class="text-muted small">bài đăng diễn đàn</span>
                </div>
                <div class="pt-2 border-top">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts" class="text-decoration-none small fw-medium" style="color: var(--color-ink);">
                        Quản lý bài viết &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Thành viên -->
        <div class="col-12 col-md-4">
            <div class="nature-card h-100 position-relative" style="border-radius: 28px; padding: 1.75rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="nature-label mb-0">Thành viên</span>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="d-inline-flex align-items-center justify-content-center text-decoration-none" style="width: 44px; height: 44px; border-radius: 50%; background: rgba(255, 193, 7, 0.15); color: #ffb703;" title="Xem danh sách thành viên">
                        <i class="bi bi-people-fill fs-5"></i>
                    </a>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="display-6 fw-normal" style="color: var(--color-ink);"><?= (int)($userCount ?? 0) ?></span>
                    <span class="text-muted small">tài khoản đã đăng ký</span>
                </div>
                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="text-decoration-none small fw-medium" style="color: var(--color-ink);">
                        Quản lý thành viên &rarr;
                    </a>
                    <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 11px;">
                        <i class="bi bi-key me-1"></i>Cấp lại pass
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Quick Table -->
    <div class="nature-card mb-4" style="border-radius: 32px; padding: 2rem;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="h5 fw-normal mb-1" style="color: var(--color-ink);">Chuyên mục gần đây</h2>
                <p class="small text-muted mb-0">Quản trị nhanh các danh mục bài viết dã ngoại trên hệ thống.</p>
            </div>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="nature-btn-secondary py-1 px-3 text-dark border-dark-subtle" style="font-size: 13px;">
                Xem toàn bộ <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (empty($recentCategories)): ?>
            <p class="text-muted text-center py-4 mb-0">Chưa có chuyên mục nào trong hệ thống.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 60px;">ID</th>
                            <th scope="col">Tên chuyên mục</th>
                            <th scope="col">Mô tả</th>
                            <th scope="col" class="text-center" style="width: 130px;">Số bài viết</th>
                            <th scope="col" class="text-end" style="width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentCategories as $cat): ?>
                            <tr>
                                <td class="fw-medium text-muted">#<?= (int)$cat['id'] ?></td>
                                <td class="fw-medium text-dark">
                                    <i class="bi bi-folder2-open text-success me-2"></i>
                                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars(mb_strimwidth($cat['description'] ?? 'Chưa có mô tả', 0, 60, '...'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-light border">
                                        <?= (int)($cat['post_count'] ?? 0) ?> bài
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/edit/<?= (int)$cat['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 me-1" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0" title="Chi tiết">
                                        <i class="bi bi-gear"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    </div>