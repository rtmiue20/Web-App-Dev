<?php
$currentUser = class_exists('Auth') ? Auth::user() : Session::get('user');
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

<!-- ===================================================================== -->
<!-- FLOATING DOCK NAVIGATION (Đồng bộ 100% UI & Chức năng: Desktop, Tablet & Mobile) -->
<!-- ===================================================================== -->
<header class="dock-header">
    <nav class="dock-nav" aria-label="Điều hướng chính diễn đàn">
        <!-- Brand / Celtic Knot Logo -->
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
           class="dock-logo" 
           aria-label="Outdoor Forum — về trang chủ">
            <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-logo.png" 
                 alt="Outdoor Forum Logo"
                 style="width: 38px; height: 38px; max-width: 38px; max-height: 38px; object-fit: contain; border-radius: 50%; display: block;">
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

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/checklists"
           class="dock-pill <?= $isActive('/checklists') ? 'active' : '' ?>">
            Checklist
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/trips"
           class="dock-pill <?= $isActive('/trips') ? 'active' : '' ?>">
            Đi chung
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/places"
           class="dock-pill <?= $isActive('/places') || $isActive('/map') ? 'active' : '' ?>">
            Bản đồ
        </a>

        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" 
           class="dock-pill <?= $isActive('/products') ? 'active' : '' ?>">
            Dụng cụ
        </a>

        <?php if (is_array($currentUser)): ?>
            <!-- User Profile Link -->
            <?php 
            $navPendingCount = 0;
            try {
                $navUid = (int)($currentUser['id'] ?? 0);
                if ($navUid > 0 && class_exists('Database')) {
                    $navPendingCount = (int)Database::queryValue(
                        "SELECT COUNT(*) FROM posts WHERE user_id = :uid AND status = 'pending'",
                        ['uid' => $navUid]
                    );
                }
            } catch (Throwable $e) {}
            ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile" 
               class="dock-user-badge <?= $isActive('/profile') ? 'active' : '' ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span><?= htmlspecialchars($currentUser['username'] ?? 'Cá nhân', ENT_QUOTES, 'UTF-8') ?></span>
                <?php if ($navPendingCount > 0): ?>
                    <span class="badge rounded-pill bg-warning text-dark ms-1 fw-bold" style="font-size: 10.5px; padding: 2px 7px;" title="Bạn có <?= $navPendingCount ?> bài viết đang chờ duyệt">
                        <?= $navPendingCount ?> ⏳
                    </span>
                <?php endif; ?>
                <?php if (class_exists('Auth') && Auth::isGuest($currentUser)): ?>
                    <span class="badge rounded-pill text-bg-secondary ms-1" style="font-size: 9.5px; padding: 2px 6px;">GUEST</span>
                <?php endif; ?>
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

<?php if (is_array($currentUser)): ?>
<!-- ===================================================================== -->
<!-- FLOATING NOTIFICATION WIDGET (Hiển thị đồng bộ ở góc trên bên phải)   -->
<!-- ===================================================================== -->
<aside class="floating-notification-widget" id="floatingNotifWidget" aria-label="Thông báo hệ thống">
    <!-- Nút chuông nổi độc lập (hiển thị trên mọi kích thước màn hình) -->
    <button type="button" 
            class="floating-bell-btn" 
            id="btnNotificationToggle" 
            onclick="event.stopPropagation(); ForumAjax.toggleNotificationsDropdown(event);" 
            aria-label="Thông báo"
            title="Thông báo của bạn">
        <i class="bi bi-bell-fill"></i>
        <span class="bell-badge-pulse" id="navUnreadBadge" style="display: none;">0</span>
    </button>

    <!-- Panel Popup Thông Báo (Nature Paper Card phong cách đồng bộ) -->
    <div id="notificationsDropdownMenu" class="floating-notifications-panel shadow-lg" style="display: none;">
        <div class="floating-notif-header">
            <div class="d-flex align-items-center gap-2">
                <span class="floating-notif-icon-badge">
                    <i class="bi bi-bell-fill"></i>
                </span>
                <span class="floating-notif-title">Thông báo của bạn</span>
            </div>
            <button type="button" class="floating-notif-markall" onclick="ForumAjax.markAllNotificationsRead()" title="Đánh dấu đã đọc tất cả">
                <i class="bi bi-check2-all me-1"></i>Đã đọc tất cả
            </button>
        </div>
        <div id="notificationsListContainer" class="notifications-list-scroll">
            <div class="text-center py-4 text-muted small">
                <span class="spinner-border spinner-border-sm me-1" role="status"></span> Đang tải thông báo...
            </div>
        </div>
        <div class="floating-notif-footer">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile?tab=activity" class="floating-notif-link">
                <i class="bi bi-clock-history me-1"></i>Xem hoạt động cá nhân
            </a>
        </div>
    </div>
</aside>
<?php endif; ?>