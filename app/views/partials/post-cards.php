<?php if (empty($posts)): ?>
    <div class="alert alert-info mb-0" role="status">Chưa có bài viết nào.</div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        <?php foreach ($posts as $post): ?>
            <div class="col">
                <article class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <?php if (!empty($post['category_name'])): ?>
                            <span class="badge text-bg-light border align-self-start mb-2">
                                <?= htmlspecialchars($post['category_name'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php endif; ?>
                        <h2 class="h5 card-title">
                            <a class="link-dark text-decoration-none" href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int) $post['id'] ?>">
                                <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>
                        <?php if (!empty($post['username'])): ?>
                            <p class="card-text text-body-secondary small mt-auto mb-0">
                                Tác giả: <?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
