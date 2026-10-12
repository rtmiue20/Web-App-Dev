<?php if (!$posts): ?>
    <p>Chưa có bài viết nào.</p>
<?php else: ?>
    <ul>
        <?php foreach ($posts as $post): ?>
            <li>
                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int) $post['id'] ?>">
                    <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
                — <?= htmlspecialchars($post['category_name'], ENT_QUOTES, 'UTF-8') ?>
                bởi <?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
