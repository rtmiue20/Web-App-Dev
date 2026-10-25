<?php
$title = $post['title'] ?? 'Chi tiết bài viết';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$isLoggedIn = Session::has('user');
$currentUser = Session::get('user');
$currentUserId = is_array($currentUser) ? (int)($currentUser['id'] ?? 0) : 0;
$isAdmin = is_array($currentUser) && ((($currentUser['role_name'] ?? '') === 'admin') || ((int)($currentUser['role_id'] ?? 0) === 2));
$isLiked = !empty($isLiked);

// ── Collect gallery images (Từ nhánh main) ────────────────────────
$galleryImages = [];
if (!empty($post['images']) && is_array($post['images'])) {
    foreach ($post['images'] as $img) {
        $url = $img['image_url'] ?? '';
        if (!empty($url)) {
            $galleryImages[] = str_starts_with($url, 'http')
                ? $url
                : $baseUrl . '/' . ltrim($url, '/');
        }
    }
}
if (empty($galleryImages) && !empty($post['image_url'])) {
    $url = $post['image_url'];
    $galleryImages[] = str_starts_with($url, 'http') ? $url : $baseUrl . '/' . ltrim($url, '/');
}
if (empty($galleryImages)) {
    $galleryImages[] = $baseUrl . '/assets/img/ta-xua-trek.png';
}
$galleryCount = count($galleryImages);

// ── Pagination variables for comments (Từ nhánh PhucVuong) ────────
$totalCommentsCount = count($comments ?? []);
$pageSize = 5;
$firstPage = array_slice($comments ?? [], 0, $pageSize);
?>
<div class="container py-5 mt-5" style="max-width: 900px;">
    <!-- Nút quay lại danh sách -->
    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="btn btn-outline-light rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách bài viết
        </a>
    </div>

<!-- ══════════════════════════════════════════
         IMAGE CAROUSEL (Nhiều ảnh)
    ══════════════════════════════════════════ -->
    <div class="post-carousel-wrap mb-4" id="postCarousel">

        <!-- Slide viewport -->
        <div class="pc-viewport" id="pcViewport"
             ontouchstart="CarouselLB.touchStart(event)"
             ontouchmove="CarouselLB.touchMove(event)"
             ontouchend="CarouselLB.touchEnd(event)">
            <?php foreach ($galleryImages as $idx => $imgUrl): ?>
                <div class="pc-slide <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>">
                    <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                         alt="Ảnh <?= $idx + 1 ?> / <?= $galleryCount ?>"
                         class="pc-img"
                         onclick="CarouselLB.openLightbox(<?= $idx ?>)"
                         title="Bấm để xem toàn màn hình">
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($galleryCount > 1): ?>
        <!-- Prev / Next arrows -->
        <button class="pc-btn pc-btn-prev" onclick="CarouselLB.prev()" aria-label="Ảnh trước">
            <i class="bi bi-chevron-compact-left"></i>
        </button>
        <button class="pc-btn pc-btn-next" onclick="CarouselLB.next()" aria-label="Ảnh sau">
            <i class="bi bi-chevron-compact-right"></i>
        </button>

        <!-- Counter & play/pause -->
        <div class="pc-footer">
            <span class="pc-counter" id="pcCounter">1 / <?= $galleryCount ?></span>
            <button class="pc-playpause" id="pcPlayPause" onclick="CarouselLB.togglePlay()" title="Tự động / Dừng">
                <i class="bi bi-pause-circle-fill" id="pcPlayIcon"></i>
            </button>
        </div>

        <!-- Dot indicators -->
        <div class="pc-dots" id="pcDots">
            <?php for ($i = 0; $i < $galleryCount; $i++): ?>
                <span class="pc-dot <?= $i === 0 ? 'active' : '' ?>"
                      onclick="CarouselLB.goTo(<?= $i ?>)"
                      aria-label="Ảnh <?= $i + 1 ?>"></span>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════
         LIGHTBOX MODAL
    ══════════════════════════════════════════ -->
    <div class="lb-overlay" id="lbOverlay" onclick="CarouselLB.closeLightbox()" style="display:none;">
        <div class="lb-box" onclick="event.stopPropagation()">
            <button class="lb-close" onclick="CarouselLB.closeLightbox()" aria-label="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
            <?php if ($galleryCount > 1): ?>
            <button class="lb-nav lb-nav-prev" onclick="CarouselLB.lbPrev()" aria-label="Ảnh trước">
                <i class="bi bi-chevron-compact-left"></i>
            </button>
            <button class="lb-nav lb-nav-next" onclick="CarouselLB.lbNext()" aria-label="Ảnh sau">
                <i class="bi bi-chevron-compact-right"></i>
            </button>
            <?php endif; ?>
            <img src="" alt="" id="lbImg" class="lb-img">
            <div class="lb-caption" id="lbCaption"></div>
        </div>
    </div>

    <!-- Nội dung bài viết (Bootstrap Card) -->
    <article class="card border-0 shadow-lg rounded-4 bg-light text-dark mb-5">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <span class="badge rounded-pill text-bg-success px-3 py-2">
                    <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($post['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?>
                </span>

                <?php if (!empty($post['location'])): ?>
                    <span class="badge rounded-pill text-bg-secondary px-3 py-2"
                          id="weather-badge"
                          data-lat="<?= htmlspecialchars((string)($post['lat'] ?? '21.28'), ENT_QUOTES, 'UTF-8') ?>"
                          data-lng="<?= htmlspecialchars((string)($post['lng'] ?? '104.31'), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($post['location'], ENT_QUOTES, 'UTF-8') ?>
                        <span id="weather-info" class="ms-1"></span>
                    </span>
                <?php endif; ?>
            </div>

            <h1 class="h2 fw-semibold mb-3 text-dark">
                <?= htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <!-- Thanh thông tin tác giả & nút tương tác (FR05, FR08) -->
            <div class="d-flex flex-wrap align-items-center gap-3 pb-3 mb-4 border-bottom text-secondary small">
                <span>
                    <i class="bi bi-person-circle me-1"></i>
                    <strong class="text-dark"><?= htmlspecialchars($post['username'] ?? 'Tác giả', ENT_QUOTES, 'UTF-8') ?></strong>
                </span>
                <span>
                    <i class="bi bi-calendar-event me-1"></i>
                    <?= !empty($post['created_at']) ? date('d/m/Y H:i', strtotime($post['created_at'])) : '' ?>
                </span>

                <div class="ms-auto d-flex flex-wrap align-items-center gap-2">
                    <?php if (!empty($canManage)): ?>
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/edit"
                           class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                           title="Chỉnh sửa bài viết">
                            <i class="bi bi-pencil-square me-1"></i> Sửa
                        </a>
                        <form method="post"
                              action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/delete"
                              class="d-inline m-0"
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                    title="Xóa bài viết">
                                <i class="bi bi-trash3 me-1"></i> Xóa
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Nút Thích / Bỏ thích bằng AJAX (FR08) -->
                    <button type="button"
                            id="btn-like-post"
                            class="btn btn-sm <?= $isLiked ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3"
                            data-post-id="<?= (int)$post['id'] ?>"
                            aria-pressed="<?= $isLiked ? 'true' : 'false' ?>">
                        <i class="bi <?= $isLiked ? 'bi-heart-fill' : 'bi-heart' ?> me-1"></i>
                        <span class="like-count fw-semibold"><?= (int)($post['likes'] ?? 0) ?></span> thích
                    </button>
                </div>
            </div>

            <!-- Nội dung bài viết -->
            <div class="fs-6 lh-lg text-dark">
                <?= nl2br(htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </div>
    </article>

    <!-- Khu vực Bình luận AJAX (FR07 - Vương) -->
    <section class="mt-4" aria-label="Bình luận bài viết">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h4 text-white mb-0">
                <i class="bi bi-chat-left-text-fill text-success me-2"></i>Bình luận
                <span class="badge rounded-pill text-bg-secondary ms-2" id="comments-total-badge"><?= $totalCommentsCount ?></span>
            </h2>
        </div>

        <!-- Form gửi bình luận bằng AJAX -->
        <?php if ($isLoggedIn): ?>
            <div class="card border-0 shadow rounded-4 bg-light text-dark mb-4">
                <div class="card-body p-4">
                    <form id="comment-form" novalidate>
                        <div class="mb-3">
                            <label for="comment-content" class="form-label fw-semibold">
                                Viết cảm nghĩ hoặc câu hỏi của bạn
                            </label>
                            <textarea class="form-control rounded-3"
                                      id="comment-content"
                                      name="content"
                                      rows="3"
                                      required
                                      maxlength="1000"
                                      placeholder="Chia sẻ kinh nghiệm hoặc đặt câu hỏi cho tác giả..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                <i class="bi bi-send me-1"></i> Gửi bình luận
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary border-0 rounded-4 d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4" role="alert">
                <span><i class="bi bi-info-circle me-1"></i> Vui lòng đăng nhập để tham gia thảo luận và gửi bình luận.</span>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="btn btn-sm btn-dark rounded-pill px-3">
                    Đăng nhập ngay
                </a>
            </div>
        <?php endif; ?>

        <!-- Danh sách bình luận (FR07) -->
        <div id="comments-list" class="d-flex flex-column gap-3" data-post-id="<?= (int)$post['id'] ?>" data-page="1">
            <?php if (!empty($firstPage)): ?>
                <?php foreach ($firstPage as $cm):
                    $canDeleteComment = ($currentUserId > 0 && ($currentUserId === (int)($cm['user_id'] ?? 0) || $isAdmin));
                ?>
                    <div class="card border-0 shadow-sm rounded-4 bg-dark bg-opacity-50 text-white comment-card" data-comment-id="<?= (int)$cm['id'] ?>">
                        <div class="card-body p-3 px-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-success">
                                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($cm['username'] ?? 'Thành viên', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-white-50 small">
                                        <?= !empty($cm['created_at']) ? date('d/m/Y H:i', strtotime($cm['created_at'])) : '' ?>
                                    </span>
                                    <?php if ($canDeleteComment): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0 btn-delete-comment"
                                                data-comment-id="<?= (int)$cm['id'] ?>"
                                                title="Xóa bình luận của bạn">
                                            <i class="bi bi-trash3 me-1"></i>Xóa
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="comment-text text-light" style="white-space: pre-line;"><?= htmlspecialchars($cm['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-dark border-secondary border-opacity-25 rounded-4 text-white-50 fst-italic mb-0" id="no-comments-msg">
                    Chưa có bình luận nào. Hãy để lại bình luận đầu tiên!
                </div>
            <?php endif; ?>
        </div>

        <!-- Nút Xem thêm bình luận (Load More AJAX) -->
        <?php if ($totalCommentsCount > $pageSize): ?>
            <div class="text-center mt-4">
                <button type="button" id="btn-load-more" class="btn btn-outline-light rounded-pill px-4 py-2">
                    <i class="bi bi-chevron-down me-1"></i> Xem thêm bình luận
                </button>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Template Bootstrap cho 1 bình luận mới / tải thêm (Từ nhánh PhucVuong) -->
<template id="comment-template">
    <div class="card border-0 shadow-sm rounded-4 bg-dark bg-opacity-50 text-white comment-card" data-comment-id="">
        <div class="card-body p-3 px-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold text-success">
                    <i class="bi bi-person-circle me-1"></i><span class="js-username"></span>
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white-50 small js-time"></span>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0 btn-delete-comment d-none" title="Xóa bình luận của bạn">
                        <i class="bi bi-trash3 me-1"></i>Xóa
                    </button>
                </div>
            </div>
            <div class="comment-text text-light js-content" style="white-space: pre-line;"></div>
        </div>
    </div>
</template>

<!-- ══════════════════════════════════════════════════════════════════
     CAROUSEL + LIGHTBOX STYLES (Từ nhánh main)
══════════════════════════════════════════════════════════════════ -->
<style>
/* ── Carousel Wrapper ── */
.post-carousel-wrap {
    position: relative;
    width: 100%;
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.4);
    background: #0d0d0d;
    user-select: none;
}

/* ── Viewport ── */
.pc-viewport {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
    cursor: grab;
}
.pc-viewport:active { cursor: grabbing; }

/* ── Slides ── */
.pc-slide {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: translateX(60px);
    transition: opacity 0.4s ease, transform 0.4s ease;
    pointer-events: none;
}
.pc-slide.active {
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
}
.pc-slide.slide-out-left {
    opacity: 0;
    transform: translateX(-60px);
}
.pc-slide.slide-out-right {
    opacity: 0;
    transform: translateX(60px);
}
.pc-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    cursor: zoom-in;
}

/* ── Prev / Next Buttons ── */
.pc-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.55);
    border: none;
    color: #fff;
    font-size: 1.6rem;
    width: 44px;
    height: 64px;
    border-radius: 12px;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, transform 0.15s;
}
.pc-btn:hover { background: rgba(0,0,0,0.8); transform: translateY(-50%) scale(1.08); }
.pc-btn-prev { left: 10px; }
.pc-btn-next { right: 10px; }

/* ── Footer (counter + play/pause) ── */
.pc-footer {
    position: absolute;
    bottom: 48px;
    right: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 10;
}
.pc-counter {
    background: rgba(0,0,0,0.6);
    color: #fff;
    font-size: 0.78rem;
    padding: 3px 10px;
    border-radius: 20px;
    font-variant-numeric: tabular-nums;
}
.pc-playpause {
    background: none;
    border: none;
    color: rgba(255,255,255,0.85);
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    transition: color 0.2s, transform 0.15s;
}
.pc-playpause:hover { color: #fff; transform: scale(1.15); }

/* ── Dot Indicators ── */
.pc-dots {
    position: absolute;
    bottom: 14px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    z-index: 10;
}
.pc-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.4);
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
}
.pc-dot.active {
    background: #fff;
    transform: scale(1.35);
}

/* ══ LIGHTBOX ══════════════════════════════════════ */
.lb-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.92);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: lbFadeIn 0.22s ease;
}
@keyframes lbFadeIn { from { opacity: 0; } to { opacity: 1; } }

.lb-box {
    position: relative;
    max-width: 95vw;
    max-height: 95vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.lb-img {
    max-width: 90vw;
    max-height: 85vh;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.6);
    display: block;
}
.lb-close {
    position: fixed;
    top: 18px;
    right: 22px;
    background: rgba(0,0,0,0.6);
    border: none;
    color: #fff;
    font-size: 1.35rem;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10001;
    transition: background 0.2s;
}
.lb-close:hover { background: rgba(220,50,50,0.8); }

.lb-nav {
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.55);
    border: none;
    color: #fff;
    font-size: 2rem;
    width: 52px;
    height: 80px;
    border-radius: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10001;
    transition: background 0.2s;
}
.lb-nav:hover { background: rgba(0,0,0,0.85); }
.lb-nav-prev { left: 12px; }
.lb-nav-next { right: 12px; }

.lb-caption {
    margin-top: 12px;
    color: rgba(255,255,255,0.7);
    font-size: 0.82rem;
    text-align: center;
}
</style>

<!-- ══════════════════════════════════════════════════════════════════
     CAROUSEL + LIGHTBOX JAVASCRIPT (Đã lược bỏ Ajax rác)
══════════════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';

    const IMAGES  = <?= json_encode($galleryImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const TOTAL   = IMAGES.length;
    const AUTO_MS = 1000; // 1 second auto-cycle

    let current   = 0;
    let playing   = TOTAL > 1;
    let timer     = null;
    let touchX0   = 0;
    let isDragging = false;

    // ── DOM refs ──────────────────────────────────────────────────
    const slides    = document.querySelectorAll('.pc-slide');
    const dots      = document.querySelectorAll('.pc-dot');
    const counter   = document.getElementById('pcCounter');
    const playIcon  = document.getElementById('pcPlayIcon');
    const viewport  = document.getElementById('pcViewport');
    const lbOverlay = document.getElementById('lbOverlay');
    const lbImg     = document.getElementById('lbImg');
    const lbCaption = document.getElementById('lbCaption');

    // ── Core: go to slide index ───────────────────────────────────
    function goToSlide(next, dir) {
        if (TOTAL <= 1) return;
        if (next === current) return;

        const outClass = dir === 'left' ? 'slide-out-left' : 'slide-out-right';

        // Animate out current
        slides[current].classList.remove('active');
        slides[current].classList.add(outClass);
        setTimeout(() => slides[current]?.classList.remove(outClass), 450);

        current = (next + TOTAL) % TOTAL;

        // Animate in next
        slides[current].classList.add('active');

        // Update dots + counter
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
        if (counter) counter.textContent = (current + 1) + ' / ' + TOTAL;
    }

    // ── Public API (called from onclick) ──────────────────────────
    window.CarouselLB = {
        goTo(idx) {
            const dir = idx > current ? 'left' : 'right';
            goToSlide(idx, dir);
        },
        next() {
            goToSlide(current + 1, 'left');
        },
        prev() {
            goToSlide(current - 1, 'right');
        },
        togglePlay() {
            playing = !playing;
            if (playIcon) {
                playIcon.className = playing
                    ? 'bi bi-pause-circle-fill'
                    : 'bi bi-play-circle-fill';
            }
            if (playing) startTimer(); else stopTimer();
        },

        // ── Lightbox ──
        openLightbox(idx) {
            if (!lbOverlay || !lbImg) return;
            current = (idx + TOTAL) % TOTAL;
            lbImg.src = IMAGES[current];
            lbImg.alt = 'Ảnh ' + (current + 1) + ' / ' + TOTAL;
            if (lbCaption) lbCaption.textContent = TOTAL > 1 ? (current + 1) + ' / ' + TOTAL : '';
            lbOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            stopTimer();
        },
        closeLightbox() {
            if (!lbOverlay) return;
            lbOverlay.style.display = 'none';
            document.body.style.overflow = '';
            if (playing) startTimer();
        },
        lbPrev() {
            current = (current - 1 + TOTAL) % TOTAL;
            if (lbImg) { lbImg.src = IMAGES[current]; lbImg.alt = 'Ảnh ' + (current+1) + ' / ' + TOTAL; }
            if (lbCaption) lbCaption.textContent = (current + 1) + ' / ' + TOTAL;
            dots.forEach((d, i) => d.classList.toggle('active', i === current));
            if (counter) counter.textContent = (current + 1) + ' / ' + TOTAL;
        },
        lbNext() {
            current = (current + 1) % TOTAL;
            if (lbImg) { lbImg.src = IMAGES[current]; lbImg.alt = 'Ảnh ' + (current+1) + ' / ' + TOTAL; }
            if (lbCaption) lbCaption.textContent = (current + 1) + ' / ' + TOTAL;
            dots.forEach((d, i) => d.classList.toggle('active', i === current));
            if (counter) counter.textContent = (current + 1) + ' / ' + TOTAL;
        },

        // ── Touch/swipe handlers ──
        touchStart(e) {
            touchX0 = e.touches[0].clientX;
            isDragging = true;
        },
        touchMove(e) {
            if (!isDragging) return;
            e.preventDefault();
        },
        touchEnd(e) {
            if (!isDragging) return;
            isDragging = false;
            const delta = e.changedTouches[0].clientX - touchX0;
            if (Math.abs(delta) > 40) {
                delta < 0 ? CarouselLB.next() : CarouselLB.prev();
            }
        }
    };

    // ── Auto-play timer ───────────────────────────────────────────
    function startTimer() {
        stopTimer();
        if (TOTAL <= 1) return;
        timer = setInterval(() => {
            goToSlide(current + 1, 'left');
        }, AUTO_MS);
    }
    function stopTimer() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    // ── Pause on hover ────────────────────────────────────────────
    if (viewport) {
        viewport.addEventListener('mouseenter', stopTimer);
        viewport.addEventListener('mouseleave', () => { if (playing) startTimer(); });
    }

    // ── Keyboard navigation ───────────────────────────────────────
    document.addEventListener('keydown', function(e) {
        if (lbOverlay && lbOverlay.style.display !== 'none') {
            if (e.key === 'ArrowLeft')  CarouselLB.lbPrev();
            if (e.key === 'ArrowRight') CarouselLB.lbNext();
            if (e.key === 'Escape')     CarouselLB.closeLightbox();
        } else {
            if (e.key === 'ArrowLeft')  CarouselLB.prev();
            if (e.key === 'ArrowRight') CarouselLB.next();
        }
    });

    if (playing) startTimer();
})();
</script>
