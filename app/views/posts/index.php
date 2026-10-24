<?php
$title = 'Bài viết dã ngoại';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$currentPage = isset($currentPage) ? (int)$currentPage : 1;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$totalPosts = isset($totalPosts) ? (int)$totalPosts : count($posts ?? []);
$catQuery = !empty($selectedCategoryId) ? 'category=' . (int)$selectedCategoryId . '&' : '';
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

    <!-- Category filter buttons (FR03) -->
    <?php if (!empty($categories)): ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 pb-2" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <div class="d-flex flex-wrap gap-2">
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
            
            <div class="text-white-50 small">
                <i class="bi bi-collection me-1"></i> Tổng cộng: <strong><?= $totalPosts ?></strong> bài viết
            </div>
        </div>
    <?php endif; ?>

    <!-- Post list grid -->
    <?php require __DIR__ . '/../partials/post-cards.php'; ?>

    <!-- Thanh phân trang Bootstrap (FR03 - Tuần 4 - Phúc) -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Điều hướng phân trang bài viết" class="mt-5 mb-4">
            <ul class="pagination forum-pagination justify-content-center flex-wrap">
                <!-- Nút trang trước -->
                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill" 
                       href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $catQuery ?>page=<?= max(1, $currentPage - 1) ?>" 
                       tabindex="<?= ($currentPage <= 1) ? '-1' : '0' ?>"
                       aria-disabled="<?= ($currentPage <= 1) ? 'true' : 'false' ?>">
                        <i class="bi bi-chevron-left me-1"></i> Trước
                    </a>
                </li>

                <!-- Danh sách số trang -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $catQuery ?>page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Nút trang sau -->
                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill" 
                       href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $catQuery ?>page=<?= min($totalPages, $currentPage + 1) ?>" 
                       tabindex="<?= ($currentPage >= $totalPages) ? '-1' : '0' ?>"
                       aria-disabled="<?= ($currentPage >= $totalPages) ? 'true' : 'false' ?>">
                        Sau <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </li>
            </ul>

            <div class="text-center text-white-50 small mt-2">
                Trang <?= $currentPage ?> / <?= $totalPages ?> (Hiển thị tối đa 6 bài / trang)
            </div>
        </nav>

        <style>
            .forum-pagination .page-link {
                background-color: rgba(34, 40, 31, 0.7);
                color: #ffffff;
                border: 1px solid rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(8px);
                padding: 0.5rem 1rem;
                margin: 0 3px;
                transition: all 0.2s ease;
                box-shadow: none;
            }
            .forum-pagination .page-link:hover {
                background-color: var(--color-moss, #9bbd55);
                color: #22281f;
                border-color: var(--color-moss, #9bbd55);
            }
            .forum-pagination .page-item.active .page-link {
                background-color: var(--color-moss, #9bbd55);
                color: #22281f;
                border-color: var(--color-moss, #9bbd55);
                font-weight: 600;
            }
            .forum-pagination .page-item.disabled .page-link {
                background-color: rgba(255, 255, 255, 0.05);
                color: rgba(255, 255, 255, 0.3);
                border-color: rgba(255, 255, 255, 0.05);
            }
        </style>
    <?php endif; ?>
</div>