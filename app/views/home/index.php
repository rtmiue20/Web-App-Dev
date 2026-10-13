<section class="p-4 p-md-5 mb-5 bg-white rounded-3 shadow-sm">
    <div class="container-fluid py-2">
        <h1 class="display-6 fw-bold">Diễn đàn dã ngoại</h1>
        <p class="col-md-8 fs-5 text-body-secondary">
            Chia sẻ kinh nghiệm cắm trại, trekking và khám phá thiên nhiên.
        </p>
        <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts">
            Xem bài viết
        </a>
    </div>
</section>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h3 mb-0">Bài viết mới</h2>
    <a class="btn btn-outline-primary" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts">
        Tất cả bài viết
    </a>
</div>
<?php require __DIR__ . '/../partials/post-cards.php'; ?>