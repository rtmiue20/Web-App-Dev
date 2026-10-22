<?php
$title = 'Hồ sơ cá nhân';
$avatarUrl = upload_url($user['avatar_path'] ?? null);
$username = (string) ($user['username'] ?? '');
?>
<div class="app-container" style="max-width: 800px;">
    <div class="nature-card mb-4" style="border-radius: 36px; padding: 2rem;">
        <?php if (!empty($errors['form'])): ?>
            <div class="nature-alert nature-alert-error mb-4" role="alert">
                <?= e($errors['form']) ?>
            </div>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-3 mb-4">
            <?php if ($avatarUrl): ?>
                <img src="<?= e($avatarUrl) ?>" alt="Ảnh đại diện của <?= e($username) ?>"
                     class="rounded-circle object-fit-cover" width="72" height="72">
            <?php else: ?>
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width: 72px; height: 72px; background: #2d3328; color: #fff; font-size: 1.5rem;">
                    <?= e(mb_strtoupper(mb_substr($username !== '' ? $username : 'U', 0, 1))) ?>
                </div>
            <?php endif; ?>
            <div>
                <p class="nature-label mb-1">Thành viên cộng đồng</p>
                <h1 class="h3 fw-normal mb-0" style="color: var(--color-ink);"><?= e($username) ?></h1>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-4"><span class="nature-label">Địa chỉ email</span></div>
            <div class="col-sm-8 text-dark"><?= e($user['email'] ?? '') ?></div>
            <div class="col-sm-4"><span class="nature-label">Vai trò</span></div>
            <div class="col-sm-8">
                <span class="badge text-bg-success px-3 py-1 rounded-pill"><?= e($user['role_name'] ?? 'Thành viên') ?></span>
            </div>
            <div class="col-sm-4"><span class="nature-label">Ngày tham gia</span></div>
            <div class="col-sm-8 text-dark">
                <?= !empty($user['created_at']) ? e(date('d/m/Y H:i', strtotime($user['created_at']))) : 'Mới tham gia' ?>
            </div>
            <?php if (!empty($user['bio'])): ?>
                <div class="col-sm-4"><span class="nature-label">Giới thiệu</span></div>
                <div class="col-sm-8 text-dark"><?= nl2br(e($user['bio'])) ?></div>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?= e(base_url('/posts/create')) ?>" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                <i class="bi bi-pencil-fill me-1"></i> Viết bài mới
            </a>
            <a href="<?= e(base_url('/posts')) ?>" class="nature-btn-secondary px-4 py-2 text-dark border-dark-subtle" style="height: 44px;">
                Xem các bài viết
            </a>
        </div>
    </div>

    <div class="nature-card mb-4" style="border-radius: 36px; padding: 2rem;">
        <h2 class="h4 mb-3">Chỉnh sửa hồ sơ</h2>
        <form method="post" action="<?= e(base_url('/profile')) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="username" class="nature-label">Tên người dùng</label>
                <input class="nature-input" id="username" name="username" type="text" minlength="3" maxlength="50"
                       required value="<?= e($old['username'] ?? $username) ?>">
                <?php if (!empty($errors['username'])): ?>
                    <div class="text-danger small mt-1"><?= e($errors['username']) ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="bio" class="nature-label">Giới thiệu (tối đa 500 ký tự)</label>
                <textarea class="nature-textarea" id="bio" name="bio" rows="4" maxlength="500"><?= e($old['bio'] ?? $user['bio'] ?? '') ?></textarea>
                <?php if (!empty($errors['bio'])): ?>
                    <div class="text-danger small mt-1"><?= e($errors['bio']) ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="avatar" class="nature-label">Ảnh đại diện (JPG, PNG hoặc WEBP, tối đa 2MB)</label>
                <input class="nature-input" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp">
                <?php if (!empty($errors['avatar'])): ?>
                    <div class="text-danger small mt-1"><?= e($errors['avatar']) ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                Lưu hồ sơ
            </button>
        </form>
    </div>

    <div class="nature-card" style="border-radius: 36px; padding: 2rem;">
        <h2 class="h4 mb-3">Đổi mật khẩu</h2>
        <?php if (!empty($errors['password_form'])): ?>
            <div class="nature-alert nature-alert-error mb-3" role="alert"><?= e($errors['password_form']) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= e(base_url('/profile/password')) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="current_password" class="nature-label">Mật khẩu hiện tại</label>
                <input class="nature-input" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                <?php if (!empty($errors['current_password'])): ?>
                    <div class="text-danger small mt-1"><?= e($errors['current_password']) ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="new_password" class="nature-label">Mật khẩu mới</label>
                <input class="nature-input" id="new_password" name="new_password" type="password" minlength="8" autocomplete="new-password" required>
                <?php if (!empty($errors['new_password'])): ?>
                    <div class="text-danger small mt-1"><?= e($errors['new_password']) ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="nature-label">Xác nhận mật khẩu mới</label>
                <input class="nature-input" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                <?php if (!empty($errors['password_confirmation'])): ?>
                    <div class="text-danger small mt-1"><?= e($errors['password_confirmation']) ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                Cập nhật mật khẩu
            </button>
        </form>
    </div>
</div>
