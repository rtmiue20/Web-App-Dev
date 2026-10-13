<?php if (empty($posts)): ?>
    <p>Chưa có bài viết nào.</p>
<?php else: ?>
    <ul>
        <?php foreach ($posts as $post): ?>
            <li>
                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int) $post['id'] ?>">
                    <?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php if (!empty($post['category_name'])): ?>
                    <span>— <?= htmlspecialchars($post['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <?php if (!empty($post['username'])): ?>
                    <span> · <?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
