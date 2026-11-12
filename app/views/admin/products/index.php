<?php
$title = 'Quản lý Dụng cụ & Sản phẩm';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$defaultImg = $baseUrl . '/assets/img/tent-forest.png';
?>
<div class="app-container" style="max-width: 1140px;">
    <!-- Breadcrumb -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Dụng cụ &amp; Sản phẩm</li>
            </ol>
        </nav>
    </div>

    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">Quản lý Dụng cụ &amp; Thiết bị (FR12)</h1>
            <p class="page-subtitle">Thêm mới, cập nhật giá, hình ảnh, mô tả và liên kết mua sắm cho các trang thiết bị dã ngoại.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" target="_blank" class="nature-btn-secondary px-3 py-2 text-white border-white-20" style="height: 44px;">
                <i class="bi bi-box-arrow-up-right me-1"></i> Xem trang công khai
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                <i class="bi bi-plus-lg me-1"></i> Thêm dụng cụ mới
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="nature-card mb-4" style="border-radius: 24px; padding: 1.25rem 1.75rem;">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-5">
                <div class="admin-search-pill">
                    <i class="bi bi-search admin-search-icon"></i>
                    <input type="text" id="productSearchInput" class="admin-search-input" placeholder="Tìm nhanh theo tên dụng cụ...">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <form method="get" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products" class="d-flex gap-2">
                    <select name="category" class="form-select rounded-pill py-2 shadow-none" onchange="this.form.submit()">
                        <option value="">-- Tất cả chuyên mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($selectedCategoryId ?? null) == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="col-12 col-md-3 text-md-end text-muted small">
                <span>Tổng cộng: <strong class="text-dark" id="totalProductCount"><?= count($products ?? []) ?></strong> dụng cụ</span>
            </div>
        </div>
    </div>

    <!-- Products Data Table -->
    <div class="admin-table">
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box-seam fs-1 text-muted d-block mb-3"></i>
                <h3 class="h5 fw-normal text-dark mb-1">Chưa có dụng cụ nào</h3>
                <p class="text-muted small mb-3">Hãy thêm trang thiết bị dã ngoại đầu tiên vào danh mục.</p>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/create" class="nature-btn-primary px-4 py-2 d-inline-flex" style="width: auto; height: 42px;">
                    <i class="bi bi-plus-circle me-1"></i> Thêm dụng cụ
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="productsTable">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 65px;">ID</th>
                            <th scope="col" style="min-width: 240px;">Dụng cụ / Thiết bị</th>
                            <th scope="col" style="width: 150px;">Chuyên mục</th>
                            <th scope="col" style="width: 140px;">Giá tham khảo</th>
                            <th scope="col">Mô tả &amp; Liên kết</th>
                            <th scope="col" class="text-end" style="width: 130px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod):
                            $rawImg = trim((string)($prod['image_url'] ?? ''));
                            if ($rawImg === '' || str_starts_with($rawImg, 'bi-')) {
                                $img = $defaultImg;
                            } elseif (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) {
                                $img = $rawImg;
                            } else {
                                $img = $baseUrl . '/' . ltrim($rawImg, '/');
                            }
                        ?>
                            <tr class="product-row">
                                <td class="fw-medium text-muted">#<?= (int)$prod['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>"
                                             class="rounded-3 object-fit-cover flex-shrink-0"
                                             style="width: 52px; height: 52px; border: 1px solid rgba(0,0,0,0.08);">
                                        <div class="fw-semibold text-dark product-name">
                                            <?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill text-bg-light border text-dark px-3 py-1">
                                        <?= htmlspecialchars($prod['category_name'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-success">
                                    <?= number_format((float)($prod['price'] ?? 0), 0, ',', '.') ?> đ
                                </td>
                                <td class="small text-secondary">
                                    <div class="mb-1"><?= htmlspecialchars(mb_strimwidth($prod['description'] ?? 'Chưa có mô tả', 0, 75, '...'), ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if (!empty($prod['link'])): ?>
                                        <a href="<?= htmlspecialchars($prod['link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none" style="font-size: 12px;">
                                            <i class="bi bi-link-45deg"></i> Xem liên kết mua
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/edit/<?= (int)$prod['id'] ?>"
                                       class="admin-action-btn btn-edit me-1"
                                       title="Chỉnh sửa dụng cụ">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="post"
                                          action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/delete/<?= (int)$prod['id'] ?>"
                                          class="d-inline"
                                          onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn xóa dụng cụ “<?= htmlspecialchars(addslashes($prod['name']), ENT_QUOTES, 'UTF-8') ?>” khỏi kho dụng cụ cộng đồng không? Hành động này sẽ gỡ dụng cụ này khỏi danh mục chung.', 'Xác nhận xóa', 'Xóa dụng cụ khỏi kho?');">
                                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="admin-action-btn btn-delete" title="Xóa dụng cụ">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('productSearchInput')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#productsTable .product-row');
    let visible = 0;
    rows.forEach(function(row) {
        const name = (row.querySelector('.product-name')?.textContent || '').toLowerCase();
        const match = !q || name.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const countEl = document.getElementById('totalProductCount');
    if (countEl) countEl.textContent = visible;
});
</script>

