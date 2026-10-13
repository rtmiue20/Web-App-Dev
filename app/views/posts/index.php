<div class="mb-4">
    <h1 class="h2 mb-1"><?= htmlspecialchars($title ?? 'Bài viết', ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-body-secondary mb-0">Các chia sẻ kinh nghiệm từ cộng đồng dã ngoại.</p>
</div>

<?php require __DIR__ . '/../partials/post-cards.php'; ?>