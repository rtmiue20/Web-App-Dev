<?php
$currentUser = Session::get('user');
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

// Helper to check active link
$isActive = function (string $path) use ($currentUri, $baseUrl): bool {
    $fullPath = rtrim($baseUrl . $path, '/');
    $current = rtrim((string) $currentUri, '/');
    if ($path === '/' || $path === '') {
        return $current === '' || $current === rtrim($baseUrl, '/');
    }
    return str_starts_with($current, $fullPath);
};
?>
<header class="dock-header">
    <nav class="dock-nav" aria-label="Điều hướng chính">
        <!-- Brand / Celtic Knot Logo -->
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
           class="dock-logo" 
           aria-label="Outdoor Forum — về trang chủ">
            <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-logo.png" 
                 alt="Outdoor Forum Logo" 
                 style="width: 26px; height: 26px; filter: brightness(0); opacity: 0.85; object-fit: contain;">
        </a>

        <!-- Main Nav Links -->
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
           class="dock-pill <?= $isActive('/') ? 'active' : '' ?>">
            Trang chủ
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories" 
           class="dock-pill <?= $isActive('/categories') ? 'active' : '' ?>">
            Chuyên mục
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" 
           class="dock-pill <?= $isActive('/posts') ? 'active' : '' ?>">
            Bài viết
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" 
           class="dock-pill <?= $isActive('/products') ? 'active' : '' ?>">
            Dụng cụ
        </a>

        <?php if (is_array($currentUser)): ?>
            <!-- Notification Bell Icon (Tính năng thông báo thời gian thực) -->
            <div class="position-relative d-inline-block">
                <button type="button" 
                        class="dock-pill position-relative d-inline-flex align-items-center justify-content-center p-0" 
                        id="btnNotificationToggle" 
                        onclick="ForumAjax.toggleNotificationsDropdown()"
                        aria-label="Thông báo" 
                        style="width: 38px; height: 38px; border-radius: 50%; cursor: pointer;">
                    <i class="bi bi-bell-fill fs-6" style="color: rgba(241, 241, 236, 0.85);"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                          id="navUnreadBadge" 
                          style="display: none; font-size: 9px; padding: 2px 5px;">
                        0
                    </span>
                </button>

                <!-- Notifications Dropdown Popup -->
                <div id="notificationsDropdownMenu" class="notifications-dropdown-menu shadow-lg" style="display: none;">
                    <div class="d-flex align-items-center justify-content-between p-3 border-bottom border-secondary border-opacity-25">
                        <span class="fw-bold small text-white"><i class="bi bi-bell me-1 text-success"></i>Thông báo của bạn</span>
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 small" style="color: #9bbd55; font-size: 11.5px;" onclick="ForumAjax.markAllNotificationsRead()">
                            Đã đọc tất cả
                        </button>
                    </div>
                    <div id="notificationsListContainer" class="notifications-list-scroll" style="max-height: 320px; overflow-y: auto;">
                        <div class="text-center py-4 text-white-50 small">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span> Đang tải thông báo...
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Profile Link -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile" 
               class="dock-user-badge <?= $isActive('/profile') ? 'active' : '' ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span><?= htmlspecialchars($currentUser['username'] ?? 'Cá nhân', ENT_QUOTES, 'UTF-8') ?></span>
            </a>

            <?php if (class_exists('Auth') && Auth::isModerator($currentUser)): ?>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" 
                   class="dock-pill <?= $isActive('/admin') ? 'active' : '' ?>">
                    <?= Auth::isAdmin($currentUser) ? 'Quản trị' : 'Kiểm duyệt' ?>
                </a>
            <?php endif; ?>

            <!-- Logout Button (POST with CSRF) -->
            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/logout" style="margin: 0; display: inline;">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="dock-pill" style="cursor: pointer; border: none;">
                    Thoát
                </button>
            </form>
        <?php else: ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" 
               class="dock-pill <?= $isActive('/login') ? 'active' : '' ?>">
                Đăng nhập
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/register" 
               class="dock-pill <?= $isActive('/register') ? 'active' : '' ?>">
                Đăng ký
            </a>
        <?php endif; ?>
    </nav>
</header>