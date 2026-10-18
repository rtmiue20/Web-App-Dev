<?php
$title = 'Bài viết dã ngoại';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center page-header gap-3">
        <div>
            <h1 class="page-title">Cộng đồng chia sẻ</h1>
            <p class="page-subtitle">Khám phá kinh nghiệm, cẩm nang và hành trình thực tế từ các phượt thủ.</p>
        </div>
        <div>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 46px;">
                <i class="bi bi-pencil-fill me-2"></i> Viết bài chia sẻ
            </a>
        </div>
    </div>

    <!-- Category filter buttons -->
    <?php if (!empty($categories)): ?>
        <div class="d-flex flex-wrap gap-2 mb-4 pb-2" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" 
               class="dock-pill <?= empty($selectedCategoryId) ? 'active' : '' ?>">
                Tất cả
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?category=<?= (int)$cat['id'] ?>" 
                   class="dock-pill <?= ($selectedCategoryId ?? null) == $cat['id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Post list grid -->
    <?php require __DIR__ . '/../partials/post-cards.php'; ?>
</div>