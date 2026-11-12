<?php
$title = 'Chuyên mục dã ngoại';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 1100px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 page-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(155, 189, 85, 0.18); color: var(--color-moss); border: 1px solid rgba(155, 189, 85, 0.35);">
                    <i class="bi bi-grid-fill me-1"></i>Chủ đề dã ngoại
                </span>
            </div>
            <h1 class="page-title mb-1">Chuyên mục</h1>
            <p class="page-subtitle mb-0">Khám phá các bài viết và kinh nghiệm theo từng chủ đề dã ngoại.</p>
        </div>
        <?php if (Session::has('user') && (((Session::get('user')['role_name'] ?? '') === 'admin') || (int)(Session::get('user')['role_id'] ?? 0) === 2)): ?>
            <div class="d-flex gap-2">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="nature-btn-secondary px-3 py-2 text-white border-white-20 d-inline-flex" style="height: 44px; font-size: 14px;">
                    <i class="bi bi-gear me-1"></i> Quản trị danh mục
                </a>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
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

        <!-- Thanh phân trang Chuyên mục (Đồng bộ chuẩn Nature Glass giống Dụng cụ) -->
        <?php if (($totalCategories ?? 0) > 0): ?>
            <nav aria-label="Điều hướng phân trang chuyên mục" class="mt-5 mb-4">
                <ul class="pagination forum-pagination justify-content-center flex-wrap">
                    <!-- Nút trang trước -->
                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories?page=<?= max(1, $currentPage - 1) ?>" 
                           tabindex="<?= ($currentPage <= 1) ? '-1' : '0' ?>"
                           aria-disabled="<?= ($currentPage <= 1) ? 'true' : 'false' ?>">
                            <i class="bi bi-chevron-left me-1"></i> Trước
                        </a>
                    </li>

                    <!-- Danh sách số trang -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                            <a class="page-link rounded-pill" 
                               href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories?page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Nút trang sau -->
                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories?page=<?= min($totalPages, $currentPage + 1) ?>" 
                           tabindex="<?= ($currentPage >= $totalPages) ? '-1' : '0' ?>"
                           aria-disabled="<?= ($currentPage >= $totalPages) ? 'true' : 'false' ?>">
                            Sau <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                </ul>

                <div class="text-center text-white-50 small mt-2">
                    Trang <?= $currentPage ?> / <?= $totalPages ?> (Hiển thị <?= count($categories) ?> chuyên mục / trang • Tổng số <?= $totalCategories ?> chuyên mục)
                </div>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>