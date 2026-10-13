<?php $title = 'Đăng nhập'; ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Đăng nhập</h1>
                <?php if (!empty($errors['form'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/login">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" required maxlength="120"
                               autocomplete="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!empty($errors['email'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Mật khẩu</label>
                        <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password">
                        <?php if (!empty($errors['password'])): ?><div class="text-danger small"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <button class="btn btn-success w-100" type="submit">Đăng nhập</button>
                </form>
                <p class="mt-3 mb-0">Chưa có tài khoản? <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/register">Đăng ký</a></p>
            </div>
        </section>
    </div>
</div>