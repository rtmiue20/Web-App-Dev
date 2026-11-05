<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$user = Session::get('user');
$userId = is_array($user) ? (int) $user['id'] : null;
$isAdmin = is_array($user) && (((int) ($user['role_id'] ?? 0) === 2) || (($user['role_name'] ?? '') === 'admin'));
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<main class="app-container" style="max-width: 1100px;">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 page-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(112, 214, 255, 0.15); color: #70d6ff; border: 1px solid rgba(112, 214, 255, 0.35);">
                    <i class="bi bi-people-fill me-1"></i>Cộng đồng kết nối
                </span>
            </div>
            <h1 class="page-title mb-1">Nhóm đi chung</h1>
            <p class="page-subtitle mb-0">Rủ rê bạn đồng hành, ghép xe và cùng nhau chia sẻ chi phí cho những chuyến dã ngoại tuyệt vời.</p>
        </div>
        <div class="d-flex gap-2">
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

    <!-- Create Trip Section (Logged-in only) -->
    <?php if (is_array($user)): ?>
        <section class="nature-card p-4 mb-4" style="border-radius: 28px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: rgba(112, 214, 255, 0.2); color: #70d6ff;">
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
                        <label class="nature-label" for="trip-date">Thời gian khởi hành <span class="text-danger">*</span></label>
                        <input class="nature-input" id="trip-date" name="starts_at" type="datetime-local" required
                               min="<?= date('Y-m-d\TH:i') ?>"
                               value="<?= $escape($old['starts_at'] ?? '') ?>">
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
    <?php else: ?>
        <div class="p-3 mb-4 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(112, 214, 255, 0.12); border: 1px solid rgba(112, 214, 255, 0.25);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill text-info fs-5"></i>
                <span class="text-white">Đăng nhập để đăng chuyến đi hoặc đăng ký tham gia nhóm.</span>
            </div>
            <a href="<?= $escape($baseUrl) ?>/login" class="nature-btn-primary py-1 px-3" style="width: auto; height: 36px; font-size: 13px;">
                Đăng nhập ngay
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
            $canDelete = $isOwner || $isAdmin;
            $pCount = (int) $trip['participant_count'];
            $maxP = (int) $trip['max_participants'];
            $spotsLeft = max(0, $maxP - $pCount);
            $hasStarted = strtotime($trip['starts_at']) <= time();
            $percent = min(100, (int) round(($pCount / max(1, $maxP)) * 100));
            ?>
            <div class="col-12 col-md-6">
                <article class="nature-card h-100 d-flex flex-column p-4" style="border-radius: 28px;">
                    <!-- Top Status -->
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($hasStarted): ?>
                                <span class="badge text-bg-secondary"><i class="bi bi-check-all me-1"></i>Đã khởi hành</span>
                            <?php elseif ($spotsLeft === 0): ?>
                                <span class="badge text-bg-warning"><i class="bi bi-lock-fill me-1"></i>Đã đủ người</span>
                            <?php else: ?>
                                <span class="badge text-bg-success"><i class="bi bi-person-plus-fill me-1"></i>Còn <?= $spotsLeft ?> chỗ trống</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($canDelete): ?>
                            <form method="post" action="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>/delete"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn hủy chuyến đi này?');" class="m-0">
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
                            <i class="bi bi-person-fill flex-shrink-0"></i>
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

                    <!-- View Details Button -->
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                        <?php if ($trip['lat'] && $trip['lng']): ?>
                            <span class="text-muted small"><i class="bi bi-cloud-sun text-info me-1"></i>Có dự báo thời tiết</span>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <a class="nature-btn-secondary py-1 px-3 d-inline-flex align-items-center gap-1"
                           href="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>" style="font-size: 13px; height: 36px;">
                            Xem chi tiết & Đăng ký <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</main>
