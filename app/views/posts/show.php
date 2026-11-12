<?php

$title = $post['title'] ?? 'Chi tiết bài viết';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';

$isLoggedIn = Session::has('user');
$escape = function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};



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

// Chỉ hiển thị ảnh nếu tác giả thực sự có tải ảnh lên (không tự động gán ảnh placeholder giả)
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
        <div class="user-pending-alert-banner mb-4 p-4 rounded-4 shadow-sm" 
             style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, rgba(217, 119, 6, 0.08) 100%); border: 2px solid rgba(245, 158, 11, 0.5); backdrop-filter: blur(12px);" 
             role="alert">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5" 
                          style="background: #f59e0b; color: #1e1e1e; font-weight: 700; font-size: 13px;">
                        <i class="bi bi-hourglass-split"></i> TRẠNG THÁI: CHỜ DUYỆT (PENDING)
                    </span>
                    <span class="badge text-bg-dark border border-warning border-opacity-50 text-warning px-2.5 py-1" style="font-size: 11px;">
                        Chỉ bạn &amp; Ban Quản Trị thấy
                    </span>
                </div>
                <?php if ($canManage): ?>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/edit" 
                           class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1 text-decoration-none" 
                           style="font-size: 13px;">
                            <i class="bi bi-pencil"></i> Chỉnh sửa bài viết
                        </a>
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile?tab=posts" 
                           class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1 text-decoration-none" 
                           style="font-size: 13px;">
                            <i class="bi bi-folder2-open"></i> Quản lý trong Hồ sơ
                        </a>
                        <form method="post" 
                              action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/delete" 
                              class="d-inline m-0" 
                              onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn hủy bỏ và xóa bài viết đang chờ duyệt “<?= htmlspecialchars(addslashes($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>” không? Hành động này không thể hoàn tác.', 'Xác nhận xóa', 'Hủy bỏ bài viết chờ duyệt?');">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="redirect" value="profile">
                            <button type="submit" 
                                    class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1" 
                                    style="font-size: 13px;"
                                    title="Hủy bỏ và xóa bài viết này">
                                <i class="bi bi-trash3"></i> Hủy / Xóa bài viết
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <p class="text-white mb-2" style="font-size: 14px; opacity: 0.95; line-height: 1.5;">
                Bài viết này đã được tiếp nhận an toàn và đang nằm trong danh sách kiểm duyệt của <strong>Ban Quản Trị</strong>. Ngay sau khi được phê duyệt, bài viết sẽ tự động xuất hiện công khai trên diễn đàn cho tất cả thành viên.
            </p>
            <div class="d-flex align-items-center gap-2 text-white-50 small">
                <i class="bi bi-info-circle text-warning"></i>
                <span>Bạn vẫn có thể bổ sung hình ảnh và chỉnh sửa lại nội dung bài viết bất cứ lúc nào trước khi Admin duyệt.</span>
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

    <?php if (!empty($galleryImages)): ?>
    <!-- ══════════════════════════════════════════
         INSTAGRAM-STYLE IMAGE CAROUSEL
    ══════════════════════════════════════════ -->
    <div class="insta-carousel-wrap mb-4" id="postCarousel">
        <!-- Slide viewport -->
        <div class="insta-viewport" id="pcViewport"
             ontouchstart="CarouselLB.touchStart(event)"
             ontouchmove="CarouselLB.touchMove(event)"
             ontouchend="CarouselLB.touchEnd(event)">
            
            <div class="insta-track" id="pcTrack">
                <?php foreach ($galleryImages as $idx => $imgUrl): ?>
                    <div class="insta-slide" data-index="<?= $idx ?>">
                        <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                             alt="Ảnh <?= $idx + 1 ?> / <?= $galleryCount ?>"
                             class="insta-img"
                             onclick="CarouselLB.openLightbox(<?= $idx ?>)"
                             title="Bấm để xem toàn màn hình">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($galleryCount > 1): ?>
        <!-- Instagram Counter Badge (Top-Right) -->
        <div class="insta-counter-badge" id="pcCounter">
            <i class="bi bi-images me-1"></i><span>1/<?= $galleryCount ?></span>
        </div>

        <!-- Instagram Circular Prev / Next Navigation Buttons -->
        <button type="button" class="insta-nav-btn insta-prev-btn d-none" id="pcPrevBtn" onclick="CarouselLB.prev()" aria-label="Ảnh trước" title="Ảnh trước">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="insta-nav-btn insta-next-btn" id="pcNextBtn" onclick="CarouselLB.next()" aria-label="Ảnh sau" title="Ảnh sau">
            <i class="bi bi-chevron-right"></i>
        </button>

        <!-- Instagram Dot Indicators (Bottom-Center) -->
        <div class="insta-dots-bar" id="pcDots">
            <?php for ($i = 0; $i < $galleryCount; $i++): ?>
                <span class="insta-dot <?= $i === 0 ? 'active' : '' ?>"
                      onclick="CarouselLB.goTo(<?= $i ?>)"
                      aria-label="Đến ảnh <?= $i + 1 ?>"></span>
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
                <i class="bi bi-chevron-left"></i>
            </button>
            <button class="lb-nav lb-nav-next" onclick="CarouselLB.lbNext()" aria-label="Ảnh sau">
                <i class="bi bi-chevron-right"></i>
            </button>
            <?php endif; ?>
            <img src="" alt="" id="lbImg" class="lb-img">
            <div class="lb-caption" id="lbCaption"></div>
        </div>
    </div>
    <?php endif; /* end if !empty($galleryImages) */ ?>

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

            <span>

                <i class="bi bi-eye me-1"></i>

                <?= number_format((int)($post['views_count'] ?? 0)) ?> lượt xem

            </span>

            <span class="ms-auto d-flex align-items-center flex-wrap gap-2">

                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <?php if (!empty($canManage)): ?>

                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/edit" 

                       class="nature-btn-secondary py-1 px-3 text-dark border-dark-subtle"

                       title="Chỉnh sửa bài viết">

                        <i class="bi bi-pencil-square me-1"></i> Sửa

                    </a>

                    <form method="post" 

                          action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/delete" 

                          class="d-inline" 

                          onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn xóa bài viết “<?= htmlspecialchars(addslashes($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>” khỏi diễn đàn không? Hành động này không thể hoàn tác.', 'Xác nhận xóa', 'Xóa bài viết khỏi diễn đàn?');">

                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                        <button type="submit" 

                                class="nature-btn-secondary py-1 px-3 text-danger border-danger-subtle"

                                title="Xóa bài viết">

                            <i class="bi bi-trash3 me-1"></i> Xóa

                        </button>

                    </form>

                <?php endif; ?>

                <?php

                    $currentVote = (int)($userVote ?? 0);

                    $voteScore = (int)($post['vote_score'] ?? $post['likes'] ?? 0);

                    $upvotesCount = (int)($post['upvotes'] ?? max(0, $voteScore));

                    $downvotesCount = (int)($post['downvotes'] ?? 0);

                ?>

                <div class="d-inline-flex align-items-center rounded-pill border px-1 py-1"

                     id="vote-widget-<?= (int)$post['id'] ?>"

                     style="background: rgba(34, 40, 31, 0.06); border-color: rgba(34, 40, 31, 0.2) !important;">

                    <button type="button"

                            class="btn btn-sm rounded-pill border-0 px-2 py-1 d-inline-flex align-items-center justify-content-center vote-up-btn <?= $currentVote === 1 ? 'active' : '' ?>"

                            data-post-id="<?= (int)$post['id'] ?>"

                            onclick="ForumAjax.votePost(<?= (int)$post['id'] ?>, 1, this)"

                            title="Upvote bài viết hữu ích (+1)"

                            style="<?= $currentVote === 1 ? 'background: #2f5e2e; color: #fff; font-weight: 600;' : 'color: #2f5e2e; font-weight: 600;' ?>">

                        <i class="bi bi-caret-up-fill"></i>

                    </button>



                    <span class="fw-bold px-2 vote-score like-count"

                          data-post-id="<?= (int)$post['id'] ?>"

                          style="font-size: 0.95rem; color: #22281f; min-width: 30px; text-align: center;"

                          title="Tổng điểm Vote (Upvotes - Downvotes)">

                        <?= $voteScore ?>

                    </span>



                    <button type="button"

                            class="btn btn-sm rounded-pill border-0 px-2 py-1 d-inline-flex align-items-center justify-content-center vote-down-btn <?= $currentVote === -1 ? 'active' : '' ?>"

                            data-post-id="<?= (int)$post['id'] ?>"

                            onclick="ForumAjax.votePost(<?= (int)$post['id'] ?>, -1, this)"

                            title="Downvote bài viết (-1)"

                            style="<?= $currentVote === -1 ? 'background: #b02a37; color: #fff; font-weight: 600;' : 'color: #8c3b2b; font-weight: 600;' ?>">

                        <i class="bi bi-caret-down-fill"></i>

                    </button>

                </div>

                <span class="small text-muted vote-breakdown me-2"

                      data-post-id="<?= (int)$post['id'] ?>"

                      title="Chi tiết lượt Upvote / Downvote">

                    (+<?= $upvotesCount ?> / -<?= $downvotesCount ?>)

                </span>



                <!-- Nút Lưu bài viết (Bookmark) qua AJAX -->

                <button type="button"

                        class="btn btn-sm rounded-pill border px-3 py-1 d-inline-flex align-items-center gap-1 bookmark-btn <?= !empty($isBookmarked) ? 'bookmarked text-danger' : 'text-dark' ?>"

                        id="btnBookmarkPost"

                        onclick="ForumAjax.toggleBookmark(<?= (int)$post['id'] ?>, this)"

                        title="<?= !empty($isBookmarked) ? 'Bỏ lưu bài viết' : 'Lưu bài viết vào bộ sưu tập' ?>"

                        style="background: rgba(34, 40, 31, 0.06); border-color: rgba(34, 40, 31, 0.2) !important;">

                    <i class="bi <?= !empty($isBookmarked) ? 'bi-bookmark-heart-fill' : 'bi-bookmark-heart' ?>"></i>

                    <span class="bookmark-text"><?= !empty($isBookmarked) ? 'Đã lưu' : 'Lưu bài' ?></span>

                </button>

                <!-- Nút Mã QR chia sẻ bài viết bằng điện thoại (QuickChart / GoQR API) -->
                <button type="button"
                        class="btn btn-sm rounded-pill border px-3 py-1 d-inline-flex align-items-center gap-1.5 fw-semibold"
                        id="btnQrSharePost"
                        onclick="ForumAjax.openQrShareModal('/posts/<?= (int)$post['id'] ?>', '<?= htmlspecialchars(addslashes($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', 'post')"
                        title="Sinh mã QR chia sẻ bài viết nhanh qua điện thoại"
                        style="background: rgba(35, 61, 44, 0.08); color: #1f4d32; border-color: rgba(35, 61, 44, 0.25) !important;">
                    <i class="bi bi-qr-code-scan text-success"></i>
                    <span>Mã QR chia sẻ</span>
                </button>



                <!-- Nút Báo cáo vi phạm (Report) -->

                <button type="button"

                        class="btn btn-sm rounded-pill border px-2 py-1 text-muted"

                        data-bs-toggle="modal"

                        data-bs-target="#reportPostModal"

                        title="Báo cáo bài viết vi phạm cho Moderator">

                    <i class="bi bi-flag"></i>

                </button>

            </span>

        </div>



        <div class="post-body">

            <?= nl2br(htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8')) ?>

        </div>



        <!-- ══════════════════════════════════════════════════════════

             BÀI VIẾT DẠNG "CHUYẾN ĐI" (Trip Details Widget)

        ══════════════════════════════════════════════════════════ -->

        <?php

        $hasTripInfo = !empty($post['route']) || !empty($post['trip_duration']) || !empty($post['estimated_cost']) || !empty($post['gear_needed']) || !empty($post['difficulty']) || !empty($post['region']) || !empty($post['activity_type']);

        if ($hasTripInfo):

            $diffLabels = [

                'easy'    => ['label' => 'Dễ (Người mới)', 'badge' => 'text-bg-success', 'icon' => 'bi-emoji-smile'],

                'medium'  => ['label' => 'Trung bình',     'badge' => 'text-bg-primary', 'icon' => 'bi-compass'],

                'hard'    => ['label' => 'Khó (Dốc cao)',  'badge' => 'text-bg-warning text-dark', 'icon' => 'bi-exclamation-triangle'],

                'extreme' => ['label' => 'Thử thách cao',  'badge' => 'text-bg-danger',  'icon' => 'bi-fire'],

            ];

            $dInfo = $diffLabels[$post['difficulty'] ?? 'medium'] ?? $diffLabels['medium'];

        ?>

            <div class="trip-details-card mt-4 p-4 rounded-4" style="background: rgba(155, 189, 85, 0.1); border: 1.5px solid rgba(155, 189, 85, 0.35);">

                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">

                    <div class="d-flex align-items-center gap-2">

                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: var(--color-moss, #9bbd55); color: #1c2219;">

                            <i class="bi bi-signpost-2-fill fs-5"></i>

                        </span>

                        <div>

                            <h3 class="h5 fw-bold mb-0 text-dark">Hành trình chuyến đi (Trip Info)</h3>

                            <span class="text-muted small" style="font-size: 11.5px;">Cẩm nang và thông số thực tế của chuyến đi này</span>

                        </div>

                    </div>



                    <!-- Badges phân loại -->

                    <div class="d-flex align-items-center gap-2 flex-wrap">

                        <?php if (!empty($post['activity_type'])): ?>

                            <span class="badge rounded-pill text-bg-dark text-capitalize px-3 py-1" style="font-size: 11px;">

                                <i class="bi bi-activity me-1"></i><?= htmlspecialchars($post['activity_type'], ENT_QUOTES, 'UTF-8') ?>

                            </span>

                        <?php endif; ?>

                        <?php if (!empty($post['region'])): ?>

                            <span class="badge rounded-pill text-bg-info text-dark px-3 py-1" style="font-size: 11px;">

                                <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($post['region'], ENT_QUOTES, 'UTF-8') ?>

                            </span>

                        <?php endif; ?>

                        <span class="badge rounded-pill <?= $dInfo['badge'] ?> px-3 py-1" style="font-size: 11px;">

                            <i class="bi <?= $dInfo['icon'] ?> me-1"></i><?= $dInfo['label'] ?>

                        </span>

                    </div>

                </div>



                <div class="row g-3">

                    <?php if (!empty($post['route'])): ?>

                        <div class="col-12 col-md-5">

                            <div class="p-2.5 rounded-3 bg-white border">

                                <span class="text-muted small d-block mb-1"><i class="bi bi-map-fill text-success me-1"></i>Cung đường:</span>

                                <strong class="text-dark small"><?= htmlspecialchars($post['route'], ENT_QUOTES, 'UTF-8') ?></strong>

                            </div>

                        </div>

                    <?php endif; ?>



                    <?php if (!empty($post['trip_duration'])): ?>

                        <div class="col-6 col-md-3">

                            <div class="p-2.5 rounded-3 bg-white border">

                                <span class="text-muted small d-block mb-1"><i class="bi bi-stopwatch text-primary me-1"></i>Thời gian:</span>

                                <strong class="text-dark small"><?= htmlspecialchars($post['trip_duration'], ENT_QUOTES, 'UTF-8') ?></strong>

                            </div>

                        </div>

                    <?php endif; ?>



                    <?php if (!empty($post['estimated_cost'])): ?>

                        <div class="col-6 col-md-4">

                            <div class="p-2.5 rounded-3 bg-white border">

                                <span class="text-muted small d-block mb-1"><i class="bi bi-cash-stack text-warning me-1"></i>Chi phí dự tính:</span>

                                <strong class="text-success small"><?= htmlspecialchars($post['estimated_cost'], ENT_QUOTES, 'UTF-8') ?></strong>

                            </div>

                        </div>

                    <?php endif; ?>



                    <?php if (!empty($post['gear_needed'])): ?>

                        <div class="col-12">

                            <div class="p-3 rounded-3 bg-white border">

                                <span class="text-muted small d-block mb-1"><i class="bi bi-backpack4-fill text-danger me-1"></i>Đồ cần mang (Checklist trang bị):</span>

                                <p class="mb-0 text-dark small"><?= nl2br(htmlspecialchars($post['gear_needed'], ENT_QUOTES, 'UTF-8')) ?></p>

                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>



        <!-- ══════════════════════════════════════════════════════════

             DỰ BÁO THỜI TIẾT DÃ NGOẠI & 4 NGÀY TỚI (Web Service FR06)

        ══════════════════════════════════════════════════════════ -->

        <?php if (!empty($post['lat']) && !empty($post['lng'])): ?>

        <div class="weather-forecast-card mt-4 p-4 p-md-5 border shadow-sm" style="background: linear-gradient(160deg, rgba(28, 38, 24, 0.95) 0%, rgba(18, 25, 15, 0.98) 100%); border-color: rgba(155, 189, 85, 0.35) !important; border-radius: 28px !important; backdrop-filter: blur(16px); box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35) !important;">

            <div class="d-flex align-items-center justify-content-between mb-3.5 flex-wrap gap-2.5 pb-3 border-bottom border-white border-opacity-10">

                <div class="d-flex align-items-center gap-2.5">

                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 shadow-sm flex-shrink-0" style="width: 42px; height: 42px; background: linear-gradient(135deg, rgba(155, 189, 85, 0.3) 0%, rgba(95, 138, 55, 0.2) 100%); color: #d4f291; border: 1px solid rgba(155, 189, 85, 0.45); border-radius: 14px !important;">

                        <i class="bi bi-cloud-sun-fill fs-5"></i>

                    </span>

                    <div>

                        <div class="fw-bold" style="color: #ffffff !important; font-size: 17px !important; letter-spacing: 0.2px; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">Thời tiết &amp; Môi trường dã ngoại</div>

                        <span style="font-size: 12.5px; color: rgba(235, 245, 230, 0.82) !important; line-height: 1.4; display: block; margin-top: 2px;">
                            Theo dõi điều kiện tự nhiên theo thời gian thực để chuẩn bị chuyến đi an toàn
                        </span>

                    </div>

                </div>

                <?php if (!empty($post['location'])): ?>

                    <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 shadow-sm ms-auto ms-sm-0" style="background: rgba(255, 255, 255, 0.12); color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.22); font-size: 12.5px; backdrop-filter: blur(10px);">

                        <i class="bi bi-geo-alt-fill text-danger"></i><?= htmlspecialchars($post['location'], ENT_QUOTES, 'UTF-8') ?>

                    </span>

                <?php endif; ?>

            </div>

            <div id="weather-forecast-panel" class="weather-forecast-content pt-1">

                <div class="text-center py-4 text-white small" style="opacity: 0.85;">

                    <span class="spinner-border spinner-border-sm me-2 text-success" role="status"></span>Đang tải dữ liệu thời tiết thực địa...

                </div>

            </div>

        </div>

        <?php elseif (!empty($post['location'])): ?>

            <div class="alert alert-warning mt-4" role="status">

                <i class="bi bi-exclamation-triangle-fill me-2"></i>Chưa có tọa độ địa điểm; không thể tải cảnh báo thời tiết. Hãy kiểm tra dự báo địa phương trước chuyến đi.

            </div>

        <?php endif; ?>



        <!-- ══════════════════════════════════════════════════════════

             AUTHOR BIO & GAMIFICATION WIDGET

        ══════════════════════════════════════════════════════════ -->

        <?php 

        $authorUser = (new User())->findById((int)$post['user_id']);

        if ($authorUser):

            $ag = $authorGamification ?? Gamification::calculate($authorUser->getId());

            $authorAvatar = upload_url($authorUser->getAvatarPath());

            $authorId = $authorUser->getId();

        ?>

            <div class="author-card-widget mt-4 p-3 rounded-4 border d-flex flex-wrap align-items-center justify-content-between gap-3" 

                 style="background: rgba(0,0,0,0.02); border-color: rgba(0,0,0,0.08) !important;">

                <div class="d-flex align-items-center gap-3">

                    <?php if ($authorAvatar): ?>

                        <img src="<?= htmlspecialchars($authorAvatar, ENT_QUOTES, 'UTF-8') ?>" 

                             alt="Avatar" 

                             class="rounded-circle object-fit-cover shadow-sm" 

                             width="52" height="52">

                    <?php else: ?>

                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"

                             style="width: 52px; height: 52px; background: #2d3328; color: #fff; font-size: 1.3rem; font-weight: 600;">

                            <?= htmlspecialchars(mb_strtoupper(mb_substr($authorUser->getUsername(), 0, 1)), ENT_QUOTES, 'UTF-8') ?>

                        </div>

                    <?php endif; ?>

                    <div>

                        <div class="d-flex align-items-center gap-2">

                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/users/<?= $authorId ?>" class="fw-bold text-dark text-decoration-none">

                                <?= htmlspecialchars($authorUser->getUsername(), ENT_QUOTES, 'UTF-8') ?>

                            </a>

                            <!-- Huy hiệu gamification -->

                            <span class="badge rounded-pill border" style="background: <?= htmlspecialchars($ag['bg'] ?? 'rgba(0,0,0,0.05)', ENT_QUOTES, 'UTF-8') ?>; border-color: <?= htmlspecialchars($ag['border'] ?? '#ccc', ENT_QUOTES, 'UTF-8') ?> !important; font-size: 11px; color: <?= htmlspecialchars($ag['color'] ?? '#333', ENT_QUOTES, 'UTF-8') ?>;">

                                <i class="bi <?= htmlspecialchars($ag['icon'] ?? 'bi-award', ENT_QUOTES, 'UTF-8') ?> me-1"></i><?= htmlspecialchars($ag['title'] ?? 'Tân thủ', ENT_QUOTES, 'UTF-8') ?>

                            </span>

                        </div>

                        <div class="text-muted small" style="font-size: 11.5px;">

                            <span><i class="bi bi-star-fill text-warning me-1"></i><?= (int)($ag['points'] ?? 0) ?> điểm uy tín</span>

                            <span class="mx-2">•</span>

                            <span id="authorFollowersCount"><i class="bi bi-people me-1"></i><?= (int)($authorFollowersCount ?? 0) ?> người theo dõi</span>

                        </div>

                    </div>

                </div>



                <div>

                    <?php if (Auth::check() && (int)Auth::id() !== $authorId): ?>

                        <button type="button" 

                                class="nature-btn-secondary px-3 py-1.5 text-dark border-dark-subtle follow-user-btn <?= !empty($isFollowingAuthor) ? 'following' : '' ?>"

                                onclick="ForumAjax.toggleFollowUser(<?= $authorId ?>, this)"

                                style="font-size: 12px; height: 36px;">

                            <i class="bi <?= !empty($isFollowingAuthor) ? 'bi-check-lg text-success' : 'bi-person-plus-fill' ?> me-1"></i>

                            <span class="follow-btn-text"><?= !empty($isFollowingAuthor) ? 'Đang theo dõi' : 'Theo dõi tác giả' ?></span>

                        </button>

                    <?php elseif (!Auth::check()): ?>

                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-secondary px-3 py-1.5 text-dark border-dark-subtle" style="font-size: 12px; height: 36px;">

                            <i class="bi bi-person-plus-fill me-1"></i> Theo dõi

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>



        <!-- Dụng cụ & Trang thiết bị chuyến đi (Tools & Gear) -->

        <?php if (!empty($tools)): ?>

            <style>

                .tool-post-card { overflow: hidden; max-width: 100%; box-sizing: border-box; }

                .tool-title-marquee { position: relative; width: 100%; max-width: 100%; overflow: hidden; white-space: nowrap; line-height: 1.35; }

                .tool-title-marquee.has-overflow {

                    mask-image: linear-gradient(to right, #000 0%, #000 calc(100% - 16px), transparent 100%);

                    -webkit-mask-image: linear-gradient(to right, #000 0%, #000 calc(100% - 16px), transparent 100%);

                }

                .tool-title-inner { display: inline-block; white-space: nowrap; }

                .tool-title-marquee.has-overflow .tool-title-inner {

                    will-change: transform;

                    animation: toolTextMarquee var(--marquee-duration, 8s) ease-in-out infinite;

                }

                .tool-post-card:hover .tool-title-inner,

                .tool-post-card:focus-within .tool-title-inner {

                    animation-play-state: paused;

                }

                @keyframes toolTextMarquee {

                    0%, 20% { transform: translateX(0); }

                    68%, 82% { transform: translateX(var(--marquee-distance, -80px)); }

                    94%, 100% { transform: translateX(0); }

                }

                .btn-shop-link {

                    transition: transform 0.15s ease, filter 0.15s ease, box-shadow 0.15s ease;

                    text-decoration: none !important;

                }

                .btn-shop-link:hover {

                    transform: translateY(-1px);

                    filter: brightness(1.08);

                }

            </style>

            <div class="post-tools-section mt-5 pt-4 border-top" style="border-color: rgba(0,0,0,0.08) !important;">

                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">

                    <div class="d-flex align-items-center gap-2">

                        <span class="nature-badge" style="background: rgba(155, 189, 85, 0.18); color: var(--color-ink); border-color: rgba(155, 189, 85, 0.4);">

                            <i class="bi bi-tools me-1 text-success"></i>Dụng cụ chuyến đi

                        </span>

                        <h3 class="h5 fw-semibold mb-0" style="color: var(--color-ink);">Công cụ hữu dụng tác giả sử dụng</h3>

                    </div>

                    <span class="small text-muted">

                        <i class="bi bi-check-circle-fill text-success me-1"></i><?= count($tools) ?> dụng cụ được chia sẻ kèm link &amp; giá

                    </span>

                </div>



                <div class="row row-cols-1 row-cols-sm-2 g-3">

                    <?php foreach ($tools as $tool):

                        $rawToolImg = trim((string)($tool['image_url'] ?? ''));

                        if ($rawToolImg === '' || str_starts_with($rawToolImg, 'bi-')) {

                            $toolImg = $baseUrl . '/assets/img/tent-forest.png';

                        } elseif (str_starts_with($rawToolImg, 'http://') || str_starts_with($rawToolImg, 'https://')) {

                            $toolImg = $rawToolImg;

                        } else {

                            $toolImg = $baseUrl . '/' . ltrim($rawToolImg, '/');

                        }

                        $toolLink = trim((string)($tool['link'] ?? ''));

                        if ($toolLink !== '' && !preg_match('#^https?://#i', $toolLink)) {

                            $toolLink = 'https://' . $toolLink;

                        }



                        // Nhận diện sàn thương mại điện tử

                        $linkLower = mb_strtolower($toolLink);

                        $badgeName = 'Nơi mua sản phẩm';

                        $badgeIcon = 'bi-bag-check';

                        $badgeStyle = 'background: #2e7d32; color: #fff; border: 1px solid #2e7d32; box-shadow: 0 3px 8px rgba(46, 125, 50, 0.25);';



                        if (strpos($linkLower, 'shopee') !== false || strpos($linkLower, 'shp.ee') !== false) {

                            $badgeName = 'Mua tại Shopee';

                            $badgeIcon = 'bi-bag-fill';

                            $badgeStyle = 'background: #ee4d2d; color: #fff; border: 1px solid #ee4d2d; box-shadow: 0 3px 10px rgba(238, 77, 45, 0.35);';

                        } elseif (strpos($linkLower, 'lazada') !== false || strpos($linkLower, 'laz.vn') !== false) {

                            $badgeName = 'Mua tại Lazada';

                            $badgeIcon = 'bi-cart4';

                            $badgeStyle = 'background: #0f146d; color: #fff; border: 1px solid #0f146d; box-shadow: 0 3px 10px rgba(15, 20, 109, 0.35);';

                        } elseif (strpos($linkLower, 'tiki.vn') !== false || strpos($linkLower, 'ti.ki') !== false) {

                            $badgeName = 'Mua tại Tiki';

                            $badgeIcon = 'bi-shop';

                            $badgeStyle = 'background: #1a94ff; color: #fff; border: 1px solid #1a94ff; box-shadow: 0 3px 10px rgba(26, 148, 255, 0.35);';

                        } elseif (strpos($linkLower, 'tiktok.com') !== false) {

                            $badgeName = 'Mua tại TikTok Shop';

                            $badgeIcon = 'bi-tiktok';

                            $badgeStyle = 'background: #000000; color: #fff; border: 1px solid #222; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.35);';

                        }

                    ?>

                        <div class="col">

                            <div class="tool-post-card p-3 rounded-4 border d-flex gap-3 align-items-center h-100 position-relative overflow-hidden" 

                                 style="background: rgba(255,255,255,0.92); border-color: rgba(0,0,0,0.08) !important; box-shadow: 0 4px 16px rgba(0,0,0,0.04); transition: transform 0.2s ease, box-shadow 0.2s ease; max-width: 100%;">

                                <img src="<?= htmlspecialchars($toolImg, ENT_QUOTES, 'UTF-8') ?>" 

                                     alt="<?= htmlspecialchars($tool['name'], ENT_QUOTES, 'UTF-8') ?>" 

                                     class="rounded-3 object-fit-cover flex-shrink-0" 

                                     style="width: 78px; height: 78px; border: 1px solid rgba(0,0,0,0.08);">

                                

                                <div class="flex-grow-1 min-w-0 overflow-hidden d-flex flex-column justify-content-between h-100">

                                    <div>

                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">

                                            <?php if (!empty($tool['category_name'])): ?>

                                                <span class="badge rounded-pill bg-light text-dark border text-uppercase" style="font-size: 10px; letter-spacing: 0.4px;">

                                                    <?= htmlspecialchars($tool['category_name'], ENT_QUOTES, 'UTF-8') ?>

                                                </span>

                                            <?php else: ?>

                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">

                                                    <i class="bi bi-person-check-fill me-1"></i>Tác giả chia sẻ

                                                </span>

                                            <?php endif; ?>



                                            <?php if (!empty($tool['price']) && (float)$tool['price'] > 0): ?>

                                                <span class="fw-bold small text-success">

                                                    <?= number_format((float)$tool['price'], 0, ',', '.') ?> đ

                                                </span>

                                            <?php endif; ?>

                                        </div>



                                        <div class="tool-title-marquee fw-semibold mb-1" style="color: var(--color-ink); font-size: 0.95rem;" title="<?= htmlspecialchars($tool['name'], ENT_QUOTES, 'UTF-8') ?>">

                                            <span class="tool-title-inner"><?= htmlspecialchars($tool['name'], ENT_QUOTES, 'UTF-8') ?></span>

                                        </div>



                                        <?php if (!empty($tool['description'])): ?>

                                            <div class="text-muted small mb-2" style="font-size: 12px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="<?= htmlspecialchars($tool['description'], ENT_QUOTES, 'UTF-8') ?>">

                                                <?= htmlspecialchars($tool['description'], ENT_QUOTES, 'UTF-8') ?>

                                            </div>

                                        <?php endif; ?>

                                    </div>



                                    <div class="pt-1 mt-auto d-flex justify-content-end">

                                        <?php if ($toolLink !== ''): ?>

                                            <a href="<?= htmlspecialchars($toolLink, ENT_QUOTES, 'UTF-8') ?>" 

                                               target="_blank" 

                                               rel="noopener noreferrer" 

                                               class="btn btn-sm rounded-pill px-3 py-1 btn-shop-link fw-semibold d-inline-flex align-items-center gap-1.5 text-nowrap"

                                               style="<?= $badgeStyle ?> font-size: 11.5px;"

                                               title="Mở liên kết mua sản phẩm tại tab mới">

                                                <i class="bi <?= $badgeIcon ?>"></i>

                                                <span><?= $badgeName ?></span>

                                            </a>

                                        <?php else: ?>

                                            <span class="badge rounded-pill bg-light text-muted border px-2.5 py-1" style="font-size: 11px;">

                                                <i class="bi bi-check2-circle text-success me-1"></i>Đồ dùng khuyên mang

                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php elseif (!empty($canManage)): ?>

            <div class="post-tools-section mt-5 pt-4 border-top" style="border-color: rgba(0,0,0,0.08) !important;">

                <div class="d-flex align-items-center justify-content-between p-3 rounded-4" style="background: rgba(0,0,0,0.02); border: 1px dashed rgba(0,0,0,0.12);">

                    <div class="d-flex align-items-center gap-2">

                        <i class="bi bi-tools text-muted fs-4"></i>

                        <div>

                            <strong class="d-block text-dark small">Chưa đính kèm dụng cụ cho chuyến đi này</strong>

                            <span class="text-muted" style="font-size: 12px;">Bạn có thể chỉnh sửa bài viết để chia sẻ những dụng cụ hoặc thiết bị bạn đã dùng.</span>

                        </div>

                    </div>

                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/edit" 

                       class="nature-btn-secondary py-1 px-3 text-dark border-dark-subtle text-nowrap" style="font-size: 12px;">

                        <i class="bi bi-plus-lg me-1"></i> Thêm dụng cụ

                    </a>

                </div>

            </div>

        <?php endif; ?>

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

            <?php 
            $currentUserId = Auth::check() ? (int) Auth::id() : 0;
            $currentUserObj = Auth::user();
            $isCurAdmin = Auth::isAdmin($currentUserObj);
            $isAuthorOfPost = $currentUserId > 0 && $currentUserId === (int)($post['user_id'] ?? 0);
            $canReviewPlace = Auth::isMember($currentUserObj) && (!$isAuthorOfPost || $isCurAdmin);
            ?>

            <?php if ($canReviewPlace): ?>

                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int) $post['id'] ?>/reviews" class="row g-3 mt-2" id="placeReviewForm">

                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="rating" id="place-rating-input" value="5" required>

                    <!-- Interactive Star Rating Widget (App Store style) -->
                    <div class="col-12">
                        <label class="form-label d-block fw-semibold mb-1" style="font-size: 13.5px;">Chọn số sao đánh giá</label>
                        <div class="d-inline-flex align-items-center gap-3 p-2 px-3 rounded-4 bg-light border shadow-sm">
                            <div class="star-rating-widget d-inline-flex align-items-center gap-1.5" 
                                 id="starRatingWidget"
                                 role="radiogroup" 
                                 aria-label="Đánh giá số sao"
                                 style="cursor: pointer; user-select: none; font-size: 26px; line-height: 1;">
                                <i class="bi bi-star-fill star-item" data-value="1" title="1 sao - Không tốt" style="color: #ff9800; transition: transform 0.15s ease, color 0.15s ease;"></i>
                                <i class="bi bi-star-fill star-item" data-value="2" title="2 sao - Chưa tốt" style="color: #ff9800; transition: transform 0.15s ease, color 0.15s ease;"></i>
                                <i class="bi bi-star-fill star-item" data-value="3" title="3 sao - Bình thường" style="color: #ff9800; transition: transform 0.15s ease, color 0.15s ease;"></i>
                                <i class="bi bi-star-fill star-item" data-value="4" title="4 sao - Tốt" style="color: #ff9800; transition: transform 0.15s ease, color 0.15s ease;"></i>
                                <i class="bi bi-star-fill star-item" data-value="5" title="5 sao - Rất tốt" style="color: #ff9800; transition: transform 0.15s ease, color 0.15s ease;"></i>
                            </div>
                            <span class="fw-bold small text-dark" id="starRatingLabel" style="min-width: 110px;">5 sao - Rất tốt</span>
                        </div>
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

            <?php elseif ($isLoggedIn && Auth::isGuest()): ?>

                <div class="d-flex align-items-center gap-2 mt-3 mb-0 p-3 rounded-4" style="background: rgba(155, 189, 85, 0.12); border: 1px solid rgba(155, 189, 85, 0.28);">
                    <i class="bi bi-eye-fill fs-5" style="color: var(--color-moss);"></i>
                    <span class="small" style="color: var(--color-ink);">Tài khoản của bạn đang ở vai trò <strong>Khách (Chỉ xem)</strong> nên không thể gửi đánh giá địa điểm.</span>
                </div>

            <?php elseif ($isLoggedIn && $isAuthorOfPost && !$isCurAdmin): ?>

                <div class="d-flex align-items-center gap-2 mt-3 mb-0 p-3 rounded-4" style="background: rgba(0,0,0,0.03); border: 1px dashed rgba(0,0,0,0.15);">
                    <i class="bi bi-info-circle-fill text-muted fs-5"></i>
                    <span class="small text-muted">Bạn là tác giả bài viết này nên không thể tự đánh giá địa điểm của chính mình. Chỉ Admin và các thành viên khác mới có thể gửi đánh giá.</span>
                </div>

            <?php else: ?>

                <div class="p-3 mt-3 mb-0 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(155, 189, 85, 0.12); border: 1px solid rgba(155, 189, 85, 0.28);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5" style="color: var(--color-moss);"></i>
                        <span class="small" style="color: var(--color-ink);">Đăng nhập tài khoản để đánh giá địa điểm dã ngoại này.</span>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-primary py-1 px-3 d-inline-flex align-items-center gap-1" style="width: auto; height: 34px; font-size: 12.5px;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                    </a>
                </div>

            <?php endif; ?>



            <div class="mt-4">

                <?php foreach ($placeReviews as $review): ?>

                    <article class="border-top py-3">

                        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">

                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($review['avatar_path'])): ?>
                                    <img src="<?= htmlspecialchars(upload_url($review['avatar_path']), ENT_QUOTES, 'UTF-8') ?>" alt="" class="rounded-circle object-fit-cover shadow-sm border flex-shrink-0" width="28" height="28">
                                <?php else: ?>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 28px; height: 28px; background: #9bbd55; font-size: 13px;">
                                        <?= strtoupper(mb_substr($review['username'] ?? 'U', 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($review['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                            <span class="text-warning fw-semibold">★ <?= (int) $review['rating'] ?>/5</span>

                        </div>

                        <p class="mb-1"><?= nl2br(htmlspecialchars($review['review'], ENT_QUOTES, 'UTF-8')) ?></p>

                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></small>

                    </article>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>



    <?php if (!empty($post['lat']) && !empty($post['lng'])): ?>

        <div id="post-weather-warning" class="d-none mt-3" role="status"></div>

    <?php endif; ?>



    <!-- Comments Section (Hỗ trợ Bình luận lồng nhau nhiều cấp - Multi-level Nested Replies) -->

    <?php $canComment = class_exists('Auth') ? Auth::isMember() : $isLoggedIn; ?>

    <section class="mt-5" aria-label="Bình luận">

        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">

            <h2 class="h4 mb-0 text-white d-flex align-items-center gap-2">

                <i class="bi bi-chat-left-text-fill" style="color: var(--color-moss);"></i>

                <span>Bình luận & Thảo luận</span>

                <span class="badge rounded-pill px-3 py-1" id="comments-count-badge" style="background: rgba(155, 189, 85, 0.2); color: #c4e482; font-size: 14px;">

                    <?= (int)($totalComments ?? count($comments ?? [])) ?>

                </span>

            </h2>

            <span class="badge rounded-pill border fw-normal py-1 px-3 d-inline-flex align-items-center gap-1" 
                  id="realtime-comment-status" 
                  style="background: rgba(155, 189, 85, 0.12); color: #c4e482; border-color: rgba(155, 189, 85, 0.35) !important; font-size: 11px;">
                <span class="spinner-grow spinner-grow-sm text-success" style="width: 7px; height: 7px;" role="status"></span>
                Trực tiếp
            </span>

        </div>



        <!-- Comment input form -->

        <?php if ($canComment): ?>

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

        <?php elseif (class_exists('Auth') && Auth::check() && Auth::isGuest()): ?>

            <div class="nature-alert nature-alert-success mb-4 d-flex align-items-center gap-2">

                <i class="bi bi-eye-fill fs-5"></i>

                <span>Tài khoản của bạn đang ở vai trò <strong>Khách (Chỉ xem)</strong> nên chỉ có thể xem nội dung thảo luận, không thể gửi bình luận mới.</span>

            </div>

        <?php else: ?>

            <div class="nature-alert nature-alert-success mb-4 d-flex justify-content-between align-items-center">

                <span>Vui lòng đăng nhập tài khoản Thành viên để tham gia thảo luận và trả lời bình luận.</span>

                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-secondary py-1 px-3">

                    Đăng nhập

                </a>

            </div>

        <?php endif; ?>



        <!-- Recursive Multi-level Comments Tree -->

        <?php

        $postIdForComments = (int)($post['id'] ?? 0);

        $renderCommentTree = function (array $nodes, int $depth = 0) use (&$renderCommentTree, $canComment, $postIdForComments): void {

            foreach ($nodes as $cm) {

                $cmId = (int)($cm['id'] ?? 0);

                $cmUserId = (int)($cm['user_id'] ?? 0);

                $cmUsername = (string)($cm['username'] ?? 'Người dùng');

                $cmRole = strtolower((string)($cm['role_name'] ?? 'member'));

                $parentUsername = $cm['parent_username'] ?? null;

                $canDeleteCm = class_exists('Auth') ? Auth::canDeleteComment($cmUserId) : false;

                $isReply = ($depth > 0 || !empty($cm['parent_id']));

                $safeJsUsername = htmlspecialchars(addslashes($cmUsername), ENT_QUOTES, 'UTF-8');

                ?>

                <div class="comment-thread-node <?= $isReply ? 'comment-reply-node' : '' ?>" id="comment-thread-<?= $cmId ?>">

                    <div class="comment-card" id="comment-item-<?= $cmId ?>">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">

                            <div class="d-flex align-items-center flex-wrap">

                                <span class="comment-author d-inline-flex align-items-center gap-1.5">

                                    <?php if (!empty($cm['avatar_path'])): ?>
                                        <img src="<?= htmlspecialchars(upload_url($cm['avatar_path']), ENT_QUOTES, 'UTF-8') ?>" alt="" class="rounded-circle object-fit-cover shadow-sm border flex-shrink-0" width="22" height="22">
                                    <?php else: ?>
                                        <i class="bi bi-person-fill me-1"></i>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($cmUsername, ENT_QUOTES, 'UTF-8') ?></span>

                                </span>

                                <?php if ($cmRole === 'admin'): ?>

                                    <span class="badge rounded-pill text-bg-danger ms-1" style="font-size: 10px;">ADMIN</span>

                                <?php elseif ($cmRole === 'moderator'): ?>

                                    <span class="badge rounded-pill text-bg-primary ms-1" style="font-size: 10px;">MOD</span>

                                <?php endif; ?>

                                <?php if (!empty($parentUsername)): ?>

                                    <span class="badge rounded-pill ms-2 fw-normal" style="background: rgba(155, 189, 85, 0.16); color: #c4e482; border: 1px solid rgba(155, 189, 85, 0.35); font-size: 11px;">

                                        <i class="bi bi-reply-fill me-1"></i>@<?= htmlspecialchars((string)$parentUsername, ENT_QUOTES, 'UTF-8') ?>

                                    </span>

                                <?php endif; ?>

                            </div>

                            <div class="d-flex align-items-center gap-3">

                                <span class="comment-time" style="color: rgba(255, 255, 255, 0.55);">

                                    <?= !empty($cm['created_at']) ? date('d/m/Y H:i', strtotime((string)$cm['created_at'])) : '' ?>

                                </span>

                                <button type="button"

                                        class="btn btn-sm btn-link p-0 text-decoration-none small d-inline-flex align-items-center gap-1 comment-like-btn <?= !empty($cm['user_liked']) ? 'liked' : '' ?>"

                                        data-comment-id="<?= $cmId ?>"

                                        style="<?= !empty($cm['user_liked']) ? 'color: #ff6b81 !important;' : 'color: rgba(255,255,255,0.65) !important;' ?>"

                                        onclick="if(window.ForumAjax) window.ForumAjax.likeComment(<?= $cmId ?>, this)"

                                        title="Thích bình luận">

                                    <i class="bi <?= !empty($cm['user_liked']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>

                                    <span class="comment-likes-count"><?= !empty($cm['likes_count']) ? (int)$cm['likes_count'] : '' ?></span>

                                </button>

                                <?php if ($canComment && $cmId > 0): ?>

                                    <button type="button"

                                            class="btn btn-sm btn-link p-0 text-decoration-none small fw-medium"

                                            style="color: #c4e482;"

                                            onclick="if(window.ForumAjax) window.ForumAjax.toggleReplyBox(<?= $cmId ?>, <?= $postIdForComments ?>, '<?= $safeJsUsername ?>')">

                                        <i class="bi bi-reply-fill"></i> Trả lời

                                    </button>

                                <?php endif; ?>

                                <?php if ($canDeleteCm && $cmId > 0): ?>

                                    <button type="button"

                                            class="btn btn-sm text-danger p-0 border-0 bg-transparent"

                                            title="Xóa bình luận"

                                            onclick="if(window.ForumAjax) window.ForumAjax.deleteComment(<?= $cmId ?>, <?= $postIdForComments ?>, this)">

                                        <i class="bi bi-trash3"></i>

                                    </button>

                                <?php endif; ?>

                            </div>

                        </div>

                        <div class="comment-text">

                            <?= nl2br(htmlspecialchars((string)($cm['content'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>

                        </div>

                        <div id="reply-box-container-<?= $cmId ?>" class="mt-2" style="display: none;"></div>

                    </div>



                    <div class="comment-children-list" id="comment-children-<?= $cmId ?>">

                        <?php if (!empty($cm['children']) && is_array($cm['children'])): ?>

                            <?php $renderCommentTree($cm['children'], $depth + 1); ?>

                        <?php endif; ?>

                    </div>

                </div>

                <?php

            }

        };

        ?>



        <div id="comments-list">

            <?php if (!empty($comments)): ?>

                <?php $renderCommentTree($comments, 0); ?>

            <?php else: ?>

                <div class="text-white-50 small fst-italic py-3" id="no-comments-msg">

                    Chưa có bình luận nào. Hãy để lại lời nhắn đầu tiên!

                </div>

            <?php endif; ?>

        </div>

    </section>



    <!-- Modal Hộp thoại xác nhận xóa bình luận -->

    <div class="modal fade" id="deleteCommentModal" tabindex="-1" aria-labelledby="deleteCommentModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden; background: #ffffff;">

                <div class="modal-header border-0 pb-0 pt-4 px-4" style="background: transparent;">

                    <div>

                        <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.35); font-weight: 600;">

                            <i class="bi bi-exclamation-triangle me-1"></i>Xác nhận xóa

                        </span>

                        <h5 class="modal-title fw-bold fs-5" id="deleteCommentModalLabel" style="color: #23261f;">Xóa bình luận khỏi bài viết?</h5>

                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>

                </div>

                <div class="modal-body p-4" style="color: #4a4f44; background: #ffffff;">

                    Bạn có chắc chắn muốn xóa bình luận này (cùng các phản hồi trả lời bên dưới nếu có) khỏi bài viết không? Hành động này không thể hoàn tác.

                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4" style="background: transparent;">

                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>

                    <button type="button" class="btn btn-danger rounded-pill px-4 py-2 fw-semibold shadow-sm" id="btnConfirmDeleteComment">

                        <i class="bi bi-trash-fill me-1"></i> Xác nhận xóa

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>



<style>

/* ── Nested Comments Tree Styles (Bình luận lồng nhau nhiều cấp) ── */

.comment-thread-node {

    position: relative;

}

.comment-children-list {

    margin-left: 1.75rem;

    padding-left: 1.1rem;

    border-left: 2px solid rgba(155, 189, 85, 0.4);

}

.comment-reply-node > .comment-card {

    background: rgba(255, 255, 255, 0.065);

    border: 1px solid rgba(255, 255, 255, 0.12);

    border-left: 3px solid var(--color-moss, #9bbd55);

}

@media (max-width: 576px) {

    .comment-children-list {

        margin-left: 0.75rem;

        padding-left: 0.65rem;

    }

}

</style>



<!-- ══════════════════════════════════════════════════════════════════

     CAROUSEL + LIGHTBOX STYLES

══════════════════════════════════════════════════════════════════ -->

<style>

/* ── Instagram-Style Carousel ── */
.insta-carousel-wrap {
    position: relative;
    width: 100%;
    border-radius: 28px;
    overflow: hidden;
    background: #0d110d;
    box-shadow: 0 16px 45px rgba(0, 0, 0, 0.45);
    border: 1px solid rgba(255, 255, 255, 0.08);
    user-select: none;
}

.insta-viewport {
    position: relative;
    width: 100%;
    max-height: 600px;
    aspect-ratio: 16/10;
    overflow: hidden;
    cursor: grab;
    display: flex;
    align-items: center;
    background: #050705;
}
.insta-viewport:active {
    cursor: grabbing;
}

.insta-track {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.38s cubic-bezier(0.22, 1, 0.36, 1);
    will-change: transform;
}

.insta-slide {
    flex: 0 0 100%;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: #050705;
}

.insta-img {
    max-width: 100%;
    max-height: 600px;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    cursor: zoom-in;
    transition: transform 0.2s ease;
}

/* Instagram Counter Badge (Top-Right) */
.insta-counter-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    background: rgba(18, 22, 16, 0.78);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.5px;
    padding: 5px 12px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
    z-index: 15;
    pointer-events: none;
    display: inline-flex;
    align-items: center;
}

/* Instagram Circular Navigation Buttons */
.insta-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(255, 255, 255, 0.6);
    color: #1a1e17;
    font-size: 1.15rem;
    cursor: pointer;
    z-index: 20;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
    transition: all 0.22s cubic-bezier(0.2, 0.8, 0.2, 1);
    opacity: 0.92;
}
.insta-nav-btn:hover {
    background: #ffffff;
    color: #000;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.5);
    opacity: 1;
}
.insta-prev-btn {
    left: 14px;
}
.insta-next-btn {
    right: 14px;
}
.insta-nav-btn.d-none {
    display: none !important;
}

/* Instagram Dots Indicator (Bottom-Center) */
.insta-dots-bar {
    position: absolute;
    bottom: 14px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border-radius: 16px;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 15;
}
.insta-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.45);
    cursor: pointer;
    transition: all 0.25s ease;
}
.insta-dot.active {
    width: 18px;
    border-radius: 3px;
    background: #9bbd55;
    box-shadow: 0 0 8px rgba(155, 189, 85, 0.8);
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

<!-- ══════════════════════════════════════════

     MODAL BÁO CÁO VI PHẠM (REPORT MODAL)

══════════════════════════════════════════ -->

<div class="modal fade" id="reportPostModal" tabindex="-1" aria-labelledby="reportPostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; background: #ffffff; color: #23261f; overflow: hidden;">
            <div class="modal-header border-bottom px-4 pt-4 pb-3" style="border-color: #edf0e8 !important; background: #ffffff;">
                <h5 class="modal-title d-flex align-items-center gap-2 fs-6 fw-bold" id="reportPostModalLabel" style="color: #23261f;">
                    <i class="bi bi-shield-exclamation text-danger"></i> Báo cáo bài viết vi phạm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="reportPostForm" onsubmit="event.preventDefault(); ForumAjax.submitReport(this, <?= (int)$post['id'] ?>, 'post');">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-body p-4" style="background: #ffffff; color: #23261f;">
                    <p class="small mb-3" style="color: #686d62;">
                        Báo cáo của bạn sẽ được gửi trực tiếp đến hàng chờ kiểm duyệt của Ban điều hành (Moderator / Admin) để xử lý.
                    </p>
                    <div class="mb-3">
                        <label for="reportReason" class="form-label small fw-semibold" style="color: #23261f;">Lý do vi phạm *</label>
                        <select class="form-select rounded-3" id="reportReason" name="reason" required style="background: #ffffff; border: 1px solid #dcdfd6; color: #23261f;">
                            <option value="">-- Chọn lý do --</option>
                            <option value="Spam / Quảng cáo trái phép">Spam / Quảng cáo rác</option>
                            <option value="Ngôn từ xúc phạm, thù địch">Ngôn từ xúc phạm, thiếu văn hóa</option>
                            <option value="Thông tin sai lệch hoặc nguy hiểm">Thông tin địa điểm sai lệch / nguy hiểm</option>
                            <option value="Nội dung vi phạm pháp luật / thuần phong mỹ tục">Vi phạm pháp luật / thuần phong mỹ tục</option>
                            <option value="Lý do khác">Lý do khác</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="reportDetails" class="form-label small fw-semibold" style="color: #23261f;">Mô tả chi tiết (tùy chọn)</label>
                        <textarea class="form-control rounded-3" 
                                  id="reportDetails" 
                                  name="details" 
                                  rows="3" 
                                  placeholder="Cung cấp thêm ngữ cảnh hoặc chi tiết vi phạm..."
                                  style="background: #ffffff; border: 1px solid #dcdfd6; color: #23261f;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center" style="border-color: #edf0e8 !important; background: #fafbfa;">
                    <button type="button" class="nature-btn-secondary px-3 py-1.5" data-bs-dismiss="modal" style="font-size: 13.5px;">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4" id="btnSubmitReport" style="height: 38px; font-weight: 600;">
                        <i class="bi bi-send-fill me-1"></i> Gửi báo cáo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

            </form>

        </div>

    </div>

</div>



<script>

(function () {

    'use strict';



    const IMAGES  = <?= json_encode($galleryImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const TOTAL   = IMAGES.length;

    if (TOTAL === 0) {
        window.CarouselLB = {
            goTo: function() {}, next: function() {}, prev: function() {},
            openLightbox: function() {}, closeLightbox: function() {},
            lbNext: function() {}, lbPrev: function() {}, touchStart: function() {},
            touchMove: function() {}, touchEnd: function() {}
        };
        return;
    }

    let current = 0;
    let touchX0 = 0;
    let isDragging = false;

    // DOM references
    const track     = document.getElementById('pcTrack');
    const prevBtn   = document.getElementById('pcPrevBtn');
    const nextBtn   = document.getElementById('pcNextBtn');
    const dots      = document.querySelectorAll('.insta-dot');
    const counter   = document.getElementById('pcCounter');
    const viewport  = document.getElementById('pcViewport');
    const lbOverlay = document.getElementById('lbOverlay');
    const lbImg     = document.getElementById('lbImg');
    const lbCaption = document.getElementById('lbCaption');

    function updateUiState() {
        if (track) {
            track.style.transform = 'translateX(-' + (current * 100) + '%)';
        }

        // Instagram button behavior: Hide prev at index 0, hide next at last index
        if (prevBtn) {
            if (current <= 0) {
                prevBtn.classList.add('d-none');
            } else {
                prevBtn.classList.remove('d-none');
            }
        }
        if (nextBtn) {
            if (current >= TOTAL - 1) {
                nextBtn.classList.add('d-none');
            } else {
                nextBtn.classList.remove('d-none');
            }
        }

        // Instagram counter badge: e.g. "1/4"
        if (counter) {
            counter.innerHTML = '<i class="bi bi-images me-1"></i><span>' + (current + 1) + '/' + TOTAL + '</span>';
        }

        // Instagram dots indicator
        dots.forEach(function(d, i) {
            d.classList.toggle('active', i === current);
        });
    }

    // Initialize state
    updateUiState();

    window.CarouselLB = {
        goTo: function(idx) {
            if (idx < 0) idx = 0;
            if (idx >= TOTAL) idx = TOTAL - 1;
            current = idx;
            updateUiState();
        },

        next: function() {
            if (current < TOTAL - 1) {
                current++;
                updateUiState();
            }
        },

        prev: function() {
            if (current > 0) {
                current--;
                updateUiState();
            }
        },

        // Lightbox
        openLightbox: function(idx) {
            if (!lbOverlay || !lbImg) return;
            current = (idx + TOTAL) % TOTAL;
            lbImg.src = IMAGES[current];
            lbImg.alt = 'Ảnh ' + (current + 1) + ' / ' + TOTAL;
            if (lbCaption) lbCaption.textContent = TOTAL > 1 ? (current + 1) + ' / ' + TOTAL : '';
            lbOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            updateUiState();
        },

        closeLightbox: function() {
            if (!lbOverlay) return;
            lbOverlay.style.display = 'none';
            document.body.style.overflow = '';
        },

        lbPrev: function() {
            current = (current - 1 + TOTAL) % TOTAL;
            if (lbImg) { lbImg.src = IMAGES[current]; lbImg.alt = 'Ảnh ' + (current+1) + ' / ' + TOTAL; }
            if (lbCaption) lbCaption.textContent = (current + 1) + ' / ' + TOTAL;
            updateUiState();
        },

        lbNext: function() {
            current = (current + 1) % TOTAL;
            if (lbImg) { lbImg.src = IMAGES[current]; lbImg.alt = 'Ảnh ' + (current+1) + ' / ' + TOTAL; }
            if (lbCaption) lbCaption.textContent = (current + 1) + ' / ' + TOTAL;
            updateUiState();
        },

        // Touch & Swipe handlers
        touchStart: function(e) {
            if (TOTAL <= 1) return;
            touchX0 = e.touches[0].clientX;
            isDragging = true;
        },

        touchMove: function(e) {
            // Passive touch move
        },

        touchEnd: function(e) {
            if (!isDragging || TOTAL <= 1) return;
            isDragging = false;
            const delta = e.changedTouches[0].clientX - touchX0;
            if (Math.abs(delta) > 40) {
                if (delta < 0) {
                    CarouselLB.next();
                } else {
                    CarouselLB.prev();
                }
            }
        }
    };

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (lbOverlay && lbOverlay.style.display !== 'none') {
            if (e.key === 'ArrowLeft')  CarouselLB.lbPrev();
            if (e.key === 'ArrowRight') CarouselLB.lbNext();
            if (e.key === 'Escape')     CarouselLB.closeLightbox();
        } else if (TOTAL > 1) {
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

    ForumAjax.postComment(<?= (int)$post['id'] ?>, input.value, list).then(function(success) {

        if (success) {

            input.value = '';

            const noMsg = document.getElementById('no-comments-msg');

            if (noMsg) noMsg.remove();

        }

    });

}



// ── Weather & Tool Marquee & Realtime Comments ───────────────────

document.addEventListener('DOMContentLoaded', function() {

    if (window.initToolMarquees) {

        window.initToolMarquees();

    }

    if (window.ForumAjax) {

        window.ForumAjax.startCommentPolling(<?= (int)$post['id'] ?>);

    }

    const weatherElem = document.getElementById('weather-info');

    const weatherPanel = document.getElementById('weather-forecast-panel');

    if (weatherElem || weatherPanel) {

        <?php if (!empty($post['lat']) && !empty($post['lng'])): ?>

            ForumAjax.fetchWeather(

                <?= json_encode((string) $post['lat']) ?>,

                <?= json_encode((string) $post['lng']) ?>,

                weatherElem,

                weatherPanel,

                document.getElementById('post-weather-warning')

            );

        <?php endif; ?>

    }

    // Star Rating Widget interaction (App Store drag & click style)
    var starWidget = document.getElementById('starRatingWidget');
    var ratingInput = document.getElementById('place-rating-input');
    var ratingLabel = document.getElementById('starRatingLabel');

    if (starWidget && ratingInput && ratingLabel) {
        var stars = starWidget.querySelectorAll('.star-item');
        var labels = {
            1: '1 sao - Không tốt',
            2: '2 sao - Chưa tốt',
            3: '3 sao - Bình thường',
            4: '4 sao - Tốt',
            5: '5 sao - Rất tốt'
        };

        function updateStars(val) {
            val = parseInt(val, 10) || 5;
            for (var i = 0; i < stars.length; i++) {
                var sVal = parseInt(stars[i].getAttribute('data-value'), 10);
                if (sVal <= val) {
                    stars[i].classList.remove('bi-star');
                    stars[i].classList.add('bi-star-fill');
                    stars[i].style.color = '#ff9800';
                } else {
                    stars[i].classList.remove('bi-star-fill');
                    stars[i].classList.add('bi-star');
                    stars[i].style.color = '#cbd5e1';
                }
            }
            if (labels[val]) {
                ratingLabel.textContent = labels[val];
            }
        }

        // Set initial state
        updateStars(ratingInput.value || 5);

        for (var j = 0; j < stars.length; j++) {
            (function(star) {
                star.addEventListener('mouseenter', function() {
                    var hVal = this.getAttribute('data-value');
                    updateStars(hVal);
                });

                star.addEventListener('click', function(e) {
                    e.preventDefault();
                    var cVal = this.getAttribute('data-value');
                    ratingInput.value = cVal;
                    updateStars(cVal);
                });
            })(stars[j]);
        }

        starWidget.addEventListener('mouseleave', function() {
            updateStars(ratingInput.value);
        });

        // Touch / drag support across stars
        var isDragging = false;
        function getStarFromPoint(x, y) {
            var el = document.elementFromPoint(x, y);
            if (el && el.classList.contains('star-item')) {
                return el;
            }
            if (el && el.closest('.star-item')) {
                return el.closest('.star-item');
            }
            return null;
        }

        starWidget.addEventListener('touchstart', function(e) {
            isDragging = true;
            var touch = e.touches[0];
            var star = getStarFromPoint(touch.clientX, touch.clientY);
            if (star) {
                var val = star.getAttribute('data-value');
                ratingInput.value = val;
                updateStars(val);
            }
        }, { passive: true });

        starWidget.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            var touch = e.touches[0];
            var star = getStarFromPoint(touch.clientX, touch.clientY);
            if (star) {
                var val = star.getAttribute('data-value');
                ratingInput.value = val;
                updateStars(val);
            }
        }, { passive: true });

        starWidget.addEventListener('touchend', function() {
            isDragging = false;
        });
    }

});

</script>

