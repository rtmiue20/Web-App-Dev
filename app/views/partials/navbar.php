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

<!-- ===================================================================== -->
<!-- 1. DESKTOP FLOATING DOCK (Hiển thị trên máy tính >= 992px)            -->
<!-- ===================================================================== -->
<header class="dock-header d-none d-lg-flex">
    <nav class="dock-nav" aria-label="Điều hướng chính desktop">
        <!-- Brand / Celtic Knot Logo -->
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
           class="dock-logo" 
           aria-label="Outdoor Forum — về trang chủ">
            <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-logo.png" 
                 alt="Outdoor Forum Logo" 
                 style="width: 38px; height: 38px; object-fit: contain; border-radius: 50%;">
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

<?php if (is_array($currentUser)): ?>
<!-- ===================================================================== -->
<!-- FLOATING NOTIFICATION WIDGET (Tách riêng ở góc trên bên phải màn hình)-->
<!-- ===================================================================== -->
<aside class="floating-notification-widget" id="floatingNotifWidget" aria-label="Thông báo hệ thống">
    <!-- Nút chuông nổi độc lập (Desktop) -->
    <button type="button" 
            class="floating-bell-btn d-none d-lg-flex" 
            id="btnNotificationToggle" 
            onclick="event.stopPropagation(); ForumAjax.toggleNotificationsDropdown(event);" 
            aria-label="Thông báo"
            title="Thông báo của bạn">
        <i class="bi bi-bell-fill"></i>
        <span class="bell-badge-pulse" id="navUnreadBadge" style="display: none;">0</span>
    </button>

    <!-- Panel Popup Thông Báo (Nature Paper Card phong cách đồng bộ Admin Dashboard) -->
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
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile" class="floating-notif-link">
                <i class="bi bi-clock-history me-1"></i>Xem hoạt động cá nhân
            </a>
        </div>
    </div>
</aside>
<?php endif; ?>

<!-- ===================================================================== -->
<!-- 2. MOBILE & TABLET TOP HEADER BAR (Hiển thị trên màn hình < 992px)    -->
<!-- ===================================================================== -->
<header class="mobile-top-bar d-flex d-lg-none align-items-center justify-content-between px-3">
    <!-- Brand Logo + Name -->
    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" class="d-flex align-items-center gap-2 text-decoration-none">
        <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-logo.png" 
             alt="Outdoor Forum Logo" 
             style="width: 34px; height: 34px; object-fit: contain; border-radius: 50%;">
        <span class="fw-semibold text-white" style="letter-spacing: -0.02em; font-size: 1.05rem;">
            Outdoor<span style="color: #9bbd55;">Forum</span>
        </span>
    </a>

    <!-- Right Controls: Notification + Mobile Menu Button -->
    <div class="d-flex align-items-center gap-2">
        <?php if (is_array($currentUser)): ?>
            <!-- Mobile Notification Bell -->
            <div class="position-relative d-inline-block">
                <button type="button" 
                        class="btn btn-sm btn-dark rounded-circle p-0 d-flex align-items-center justify-content-center" 
                        id="mobileBtnNotificationToggle"
                        onclick="event.stopPropagation(); ForumAjax.toggleNotificationsDropdown(event);"
                        aria-label="Thông báo"
                        style="width: 36px; height: 36px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);">
                    <i class="bi bi-bell-fill fs-6" style="color: rgba(241, 241, 236, 0.88);"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                          id="mobileNavUnreadBadge" 
                          style="display: none; font-size: 9px; padding: 2px 5px;">
                        0
                    </span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Hamburger Toggle Button (Mở Drawer menu đầy đủ) -->
        <button type="button" 
                class="btn btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-2" 
                data-bs-toggle="offcanvas" 
                data-bs-target="#mobileMenuOffcanvas" 
                aria-controls="mobileMenuOffcanvas"
                aria-label="Mở Menu điều hướng"
                style="background: rgba(155, 189, 85, 0.18); border: 1px solid rgba(155, 189, 85, 0.4); color: #9bbd55; height: 36px;">
            <i class="bi bi-list fs-5"></i>
            <span class="small fw-medium">Menu</span>
        </button>
    </div>
</header>

<!-- ===================================================================== -->
<!-- 3. MOBILE OFFCANVAS DRAWER (Menu trượt toàn diện trên điện thoại)     -->
<!-- ===================================================================== -->
<div class="offcanvas offcanvas-end mobile-offcanvas d-lg-none" tabindex="-1" id="mobileMenuOffcanvas" aria-labelledby="mobileMenuOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-white border-opacity-10 py-3">
        <div class="d-flex align-items-center gap-2">
            <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-logo.png" 
                 alt="Outdoor Forum Logo" 
                 style="width: 32px; height: 32px; object-fit: contain; border-radius: 50%;">
            <span class="fw-bold text-white fs-6" id="mobileMenuOffcanvasLabel">Outdoor<span style="color: #9bbd55;">Forum</span></span>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
    </div>

    <div class="offcanvas-body p-3 d-flex flex-column justify-content-between">
        <div>
            <!-- User Status Card -->
            <?php if (is_array($currentUser)): ?>
                <div class="p-3 rounded-4 mb-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" 
                             style="width: 44px; height: 44px; background: rgba(155, 189, 85, 0.2); color: #9bbd55; border: 1px solid rgba(155, 189, 85, 0.4);">
                            <i class="bi bi-person-fill fs-5"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-white text-truncate"><?= htmlspecialchars($currentUser['username'] ?? 'Thành viên', ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size: 10px;">
                                    <?= htmlspecialchars($currentUser['role_name'] ?? 'Thành viên', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="text-white-50 small" style="font-size: 11px;">
                                    <i class="bi bi-stars text-warning me-1"></i><?= (int)($currentUser['karma'] ?? 0) ?> karma
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top border-white border-opacity-10 d-flex justify-content-between">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile" class="small text-decoration-none" style="color: #9bbd55;">
                            <i class="bi bi-person-gear me-1"></i>Xem trang cá nhân
                        </a>
                        <?php if (class_exists('Auth') && Auth::isModerator($currentUser)): ?>
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="small text-warning text-decoration-none">
                                <i class="bi bi-shield-lock me-1"></i>Quản trị viên
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-3 rounded-4 mb-3 text-center" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <p class="small text-white-50 mb-3">Chào mừng bạn đến với cộng đồng dã ngoại!</p>
                    <div class="d-grid gap-2">
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-primary py-2 text-center text-decoration-none">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                        </a>
                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/register" class="nature-btn-secondary py-2 text-center text-decoration-none text-white border-white-50" style="background: rgba(255,255,255,0.08);">
                            <i class="bi bi-person-plus me-1"></i> Đăng ký tài khoản
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Navigation Links List -->
            <nav class="nav flex-column gap-1">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
                   class="mobile-nav-link <?= $isActive('/') ? 'active' : '' ?>">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Trang chủ</span>
                </a>

                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories" 
                   class="mobile-nav-link <?= $isActive('/categories') ? 'active' : '' ?>">
                    <i class="bi bi-grid-fill"></i>
                    <span>Chuyên mục</span>
                </a>

                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" 
                   class="mobile-nav-link <?= $isActive('/posts') ? 'active' : '' ?>">
                    <i class="bi bi-card-text"></i>
                    <span>Bài viết cộng đồng</span>
                </a>

                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" 
                   class="mobile-nav-link text-success fw-medium <?= $isActive('/posts/create') ? 'active' : '' ?>"
                   style="background: rgba(47, 94, 46, 0.2); border: 1px solid rgba(155, 189, 85, 0.3);">
                    <i class="bi bi-pencil-square text-success"></i>
                    <span>Viết bài chia sẻ mới</span>
                </a>

                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/checklists" 
                   class="mobile-nav-link <?= $isActive('/checklists') ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i>
                    <span>Checklist dã ngoại</span>
                </a>

                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/trips" 
                   class="mobile-nav-link <?= $isActive('/trips') ? 'active' : '' ?>">
                    <i class="bi bi-compass-fill"></i>
                    <span>Tìm bạn đi chung (Trips)</span>
                </a>

                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/places" 
                   class="mobile-nav-link <?= $isActive('/places') || $isActive('/map') ? 'active' : '' ?>">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Bản đồ dã ngoại</span>
                </a>

                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" 
                   class="mobile-nav-link <?= $isActive('/products') ? 'active' : '' ?>">
                    <i class="bi bi-tools"></i>
                    <span>Dụng cụ &amp; Thiết bị</span>
                </a>

                <?php if (is_array($currentUser) && class_exists('Auth') && Auth::isModerator($currentUser)): ?>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" 
                       class="mobile-nav-link text-warning <?= $isActive('/admin') ? 'active' : '' ?>">
                        <i class="bi bi-shield-shaded text-warning"></i>
                        <span>Bảng quản trị (<?= Auth::isAdmin($currentUser) ? 'Admin' : 'Mod' ?>)</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>

        <?php if (is_array($currentUser)): ?>
            <div class="pt-3 border-top border-white border-opacity-10 mt-3">
                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/logout" class="m-0">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Đăng xuất tài khoản</span>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===================================================================== -->
<!-- 4. MOBILE BOTTOM NAVIGATION DOCK (Cố định dưới màn hình điện thoại)   -->
<!-- ===================================================================== -->
<nav class="mobile-bottom-bar d-flex d-lg-none" aria-label="Điều hướng nhanh di động">
    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
       class="mobile-bottom-item <?= $isActive('/') ? 'active' : '' ?>">
        <i class="bi bi-house-door-fill"></i>
        <span>Trang chủ</span>
    </a>

    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" 
       class="mobile-bottom-item <?= ($isActive('/posts') && !$isActive('/posts/create')) ? 'active' : '' ?>">
        <i class="bi bi-card-text"></i>
        <span>Bài viết</span>
    </a>

    <!-- Nút FAB nổi bật ở trung tâm: Viết bài -->
    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" 
       class="mobile-bottom-fab" 
       title="Viết bài mới"
       aria-label="Đăng bài mới">
        <i class="bi bi-plus-lg fs-4"></i>
    </a>

    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" 
       class="mobile-bottom-item <?= $isActive('/products') ? 'active' : '' ?>">
        <i class="bi bi-tools"></i>
        <span>Dụng cụ</span>
    </a>

    <button type="button" 
            class="mobile-bottom-item" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#mobileMenuOffcanvas" 
            aria-controls="mobileMenuOffcanvas"
            aria-label="Mở Menu điều hướng">
        <i class="bi bi-grid-fill"></i>
        <span>Menu</span>
    </button>
</nav>