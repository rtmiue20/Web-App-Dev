<h1>Thêm bài viết</h1>

<?php if (!$canCreate): ?>
    <p>Bạn cần đăng nhập để thêm bài viết.</p>
<?php elseif (!$categories): ?>
    <p>Chưa có danh mục. Hãy thêm danh mục trước khi đăng bài.</p>
<?php else: ?>
    <form method="post" action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts/create">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <label for="title">Tiêu đề</label><br>
        <input id="title" name="title" maxlength="200" required
               value="<?= htmlspecialchars($form['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><br>
        <?php if (isset($errors['title'])): ?><small><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></small><br><?php endif; ?>

        <label for="category_id">Danh mục</label><br>
        <select id="category_id" name="category_id" required>
            <option value="">Chọn danh mục</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>"
                    <?= (string) ($form['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <?php if (isset($errors['category_id'])): ?><small><?= htmlspecialchars($errors['category_id'], ENT_QUOTES, 'UTF-8') ?></small><br><?php endif; ?>

        <label for="location">Địa điểm (không bắt buộc)</label><br>
        <input id="location" name="location" maxlength="150"
               value="<?= htmlspecialchars($form['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><br>
        <?php if (isset($errors['location'])): ?><small><?= htmlspecialchars($errors['location'], ENT_QUOTES, 'UTF-8') ?></small><br><?php endif; ?>

        <label for="content">Nội dung</label><br>
        <textarea id="content" name="content" rows="8" required><?= htmlspecialchars($form['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea><br>
        <?php if (isset($errors['content'])): ?><small><?= htmlspecialchars($errors['content'], ENT_QUOTES, 'UTF-8') ?></small><br><?php endif; ?>

        <button type="submit">Đăng bài</button>
    </form>
<?php endif; ?>