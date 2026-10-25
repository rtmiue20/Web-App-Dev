<?php
$title = $post['title'] ?? 'Chi tiết bài viết';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$isLoggedIn = Session::has('user');
$currentUser = Session::get('user');
$currentUserId = is_array($currentUser) ? (int)($currentUser['id'] ?? 0) : 0;
$isAdmin = is_array($currentUser) && ((($currentUser['role_name'] ?? '') === 'admin') || ((int)($currentUser['role_id'] ?? 0) === 2));
$isLiked = !empty($isLiked);

$coverImg = !empty($post['image_url'])
    ? (str_starts_with($post['image_url'], 'http') ? $post['image_url'] : $baseUrl . '/' . ltrim($post['image_url'], '/'))
    : $baseUrl . '/assets/img/ta-xua-trek.png';

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

    <!-- Ảnh bìa bài viết -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
        <img src="<?= htmlspecialchars($coverImg, ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
             class="w-100 object-fit-cover"
             style="max-height: 460px;">
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

<!-- Template Bootstrap cho 1 bình luận mới / tải thêm -->
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
