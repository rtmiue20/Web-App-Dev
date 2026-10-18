<?php
$title = 'Chuyên mục dã ngoại';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center page-header gap-3">
        <div>
            <h1 class="page-title">Chuyên mục</h1>
            <p class="page-subtitle">Khám phá các bài viết và kinh nghiệm theo từng chủ đề dã ngoại.</p>
        </div>
        <?php if (Session::has('user') && (((Session::get('user')['role_name'] ?? '') === 'admin') || (int)(Session::get('user')['role_id'] ?? 0) === 2)): ?>
            <div>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                    <i class="bi bi-plus-lg me-1"></i> Thêm chuyên mục
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($categories)): ?>
        <div class="nature-card text-center py-5">
            <i class="bi bi-tags fs-1 text-muted d-block mb-3"></i>
            <h3 class="h5 fw-normal mb-1">Chưa có chuyên mục</h3>
            <p class="text-muted small">Hiện chưa có danh mục nào được khởi tạo.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="nature-card h-100 d-flex flex-column" style="border-radius: 32px; padding: 2rem;">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <span class="nature-badge" style="background: rgba(155, 189, 85, 0.15); color: var(--color-ink); border-color: rgba(155, 189, 85, 0.4);">
                                <i class="bi bi-tag-fill me-1"></i>Chuyên mục
                            </span>
                            <?php if (isset($cat['post_count'])): ?>
                                <span class="badge text-bg-light border small text-muted">
                                    <?= (int)$cat['post_count'] ?> bài viết
                                </span>
                            <?php endif; ?>
                        </div>

                        <h2 class="h4 fw-normal mb-2">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?category=<?= (int)$cat['id'] ?>" 
                               style="color: var(--color-ink); text-decoration: none;">
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>

                        <p class="small text-muted mb-4 flex-grow-1">
                            <?= htmlspecialchars($cat['description'] ?? 'Chia sẻ kinh nghiệm và kỹ năng trong chủ đề này.', ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div>
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?category=<?= (int)$cat['id'] ?>" 
                               class="nature-btn-secondary py-1 px-3 text-dark border-dark-subtle d-inline-flex" style="font-size: 13px;">
                                Xem bài viết <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>