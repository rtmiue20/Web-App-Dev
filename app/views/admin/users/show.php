<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$uRoleName = strtolower(trim((string)($member['role_name'] ?? 'member')));
$roleLabel = Auth::roleLabel($uRoleName);
$badgeClass = Auth::roleBadgeClass($uRoleName);
?>
<div class="app-container" style="max-width: 780px;">
    <div class="mb-3">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="nature-btn-secondary px-3 py-2 text-white border-white-20">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách thành viên
        </a>
    </div>

    <div class="nature-card" style="border-radius: 32px; padding: 2rem;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-3 border-bottom">
            <div>
                <h1 class="h4 fw-semibold mb-1" style="color: var(--color-ink);">
                    <?= htmlspecialchars($member['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <span class="badge rounded-pill <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?> px-3 py-1">
                    <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <div class="text-muted small">
                Mã thành viên: <strong>#<?= (int)($member['id'] ?? 0) ?></strong>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-4 text-muted">Email:</div>
            <div class="col-sm-8 fw-medium text-dark"><?= htmlspecialchars($member['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>

            <div class="col-sm-4 text-muted">Số bài viết đã đăng:</div>
            <div class="col-sm-8 fw-medium text-dark"><?= (int)($member['total_posts'] ?? 0) ?> bài viết</div>

            <div class="col-sm-4 text-muted">Ngày tham gia:</div>
            <div class="col-sm-8 fw-medium text-dark">
                <?= !empty($member['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($member['created_at'])), ENT_QUOTES, 'UTF-8') : '—' ?>
            </div>

            <div class="col-sm-4 text-muted">Giới thiệu:</div>
            <div class="col-sm-8 text-secondary">
                <?= nl2br(htmlspecialchars($member['bio'] ?? 'Chưa cập nhật tiểu sử cá nhân', ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </div>
    </div>
</div>

