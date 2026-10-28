<?php
$title = $post['title'] ?? 'Chi tiết bài viết';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$isLoggedIn = Session::has('user');

// ── Collect gallery images ──────────────────────────────────────────
// Priority 1: entries from post_images table (attached by getById())
// Priority 2: single image_url column (legacy)
// Priority 3: default placeholder
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
?>
<div class="app-container" style="max-width: 900px;">
    <!-- Navigation back -->
    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="nature-btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <?php 
    $currentPostStatus = $post['status'] ?? 'published';
    if ($currentPostStatus === 'pending'): ?>
        <div class="alert alert-warning d-flex align-items-center gap-3 rounded-4 shadow-sm mb-4 p-3 border-warning" role="alert">
            <i class="bi bi-hourglass-split fs-3 text-warning"></i>
            <div>
                <strong class="d-block mb-1">Bài viết đang chờ duyệt (Pending)</strong>
                <span class="small text-muted">Bài viết này hiện chưa hiển thị công khai trên diễn đàn. Chỉ bạn (tác giả) và Quản trị viên mới có thể xem được nội dung này cho đến khi được duyệt.</span>
            </div>
        </div>
    <?php elseif ($currentPostStatus === 'rejected'): ?>
        <div class="alert alert-danger d-flex align-items-center gap-3 rounded-4 shadow-sm mb-4 p-3 border-danger" role="alert">
            <i class="bi bi-x-circle-fill fs-3 text-danger"></i>
            <div>
                <strong class="d-block mb-1">Bài viết bị từ chối (Rejected)</strong>
                <span class="small text-muted">Bài viết này không được Quản trị viên duyệt và không xuất hiện trên diễn đàn.</span>
            </div>
        </div>
    <?php elseif ($currentPostStatus === 'hidden'): ?>
        <div class="alert alert-secondary d-flex align-items-center gap-3 rounded-4 shadow-sm mb-4 p-3 border-secondary" role="alert">
            <i class="bi bi-eye-slash-fill fs-3 text-secondary"></i>
            <div>
                <strong class="d-block mb-1">Bài viết đã bị ẩn (Hidden)</strong>
                <span class="small text-muted">Bài viết này đã được tạm ẩn khỏi diễn đàn công khai.</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════
         IMAGE CAROUSEL
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

    <!-- Article Content Box -->
    <article class="post-content-box">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <span class="card-meta-tag mb-0">
                <?= htmlspecialchars($post['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?>
            </span>
            
            <?php if (!empty($post['location'])): ?>
                <span class="badge text-bg-secondary" id="weather-badge">
                    <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($post['location'], ENT_QUOTES, 'UTF-8') ?>
                    <span id="weather-info" class="ms-2"></span>
                </span>
            <?php endif; ?>
        </div>

        <h1 class="h2 fw-normal mb-3" style="color: var(--color-ink);">
            <?= htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <div class="post-meta-bar">
            <span>
                <i class="bi bi-person-circle me-1"></i>
                <strong><?= htmlspecialchars($post['username'] ?? 'Tác giả', ENT_QUOTES, 'UTF-8') ?></strong>
            </span>
            <span>
                <i class="bi bi-calendar-event me-1"></i>
                <?= !empty($post['created_at']) ? date('d/m/Y H:i', strtotime($post['created_at'])) : '' ?>
            </span>
            <span class="ms-auto d-flex align-items-center gap-2">
                <?php if (!empty($canManage)): ?>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/edit" 
                       class="nature-btn-secondary py-1 px-3 text-dark border-dark-subtle"
                       title="Chỉnh sửa bài viết">
                        <i class="bi bi-pencil-square me-1"></i> Sửa
                    </a>
                    <form method="post" 
                          action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/delete" 
                          class="d-inline" 
                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không? Hành động này không thể hoàn tác.');">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" 
                                class="nature-btn-secondary py-1 px-3 text-danger border-danger-subtle"
                                title="Xóa bài viết">
                            <i class="bi bi-trash3 me-1"></i> Xóa
                        </button>
                    </form>
                <?php endif; ?>
                <button type="button" 
                        id="btn-like-post" 
                        class="nature-btn-secondary py-1 px-3 text-dark border-dark-subtle"
                        onclick="ForumAjax.toggleLike(<?= (int)$post['id'] ?>, this)">
                    <i class="bi bi-heart-fill text-danger me-1"></i> 
                    <span class="like-count"><?= (int)($post['likes'] ?? 0) ?></span> thích
                </button>
            </span>
        </div>

        <div class="post-body">
            <?= nl2br(htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
        </div>
    </article>

    <!-- Comments Section -->
    <section class="mt-5" aria-label="Bình luận">
        <h2 class="h4 mb-4 text-white">
            <i class="bi bi-chat-left-text-fill me-2" style="color: var(--color-moss);"></i>Bình luận
        </h2>

        <!-- Comment input form -->
        <?php if ($isLoggedIn): ?>
            <div class="nature-card mb-4" style="border-radius: 28px; padding: 1.5rem;">
                <form id="comment-form" onsubmit="event.preventDefault(); submitComment();">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3">
                        <label for="comment-content" class="nature-label">Viết cảm nghĩ hoặc câu hỏi của bạn</label>
                        <textarea class="nature-textarea" 
                                  id="comment-content" 
                                  rows="3" 
                                  required 
                                  placeholder="Chia sẻ kinh nghiệm hoặc đặt câu hỏi cho tác giả..."></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto; height: 42px;">
                            <i class="bi bi-send me-1"></i> Gửi bình luận
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="nature-alert nature-alert-success mb-4 d-flex justify-content-between align-items-center">
                <span>Vui lòng đăng nhập để tham gia thảo luận và gửi bình luận.</span>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-secondary py-1 px-3">
                    Đăng nhập
                </a>
            </div>
        <?php endif; ?>

        <!-- List of Comments -->
        <div id="comments-list">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $cm): ?>
                    <div class="comment-card">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="comment-author">
                                <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($cm['username'] ?? 'Người dùng', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="comment-time">
                                <?= !empty($cm['created_at']) ? date('d/m/Y H:i', strtotime($cm['created_at'])) : '' ?>
                            </span>
                        </div>
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($cm['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-white-50 small fst-italic py-3" id="no-comments-msg">
                    Chưa có bình luận nào. Hãy để lại lời nhắn đầu tiên!
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     CAROUSEL + LIGHTBOX STYLES
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
     CAROUSEL + LIGHTBOX JAVASCRIPT
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
            // Pause auto-play while lightbox is open
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
            // Sync carousel
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

    // ── Kick off auto-play ────────────────────────────────────────
    if (playing) startTimer();
})();

// ── Comments ──────────────────────────────────────────────────────
function submitComment() {
    const input = document.getElementById('comment-content');
    if (!input || !input.value.trim()) return;

    const list = document.getElementById('comments-list');
    ForumAjax.postComment(<?= (int)$post['id'] ?>, input.value, list).then(success => {
        if (success) {
            input.value = '';
            const noMsg = document.getElementById('no-comments-msg');
            if (noMsg) noMsg.remove();
        }
    });
}

// ── Weather ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const weatherElem = document.getElementById('weather-info');
    if (weatherElem) {
        <?php if (!empty($post['lat']) && !empty($post['lng'])): ?>
            ForumAjax.fetchWeather('<?= htmlspecialchars((string)$post['lat'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars((string)$post['lng'], ENT_QUOTES, 'UTF-8') ?>', weatherElem);
        <?php else: ?>
            // Default: Northern Vietnam mountains (Ta Xua)
            ForumAjax.fetchWeather('21.28', '104.31', weatherElem);
        <?php endif; ?>
    }
});
</script>
