<?php
$title = 'Hồ sơ cá nhân';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 680px;">
    <div class="nature-card" style="border-radius: 40px; padding: 2.5rem;">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #2d3328; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 500;">
                <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <p class="nature-label mb-1">Thành viên cộng đồng</p>
                <h1 class="h3 fw-normal mb-0" style="color: var(--color-ink);">
                    <?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </h1>
            </div>
        </div>

        <div style="background: #ffffff; border-radius: 20px; padding: 1.5rem; border: 1px solid #dcdfd6; margin-bottom: 2rem;">
            <div class="row g-3">
                <div class="col-sm-4">
                    <span class="nature-label">Địa chỉ email</span>
                </div>
                <div class="col-sm-8 text-dark">
                    <?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="col-sm-4">
                    <span class="nature-label">Vai trò</span>
                </div>
                <div class="col-sm-8">
                    <span class="badge text-bg-success px-3 py-1 rounded-pill">
                        <?= htmlspecialchars($user['role_name'] ?? 'Thành viên', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <div class="col-sm-4">
                    <span class="nature-label">Ngày tham gia</span>
                </div>
                <div class="col-sm-8 text-dark">
                    <?= !empty($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'Mới tham gia' ?>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                <i class="bi bi-pencil-fill me-1"></i> Viết bài mới
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="nature-btn-secondary px-4 py-2 text-dark border-dark-subtle" style="height: 44px;">
                Xem các bài viết
            </a>
        </div>
    </div>
</div>