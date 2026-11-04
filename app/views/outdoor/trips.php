<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$user = Session::get('user');
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<main class="app-container" style="max-width: 1100px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-white-50 small mb-1">Rủ bạn đồng hành</p>
            <h1 class="text-white h2 mb-0">Nhóm đi chung</h1>
        </div>
        <a class="nature-btn-secondary" href="<?= $escape($baseUrl) ?>/places">Xem bản đồ địa điểm</a>
    </div>

    <?php if (is_array($user)): ?>
        <section class="nature-card p-4 mb-4">
            <h2 class="h5 mb-3">Đăng chuyến đi</h2>
            <form method="post" action="<?= $escape($baseUrl) ?>/trips">
                <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="trip-title">Tên chuyến</label>
                        <input class="form-control" id="trip-title" name="title" maxlength="150" required
                               value="<?= $escape($old['title'] ?? '') ?>" placeholder="Ví dụ: Trekking cuối tuần">
                        <?php if (!empty($errors['title'])): ?><div class="text-danger small"><?= $escape($errors['title']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trip-location">Địa điểm</label>
                        <input class="form-control" id="trip-location" name="location" maxlength="150" required
                               value="<?= $escape($old['location'] ?? '') ?>">
                        <?php if (!empty($errors['location'])): ?><div class="text-danger small"><?= $escape($errors['location']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trip-date">Thời gian khởi hành</label>
                        <input class="form-control" id="trip-date" name="starts_at" type="datetime-local" required
                               value="<?= $escape($old['starts_at'] ?? '') ?>">
                        <?php if (!empty($errors['starts_at'])): ?><div class="text-danger small"><?= $escape($errors['starts_at']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trip-limit">Số người tối đa (tính cả người tổ chức)</label>
                        <input class="form-control" id="trip-limit" name="max_participants" type="number" min="2" max="100" required
                               value="<?= $escape($old['max_participants'] ?? '10') ?>">
                        <?php if (!empty($errors['max_participants'])): ?><div class="text-danger small"><?= $escape($errors['max_participants']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trip-lat">Vĩ độ (tuỳ chọn, dùng để xem cảnh báo thời tiết)</label>
                        <input class="form-control" id="trip-lat" name="lat" type="number" step="any"
                               value="<?= $escape($old['lat'] ?? '') ?>" placeholder="Ví dụ: 21.2827">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trip-lng">Kinh độ</label>
                        <input class="form-control" id="trip-lng" name="lng" type="number" step="any"
                               value="<?= $escape($old['lng'] ?? '') ?>" placeholder="Ví dụ: 104.3047">
                        <?php if (!empty($errors['coordinates'])): ?><div class="text-danger small"><?= $escape($errors['coordinates']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="trip-description">Mô tả chuyến đi</label>
                        <textarea class="form-control" id="trip-description" name="description" rows="3" maxlength="3000" required><?= $escape($old['description'] ?? '') ?></textarea>
                        <?php if (!empty($errors['description'])): ?><div class="text-danger small"><?= $escape($errors['description']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12 text-end">
                        <button class="nature-btn-primary" type="submit">Đăng chuyến</button>
                    </div>
                </div>
            </form>
        </section>
    <?php else: ?>
        <div class="nature-alert nature-alert-success mb-4">
            <a href="<?= $escape($baseUrl) ?>/login">Đăng nhập</a> để đăng chuyến hoặc tham gia nhóm.
        </div>
    <?php endif; ?>

    <section class="row g-3">
        <?php if ($trips === []): ?>
            <p class="text-white-50">Chưa có chuyến đi sắp tới.</p>
        <?php endif; ?>
        <?php foreach ($trips as $trip): ?>
            <div class="col-md-6">
                <article class="nature-card p-4 h-100">
                    <div class="d-flex justify-content-between gap-2">
                        <h2 class="h5"><?= $escape($trip['title']) ?></h2>
                        <span class="badge text-bg-success align-self-start">
                            <?= (int) $trip['participant_count'] ?>/<?= (int) $trip['max_participants'] ?> người
                        </span>
                    </div>
                    <p class="mb-1"><i class="bi bi-geo-alt me-1"></i><?= $escape($trip['location']) ?></p>
                    <p class="text-muted small mb-2">
                        <?= date('d/m/Y H:i', strtotime($trip['starts_at'])) ?> · Tổ chức bởi <?= $escape($trip['username']) ?>
                    </p>
                    <p><?= $escape(mb_strimwidth($trip['description'], 0, 180, '…')) ?></p>
                    <a class="nature-btn-secondary" href="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>">Xem chuyến đi</a>
                </article>
            </div>
        <?php endforeach; ?>
    </section>
</main>
