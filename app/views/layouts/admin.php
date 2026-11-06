<?php
$currentUser = Session::get('user');
$successMessage = $success ?? Session::flash('success');
$errorMessage = $message ?? Session::flash('error');
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
    <title><?= htmlspecialchars($title ?? 'Bảng điều khiển Quản trị', ENT_QUOTES, 'UTF-8') ?> — Quản trị Forum Dã Ngoại</title>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon-dark-32x32.png">
    <link rel="shortcut icon" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon-dark-32x32.png">
    
    <!-- Fonts & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?: time() ?>" rel="stylesheet">

    <style>
        .admin-nav-bar {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: min(94vw, 1200px);
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
    </style>
    
    <script>
        window.BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
    </script>
</head>
<body style="padding-top: 5rem; min-height: 100vh; display: flex; flex-direction: column;">

    <!-- Admin Navigation Dock -->
    <header class="admin-nav-bar d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <?php $isFullAdmin = class_exists('Auth') ? Auth::isAdmin($currentUser) : (($currentUser['role_name'] ?? '') === 'admin'); ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-decoration-none d-inline-flex align-items-center" title="Trang quản trị / kiểm duyệt" style="margin-right: 4px;">
                <span class="admin-logo-wrap">
                    <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-logo.png" 
                         alt="Outdoor Forum Logo">
                    <span class="admin-role-badge"><?= $isFullAdmin ? 'ADMIN' : 'MOD' ?></span>
                </span>
            </a>

            <div class="vr mx-2 bg-secondary opacity-25 d-none d-md-block" style="height: 24px;"></div>

            <!-- Admin / Moderator Menus -->
            <nav class="d-flex align-items-center gap-1 flex-nowrap" style="overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
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

        <div class="d-flex align-items-center gap-2">
            <!-- Back to user forum -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
               class="dock-pill text-nowrap" style="color: var(--color-moss); border-color: rgba(155, 189, 85, 0.3);">
                <i class="bi bi-arrow-left-circle me-1"></i> Diễn đàn
            </a>

            <!-- Admin Profile / Name -->
            <div class="d-none d-sm-inline-flex align-items-center gap-1 text-white-50 small px-2">
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

    <!-- Flash Toast Messages Container (Apple Liquid Glass ở góc phải) -->
    <?php if ($successMessage || $errorMessage): ?>
        <div id="flash-toast-container">
            <?php if ($successMessage): ?>
                <div class="app-toast app-toast-success shadow-lg" role="alert" id="toast-success">
                    <div class="toast-body-row d-flex align-items-center gap-2">
                        <div class="toast-icon-badge toast-icon-success flex-shrink-0">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="toast-title">Thành công!</div>
                            <div class="toast-desc"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <button type="button" 
                                class="toast-glass-close flex-shrink-0" 
                                onclick="window.dismissToast(this.closest('.app-toast'))" 
                                aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="toast-progress-track"><div class="toast-progress-fill toast-progress-success"></div></div>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="app-toast app-toast-error shadow-lg" role="alert" id="toast-error">
                    <div class="toast-body-row d-flex align-items-center gap-2">
                        <div class="toast-icon-badge toast-icon-error flex-shrink-0">
                            <i class="bi bi-exclamation-lg"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="toast-title">Thông báo</div>
                            <div class="toast-desc"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <button type="button" 
                                class="toast-glass-close flex-shrink-0" 
                                onclick="window.dismissToast(this.closest('.app-toast'))" 
                                aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="toast-progress-track"><div class="toast-progress-fill toast-progress-error"></div></div>
                </div>
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

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/js/main.js') ?: time() ?>"></script>
</body>
</html>
