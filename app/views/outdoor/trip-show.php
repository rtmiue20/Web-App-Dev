<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$hasCoordinates = !empty($trip['lat']) && !empty($trip['lng']);
$isLoggedIn = Auth::check();
$isMember = Auth::isMember();
$isGuestAccount = $isLoggedIn && Auth::isGuest();
$pCount = (int) $trip['participant_count'];
$maxP = (int) $trip['max_participants'];
$spotsLeft = max(0, $maxP - $pCount);
$hasStarted = strtotime($trip['starts_at']) <= time();
?>
<main class="app-container" style="max-width: 960px;">
    <!-- Back Link -->
    <div class="mb-3">
        <a class="nature-btn-secondary px-3 py-1.5 d-inline-flex align-items-center gap-1.5" href="<?= $escape($baseUrl) ?>/trips" style="font-size: 13px; height: 36px;">
            <i class="bi bi-arrow-left"></i> Danh sách chuyến đi
        </a>
    </div>

    <!-- Main Trip Card -->
    <article class="nature-card p-4 p-md-5 mb-4" style="border-radius: 32px;">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <span class="badge" style="background: rgba(112, 214, 255, 0.15); color: #70d6ff; border: 1px solid rgba(112, 214, 255, 0.35);">
                    <i class="bi bi-compass-fill me-1"></i>Nhóm dã ngoại
                </span>
                <h1 class="h2 fw-bold mt-2 mb-1" style="color: var(--color-ink);"><?= $escape($trip['title']) ?></h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if ($hasStarted): ?>
                    <span class="badge text-bg-secondary px-3 py-2 fs-6">Đã khởi hành</span>
                <?php elseif ($spotsLeft === 0): ?>
                    <span class="badge text-bg-warning px-3 py-2 fs-6">Đã đủ số người</span>
                <?php else: ?>
                    <span class="badge text-bg-success px-3 py-2 fs-6"><?= $pCount ?> / <?= $maxP ?> người (Còn <?= $spotsLeft ?> chỗ)</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Meta info -->
        <div class="row g-3 py-3 my-2 border-top border-bottom" style="font-size: 14.5px; color: var(--color-ink);">
            <div class="col-sm-6 col-md-4 d-flex align-items-center gap-2">
                <i class="bi bi-geo-alt-fill text-danger fs-5 flex-shrink-0"></i>
                <div>
                    <div class="text-muted small">Điểm đến</div>
                    <strong><?= $escape($trip['location']) ?></strong>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 d-flex align-items-center gap-2">
                <i class="bi bi-calendar2-event-fill text-success fs-5 flex-shrink-0"></i>
                <div>
                    <div class="text-muted small">Thời gian khởi hành</div>
                    <strong><?= date('H:i · d/m/Y', strtotime($trip['starts_at'])) ?></strong>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 d-flex align-items-center gap-2">
                <i class="bi bi-person-badge-fill text-info fs-5 flex-shrink-0"></i>
                <div>
                    <div class="text-muted small">Người tổ chức</div>
                    <strong><?= $escape($trip['username']) ?></strong>
                </div>
            </div>
        </div>

        <!-- Trip Description -->
        <div class="my-4" style="color: var(--color-ink); line-height: 1.7; font-size: 15.5px;">
            <h2 class="h5 fw-semibold mb-2">Lịch trình & Kế hoạch chuyến đi</h2>
            <div><?= nl2br($escape($trip['description'])) ?></div>
        </div>

        <!-- Action / Join Form -->
        <div class="pt-3 border-top">
            <?php if ($isGuestAccount): ?>
                <div class="alert alert-secondary d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-eye-fill fs-5"></i>
                    <span>Tài khoản của bạn đang ở vai trò <strong>Khách (Chỉ xem)</strong> nên chỉ có thể xem thông tin chuyến đi, không thể đăng ký tham gia.</span>
                </div>
            <?php elseif ($isLoggedIn): ?>
                <?php if ($hasStarted): ?>
                    <div class="alert alert-secondary d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <span>Chuyến đi đã bắt đầu, không thể đăng ký thêm thành viên.</span>
                    </div>
                <?php elseif ($isOrganizer): ?>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="alert alert-success mb-0 py-2 px-3 d-flex align-items-center gap-2">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span>Bạn là người khởi tạo và tổ chức chuyến đi này.</span>
                        </div>
                        <form method="post" action="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>/delete"
                              onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn hủy chuyến đi “<?= $escape(addslashes($trip['title'])) ?>” khỏi danh sách đi chung không? Hành động này không thể hoàn tác.', 'Xác nhận xóa', 'Hủy chuyến đi chung?');" class="m-0">
                            <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                            <button class="btn btn-outline-danger px-3 py-2" type="submit">
                                <i class="bi bi-trash3 me-1"></i> Hủy chuyến đi
                            </button>
                        </form>
                    </div>
                <?php elseif ($joined): ?>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="alert alert-success mb-0 py-2 px-3 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span>Bạn đã đăng ký tham gia chuyến đi này. Hãy giữ liên lạc với người tổ chức!</span>
                        </div>
                        <form method="post" action="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>/leave"
                              onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn hủy đăng ký tham gia chuyến đi “<?= $escape(addslashes($trip['title'])) ?>” không?', 'Xác nhận rời nhóm', 'Rời khỏi nhóm đi chung?', 'Xác nhận hủy');" class="m-0">
                            <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                            <button class="btn btn-outline-danger px-3 py-2" type="submit">
                                <i class="bi bi-box-arrow-left me-1"></i> Hủy đăng ký (Rời nhóm)
                            </button>
                        </form>
                    </div>
                <?php elseif ($spotsLeft === 0): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <span>Chuyến đi đã đủ số lượng người đăng ký.</span>
                    </div>
                <?php else: ?>
                    <form method="post" action="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>/join" class="m-0">
                        <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                        <button class="nature-btn-primary px-4 py-2.5 d-inline-flex align-items-center gap-2" type="submit" style="width: auto; height: 46px;">
                            <i class="bi bi-person-plus-fill fs-5"></i> Đăng ký tham gia (Còn <?= $spotsLeft ?> chỗ trống)
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span style="color: var(--color-ink);">Đăng nhập tài khoản để đăng ký tham gia chuyến đi này.</span>
                    <a class="nature-btn-primary px-4 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/login" style="width: auto; height: 42px;">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </article>

    <!-- Participants List Card -->
    <section class="nature-card p-4 p-md-5 mb-4" style="border-radius: 32px;">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 fw-semibold mb-0" style="color: var(--color-ink);">
                <i class="bi bi-people-fill text-success me-2"></i>Thành viên tham gia (<?= $pCount ?> / <?= $maxP ?>)
            </h2>
            <span class="text-muted small">Cập nhật theo thời gian thực</span>
        </div>

        <?php if (empty($participants)): ?>
            <p class="text-muted small mb-0">Chưa có ai đăng ký tham gia.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($participants as $p): ?>
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-4 border d-flex align-items-center gap-2.5" style="background: rgba(0,0,0,0.02); border-color: rgba(0,0,0,0.08) !important;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                 style="width: 40px; height: 40px; background: <?= (int)$p['is_organizer'] === 1 ? '#9bbd55' : '#70d6ff' ?>;">
                                <?= strtoupper(mb_substr($p['username'], 0, 1)) ?>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold text-truncate" style="color: var(--color-ink); font-size: 14px;">
                                    <?= $escape($p['username']) ?>
                                </div>
                                <div class="small">
                                    <?php if ((int)$p['is_organizer'] === 1): ?>
                                        <span class="badge text-bg-success" style="font-size: 10px;">Người tổ chức</span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 11px;">Đã tham gia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Weather Safety Section -->
    <section class="nature-card p-4 p-md-5" style="border-radius: 32px;">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(155, 189, 85, 0.2); color: var(--color-moss);">
                <i class="bi bi-cloud-sun-fill fs-5"></i>
            </span>
            <div>
                <h2 class="h5 fw-semibold mb-0" style="color: var(--color-ink);">Dự báo khí tượng & Khuyến nghị an toàn</h2>
                <span class="text-muted small">Cập nhật tự động từ dịch vụ Open-Meteo theo tọa độ chuyến đi</span>
            </div>
        </div>

        <?php if ($hasCoordinates): ?>
            <div id="trip-weather-panel" class="p-3.5 rounded-4 mb-3" style="background: rgba(0, 0, 0, 0.04); border: 1px solid rgba(0, 0, 0, 0.08);">
                <div class="d-flex align-items-center gap-2">
                    <span class="spinner-border spinner-border-sm text-success" role="status"></span>
                    <span id="trip-weather-info" style="color: var(--color-ink); font-size: 14.5px;">Đang tải dự báo thời tiết tại <?= $escape($trip['location']) ?>...</span>
                </div>
            </div>
            <div id="trip-weather-warning" class="d-none" role="status"></div>
            <p class="small text-muted mb-0 mt-2">
                <i class="bi bi-info-circle me-1"></i>Dự báo thời tiết áp dụng trong phạm vi 16 ngày tới. Luôn theo dõi thông tin từ ban quản lý địa phương trước khi khởi hành.
            </p>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (window.ForumAjax && typeof ForumAjax.fetchWeather === 'function') {
                        ForumAjax.fetchWeather(
                            <?= json_encode((string) $trip['lat']) ?>,
                            <?= json_encode((string) $trip['lng']) ?>,
                            document.getElementById('trip-weather-info'),
                            null,
                            document.getElementById('trip-weather-warning'),
                            <?= json_encode(date('Y-m-d', strtotime($trip['starts_at']))) ?>
                        );
                    }
                });
            </script>
        <?php else: ?>
            <div class="alert alert-secondary mb-0">
                <i class="bi bi-info-circle me-1"></i>Chuyến đi này chưa có tọa độ GPS nên chưa thể hiển thị dữ liệu thời tiết tự động.
            </div>
        <?php endif; ?>
    </section>
</main>
