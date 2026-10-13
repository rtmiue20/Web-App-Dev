<?php $title = 'Đăng ký'; ?>
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Tạo tài khoản</h1>
                <?php if (!empty($errors['form'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/register">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3">
                        <label class="form-label" for="username">Tên người dùng</label>
                        <input class="form-control" id="username" name="username" type="text" required minlength="3" maxlength="50"
                               autocomplete="username" value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!empty($errors['username'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" required maxlength="120"
                               autocomplete="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!empty($errors['email'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Mật khẩu (ít nhất 8 ký tự)</label>
                        <input class="form-control" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                        <?php if (!empty($errors['password'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Nhập lại mật khẩu</label>
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                        <?php if (!empty($errors['password_confirmation'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['password_confirmation'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <button class="btn btn-success w-100" type="submit">Đăng ký</button>
                </form>
                <p class="mt-3 mb-0">Đã có tài khoản? <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/login">Đăng nhập</a></p>
            </div>
        </section>
    </div>
</div>