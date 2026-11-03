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
    <title><?= htmlspecialchars($title ?? 'Diễn đàn dã ngoại', ENT_QUOTES, 'UTF-8') ?> — Bước ra thiên nhiên</title>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon-dark-32x32.png">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/icon-dark-32x32.png">
    
    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Design System & Animations -->
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../../../public/assets/css/style.css') ?: time() ?>" rel="stylesheet">
    
    <script>
        window.BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
    </script>
</head>
<body>
    <!-- Floating Dock Navbar -->
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

    <!-- Flash Toast Messages Container (Góc phải màn hình, liquid glass đậm dễ đọc & tự đóng) -->
    <?php if ($successMessage || $errorMessage): ?>
        <div id="flash-toast-container" 
             style="position: fixed !important; top: 1.25rem !important; right: 1.5rem !important; left: auto !important; bottom: auto !important; z-index: 99999 !important; width: min(88vw, 320px) !important; max-width: 320px !important; display: flex !important; flex-direction: column !important; gap: 0.5rem !important; pointer-events: none !important;">
            <?php if ($successMessage): ?>
                <div class="app-toast app-toast-success shadow-lg" role="alert" id="toast-success">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill flex-shrink-0" style="color: #a8e063; font-size: 1.15rem; line-height: 1.2; margin-top: 1px;"></i>
                        <div class="flex-grow-1 min-w-0" style="font-size: 12.5px; line-height: 1.4; color: #f2f6ee;">
                            <div class="fw-bold mb-1" style="font-size: 13.5px; color: #a8e063;">Thành công!</div>
                            <div><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <button type="button" 
                                class="toast-glass-close flex-shrink-0" 
                                onclick="window.dismissToast(this.closest('.app-toast'))" 
                                aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="toast-progress-bar toast-progress-success"></div>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="app-toast app-toast-error shadow-lg" role="alert" id="toast-error">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0" style="color: #ff808b; font-size: 1.15rem; line-height: 1.2; margin-top: 1px;"></i>
                        <div class="flex-grow-1 min-w-0" style="font-size: 12.5px; line-height: 1.4; color: #fff0f2;">
                            <div class="fw-bold mb-1" style="font-size: 13.5px; color: #ff808b;">Thông báo lỗi</div>
                            <div><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <button type="button" 
                                class="toast-glass-close flex-shrink-0" 
                                onclick="window.dismissToast(this.closest('.app-toast'))" 
                                aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="toast-progress-bar toast-progress-error"></div>
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