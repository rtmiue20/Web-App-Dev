<?php
$currentUser = Session::get('user');
$successMessage = $success ?? Session::flash('success');
$errorMessage = $message ?? Session::flash('error');
$warningMessage = Session::flash('warning');
$infoMessage    = Session::flash('info');

// Đồng bộ thông báo Toast quản trị vào Chuông thông báo "Thông báo của bạn"
$currentAdminId = is_array($currentUser) ? (int)($currentUser['id'] ?? 0) : 0;
if ($currentAdminId > 0 && class_exists('Notification')) {
    if (!empty($successMessage)) {
        Notification::recordFlashMessage($currentAdminId, 'success', (string)$successMessage);
    }
    if (!empty($warningMessage)) {
        Notification::recordFlashMessage($currentAdminId, 'warning', (string)$warningMessage);
    }
    if (!empty($infoMessage)) {
        Notification::recordFlashMessage($currentAdminId, 'info', (string)$infoMessage);
    }
}

if (!function_exists('renderToast')) {
function renderToast(string $type, string $title, string $icon, string $message): string {
    $safeMsg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    return <<<HTML
        <div class="app-toast app-toast-{$type}" role="alert">
            <div class="toast-bubble-badge">
                <i class="bi {$icon}"></i>
            </div>
            <div class="toast-decor-blobs" aria-hidden="true">
                <span class="blob blob-1"></span>
                <span class="blob blob-2"></span>
                <span class="blob blob-3"></span>
                <span class="blob blob-4"></span>
            </div>
            <div class="toast-body-content">
                <div class="toast-title">{$title}</div>
                <div class="toast-desc">{$safeMsg}</div>
            </div>
            <button type="button" class="toast-close-btn" onclick="window.dismissToast(this.closest('.app-toast'))" aria-label="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="toast-progress-bar"></div>
        </div>
HTML;
}
}
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$isActive = function (string $path) use ($currentUri, $baseUrl): bool {
    $fullPath = rtrim($baseUrl . $path, '/');
    $current = rtrim((string) $currentUri, '/');
    if ($path === '/admin') {
        return $current === $fullPath;
    }
    return str_starts_with($current, $fullPath);
};
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <?php
    $tabTitle = !empty($title) ? trim((string)$title) : 'Bảng điều khiển Quản trị';
    $tabTitle = preg_replace('/\s*[-—]\s*(Quản trị Forum Dã Ngoại|Bước ra thiên nhiên|Outdoor Forum)$/ui', '', $tabTitle);
    ?>
    <title>Outdoor Forum - <?= htmlspecialchars($tabTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon-dark-32x32.png">
    <link rel="shortcut icon" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon-dark-32x32.png">
    
    <!-- Fonts & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?: time() ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/liquid-glass.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/liquid-glass.css') ?: time() ?>" rel="stylesheet">

    <style>
        .admin-nav-bar {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            width: min(calc(100vw - 8.5rem), 1120px);
            background: rgba(34, 40, 31, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 9999px;
            padding: 0.5rem 1.25rem;
            box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.5);
        }
        .admin-table {
            background: var(--color-paper, #f2f3ef);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
        }
        .admin-table th {
            background: #e5e8e0;
            color: var(--color-ink, #23261f);
            font-weight: 500;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.25rem;
            border-bottom: 2px solid #d5d9ce;
        }
        .admin-table td {
            padding: 1.1rem 1.25rem;
            vertical-align: middle;
            color: #2b3026;
            border-bottom: 1px solid #e7eae2;
        }
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        .admin-table tr:hover td {
            background-color: rgba(155, 189, 85, 0.06);
        }
        .admin-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: #ffffff;
            color: var(--color-ink);
            transition: all 200ms ease;
            text-decoration: none;
        }
        .admin-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.12);
        }
        .admin-action-btn.btn-edit:hover {
            color: #0d6efd;
            border-color: #0d6efd;
        }
        .admin-action-btn.btn-delete:hover {
            color: #dc3545;
            border-color: #dc3545;
        }

        /* ── Admin Responsive Overhaul: 100% Mobile & Tablet Compatible ── */
        body.admin-body {
            padding-top: 5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        @media (max-width: 991.98px) {
            body.admin-body {
                padding-top: 4.25rem !important;
            }
            body.admin-body .app-container {
                padding-top: 0.5rem !important;
                padding-bottom: 2rem !important;
                padding-inline: 0.75rem !important;
            }
            .admin-nav-bar {
                top: 0.5rem !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
                width: calc(100vw - 1rem) !important;
                max-width: calc(100vw - 1rem) !important;
                height: 48px !important;
                border-radius: 9999px !important;
                padding: 0 0.5rem !important;
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                justify-content: flex-start !important;
                overflow-x: auto !important;
                overflow-y: hidden !important;
                -webkit-overflow-scrolling: touch !important;
                scrollbar-width: none !important;
                gap: 0.35rem !important;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
            }
            .admin-nav-bar::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
            }
            .admin-nav-track,
            .admin-nav-menu,
            .admin-nav-actions {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                flex-shrink: 0 !important;
                gap: 0.35rem !important;
            }
            .admin-nav-bar .dock-pill {
                flex-shrink: 0 !important;
                white-space: nowrap !important;
                font-size: 11px !important;
                height: 34px !important;
                padding: 0 0.65rem !important;
                display: inline-flex !important;
                align-items: center !important;
            }
            .admin-logo-wrap {
                width: 32px !important;
                height: 32px !important;
                flex-shrink: 0 !important;
            }
            .admin-logo-wrap img {
                width: 32px !important;
                height: 32px !important;
            }
            .admin-role-badge {
                font-size: 7px !important;
                padding: 1px 4px !important;
                bottom: -5px !important;
            }
            .chart-canvas-wrap {
                height: 240px !important;
            }
        }
        .table-responsive {
            border-radius: 20px;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .table-responsive::-webkit-scrollbar {
            display: none;
        }
    </style>
    
    <script>
        window.BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
    </script>
</head>
<body class="admin-body">

    <!-- Admin Navigation Dock -->
    <header class="admin-nav-bar d-flex align-items-center justify-content-between flex-nowrap">
        <div class="admin-nav-track d-flex align-items-center gap-1 flex-nowrap">
            <?php $isFullAdmin = class_exists('Auth') ? Auth::isAdmin($currentUser) : (($currentUser['role_name'] ?? '') === 'admin'); ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-decoration-none d-inline-flex align-items-center flex-shrink-0" title="Trang quản trị / kiểm duyệt" style="margin-right: 4px;">
                <span class="admin-logo-wrap">
                    <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-logo.png" 
                         alt="Outdoor Forum Logo">
                    <span class="admin-role-badge"><?= $isFullAdmin ? 'ADMIN' : 'MOD' ?></span>
                </span>
            </a>

            <div class="vr mx-2 bg-secondary opacity-25 d-none d-md-block flex-shrink-0" style="height: 24px;"></div>

            <!-- Admin / Moderator Menus -->
            <nav class="d-flex align-items-center gap-1 flex-nowrap admin-nav-menu">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" 
                   class="dock-pill text-nowrap <?= $isActive('/admin') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                </a>
                <?php if ($isFullAdmin): ?>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" 
                       class="dock-pill text-nowrap <?= $isActive('/admin/categories') ? 'active' : '' ?>">
                        <i class="bi bi-tags me-1"></i> Chuyên mục
                    </a>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products" 
                       class="dock-pill text-nowrap <?= $isActive('/admin/products') ? 'active' : '' ?>" title="Quản lý dụng cụ & thiết bị">
                        <i class="bi bi-tools me-1"></i> Dụng cụ
                    </a>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts" 
                   class="dock-pill text-nowrap <?= $isActive('/admin/posts') ? 'active' : '' ?>" title="Kiểm duyệt & Quản lý bài viết">
                    <i class="bi bi-file-earmark-text me-1"></i> Bài viết
                </a>
                <?php if ($isFullAdmin): ?>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" 
                       class="dock-pill text-nowrap <?= $isActive('/admin/users') ? 'active' : '' ?>" title="Quản lý thành viên, Phân quyền RBAC & Cấp lại mật khẩu">
                        <i class="bi bi-people me-1"></i> Thành viên
                    </a>
                <?php endif; ?>
            </nav>
        </div>

        <div class="admin-nav-actions d-flex align-items-center gap-2 flex-shrink-0 flex-nowrap">
            <!-- Back to user forum -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
               class="dock-pill text-nowrap" style="color: var(--color-moss); border-color: rgba(155, 189, 85, 0.3);">
                <i class="bi bi-arrow-left-circle me-1"></i> Diễn đàn
            </a>

            <!-- Admin Profile / Name -->
            <div class="d-none d-lg-inline-flex align-items-center gap-1 text-white-50 small px-2">
                <i class="bi bi-person-circle"></i>
                <span class="text-white fw-normal"><?= htmlspecialchars($currentUser['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <!-- Logout -->
            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/logout" style="margin: 0; display: inline;">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="dock-pill" style="cursor: pointer; border: none; background: rgba(220, 53, 69, 0.15); color: #ff8b8b;" title="Đăng xuất quản trị">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </header>

    <?php if (is_array($currentUser)): ?>
    <!-- ===================================================================== -->
    <!-- FLOATING NOTIFICATION WIDGET (Đồng bộ Desktop góc trên phải & Mobile FAB góc dưới phải) -->
    <!-- ===================================================================== -->
    <aside class="floating-notification-widget" id="floatingNotifWidget" aria-label="Thông báo hệ thống">
        <button type="button" 
                class="floating-bell-btn" 
                id="btnNotificationToggle" 
                onclick="event.stopPropagation(); ForumAjax.toggleNotificationsDropdown(event);" 
                aria-label="Thông báo"
                title="Thông báo của bạn">
            <i class="bi bi-bell-fill"></i>
            <span class="bell-badge-pulse" id="navUnreadBadge" style="display: none;">0</span>
        </button>

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

    <!-- Flash Toast Messages Container -->
    <?php if ($successMessage || $errorMessage || $warningMessage || $infoMessage): ?>
        <div id="flash-toast-container">
            <?php if ($successMessage): ?>
                <?= renderToast('success', 'Thành công!', 'bi-check-lg', $successMessage) ?>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <?= renderToast('error', 'Lỗi!', 'bi-x-lg', $errorMessage) ?>
            <?php endif; ?>
            <?php if ($warningMessage): ?>
                <?= renderToast('warning', 'Cảnh báo!', 'bi-exclamation-lg', $warningMessage) ?>
            <?php endif; ?>
            <?php if ($infoMessage): ?>
                <?= renderToast('info', 'Thông tin', 'bi-question-lg', $infoMessage) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main View Content -->
    <main class="flex-grow-1">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-white-50 small mt-auto border-top border-white-10" style="background: rgba(0,0,0,0.15);">
        <div class="container">
            <span>Quản trị Hệ thống Diễn đàn Dã Ngoại &copy; <?= date('Y') ?> &bull; Phiên bản tuần 3</span>
        </div>
    </footer>

    <!-- Bootstrap Bundle & App Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/js/main.js') ?: time() ?>"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/ajax.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/js/ajax.js') ?: time() ?>"></script>
</body>
</html>
