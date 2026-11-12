<?php
$successMessage = $success  ?? Session::flash('success');
$errorMessage   = $message  ?? Session::flash('error');
$warningMessage = Session::flash('warning');
$infoMessage    = Session::flash('info');
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

// Đồng bộ thông báo Toast vào Chuông thông báo "Thông báo của bạn"
$currentLoggedUser = Session::get('user');
$currentLoggedUserId = is_array($currentLoggedUser) ? (int)($currentLoggedUser['id'] ?? 0) : 0;
if ($currentLoggedUserId > 0 && class_exists('Notification')) {
    if (!empty($successMessage)) {
        Notification::recordFlashMessage($currentLoggedUserId, 'success', (string)$successMessage);
    }
    if (!empty($warningMessage)) {
        Notification::recordFlashMessage($currentLoggedUserId, 'warning', (string)$warningMessage);
    }
    if (!empty($infoMessage)) {
        Notification::recordFlashMessage($currentLoggedUserId, 'info', (string)$infoMessage);
    }
}

// Helper: render 1 toast (guard chống redeclare khi layout được include nhiều lần)
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
} // end if !function_exists
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <?php
    $tabTitle = !empty($title) ? trim((string)$title) : 'Trang chủ';
    $tabTitle = preg_replace('/\s*[-—]\s*(Bước ra thiên nhiên|Quản trị Forum Dã Ngoại|Outdoor Forum)$/ui', '', $tabTitle);
    ?>
    <title>Outdoor Forum - <?= htmlspecialchars($tabTitle, ENT_QUOTES, 'UTF-8') ?></title>
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

    <!-- Flash Toast Messages Container (góc trên bên phải) -->
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
    <?= $content ?>

    <!-- Modal Chia sẻ Mã QR (QuickChart / GoQR REST API) -->
    <?php require __DIR__ . '/../partials/qr-share-modal.php'; ?>

    <!-- Widget Trợ lý Dã ngoại AI (Google Gemini Chatbox) -->
    <?php require __DIR__ . '/../partials/chatbot-widget.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/main.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/js/main.js') ?: time() ?>"></script>
    <script src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/js/ajax.js?v=<?= time() ?>"></script>
</body>
</html>
