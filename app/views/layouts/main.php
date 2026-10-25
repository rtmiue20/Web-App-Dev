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
    <link href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/css/style.css" rel="stylesheet">
    
    <script>
        window.BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
    </script>
</head>
<body>
    <!-- Floating Dock Navbar -->
    <?php require __DIR__ . '/../partials/navbar.php'; ?>

    <!-- Flash Alerts Container -->
    <?php if ($successMessage || $errorMessage): ?>
        <div style="position: fixed; top: 5rem; left: 50%; transform: translateX(-50%); z-index: 999; width: min(90vw, 540px);">
            <?php if ($successMessage): ?>
                <div class="nature-alert nature-alert-success shadow-lg rise-in" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="nature-alert nature-alert-error shadow-lg rise-in" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main View Content -->
    <?= $content ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/ajax.js"></script>
</body>
</html>