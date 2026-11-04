<?php
$title = $post['title'] ?? 'Chi tiết bài viết';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$isLoggedIn = Session::has('user');
$coverImg = !empty($post['image_url']) 
    ? (str_starts_with($post['image_url'], 'http') ? $post['image_url'] : $baseUrl . '/' . ltrim($post['image_url'], '/'))
    : $baseUrl . '/assets/img/ta-xua-trek.png';
?>
<div class="app-container" style="max-width: 900px;">
    <!-- Navigation back -->
    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="nature-btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Cover Image -->
    <div class="post-detail-cover">
        <img src="<?= htmlspecialchars($coverImg, ENT_QUOTES, 'UTF-8') ?>" 
             alt="<?= htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <!-- Article Content Box -->
    <article class="post-content-box">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <span class="card-meta-tag mb-0">
                <?= htmlspecialchars($post['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?>
            </span>
            
            <?php if (!empty($post['location']) || (!empty($post['lat']) && !empty($post['lng']))): ?>
                <span class="badge text-bg-secondary" id="weather-badge">
                    <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($post['location'] ?: 'Địa điểm bài viết', ENT_QUOTES, 'UTF-8') ?>
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

    <?php if (!empty($post['location'])): ?>
        <section class="nature-card p-4 mt-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h2 class="h4 mb-0">Đánh giá địa điểm</h2>
                <span class="fw-semibold">
                    <?php if ((int) $placeRating['review_count'] > 0): ?>
                        ★ <?= number_format((float) $placeRating['average_rating'], 1) ?>/5
                        <span class="text-muted small">(<?= (int) $placeRating['review_count'] ?> đánh giá)</span>
                    <?php else: ?>
                        <span class="text-muted">Chưa có đánh giá</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if ($isLoggedIn): ?>
                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int) $post['id'] ?>/reviews" class="row g-3 mt-2">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="col-md-3">
                        <label class="form-label" for="place-rating">Số sao</label>
                        <select class="form-select" id="place-rating" name="rating" required>
                            <option value="">Chọn sao</option>
                            <option value="5">5 - Rất tốt</option>
                            <option value="4">4 - Tốt</option>
                            <option value="3">3 - Bình thường</option>
                            <option value="2">2 - Chưa tốt</option>
                            <option value="1">1 - Không tốt</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="place-review">Nhận xét</label>
                        <textarea class="form-control" id="place-review" name="review" rows="3" minlength="5" maxlength="1000" required
                                  placeholder="Chia sẻ đường đi, cảnh quan hoặc lưu ý tại địa điểm..."></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button class="nature-btn-primary" type="submit">Gửi đánh giá</button>
                    </div>
                </form>
            <?php else: ?>
                <p class="mt-3 mb-0"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login">Đăng nhập</a> để đánh giá địa điểm.</p>
            <?php endif; ?>

            <div class="mt-4">
                <?php foreach ($placeReviews as $review): ?>
                    <article class="border-top py-3">
                        <div class="d-flex justify-content-between gap-2">
                            <strong><?= htmlspecialchars($review['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span>★ <?= (int) $review['rating'] ?>/5</span>
                        </div>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($review['review'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($post['location']) && (empty($post['lat']) || empty($post['lng']))): ?>
        <div class="alert alert-warning mt-3" role="status">
            Chưa có tọa độ địa điểm; không thể tải cảnh báo thời tiết. Hãy kiểm tra dự báo địa phương trước chuyến đi.
        </div>
    <?php elseif (!empty($post['lat']) && !empty($post['lng'])): ?>
        <div id="post-weather-warning" class="d-none mt-3" role="status"></div>
    <?php endif; ?>

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

<script>
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

// Fetch weather if location is provided
document.addEventListener('DOMContentLoaded', () => {
    const weatherElem = document.getElementById('weather-info');
    if (weatherElem) {
        <?php if (!empty($post['lat']) && !empty($post['lng'])): ?>
            ForumAjax.fetchWeather(
                <?= json_encode((string) $post['lat']) ?>,
                <?= json_encode((string) $post['lng']) ?>,
                weatherElem,
                document.getElementById('post-weather-warning')
            );
        <?php endif; ?>
    }
});
</script>
