<?php
$successMessage = $success ?? Session::flash('success');
$errorMessage = $message ?? Session::flash('error');
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Diễn đàn dã ngoại', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require __DIR__ . '/../partials/navbar.php'; ?>
    <main class="container py-5">
        <?php if ($successMessage): ?>
            <div class="alert alert-success" role="status"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?= $content ?>
    </main>
    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>