<h1>Danh sách bài viết</h1>
<p><a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/posts/create">Thêm bài viết</a></p>

<form method="get">
    <label for="category">Lọc danh mục:</label>
    <select name="category" id="category">
        <option value="">Tất cả</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= (int) $category['id'] ?>"
                <?= (string) ($_GET['category'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Lọc</button>
</form>

<?php require __DIR__ . '/../partials/post-cards.php'; ?>