<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$defaultImages = [
    $baseUrl . '/assets/img/tent-forest.png',
    $baseUrl . '/assets/img/ta-xua-trek.png',
];
?>
<?php if (empty($posts)): ?>
    <div class="nature-card text-center py-5 my-4">
        <i class="bi bi-compass fs-1 text-muted d-block mb-3"></i>
        <h3 class="h5 fw-normal mb-2">Chưa có bài viết nào</h3>
        <p class="text-muted small mb-3">Hãy là người đầu tiên chia sẻ hành trình của mình!</p>
        <?php if (Session::has('user')): ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-secondary d-inline-flex">
                <i class="bi bi-pencil-square me-1"></i> Viết bài ngay
            </a>
        <?php else: ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-secondary d-inline-flex">
                Đăng nhập để viết bài
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($posts as $idx => $post): 
            $img = !empty($post['image_url']) 
                ? (str_starts_with($post['image_url'], 'http') ? $post['image_url'] : $baseUrl . '/' . ltrim($post['image_url'], '/'))
                : $defaultImages[$idx % count($defaultImages)];
        ?>
            <div class="col-12 col-md-6 col-lg-4">
                <article class="paper-card h-100">
                    <div class="card-header-meta">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="card-meta-tag">
                                <?= htmlspecialchars($post['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <?php if (!empty($post['location'])): ?>
                                <span class="badge text-bg-light border small text-muted">
                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($post['location'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h2 class="card-title text-truncate">
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int) $post['id'] ?>" 
                               style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>
                    </div>

                    <div class="card-image-box">
                        <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>"
                             loading="lazy">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int) $post['id'] ?>" 
                           class="card-action-btn"
                           aria-label="Xem chi tiết: <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7 7h10v10"/>
                                <path d="M7 17 17 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2 text-muted small mt-auto">
                        <span>
                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($post['username'] ?? 'Thành viên', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span>
                            <i class="bi bi-calendar3 me-1"></i><?= !empty($post['created_at']) ? date('d/m/Y', strtotime($post['created_at'])) : '' ?>
                        </span>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
