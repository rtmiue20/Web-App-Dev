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
    <title><?= htmlspecialchars($title ?? 'Bảng điều khiển Quản trị', ENT_QUOTES, 'UTF-8') ?> — Quản trị Forum Dã Ngoại</title>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon.svg">
    
    <!-- Fonts & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css" rel="stylesheet">

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
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="dock-logo d-flex align-items-center gap-2 text-decoration-none" title="Trang quản trị">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m8 19 4-4 4 4"/>
                    <path d="M12 19V9"/>
                    <path d="M12 9 8 5l4-4 4 4-4 4Z"/>
                    <path d="m18 14 3 3-5 5H8l-5-5 3-3"/>
                </svg>
                <span class="badge" style="background: var(--color-moss, #9bbd55); color: #22281f; font-weight: 600; font-size: 11px; letter-spacing: 0.05em;">ADMIN</span>
            </a>

            <div class="vr mx-2 bg-secondary opacity-25 d-none d-md-block" style="height: 24px;"></div>

            <!-- Admin Menus -->
            <nav class="d-flex align-items-center gap-1">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" 
                   class="dock-pill <?= $isActive('/admin') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                </a>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" 
                   class="dock-pill <?= $isActive('/admin/categories') ? 'active' : '' ?>">
                    <i class="bi bi-tags me-1"></i> Chuyên mục
                </a>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" 
                   class="dock-pill" title="Xem danh sách bài viết">
                    <i class="bi bi-file-earmark-text me-1"></i> Bài viết
                </a>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Back to user forum -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
               class="dock-pill" style="color: var(--color-moss); border-color: rgba(155, 189, 85, 0.3);">
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

    <!-- Flash Alerts -->
    <?php if ($successMessage || $errorMessage): ?>
        <div style="position: fixed; top: 5.5rem; left: 50%; transform: translateX(-50%); z-index: 1050; width: min(90vw, 560px);">
            <?php if ($successMessage): ?>
                <div class="nature-alert nature-alert-success shadow-lg rise-in mb-2" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="nature-alert nature-alert-error shadow-lg rise-in mb-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
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
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js"></script>
</body>
</html>
