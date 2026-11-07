<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$defaultImages = [
    $baseUrl . '/assets/img/tent-forest.png',
    $baseUrl . '/assets/img/ta-xua-trek.png',
];
?>
<input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
<?php if (empty($posts)): ?>
    <div class="nature-card text-center py-5 my-4">
        <i class="bi bi-compass fs-1 text-muted d-block mb-3"></i>
        <h3 class="h5 fw-normal mb-2">Chưa có bài viết nào</h3>
        <p class="text-muted small mb-3">Hãy là người đầu tiên chia sẻ hành trình của mình!</p>
        <?php if (class_exists('Auth') && Auth::isMember()): ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-secondary d-inline-flex">
                <i class="bi bi-pencil-square me-1"></i> Viết bài ngay
            </a>
        <?php elseif (!Auth::check()): ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-secondary d-inline-flex">
                Đăng nhập để viết bài
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row g-4" id="post-cards-row">
        <?php foreach ($posts as $idx => $post): 
            $postId = (int)$post['id'];
            $img = !empty($post['image_url']) 
                ? (str_starts_with($post['image_url'], 'http') ? $post['image_url'] : $baseUrl . '/' . ltrim($post['image_url'], '/'))
                : $defaultImages[$idx % count($defaultImages)];
            $voteScore = (int)($post['vote_score'] ?? $post['likes'] ?? 0);
            $commentsCount = (int)($post['comments_count'] ?? 0);
        ?>
            <div class="col-12 col-md-6 col-lg-4">
                <article class="paper-card h-100 d-flex flex-column">
                    <div class="card-header-meta">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="card-meta-tag">
                                <?= htmlspecialchars($post['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <?php if (!empty($post['location'])): ?>
                                <span class="badge text-bg-light border small text-muted text-truncate" style="max-width: 170px;">
                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($post['location'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h2 class="card-title text-truncate">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= $postId ?>" 
                               style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>
                    </div>

                    <div class="card-image-box">
                        <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>"
                             loading="lazy">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= $postId ?>" 
                           class="card-action-btn"
                           aria-label="Xem chi tiết: <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7 7h10v10"/>
                                <path d="M7 17 17 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2 text-muted small mt-auto flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <!-- Cụm nút Vote (Upvote / Điểm / Downvote) trực tiếp trên thẻ bài viết -->
                            <div class="d-inline-flex align-items-center rounded-pill border px-1 py-0"
                                 style="background: rgba(34, 40, 31, 0.05); border-color: rgba(34, 40, 31, 0.14) !important;">
                                <button type="button"
                                        class="btn btn-sm border-0 px-2 py-0 vote-up-btn"
                                        data-post-id="<?= $postId ?>"
                                        onclick="ForumAjax.votePost(<?= $postId ?>, 1, this)"
                                        title="Upvote (+1)"
                                        style="color: #2f5e2e; font-size: 0.85rem;">
                                    <i class="bi bi-caret-up-fill"></i>
                                </button>
                                <span class="fw-bold px-1 vote-score"
                                      data-post-id="<?= $postId ?>"
                                      style="font-size: 0.82rem; color: #22281f; min-width: 20px; text-align: center;">
                                    <?= $voteScore ?>
                                </span>
                                <button type="button"
                                        class="btn btn-sm border-0 px-2 py-0 vote-down-btn"
                                        data-post-id="<?= $postId ?>"
                                        onclick="ForumAjax.votePost(<?= $postId ?>, -1, this)"
                                        title="Downvote (-1)"
                                        style="color: #8c3b2b; font-size: 0.85rem;">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                            </div>

                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= $postId ?>#comments-list"
                               class="text-decoration-none text-muted d-inline-flex align-items-center px-2 py-1 rounded-pill"
                               style="background: rgba(34, 40, 31, 0.05); font-size: 0.8rem;"
                               title="Số bình luận">
                                <i class="bi bi-chat-dots me-1"></i><?= $commentsCount ?>
                            </a>
                        </div>

                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <span>
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($post['username'] ?? 'Thành viên', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span>·</span>
                            <span>
                                <?= !empty($post['created_at']) ? date('d/m/Y', strtotime($post['created_at'])) : '' ?>
                            </span>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
