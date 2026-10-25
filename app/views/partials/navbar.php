<?php
$currentUser = Session::get('user');
$currentUri = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$baseClean = rtrim($baseUrl, '/');

$isHomeActive = ($currentUri === '' || $currentUri === $baseClean);
$isCategoriesActive = str_starts_with($currentUri, rtrim($baseUrl . '/categories', '/'));
$isPostsActive = str_starts_with($currentUri, rtrim($baseUrl . '/posts', '/'));
$isProductsActive = str_starts_with($currentUri, rtrim($baseUrl . '/products', '/'));
$isProfileActive = str_starts_with($currentUri, rtrim($baseUrl . '/profile', '/'));
$isAdminActive = str_starts_with($currentUri, rtrim($baseUrl . '/admin', '/'));
$isLoginActive = str_starts_with($currentUri, rtrim($baseUrl . '/login', '/'));
$isRegisterActive = str_starts_with($currentUri, rtrim($baseUrl . '/register', '/'));
?>
<header class="dock-header">
    <nav class="dock-nav" aria-label="Điều hướng chính">
        <!-- Brand / Tree Pine Logo -->
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
           class="dock-logo" 
           aria-label="Dã Ngoại — về trang chủ">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="m8 19 4-4 4 4"/>
                <path d="M12 19V9"/>
                <path d="M12 9 8 5l4-4 4 4-4 4Z"/>
                <path d="m18 14 3 3-5 5H8l-5-5 3-3"/>
            </svg>
        </a>

        <!-- Main Nav Links -->
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
           class="dock-pill <?= $isHomeActive ? 'active' : '' ?>">
            Trang chủ
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories" 
           class="dock-pill <?= $isCategoriesActive ? 'active' : '' ?>">
            Chuyên mục
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" 
           class="dock-pill <?= $isPostsActive ? 'active' : '' ?>">
            Bài viết
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" 
           class="dock-pill <?= $isProductsActive ? 'active' : '' ?>">
            Thiết bị
        </a>

        <?php if (is_array($currentUser)): ?>
            <!-- User Profile Link -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile" 
               class="dock-user-badge <?= $isProfileActive ? 'active' : '' ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span><?= htmlspecialchars($currentUser['username'] ?? 'Cá nhân', ENT_QUOTES, 'UTF-8') ?></span>
            </a>

            <?php if (($currentUser['role_name'] ?? '') === 'admin' || (int)($currentUser['role_id'] ?? 0) === 2): ?>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" 
                   class="dock-pill <?= $isAdminActive ? 'active' : '' ?>">
                    Quản trị
                </a>
            <?php endif; ?>

            <!-- Logout Button (POST with CSRF) -->
            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/logout" class="d-inline m-0">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="dock-pill border-0">
                    Thoát
                </button>
            </form>
        <?php else: ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" 
               class="dock-pill <?= $isLoginActive ? 'active' : '' ?>">
                Đăng nhập
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/register" 
               class="dock-pill <?= $isRegisterActive ? 'active' : '' ?>">
                Đăng ký
            </a>
        <?php endif; ?>
    </nav>
</header>
