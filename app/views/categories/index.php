<div class="mb-4">
    <h1 class="h2 mb-1">Danh mục bài viết</h1>
    <p class="text-body-secondary mb-0">Khám phá các bài viết theo chủ đề.</p>
</div>

<?php if (empty($categories)): ?>
    <div class="alert alert-info mb-0" role="status">Chưa có danh mục.</div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        <?php foreach ($categories as $category): ?>
            <div class="col">
                <article class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <h2 class="h5 card-title mb-0">
                                <a class="link-dark text-decoration-none" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts?category=<?= (int) $category['id'] ?>">
                                    <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h2>
                            <span class="badge text-bg-primary"><?= (int) $category['post_count'] ?> bài</span>
                        </div>
                        <?php if (!empty($category['description'])): ?>
                            <p class="card-text text-body-secondary mb-0">
                                <?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>