<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$escape = 'e';
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
                <span class="badge" style="background: rgba(155, 189, 85, 0.18); color: var(--color-moss); border: 1px solid rgba(155, 189, 85, 0.35);">
                    <i class="bi bi-compass-fill me-1"></i>Nhóm dã ngoại
                </span>
                <h1 class="h2 fw-bold mt-2 mb-1" style="color: var(--color-ink);"><?= $escape($trip['title']) ?></h1>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <button type="button"
                        class="btn btn-sm rounded-pill d-inline-flex align-items-center gap-1.5 px-3 py-2 fw-semibold shadow-sm"
                        onclick="ForumAjax.openQrShareModal('/trips/<?= (int) $trip['id'] ?>', '<?= $escape(addslashes($trip['title'])) ?>', 'trip')"
                        style="background: linear-gradient(135deg, #233d2c 0%, #192b1f 100%); color: #b7e4c7; border: 1px solid rgba(132, 204, 22, 0.4); font-size: 13.5px; transition: all 0.2s ease;"
                        title="Mở mã QR chia sẻ chuyến đi bằng điện thoại">
                    <i class="bi bi-qr-code-scan" style="color: #a3e635;"></i>
                    <span>Mã QR chia sẻ</span>
                </button>
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
                    <a href="<?= $escape($baseUrl) ?>/users/<?= (int) $trip['user_id'] ?>" 
                       class="fw-bold text-decoration-none d-inline-flex align-items-center gap-1 text-dark" 
                       title="Xem hồ sơ & uy tín người tổ chức">
                        <?= $escape($trip['username']) ?> <i class="bi bi-box-arrow-up-right text-muted" style="font-size: 11px;"></i>
                    </a>
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
                <div class="p-3 rounded-4 d-flex align-items-center gap-2 mb-0" style="background: rgba(155, 189, 85, 0.14); border: 1px solid rgba(155, 189, 85, 0.32);">
                    <i class="bi bi-eye-fill fs-5" style="color: var(--color-moss);"></i>
                    <span style="color: var(--color-ink);">Tài khoản của bạn đang ở vai trò <strong>Khách (Chỉ xem)</strong> nên chỉ có thể xem thông tin chuyến đi, không thể đăng ký tham gia.</span>
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
                    <div class="p-3.5 p-md-4 rounded-4 mb-3 border shadow-sm" style="background: rgba(46, 125, 50, 0.05); border-color: rgba(46, 125, 50, 0.28) !important;">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-sm" 
                                      style="width: 42px; height: 42px; background: #2e7d32; font-size: 20px;">
                                    <i class="bi bi-check-lg"></i>
                                </span>
                                <div>
                                    <h3 class="h6 mb-1 fw-bold text-success" style="font-size: 16px;">
                                        Bạn đã đăng ký tham gia chuyến đi này thành công!
                                    </h3>
                                    <p class="text-muted small mb-0" style="line-height: 1.5;">
                                        Hãy chủ động giữ liên lạc với người tổ chức (<strong><?= $escape($trip['username']) ?></strong>) để chốt lịch hẹn, phương tiện di chuyển và phân chia đồ dùng chung.
                                    </p>
                                </div>
                            </div>
                            <form method="post" action="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>/leave"
                                  onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn hủy đăng ký tham gia chuyến đi “<?= $escape(addslashes($trip['title'])) ?>” không?', 'Xác nhận rời nhóm', 'Rời khỏi nhóm đi chung?', 'Xác nhận hủy');" class="m-0">
                                <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5" type="submit" title="Rời khỏi nhóm nếu bạn bận việc đột xuất">
                                    <i class="bi bi-box-arrow-left me-1"></i> Hủy đăng ký (Rời nhóm)
                                </button>
                            </form>
                        </div>

                        <!-- Kênh liên lạc & Kết nối trực tiếp với người tổ chức -->
                        <div class="p-3 rounded-3 bg-white border d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-xs">
                            <div class="d-flex align-items-center gap-2.5">
                                <?php if (!empty($trip['avatar_path'])): ?>
                                    <a href="<?= $escape($baseUrl) ?>/users/<?= (int) $trip['user_id'] ?>" title="Xem hồ sơ &amp; uy tín người tổ chức">
                                        <img src="<?= $escape(upload_url($trip['avatar_path'])) ?>" alt="Ảnh người tổ chức" class="rounded-circle object-fit-cover shadow-sm border" width="44" height="44">
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $escape($baseUrl) ?>/users/<?= (int) $trip['user_id'] ?>" class="text-decoration-none" title="Xem hồ sơ &amp; uy tín người tổ chức">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 44px; height: 44px; background: #233d2c; font-size: 15px;">
                                            <?= strtoupper(mb_substr($trip['username'], 0, 1)) ?>
                                        </div>
                                    </a>
                                <?php endif; ?>
                                <div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <a href="<?= $escape($baseUrl) ?>/users/<?= (int) $trip['user_id'] ?>" 
                                           class="fw-bold text-decoration-none d-inline-flex align-items-center gap-1 text-dark" 
                                           title="Xem hồ sơ &amp; uy tín người tổ chức"
                                           style="font-size: 14.5px;">
                                            <?= $escape($trip['username']) ?> <i class="bi bi-box-arrow-up-right text-muted" style="font-size: 10.5px;"></i>
                                        </a>
                                        <span class="badge text-bg-success rounded-pill" style="font-size: 10px;">Trưởng đoàn</span>
                                    </div>
                                    <div class="text-muted small mt-0.5" style="font-size: 12.5px;">
                                        <i class="bi bi-envelope-at me-1 text-primary"></i><?= $escape($trip['email'] ?? 'Chưa cập nhật email') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" 
                                        class="btn btn-sm btn-success text-white rounded-pill px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-sm"
                                        onclick="openContactOrganizerModal()"
                                        title="Xem chi tiết các kênh liên lạc &amp; quy tắc đi chung"
                                        style="height: 36px; font-size: 13px;">
                                    <i class="bi bi-chat-quote-fill"></i> Kênh liên lạc
                                </button>
                            </div>
                        </div>
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
                <div class="p-3 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(155, 189, 85, 0.14); border: 1px solid rgba(155, 189, 85, 0.32);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5" style="color: var(--color-moss);"></i>
                        <span style="color: var(--color-ink);">Đăng nhập tài khoản để đăng ký tham gia chuyến đi này.</span>
                    </div>
                    <a class="nature-btn-primary px-3.5 py-1.5 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/login" style="width: auto; height: 38px; font-size: 13.5px;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập ngay
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
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2.5 pb-2.5 border-bottom">
            <div class="d-flex align-items-center gap-2.5">
                <span class="rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: rgba(155, 189, 85, 0.2); color: var(--color-moss);">
                    <i class="bi bi-cloud-sun-fill fs-5"></i>
                </span>
                <div>
                    <h2 class="h5 fw-bold mb-0" style="color: var(--color-ink); font-size: 17px;">Thời tiết &amp; Môi trường dã ngoại</h2>
                    <span class="text-muted small" style="font-size: 12.5px;">Theo dõi điều kiện tự nhiên theo thời gian thực để chuẩn bị chuyến đi an toàn</span>
                </div>
            </div>
            <?php if (!empty($trip['location'])): ?>
                <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 shadow-sm" style="background: rgba(35, 38, 31, 0.08); color: var(--color-ink); border: 1px solid rgba(35, 38, 31, 0.16); font-size: 12px;">
                    <i class="bi bi-geo-alt-fill text-danger"></i><?= $escape($trip['location']) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($hasCoordinates): ?>
            <div id="trip-weather-panel" class="p-4 p-md-5 rounded-4 mb-3" style="background: rgba(20, 28, 17, 0.88); border: 1px solid rgba(155, 189, 85, 0.3); border-radius: 28px !important;">
                <div class="d-flex align-items-center gap-2 text-white">
                    <span class="spinner-border spinner-border-sm text-success" role="status"></span>
                    <span style="font-size: 14px;">Đang tải dữ liệu thời tiết thực địa tại <?= $escape($trip['location']) ?>...</span>
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
                            null,
                            document.getElementById('trip-weather-panel'),
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

    <!-- Modal Kênh liên lạc với Người tổ chức chuyến đi -->
    <div class="modal fade" id="contactOrganizerModal" tabindex="-1" aria-labelledby="contactOrganizerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; background: #ffffff; color: #23261f; overflow: hidden;">
                <!-- Modal Header -->
                <div class="modal-header border-bottom px-4 pt-4 pb-3" style="background: #fafbfa; border-color: rgba(35, 38, 31, 0.08) !important;">
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0" 
                              style="width: 40px; height: 40px; background: #233d2c;">
                            <i class="bi bi-chat-quote-fill fs-5"></i>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="contactOrganizerModalLabel" style="font-size: 1.1rem; color: #1e241b;">
                                Liên hệ người tổ chức chuyến đi
                            </h5>
                            <span class="text-muted small" style="font-size: 12px;"><?= $escape(mb_strimwidth($trip['title'], 0, 45, '…')) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Organizer Card -->
                    <div class="d-flex align-items-center gap-3 p-3 rounded-4 mb-3" style="background: rgba(155, 189, 85, 0.12); border: 1px solid rgba(155, 189, 85, 0.28);">
                        <?php if (!empty($trip['avatar_path'])): ?>
                            <img src="<?= $escape(upload_url($trip['avatar_path'])) ?>" alt="" class="rounded-circle object-fit-cover shadow-sm border flex-shrink-0" width="54" height="54">
                        <?php else: ?>
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0" style="width: 54px; height: 54px; background: #233d2c; font-size: 20px;">
                                <?= strtoupper(mb_substr($trip['username'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <strong class="fs-6 text-dark"><?= $escape($trip['username']) ?></strong>
                                <span class="badge text-bg-success rounded-pill" style="font-size: 10.5px;">Trưởng đoàn</span>
                            </div>
                            <div class="text-muted small mt-0.5">
                                <?= !empty($trip['bio']) ? $escape(mb_strimwidth($trip['bio'], 0, 80, '…')) : 'Thành viên khởi xướng chuyến dã ngoại' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Kênh trao đổi trực tiếp -->
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 13.5px;">
                        <i class="bi bi-send-check-fill text-success me-1"></i>Các phương thức trao đổi
                    </h6>
                    <div class="list-group list-group-flush mb-3 rounded-3 border">
                        <!-- Email -->
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center" style="gap: 14px;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                                    <i class="bi bi-envelope-at-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="small fw-bold text-dark">Email liên lạc</div>
                                    <div class="small text-muted" id="organizerEmailText"><?= $escape($trip['email'] ?? 'admin@forum.local') ?></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" onclick="copyOrganizerEmail()" style="font-size: 11.5px; height: 30px; font-weight: 500;" title="Sao chép email">
                                    <i class="bi bi-clipboard"></i> <span>Copy</span>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" 
                                        onclick="focusContactMessageForm()"
                                        style="font-size: 11.5px; height: 30px; font-weight: 500;" 
                                        title="Soạn thư gửi trưởng đoàn">
                                    <i class="bi bi-envelope-fill"></i> <span>Gửi thư</span>
                                </button>
                            </div>
                        </div>

                        <!-- Trang cá nhân -->
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center" style="gap: 14px;">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                    <i class="bi bi-person-vcard-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="small fw-bold text-dark">Hồ sơ cá nhân</div>
                                    <div class="small text-muted">Xem bài viết &amp; đánh giá uy tín</div>
                                </div>
                            </div>
                            <a href="<?= $escape($baseUrl) ?>/users/<?= (int)$trip['user_id'] ?>" target="_blank" 
                               class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" 
                               style="font-size: 11.5px; height: 30px; font-weight: 500;"
                               title="Mở hồ sơ & đánh giá uy tín">
                                <span>Mở hồ sơ</span> <i class="bi bi-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Khung Soạn thư gửi Trưởng đoàn trực tiếp -->
                    <div class="border rounded-4 p-3 mb-3" style="background: #ffffff; border-color: rgba(35, 38, 31, 0.12) !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                            <div class="fw-bold text-dark d-flex align-items-center" style="font-size: 13.5px; gap: 6px;">
                                <i class="bi bi-chat-left-text-fill text-success"></i>
                                <span>Gửi thư / Tin nhắn cho Trưởng đoàn</span>
                            </div>
                            <span class="badge rounded-pill text-bg-light border text-muted" style="font-size: 10.5px; font-weight: 500;">
                                Thông báo tức thì
                            </span>
                        </div>

                        <!-- Form soạn tin -->
                        <div id="contactFormBox">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-1" style="font-size: 11.5px;">Tên người gửi</label>
                                    <input type="text" id="contactSenderName" class="form-control form-control-sm rounded-3" 
                                           value="<?= $escape(Auth::check() ? Auth::user()['username'] : '') ?>" 
                                           placeholder="Họ tên của bạn" <?= Auth::check() ? 'readonly style="background: #f8f9fa;"' : '' ?>>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-1" style="font-size: 11.5px;">SĐT / Zalo / Email phản hồi</label>
                                    <input type="text" id="contactSenderContact" class="form-control form-control-sm rounded-3" 
                                           value="<?= $escape(Auth::check() ? (Auth::user()['email'] ?? '') : '') ?>" 
                                           placeholder="SĐT hoặc Email">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-1" style="font-size: 11.5px;">Nội dung thư / Câu hỏi trao đổi *</label>
                                <textarea id="contactMessageInput" class="form-control form-control-sm rounded-3" rows="3" 
                                          placeholder="Chào trưởng đoàn <?= $escape($trip['username']) ?>, mình muốn hỏi thêm về điểm đón, chi phí dự kiến và đồ dùng cần chuẩn bị..."
                                          style="resize: none; font-size: 13px; line-height: 1.5;"></textarea>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-danger small" id="contactErrorMessage" style="font-size: 11px; display: none;"></span>
                                    <span class="text-muted small ms-auto" id="contactCharCount" style="font-size: 11px;">Tối thiểu 5 ký tự</span>
                                </div>
                            </div>
                        </div>

                        <!-- Trạng thái gửi thư thành công -->
                        <div id="contactSuccessBox" class="d-none text-center py-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                 style="width: 52px; height: 52px; background: rgba(40, 167, 69, 0.12); color: #28a745;">
                                <i class="bi bi-check2-circle fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 15px;">Đã gửi thư thành công!</h6>
                            <p class="text-muted small mb-3" style="font-size: 12.5px; max-width: 380px; margin: 0 auto;">
                                Thư trao đổi đã được chuyển vào thông báo của Trưởng đoàn <strong><?= $escape($trip['username']) ?></strong>. Bạn sẽ sớm nhận được phản hồi.
                            </p>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" onclick="resetContactForm()" style="font-size: 12px;">
                                <i class="bi bi-pencil-square me-1"></i> Soạn thêm câu hỏi khác
                            </button>
                        </div>
                    </div>

                    <!-- Gợi ý trao đổi an toàn -->
                    <div class="p-3 rounded-3 bg-light border" style="font-size: 12.5px; line-height: 1.55; color: #4b5563;">
                        <strong class="d-block mb-1 text-dark"><i class="bi bi-shield-check text-success me-1"></i>Lưu ý khi phối hợp đi chung:</strong>
                        <ul class="mb-0 ps-3">
                            <li>Xác nhận vị trí điểm đón (vd: bến xe, điểm hẹn) ít nhất 24h trước khi khởi hành.</li>
                            <li>Tham khảo mục <a href="<?= $escape($baseUrl) ?>/checklists" class="text-success fw-semibold">Checklist</a> để chuẩn bị trang bị cá nhân phù hợp.</li>
                            <li>Thống nhất phương thức cọc / chia sẻ chi phí minh bạch với trưởng đoàn.</li>
                        </ul>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer px-4 py-3 bg-light border-top d-flex justify-content-between align-items-center">
                    <div>
                        <?php if (!empty($trip['email'])): ?>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($trip['email']) ?>&su=<?= urlencode('[Outdoor Forum] Trao đổi chuyến đi: ' . $trip['title']) ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted d-inline-flex align-items-center shadow-none" 
                               style="font-size: 12px; height: 36px;"
                               title="Mở trình soạn thư Gmail trên trình duyệt">
                                <i class="bi bi-google me-1 text-danger"></i> Mở Gmail
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <button type="button" class="btn btn-light border rounded-pill px-3.5 py-1.5 text-secondary shadow-none d-inline-flex align-items-center justify-content-center" data-bs-dismiss="modal" style="height: 38px; font-size: 13px; font-weight: 500;">
                            Đóng
                        </button>
                        <button type="button" 
                                id="btnSubmitOrganizerMessage"
                                class="btn btn-success rounded-pill px-4 py-1.5 d-inline-flex align-items-center justify-content-center shadow-sm text-white" 
                                onclick="submitOrganizerMessage(<?= (int)$trip['id'] ?>)"
                                style="height: 38px; font-size: 13px; font-weight: 600; background: #233d2c; border: 1px solid #233d2c;">
                            <i class="bi bi-send-fill me-1.5"></i> Gửi thư ngay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function openContactOrganizerModal() {
        var modalEl = document.getElementById('contactOrganizerModal');
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            var modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }
    }

    function copyOrganizerEmail() {
        var emailEl = document.getElementById('organizerEmailText');
        if (emailEl) {
            var email = emailEl.textContent.trim();
            navigator.clipboard.writeText(email).then(function() {
                if (window.ForumToast) {
                    ForumToast.show('Đã sao chép email người tổ chức vào bộ nhớ tạm!', 'success');
                } else {
                    alert('Đã sao chép email người tổ chức: ' + email);
                }
            }).catch(function() {
                prompt('Sao chép email người tổ chức:', email);
            });
        }
    }

    function focusContactMessageForm() {
        var input = document.getElementById('contactMessageInput');
        if (input) {
            input.focus();
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            input.style.borderColor = '#233d2c';
            input.style.boxShadow = '0 0 0 3px rgba(35, 61, 44, 0.15)';
            setTimeout(function() {
                input.style.borderColor = '';
                input.style.boxShadow = '';
            }, 1500);
        }
    }

    function resetContactForm() {
        var formBox = document.getElementById('contactFormBox');
        var successBox = document.getElementById('contactSuccessBox');
        var sendBtn = document.getElementById('btnSubmitOrganizerMessage');
        var input = document.getElementById('contactMessageInput');
        if (formBox) formBox.classList.remove('d-none');
        if (successBox) successBox.classList.add('d-none');
        if (sendBtn) {
            sendBtn.classList.remove('d-none');
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="bi bi-send-fill me-1.5"></i> Gửi thư ngay';
        }
        if (input) {
            input.value = '';
            input.focus();
        }
    }

    function submitOrganizerMessage(tripId) {
        var messageInput = document.getElementById('contactMessageInput');
        var nameInput = document.getElementById('contactSenderName');
        var contactInput = document.getElementById('contactSenderContact');
        var errEl = document.getElementById('contactErrorMessage');
        var sendBtn = document.getElementById('btnSubmitOrganizerMessage');
        var formBox = document.getElementById('contactFormBox');
        var successBox = document.getElementById('contactSuccessBox');

        var message = messageInput ? messageInput.value.trim() : '';
        var senderName = nameInput ? nameInput.value.trim() : '';
        var senderContact = contactInput ? contactInput.value.trim() : '';

        if (message.length < 5) {
            if (errEl) {
                errEl.textContent = 'Vui lòng nhập nội dung thư tối thiểu 5 ký tự.';
                errEl.style.display = 'block';
            }
            if (messageInput) messageInput.focus();
            return;
        }

        if (errEl) errEl.style.display = 'none';

        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Đang gửi...';
        }

        var csrfToken = '';
        var csrfInput = document.querySelector('input[name="_csrf"]');
        if (csrfInput) csrfToken = csrfInput.value;

        var body = 'message=' + encodeURIComponent(message)
                 + '&sender_name=' + encodeURIComponent(senderName)
                 + '&sender_contact=' + encodeURIComponent(senderContact)
                 + '&_csrf=' + encodeURIComponent(csrfToken);

        fetch('<?= $escape($baseUrl) ?>/trips/' + tripId + '/contact', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                if (formBox) formBox.classList.add('d-none');
                if (successBox) successBox.classList.remove('d-none');
                if (sendBtn) sendBtn.classList.add('d-none');

                if (window.ForumToast) {
                    ForumToast.show(data.message || 'Đã gửi thư cho trưởng đoàn thành công!', 'success');
                }
            } else {
                if (errEl) {
                    errEl.textContent = data.message || 'Gửi thư thất bại, vui lòng thử lại.';
                    errEl.style.display = 'block';
                }
                if (sendBtn) {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="bi bi-send-fill me-1.5"></i> Gửi thư ngay';
                }
            }
        })
        .catch(function(err) {
            if (errEl) {
                errEl.textContent = 'Lỗi kết nối máy chủ, vui lòng thử lại.';
                errEl.style.display = 'block';
            }
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="bi bi-send-fill me-1.5"></i> Gửi thư ngay';
            }
        });
    }
    </script>
</main>
