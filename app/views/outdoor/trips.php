<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$user = class_exists('Auth') ? Auth::user() : Session::get('user');
$userId = is_array($user) ? (int) $user['id'] : null;
$isAdmin = class_exists('Auth') ? Auth::isAdmin($user) : (is_array($user) && (((int) ($user['role_id'] ?? 0) === 2) || (($user['role_name'] ?? '') === 'admin')));
$isMember = class_exists('Auth') ? Auth::isMember($user) : is_array($user);
$isGuestAccount = is_array($user) && !$isMember;
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<main class="app-container" style="max-width: 1100px;">
    <style>
    /* Icon lịch bên phải ô thời gian khởi hành & kích hoạt picker mượt mà */
    #trip-date::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        top: 0;
        width: 48px;
        height: 100%;
        cursor: pointer;
        z-index: 4;
    }
    </style>
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 page-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(155, 189, 85, 0.18); color: var(--color-moss); border: 1px solid rgba(155, 189, 85, 0.35);">
                    <i class="bi bi-people-fill me-1"></i>Cộng đồng kết nối
                </span>
            </div>
            <h1 class="page-title mb-1">Nhóm đi chung</h1>
            <p class="page-subtitle mb-0">Rủ rê bạn đồng hành, ghép xe và cùng nhau chia sẻ chi phí cho những chuyến dã ngoại tuyệt vời.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <?php if ($isGuestAccount): ?>
                <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1" style="background: rgba(255, 255, 255, 0.12); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(255, 255, 255, 0.22); font-size: 13px; font-weight: 500;">
                    <i class="bi bi-eye me-1"></i> Tài khoản Khách (Chỉ xem)
                </span>
            <?php endif; ?>
            <a class="nature-btn-secondary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/checklists" style="height: 42px; font-size: 13.5px;">
                <i class="bi bi-card-checklist"></i> Checklist
            </a>
            <a class="nature-btn-secondary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/places" style="height: 42px; font-size: 13.5px;">
                <i class="bi bi-map-fill"></i> Bản đồ
            </a>
        </div>
    </div>

    <!-- Filter Tabs (Sắp tới / Tất cả / Của tôi) -->
    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <span class="text-white-50 small me-1"><i class="bi bi-funnel me-1"></i>Lọc:</span>
        <a href="<?= $escape($baseUrl) ?>/trips?filter=upcoming"
           class="badge rounded-pill text-decoration-none px-3 py-2 <?= empty($currentFilter) || $currentFilter === 'upcoming' ? 'text-bg-success' : 'text-bg-dark border border-white-20' ?>">
            <i class="bi bi-clock-history me-1"></i>Sắp khởi hành
        </a>
        <a href="<?= $escape($baseUrl) ?>/trips?filter=all"
           class="badge rounded-pill text-decoration-none px-3 py-2 <?= $currentFilter === 'all' ? 'text-bg-success' : 'text-bg-dark border border-white-20' ?>">
            <i class="bi bi-grid me-1"></i>Tất cả chuyến
        </a>
        <?php if ($userId !== null): ?>
            <a href="<?= $escape($baseUrl) ?>/trips?filter=mine"
               class="badge rounded-pill text-decoration-none px-3 py-2 <?= $currentFilter === 'mine' ? 'text-bg-success' : 'text-bg-dark border border-white-20' ?>">
                <i class="bi bi-person-check me-1"></i>Chuyến của tôi
            </a>
        <?php endif; ?>
    </div>

    <!-- Create Trip Section (Member / Moderator / Admin only) -->
    <?php if ($isMember): ?>
        <section class="nature-card p-4 mb-4" style="border-radius: 28px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: rgba(155, 189, 85, 0.2); color: var(--color-moss);">
                        <i class="bi bi-plus-circle-fill fs-5"></i>
                    </span>
                    <h2 class="h5 mb-0 fw-semibold" style="color: var(--color-ink);">Đăng chuyến đi mới</h2>
                </div>
                <span class="text-muted small">Tìm kiếm đồng đội</span>
            </div>

            <form method="post" action="<?= $escape($baseUrl) ?>/trips">
                <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="nature-label" for="trip-title">Tên chuyến đi <span class="text-danger">*</span></label>
                        <input class="nature-input" id="trip-title" name="title" maxlength="150" required
                               value="<?= $escape($old['title'] ?? '') ?>" placeholder="Ví dụ: Trekking Tà Xùa 2N1Đ, Cắm trại Hồ Trị An...">
                        <?php if (!empty($errors['title'])): ?><div class="text-danger small mt-1"><?= $escape($errors['title']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="nature-label" for="trip-location">Địa điểm đến <span class="text-danger">*</span></label>
                        <input class="nature-input" id="trip-location" name="location" maxlength="150" required
                               value="<?= $escape($old['location'] ?? '') ?>" placeholder="Ví dụ: Xã Tà Xùa, Huyện Bắc Yên, Sơn La">
                        <?php if (!empty($errors['location'])): ?><div class="text-danger small mt-1"><?= $escape($errors['location']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $rawStartsAt = (string)($old['starts_at'] ?? '');
                        $startsAtVal = '';
                        if ($rawStartsAt !== '') {
                            if (str_contains($rawStartsAt, ' ')) {
                                $rawStartsAt = str_replace(' ', 'T', $rawStartsAt);
                            }
                            $startsAtVal = strlen($rawStartsAt) >= 16 ? substr($rawStartsAt, 0, 16) : $rawStartsAt;
                        }
                        ?>
                        <label class="nature-label" for="trip-date">Thời gian khởi hành <span class="text-danger">*</span></label>
                        <div class="position-relative d-flex align-items-center">
                            <input class="nature-input pe-5" id="trip-date" name="starts_at" type="datetime-local" required
                                   step="60"
                                   min="<?= date('Y-m-d\TH:i') ?>"
                                   value="<?= $escape($startsAtVal) ?>"
                                   onclick="if(typeof this.showPicker === 'function') this.showPicker();"
                                   style="cursor: pointer;">
                            <span class="position-absolute end-0 me-3 d-flex align-items-center justify-content-center"
                                  style="pointer-events: none; z-index: 2;"
                                  title="Chọn ngày và giờ khởi hành">
                                <i class="bi bi-calendar-event fs-5" style="color: #2e7d32;"></i>
                            </span>
                        </div>
                        <?php if (!empty($errors['starts_at'])): ?><div class="text-danger small mt-1"><?= $escape($errors['starts_at']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="nature-label" for="trip-limit">Số người tối đa <span class="text-muted fw-normal">(Đã tính người tổ chức)</span> <span class="text-danger">*</span></label>
                        <input class="nature-input" id="trip-limit" name="max_participants" type="number" min="2" max="100" required
                               value="<?= $escape($old['max_participants'] ?? '8') ?>">
                        <?php if (!empty($errors['max_participants'])): ?><div class="text-danger small mt-1"><?= $escape($errors['max_participants']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="nature-label" for="trip-lat">Vĩ độ (Lat) <span class="text-muted fw-normal">(Tùy chọn, để xem dự báo thời tiết)</span></label>
                        <input class="nature-input" id="trip-lat" name="lat" type="number" step="any"
                               value="<?= $escape($old['lat'] ?? '') ?>" placeholder="Ví dụ: 21.2833">
                    </div>
                    <div class="col-md-6">
                        <label class="nature-label" for="trip-lng">Kinh độ (Lng) <span class="text-muted fw-normal">(Tùy chọn, để xem dự báo thời tiết)</span></label>
                        <input class="nature-input" id="trip-lng" name="lng" type="number" step="any"
                               value="<?= $escape($old['lng'] ?? '') ?>" placeholder="Ví dụ: 104.3167">
                        <?php if (!empty($errors['coordinates'])): ?><div class="text-danger small mt-1"><?= $escape($errors['coordinates']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="nature-label" for="trip-description">Kế hoạch & Mô tả chuyến đi <span class="text-danger">*</span></label>
                        <textarea class="nature-textarea" id="trip-description" name="description" rows="4" maxlength="3000" required
                                  placeholder="Mô tả lịch trình, phương tiện di chuyển, chi phí ước tính, trang bị cần có..."><?= $escape($old['description'] ?? '') ?></textarea>
                        <?php if (!empty($errors['description'])): ?><div class="text-danger small mt-1"><?= $escape($errors['description']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12 text-end pt-2">
                        <button class="nature-btn-primary px-4 py-2" type="submit" style="width: auto; height: 44px;">
                            <i class="bi bi-send-fill me-1"></i> Đăng chuyến đi
                        </button>
                    </div>
                </div>
            </form>
        </section>
    <?php elseif ($isGuestAccount): ?>
        <div class="p-3 mb-4 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(155, 189, 85, 0.14); border: 1px solid rgba(155, 189, 85, 0.32);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-eye-fill fs-5" style="color: var(--color-moss);"></i>
                <span class="text-white">Tài khoản của bạn đang ở vai trò <strong>Khách (Chỉ xem)</strong> nên chỉ có thể xem thông tin các nhóm đi chung, không thể đăng chuyến đi mới hoặc đăng ký tham gia.</span>
            </div>
        </div>
    <?php else: ?>
        <div class="p-3 mb-4 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(155, 189, 85, 0.14); border: 1px solid rgba(155, 189, 85, 0.32);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill fs-5" style="color: var(--color-moss);"></i>
                <span class="text-white">Đăng nhập để đăng chuyến đi hoặc đăng ký tham gia nhóm.</span>
            </div>
            <a href="<?= $escape($baseUrl) ?>/login" class="nature-btn-primary py-1 px-3.5 d-inline-flex align-items-center gap-1" style="width: auto; height: 38px; font-size: 13.5px;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập ngay
            </a>
        </div>
    <?php endif; ?>

    <!-- Trips Grid -->
    <div class="row g-4">
        <?php if (empty($trips)): ?>
            <div class="col-12">
                <div class="nature-card text-center py-5" style="border-radius: 28px;">
                    <i class="bi bi-compass fs-1 text-muted d-block mb-3"></i>
                    <h3 class="h5 fw-normal mb-1" style="color: var(--color-ink);">Chưa có chuyến đi nào phù hợp</h3>
                    <p class="text-muted small">Hãy trở thành người khởi xướng chuyến dã ngoại đầu tiên!</p>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($trips as $trip): ?>
            <?php
            $isOwner = $userId !== null && (int) $trip['user_id'] === $userId;
            $isJoined = !empty($trip['is_joined']) && (int) $trip['is_joined'] > 0;
            $canDelete = $isMember && ($isOwner || $isAdmin);
            $pCount = (int) $trip['participant_count'];
            $maxP = (int) $trip['max_participants'];
            $spotsLeft = max(0, $maxP - $pCount);
            $hasStarted = strtotime($trip['starts_at']) <= time();
            $percent = min(100, (int) round(($pCount / max(1, $maxP)) * 100));
            ?>
            <div class="col-12 col-md-6">
                <article class="nature-card h-100 d-flex flex-column p-4" style="border-radius: 28px; <?= $isJoined ? 'border-color: rgba(34, 197, 94, 0.4); box-shadow: 0 4px 20px rgba(34, 197, 94, 0.08);' : '' ?>">
                    <!-- Top Status -->
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <?php if ($hasStarted): ?>
                                <span class="badge text-bg-secondary"><i class="bi bi-check-all me-1"></i>Đã khởi hành</span>
                            <?php endif; ?>

                            <?php if ($isOwner): ?>
                                <span class="badge" style="background-color: #1e3a8a; color: #fff;"><i class="bi bi-star-fill me-1 text-warning"></i>Bạn tổ chức</span>
                            <?php elseif ($isJoined): ?>
                                <span class="badge" style="background-color: #166534; color: #fff;"><i class="bi bi-check-circle-fill me-1 text-white"></i>Bạn đã tham gia</span>
                            <?php endif; ?>

                            <?php if (!$hasStarted): ?>
                                <?php if ($spotsLeft === 0): ?>
                                    <span class="badge text-bg-warning"><i class="bi bi-lock-fill me-1"></i>Đã đủ người</span>
                                <?php elseif ($isJoined || $isOwner): ?>
                                    <span class="badge" style="background: rgba(35, 61, 44, 0.08); color: var(--color-ink); border: 1px solid rgba(35, 61, 44, 0.18);"><i class="bi bi-people me-1"></i>Còn <?= $spotsLeft ?> chỗ</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success"><i class="bi bi-person-plus-fill me-1"></i>Còn <?= $spotsLeft ?> chỗ trống</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($canDelete): ?>
                            <form method="post" action="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>/delete"
                                  onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn hủy chuyến đi “<?= $escape(addslashes($trip['title'])) ?>” khỏi danh sách đi chung không? Hành động này không thể hoàn tác.', 'Xác nhận xóa', 'Hủy chuyến đi chung?');" class="m-0">
                                <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Hủy chuyến" aria-label="Hủy chuyến">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Title -->
                    <h2 class="h5 fw-semibold mt-1 mb-2">
                        <a href="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>" style="color: var(--color-ink); text-decoration: none;">
                            <?= $escape($trip['title']) ?>
                        </a>
                    </h2>

                    <!-- Meta Details -->
                    <div class="mb-2" style="font-size: 13.5px; color: var(--color-ink);">
                        <div class="mb-1 d-flex align-items-center gap-1.5 text-truncate">
                            <i class="bi bi-geo-alt-fill text-danger flex-shrink-0"></i>
                            <span class="text-truncate"><?= $escape($trip['location']) ?></span>
                        </div>
                        <div class="mb-1 d-flex align-items-center gap-1.5">
                            <i class="bi bi-calendar-event text-success flex-shrink-0"></i>
                            <span><?= date('H:i · d/m/Y', strtotime($trip['starts_at'])) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 text-muted small">
                            <?php if (!empty($trip['avatar_path'])): ?>
                                <img src="<?= $escape(upload_url($trip['avatar_path'])) ?>" alt="" class="rounded-circle object-fit-cover shadow-sm border flex-shrink-0" width="20" height="20">
                            <?php else: ?>
                                <i class="bi bi-person-fill flex-shrink-0"></i>
                            <?php endif; ?>
                            <span>Tổ chức bởi <strong><?= $escape($trip['username']) ?></strong></span>
                        </div>
                    </div>

                    <!-- Description Preview -->
                    <p class="small text-muted flex-grow-1 mb-3" style="line-height: 1.55;">
                        <?= $escape(mb_strimwidth($trip['description'], 0, 150, '…')) ?>
                    </p>

                    <!-- Participant Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Thành viên</span>
                            <span class="fw-semibold" style="color: var(--color-ink);"><?= $pCount ?> / <?= $maxP ?> người</span>
                        </div>
                        <div class="progress" style="height: 6px; background: rgba(0,0,0,0.08); border-radius: 99px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%; border-radius: 99px;"
                                 aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- View Details & QR Share Buttons -->
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button"
                                    class="btn btn-sm rounded-pill d-inline-flex align-items-center gap-1 px-2.5 py-1 fw-semibold"
                                    onclick="ForumAjax.openQrShareModal('/trips/<?= (int) $trip['id'] ?>', '<?= $escape(addslashes($trip['title'])) ?>', 'trip')"
                                    style="background: rgba(35, 61, 44, 0.09); color: #233d2c; border: 1px solid rgba(35, 61, 44, 0.24); font-size: 12px; height: 32px;"
                                    title="Mã QR chia sẻ nhanh chuyến đi bằng điện thoại">
                                <i class="bi bi-qr-code-scan text-success"></i> Mã QR chia sẻ
                            </button>
                            <?php if ($trip['lat'] && $trip['lng']): ?>
                                <span class="text-muted small d-none d-sm-inline"><i class="bi bi-cloud-sun text-info me-1"></i>Có thời tiết</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($isOwner): ?>
                            <a class="nature-btn-secondary py-1 px-3 d-inline-flex align-items-center gap-1.5"
                               href="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>" style="font-size: 13px; height: 36px;" title="Quản lý chuyến đi bạn đã tạo">
                                <i class="bi bi-gear-fill text-primary" style="font-size: 12px;"></i> Quản lý chuyến đi <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php elseif ($isJoined): ?>
                            <a class="nature-btn-secondary py-1 px-3 d-inline-flex align-items-center gap-1.5"
                               href="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>" style="font-size: 13px; height: 36px; border-color: rgba(34, 197, 94, 0.45); background: rgba(34, 197, 94, 0.08);" title="Xem chi tiết và kế hoạch chuyến đi bạn đã tham gia">
                                <i class="bi bi-check2-circle text-success" style="font-size: 14px;"></i> Xem chi tiết chuyến đi <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php elseif ($hasStarted): ?>
                            <a class="nature-btn-secondary py-1 px-3 d-inline-flex align-items-center gap-1"
                               href="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>" style="font-size: 13px; height: 36px;">
                                Xem chuyến đi <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php elseif ($spotsLeft === 0): ?>
                            <a class="nature-btn-secondary py-1 px-3 d-inline-flex align-items-center gap-1"
                               href="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>" style="font-size: 13px; height: 36px;">
                                Xem chi tiết <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php else: ?>
                            <a class="nature-btn-secondary py-1 px-3 d-inline-flex align-items-center gap-1"
                               href="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>" style="font-size: 13px; height: 36px;">
                                Xem chi tiết & Đăng ký <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Thanh phân trang Đi chung (Đồng bộ chuẩn Nature Glass giống Dụng cụ) -->
    <?php if (($totalTrips ?? 0) > 0): ?>
        <?php $filterQuery = !empty($currentFilter) ? 'filter=' . urlencode($currentFilter) . '&' : ''; ?>
        <nav aria-label="Điều hướng phân trang nhóm đi chung" class="mt-5 mb-4">
            <ul class="pagination forum-pagination justify-content-center flex-wrap">
                <!-- Nút trang trước -->
                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill" 
                       href="<?= $escape($baseUrl) ?>/trips?<?= $filterQuery ?>page=<?= max(1, $currentPage - 1) ?>" 
                       tabindex="<?= ($currentPage <= 1) ? '-1' : '0' ?>"
                       aria-disabled="<?= ($currentPage <= 1) ? 'true' : 'false' ?>">
                        <i class="bi bi-chevron-left me-1"></i> Trước
                    </a>
                </li>

                <!-- Danh sách số trang -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= $escape($baseUrl) ?>/trips?<?= $filterQuery ?>page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Nút trang sau -->
                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill" 
                       href="<?= $escape($baseUrl) ?>/trips?<?= $filterQuery ?>page=<?= min($totalPages, $currentPage + 1) ?>" 
                       tabindex="<?= ($currentPage >= $totalPages) ? '-1' : '0' ?>"
                       aria-disabled="<?= ($currentPage >= $totalPages) ? 'true' : 'false' ?>">
                        Sau <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </li>
            </ul>

            <div class="text-center text-white-50 small mt-2">
                Trang <?= $currentPage ?> / <?= $totalPages ?> (Hiển thị <?= count($trips) ?> chuyến đi / trang • Tổng số <?= $totalTrips ?> chuyến đi)
            </div>
        </nav>
    <?php endif; ?>
</main>
