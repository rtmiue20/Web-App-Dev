<?php $currentUser = Session::get('user'); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/">Diễn đàn dã ngoại</a>
        <div class="navbar-nav ms-auto align-items-lg-center">
            <?php if (is_array($currentUser)): ?>
                <a class="nav-link" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/profile">
                    <?= htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8') ?>
                </a>
                <form method="post" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/logout" class="ms-lg-2">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn btn-outline-light btn-sm" type="submit">Đăng xuất</button>
                </form>
            <?php else: ?>
                <a class="nav-link" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/login">Đăng nhập</a>
                <a class="nav-link" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/register">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</nav>