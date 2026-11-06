<?php
$title = 'Bài viết dã ngoại';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$currentPage = isset($currentPage) ? (int)$currentPage : 1;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$totalPosts = isset($totalPosts) ? (int)$totalPosts : count($posts ?? []);
$selectedSort = isset($selectedSort) && in_array($selectedSort, ['hot', 'new', 'top'], true) ? $selectedSort : 'hot';
$catQuery = !empty($selectedCategoryId) ? 'category=' . (int)$selectedCategoryId . '&' : '';
$sortQuery = 'sort=' . urlencode($selectedSort) . '&';
$filterQuery = $catQuery . $sortQuery;
?>
<div class="app-container">
    <div class="d-flex flex-wrap justify-content-between align-items-center page-header gap-3">
        <div>
            <h1 class="page-title">Cộng đồng chia sẻ</h1>
            <p class="page-subtitle">Khám phá kinh nghiệm, cẩm nang và hành trình thực tế từ các phượt thủ.</p>
        </div>
        <div>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-primary px-4 py-2 w-100 w-sm-auto d-inline-flex justify-content-center align-items-center" style="height: 46px;">
                <i class="bi bi-pencil-fill me-2"></i> Viết bài chia sẻ
            </a>
        </div>
    </div>

    <!-- Thanh Tìm kiếm Gợi ý Autocomplete bằng AJAX (FR04 - Phúc) -->
    <div class="row justify-content-center mb-4">
        <div class="col-12 col-md-8 col-lg-7">
            <div class="search-autocomplete-wrapper position-relative">
                <div class="input-group search-input-group shadow-sm">
                    <span class="input-group-text bg-transparent border-0 ps-3" style="color: var(--color-moss, #9bbd55);">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="forumSearchInput" 
                           class="form-control border-0 nature-search-input" 
                           placeholder="Tìm nhanh bài viết, địa điểm (Tà Xùa, Đà Lạt, lều cắm trại...)" 
                           autocomplete="off">
                    <span class="input-group-text bg-transparent border-0 pe-3" id="searchSpinner" style="display: none;">
                        <span class="spinner-border spinner-border-sm text-success" role="status"></span>
                    </span>
                </div>
                <div id="searchSuggestionsDropdown" class="search-autocomplete-dropdown shadow-lg" style="display: none;"></div>
            </div>
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
                                class="category-slide-btn category-slide-prev me-2 d-none d-md-flex" 
                                id="catSlidePrev" 
                                aria-label="Cuộn sang trái" 
                                title="Xem các chuyên mục trước">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <div class="category-slidebar-inner position-relative flex-grow-1 overflow-hidden">
                            <!-- Gradient mờ 2 đầu báo hiệu còn danh mục -->
                            <div class="category-slide-fade category-slide-fade-start" id="catFadeStart"></div>
                            
                            <!-- Track trượt ngang chứa các danh mục -->
                            <div class="category-slidebar-track d-flex align-items-center gap-2" id="catSlideTrack" style="overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
                                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?sort=<?= urlencode($selectedSort) ?>" 
                                   class="dock-pill text-nowrap flex-shrink-0 <?= empty($selectedCategoryId) ? 'active' : '' ?>">
                                    Tất cả
                                </a>
                                <?php foreach ($categories as $cat): ?>
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?category=<?= (int)$cat['id'] ?>&sort=<?= urlencode($selectedSort) ?>" 
                                       class="dock-pill text-nowrap flex-shrink-0 <?= ($selectedCategoryId ?? null) == $cat['id'] ? 'active' : '' ?>">
                                        <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <div class="category-slide-fade category-slide-fade-end" id="catFadeEnd"></div>
                        </div>

                        <!-- Nút trượt sang phải -->
                        <button type="button" 
                                class="category-slide-btn category-slide-next ms-2 d-none d-md-flex" 
                                id="catSlideNext" 
                                aria-label="Cuộn sang phải" 
                                title="Xem thêm chuyên mục">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Bộ lọc sắp xếp: Hot / Mới / Top Vote -->
                <div class="col-12 col-lg-auto d-flex flex-wrap align-items-center justify-content-between justify-content-lg-end gap-3">
                    <div class="sort-filter-group d-inline-flex align-items-center p-1 rounded-pill"
                         style="background: rgba(20, 25, 18, 0.65); border: 1px solid rgba(255, 255, 255, 0.14);">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $catQuery ?>sort=hot"
                           class="sort-pill <?= $selectedSort === 'hot' ? 'active' : '' ?>"
                           title="Sắp xếp theo độ sôi nổi (Điểm Vote + Bình luận)">
                            <i class="bi bi-fire me-1"></i>Hot
                        </a>
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $catQuery ?>sort=new"
                           class="sort-pill <?= $selectedSort === 'new' ? 'active' : '' ?>"
                           title="Sắp xếp theo bài viết mới đăng nhất">
                            <i class="bi bi-stars me-1"></i>Mới
                        </a>
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $catQuery ?>sort=top"
                           class="sort-pill <?= $selectedSort === 'top' ? 'active' : '' ?>"
                           title="Sắp xếp theo điểm Vote cao nhất">
                            <i class="bi bi-trophy-fill me-1"></i>Top
                        </a>
                    </div>

                    <div class="text-white-50 small text-nowrap">
                        <i class="bi bi-collection me-1"></i> Tổng cộng: <strong><?= $totalPosts ?></strong> bài viết
                    </div>
                </div>
            </div>
        </div>

        <!-- Thanh nút mở rộng Bộ lọc nâng cao (Loại hình, Độ khó, Vùng miền, Tag) -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <button class="btn btn-sm btn-outline-light rounded-pill px-3 d-inline-flex align-items-center gap-1" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#advancedFilterCollapse" 
                    aria-expanded="<?= (!empty($filters['difficulty']) || !empty($filters['region']) || !empty($filters['activity_type']) || !empty($filters['tag_name'])) ? 'true' : 'false' ?>">
                <i class="bi bi-sliders2 text-success"></i>
                <span>Bộ lọc nâng cao (Độ khó, Vùng miền, Loại hình)</span>
                <?php if (!empty($filters['difficulty']) || !empty($filters['region']) || !empty($filters['activity_type']) || !empty($filters['tag_name'])): ?>
                    <span class="badge rounded-pill text-bg-success ms-1">Đang lọc</span>
                <?php endif; ?>
            </button>

            <?php if (!empty($popularTags)): ?>
                <div class="d-inline-flex align-items-center gap-1 flex-wrap">
                    <span class="small text-white-50 me-1"><i class="bi bi-tags me-1"></i>Tag:</span>
                    <?php foreach ($popularTags as $t): 
                        $isTagActive = ($filters['tag_name'] ?? '') === $t['name'];
                    ?>
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?tag=<?= urlencode($t['name']) ?>&sort=<?= urlencode($selectedSort) ?>" 
                           class="badge rounded-pill text-decoration-none <?= $isTagActive ? 'text-bg-success' : 'text-bg-dark border border-secondary border-opacity-50' ?>" 
                           style="font-size: 11px;">
                            #<?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Khung bộ lọc nâng cao (Collapsible) -->
        <div class="collapse <?= (!empty($filters['difficulty']) || !empty($filters['region']) || !empty($filters['activity_type']) || !empty($filters['tag_name'])) ? 'show' : '' ?> mb-4" id="advancedFilterCollapse">
            <div class="card border-0 rounded-4 p-3 p-md-4 shadow-sm" style="background: rgba(34, 40, 31, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                <form method="get" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="row g-3 align-items-end">
                    <?php if (!empty($selectedCategoryId)): ?>
                        <input type="hidden" name="category" value="<?= (int)$selectedCategoryId ?>">
                    <?php endif; ?>
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Loại hình -->
                    <div class="col-6 col-md-3">
                        <label class="nature-label text-white-50 small mb-1">Loại hình</label>
                        <select name="activity_type" class="form-select form-select-sm bg-dark text-white border-secondary border-opacity-50 rounded-pill">
                            <option value="">Tất cả loại hình</option>
                            <option value="camping" <?= (($filters['activity_type'] ?? '') === 'camping') ? 'selected' : '' ?>>Cắm trại (Camping)</option>
                            <option value="trekking" <?= (($filters['activity_type'] ?? '') === 'trekking') ? 'selected' : '' ?>>Đi bộ đường dài (Trekking)</option>
                            <option value="leo_nui" <?= (($filters['activity_type'] ?? '') === 'leo_nui') ? 'selected' : '' ?>>Leo núi (Hiking / Mountain)</option>
                            <option value="phuot" <?= (($filters['activity_type'] ?? '') === 'phuot') ? 'selected' : '' ?>>Phượt đường dài</option>
                            <option value="da_ngoai" <?= (($filters['activity_type'] ?? '') === 'da_ngoai') ? 'selected' : '' ?>>Dã ngoại ngoài trời</option>
                        </select>
                    </div>

                    <!-- Mức độ khó -->
                    <div class="col-6 col-md-3">
                        <label class="nature-label text-white-50 small mb-1">Mức độ khó</label>
                        <select name="difficulty" class="form-select form-select-sm bg-dark text-white border-secondary border-opacity-50 rounded-pill">
                            <option value="">Tất cả độ khó</option>
                            <option value="easy" <?= (($filters['difficulty'] ?? '') === 'easy') ? 'selected' : '' ?>>Dễ (Người mới, gia đình)</option>
                            <option value="medium" <?= (($filters['difficulty'] ?? '') === 'medium') ? 'selected' : '' ?>>Trung bình (Cần thể lực cơ bản)</option>
                            <option value="hard" <?= (($filters['difficulty'] ?? '') === 'hard') ? 'selected' : '' ?>>Khó (Đồi dốc, dài ngày)</option>
                            <option value="extreme" <?= (($filters['difficulty'] ?? '') === 'extreme') ? 'selected' : '' ?>>Thử thách cao (Chuyên nghiệp)</option>
                        </select>
                    </div>

                    <!-- Khu vực / Vùng miền -->
                    <div class="col-6 col-md-3">
                        <label class="nature-label text-white-50 small mb-1">Khu vực / Vùng miền</label>
                        <select name="region" class="form-select form-select-sm bg-dark text-white border-secondary border-opacity-50 rounded-pill">
                            <option value="">Tất cả khu vực</option>
                            <optgroup label="Việt Nam">
                                <option value="Tây Bắc" <?= (($filters['region'] ?? '') === 'Tây Bắc') ? 'selected' : '' ?>>Tây Bắc</option>
                                <option value="Đông Bắc" <?= (($filters['region'] ?? '') === 'Đông Bắc') ? 'selected' : '' ?>>Đông Bắc</option>
                                <option value="Bắc Bộ" <?= (($filters['region'] ?? '') === 'Bắc Bộ') ? 'selected' : '' ?>>Miền Bắc</option>
                                <option value="Bắc Trung Bộ" <?= (($filters['region'] ?? '') === 'Bắc Trung Bộ') ? 'selected' : '' ?>>Bắc Trung Bộ</option>
                                <option value="Nam Trung Bộ" <?= (($filters['region'] ?? '') === 'Nam Trung Bộ') ? 'selected' : '' ?>>Nam Trung Bộ</option>
                                <option value="Tây Nguyên" <?= (($filters['region'] ?? '') === 'Tây Nguyên') ? 'selected' : '' ?>>Tây Nguyên</option>
                                <option value="Đông Nam Bộ" <?= (($filters['region'] ?? '') === 'Đông Nam Bộ') ? 'selected' : '' ?>>Đông Nam Bộ</option>
                                <option value="Tây Nam Bộ" <?= (($filters['region'] ?? '') === 'Tây Nam Bộ') ? 'selected' : '' ?>>Tây Nam Bộ</option>
                            </optgroup>
                            <optgroup label="Quốc tế (Nước ngoài)">
                                <option value="Đông Nam Á" <?= (($filters['region'] ?? '') === 'Đông Nam Á') ? 'selected' : '' ?>>Đông Nam Á</option>
                                <option value="Châu Á" <?= (($filters['region'] ?? '') === 'Châu Á') ? 'selected' : '' ?>>Châu Á</option>
                                <option value="Châu Âu" <?= (($filters['region'] ?? '') === 'Châu Âu') ? 'selected' : '' ?>>Châu Âu</option>
                                <option value="Châu Mỹ" <?= (($filters['region'] ?? '') === 'Châu Mỹ') ? 'selected' : '' ?>>Châu Mỹ</option>
                                <option value="Châu Úc & Khác" <?= (($filters['region'] ?? '') === 'Châu Úc & Khác') ? 'selected' : '' ?>>Châu Úc / Khác</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Nút Áp dụng & Xóa lọc -->
                    <div class="col-6 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-success rounded-pill flex-grow-1">
                            <i class="bi bi-funnel-fill me-1"></i> Lọc bài
                        </button>
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts<?= !empty($selectedCategoryId) ? '?category=' . (int)$selectedCategoryId : '' ?>" 
                           class="btn btn-sm btn-outline-secondary rounded-pill text-white-50" title="Xóa bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <style>
            .sort-pill {
                display: inline-flex;
                align-items: center;
                padding: 5px 14px;
                border-radius: 999px;
                font-size: 0.82rem;
                font-weight: 600;
                color: rgba(241, 241, 236, 0.72);
                text-decoration: none;
                transition: all 0.18s ease;
            }
            .sort-pill:hover {
                color: #f1f1ec;
                background: rgba(255, 255, 255, 0.08);
            }
            .sort-pill.active {
                background: #9bbd55;
                color: #1c2219;
                box-shadow: 0 2px 8px rgba(155, 189, 85, 0.35);
            }
        </style>
    <?php endif; ?>

    <!-- Post list grid -->
    <?php require __DIR__ . '/../partials/post-cards.php'; ?>

    <!-- AJAX Tải thêm bài viết & Tự động cuộn (Infinite Scroll - Phúc) -->
    <div class="text-center my-4" id="load-more-container">
        <?php if ($currentPage < $totalPages): ?>
            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                <button type="button" 
                        id="btnLoadMorePosts" 
                        class="nature-btn-secondary px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm"
                        data-current-page="<?= $currentPage ?>"
                        data-total-pages="<?= $totalPages ?>"
                        data-category-id="<?= htmlspecialchars((string)($selectedCategoryId ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort="<?= htmlspecialchars($selectedSort, ENT_QUOTES, 'UTF-8') ?>">
                    <i class="bi bi-arrow-down-circle-fill" style="color: var(--color-moss, #9bbd55);"></i>
                    <span class="btn-text">Tải thêm bài viết</span>
                </button>
                <div class="form-check form-switch d-inline-flex align-items-center gap-2 text-white-50 small">
                    <input class="form-check-input" type="checkbox" id="toggleInfiniteScroll">
                    <label class="form-check-label" for="toggleInfiniteScroll">Tự động cuộn (Infinite Scroll)</label>
                </div>
            </div>
        <?php endif; ?>
        <div id="no-more-posts-msg" class="text-white-50 small py-3" style="display: <?= ($currentPage >= $totalPages && $totalPosts > 0) ? 'block' : 'none' ?>;">
            <i class="bi bi-check2-circle text-success me-1"></i>Bạn đã xem hết tất cả bài viết.
        </div>
    </div>

    <!-- Thanh phân trang Bootstrap (FR03 - Tuần 4 - Phúc) -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Điều hướng phân trang bài viết" class="mt-5 mb-4">
            <ul class="pagination forum-pagination justify-content-center flex-wrap">
                <!-- Nút trang trước -->
                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill" 
                       href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $filterQuery ?>page=<?= max(1, $currentPage - 1) ?>" 
                       tabindex="<?= ($currentPage <= 1) ? '-1' : '0' ?>"
                       aria-disabled="<?= ($currentPage <= 1) ? 'true' : 'false' ?>">
                        <i class="bi bi-chevron-left me-1"></i> Trước
                    </a>
                </li>

                <!-- Danh sách số trang -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $filterQuery ?>page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Nút trang sau -->
                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill" 
                       href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?<?= $filterQuery ?>page=<?= min($totalPages, $currentPage + 1) ?>" 
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
    // Khởi tạo tính năng AJAX: Tìm kiếm gợi ý & Tải thêm bài viết (Phúc)
    if (window.ForumAjax) {
        window.ForumAjax.initSearchAutocomplete('forumSearchInput', 'searchSuggestionsDropdown');
        window.ForumAjax.initInfiniteScroll({
            btnId: 'btnLoadMorePosts',
            rowId: 'post-cards-row'
        });
    }
});
</script>