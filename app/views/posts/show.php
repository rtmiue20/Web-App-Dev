<?php if ($post === false): ?>
    <h1>Không tìm thấy bài viết</h1>
<?php else: ?>
    <p><a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts">Quay lại danh sách</a></p>
    <h1><?= htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p>
        Danh mục: <?= htmlspecialchars($post['category_name'], ENT_QUOTES, 'UTF-8') ?> |
        Tác giả: <?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?> |
        <?= htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8') ?>
    </p>
    <?php if (!empty($post['location'])): ?>
        <p>Địa điểm: <?= htmlspecialchars($post['location'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <div><?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?></div>
<?php endif; ?>