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
            <span class="ms-auto">
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
        // Sample coords for Da Lat or Northern Mountains (Ta Xua)
        ForumAjax.fetchWeather('21.28', '104.31', weatherElem);
    }
});
</script>
