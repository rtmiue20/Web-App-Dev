<?php
$successMessage = $success ?? Session::flash('success');
$errorMessage = $message ?? Session::flash('error');
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title ?? 'Diễn đàn dã ngoại', ENT_QUOTES, 'UTF-8') ?> — Bước ra thiên nhiên</title>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon-dark-32x32.png">
    <link rel="shortcut icon" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon-dark-32x32.png">
    
    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Design System & Animations -->
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?: time() ?>" rel="stylesheet">
    <!-- Liquid Glass Design Layer -->
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/liquid-glass.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/liquid-glass.css') ?: time() ?>" rel="stylesheet">
    
    <script>
        window.BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
    </script>
</head>
<body>
    <!-- Floating Dock Navbar -->
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

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
    <?= $content ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/js/main.js') ?: time() ?>"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/ajax.js?v=<?= time() ?>"></script>
</body>
</html>