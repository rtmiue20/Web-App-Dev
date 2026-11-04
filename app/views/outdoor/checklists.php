<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$user = Session::get('user');
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<main class="app-container" style="max-width: 1100px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase text-white-50 small mb-1">Chuẩn bị trước chuyến đi</p>
            <h1 class="text-white h2 mb-0">Checklist đồ dùng</h1>
        </div>
        <a class="nature-btn-secondary" href="<?= $escape($baseUrl) ?>/trips">Tìm nhóm đi chung</a>
    </div>

    <?php if (is_array($user)): ?>
        <section class="nature-card p-4 mb-4">
            <h2 class="h5 mb-3">Tạo checklist mới</h2>
            <form method="post" action="<?= $escape($baseUrl) ?>/checklists">
                <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="checklist-title">Tên checklist</label>
                        <input class="form-control" id="checklist-title" name="title" maxlength="120" required
                               value="<?= $escape($old['title'] ?? '') ?>" placeholder="Ví dụ: Đồ dùng trekking 2 ngày">
                        <?php if (!empty($errors['title'])): ?><div class="text-danger small"><?= $escape($errors['title']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="trip-type">Loại chuyến đi</label>
                        <input class="form-control" id="trip-type" name="trip_type" maxlength="60" required
                               value="<?= $escape($old['trip_type'] ?? '') ?>" placeholder="Cắm trại, trekking...">
                        <?php if (!empty($errors['trip_type'])): ?><div class="text-danger small"><?= $escape($errors['trip_type']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="checklist-items">Đồ dùng (mỗi món một dòng)</label>
                        <textarea class="form-control" id="checklist-items" name="items" rows="5" required
                                  placeholder="Ba lô&#10;Bình nước&#10;Áo mưa"><?= $escape($old['items'] ?? '') ?></textarea>
                        <?php if (!empty($errors['items'])): ?><div class="text-danger small"><?= $escape($errors['items']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <label class="form-check-label">
                            <input class="form-check-input me-2" type="checkbox" name="is_public" value="1"
                                   <?= !array_key_exists('is_public', $old) || isset($old['is_public']) ? 'checked' : '' ?>>
                            Chia sẻ checklist cho mọi người
                        </label>
                        <button class="nature-btn-primary" type="submit">Lưu checklist</button>
                    </div>
                </div>
            </form>
        </section>
    <?php else: ?>
        <div class="nature-alert nature-alert-success mb-4">
            <a href="<?= $escape($baseUrl) ?>/login">Đăng nhập</a> để tự tạo và chia sẻ checklist.
        </div>
    <?php endif; ?>

    <section class="row g-3">
        <?php if ($checklists === []): ?>
            <p class="text-white-50">Chưa có checklist nào được chia sẻ.</p>
        <?php endif; ?>
        <?php foreach ($checklists as $checklist): ?>
            <div class="col-md-6">
                <article class="nature-card p-4 h-100">
                    <div class="d-flex justify-content-between gap-2">
                        <div>
                            <span class="badge text-bg-success"><?= $escape($checklist['trip_type']) ?></span>
                            <h2 class="h5 mt-2"><?= $escape($checklist['title']) ?></h2>
                        </div>
                        <span class="badge text-bg-light align-self-start">
                            <?= (int) $checklist['is_public'] === 1 ? 'Đã chia sẻ' : 'Cá nhân' ?>
                        </span>
                    </div>
                    <p class="small text-muted mb-2">Tạo bởi <?= $escape($checklist['username']) ?></p>
                    <ul class="mb-0">
                        <?php foreach ($checklist['items'] as $item): ?>
                            <li><?= $escape($item['item']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            </div>
        <?php endforeach; ?>
    </section>
</main>
