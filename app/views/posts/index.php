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

    <!-- Category Slidebar (FR03 - Tuần 4 - Phúc) -->
    <?php if (!empty($categories)): ?>
        <div class="category-slidebar-container mb-4 pb-2" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg">
                    <div class="category-slidebar-wrapper position-relative d-flex align-items-center">
                        <!-- Nút trượt sang trái -->
                        <button type="button" 
                                class="category-slide-btn category-slide-prev me-2" 
                                id="catSlidePrev" 
                                aria-label="Cuộn sang trái" 
                                title="Xem các chuyên mục trước">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <div class="category-slidebar-inner position-relative flex-grow-1 overflow-hidden">
                            <!-- Gradient mờ 2 đầu báo hiệu còn danh mục -->
                            <div class="category-slide-fade category-slide-fade-start" id="catFadeStart"></div>
                            
                            <!-- Track trượt ngang chứa các danh mục -->
                            <div class="category-slidebar-track d-flex align-items-center gap-2" id="catSlideTrack">
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

                            <div class="category-slide-fade category-slide-fade-end" id="catFadeEnd"></div>
                        </div>

                        <!-- Nút trượt sang phải -->
                        <button type="button" 
                                class="category-slide-btn category-slide-next ms-2" 
                                id="catSlideNext" 
                                aria-label="Cuộn sang phải" 
                                title="Xem thêm chuyên mục">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="col-12 col-lg-auto text-lg-end text-white-50 small text-nowrap">
                    <i class="bi bi-collection me-1"></i> Tổng cộng: <strong><?= $totalPosts ?></strong> bài viết
                </div>
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

<!-- JavaScript điều khiển Thanh Slidebar Chuyên mục (Không dùng Lambda) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var track = document.getElementById('catSlideTrack');
    var btnPrev = document.getElementById('catSlidePrev');
    var btnNext = document.getElementById('catSlideNext');
    var fadeStart = document.getElementById('catFadeStart');
    var fadeEnd = document.getElementById('catFadeEnd');

    if (!track) {
        return;
    }

    var step = 260; // Số pixel cuộn mỗi lần nhấn nút

    function updateSlidebarState() {
        var scrollLeft = track.scrollLeft;
        var maxScroll = track.scrollWidth - track.clientWidth;

        // Nếu tổng chiều rộng nội dung không tràn, ẩn nút hoặc disable
        if (maxScroll <= 2) {
            if (btnPrev) { btnPrev.disabled = true; btnPrev.classList.add('is-disabled'); }
            if (btnNext) { btnNext.disabled = true; btnNext.classList.add('is-disabled'); }
            if (fadeStart) { fadeStart.style.opacity = '0'; }
            if (fadeEnd) { fadeEnd.style.opacity = '0'; }
            return;
        }

        // Trạng thái nút trái
        if (scrollLeft <= 5) {
            if (btnPrev) { btnPrev.disabled = true; btnPrev.classList.add('is-disabled'); }
            if (fadeStart) { fadeStart.style.opacity = '0'; }
        } else {
            if (btnPrev) { btnPrev.disabled = false; btnPrev.classList.remove('is-disabled'); }
            if (fadeStart) { fadeStart.style.opacity = '1'; }
        }

        // Trạng thái nút phải
        if (scrollLeft >= maxScroll - 5) {
            if (btnNext) { btnNext.disabled = true; btnNext.classList.add('is-disabled'); }
            if (fadeEnd) { fadeEnd.style.opacity = '0'; }
        } else {
            if (btnNext) { btnNext.disabled = false; btnNext.classList.remove('is-disabled'); }
            if (fadeEnd) { fadeEnd.style.opacity = '1'; }
        }
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', function () {
            track.scrollBy({ left: -step, behavior: 'smooth' });
        });
    }

    if (btnNext) {
        btnNext.addEventListener('click', function () {
            track.scrollBy({ left: step, behavior: 'smooth' });
        });
    }

    track.addEventListener('scroll', updateSlidebarState);
    window.addEventListener('resize', updateSlidebarState);

    // Hỗ trợ kéo trượt ngang bằng chuột (Drag-to-scroll)
    var isDown = false;
    var startX = 0;
    var scrollLeftStart = 0;

    track.addEventListener('mousedown', function (e) {
        isDown = true;
        startX = e.pageX - track.offsetLeft;
        scrollLeftStart = track.scrollLeft;
    });

    track.addEventListener('mouseleave', function () {
        isDown = false;
    });

    track.addEventListener('mouseup', function () {
        isDown = false;
    });

    track.addEventListener('mousemove', function (e) {
        if (!isDown) {
            return;
        }
        e.preventDefault();
        var x = e.pageX - track.offsetLeft;
        var walk = (x - startX) * 1.5;
        track.scrollLeft = scrollLeftStart - walk;
    });

    // Cuộn ngang bằng con lăn chuột
    track.addEventListener('wheel', function (e) {
        if (Math.abs(e.deltaX) === 0 && Math.abs(e.deltaY) > 0) {
            track.scrollLeft += e.deltaY;
            e.preventDefault();
        }
    }, { passive: false });

    // Tự động cuộn đến danh mục đang active để người dùng thấy ngay
    var activePill = track.querySelector('.dock-pill.active');
    if (activePill) {
        setTimeout(function () {
            activePill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            updateSlidebarState();
        }, 120);
    } else {
        updateSlidebarState();
    }
});
</script>