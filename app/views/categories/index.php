<h1>Danh mục bài viết</h1>

<?php if (empty($categories)): ?>
    <p>Chưa có danh mục.</p>
<?php else: ?>
    <ul>
        <?php foreach ($categories as $category): ?>
            <li>
                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts?category=<?= (int) $category['id'] ?>">
                    <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
                (<?= (int) $category['post_count'] ?> bài)
                <?php if (!empty($category['description'])): ?>
                    <p><?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>