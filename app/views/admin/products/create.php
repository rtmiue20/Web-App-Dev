<?php
$title = 'Thêm dụng cụ dã ngoại mới';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 720px;">
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a></li>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products" class="text-white-50 text-decoration-none">Dụng cụ &amp; Sản phẩm</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Thêm mới</li>
            </ol>
        </nav>
    </div>

    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products" class="nature-btn-secondary px-3 py-2 text-white border-white-20 d-inline-flex" style="height: 40px; font-size: 13px;">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="nature-card" style="border-radius: 36px; padding: 2.25rem 2rem;">
        <span class="nature-label mb-2"><i class="bi bi-tools me-1"></i>FR12: Quản trị sản phẩm / dụng cụ</span>
        <h1 class="h3 fw-normal mb-2" style="color: var(--color-ink);">Thêm dụng cụ dã ngoại mới</h1>
        <p class="small text-muted mb-4">Bổ sung thiết bị cắm trại, trekking để thành viên tham khảo và đính kèm vào các bài viết chia sẻ chuyến đi.</p>

        <?php if (!empty($error)): ?>
            <div class="nature-alert nature-alert-error mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/store" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-3">
                <label for="name" class="nature-label">Tên dụng cụ / thiết bị <span class="text-danger">*</span></label>
                <input type="text"
                       class="nature-input"
                       id="name"
                       name="name"
                       required
                       placeholder="Ví dụ: Lều cắm trại 2 lớp chống mưa Naturehike Cloud Up 2"
                       value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="category_id" class="nature-label">Chuyên mục</label>
                    <select class="nature-input" id="category_id" name="category_id">
                        <option value="">-- Chọn chuyên mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= (($old['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="price" class="nature-label">Giá tham khảo (VNĐ)</label>
                    <input type="number"
                           class="nature-input"
                           id="price"
                           name="price"
                           min="0"
                           step="1000"
                           placeholder="Ví dụ: 1450000"
                           value="<?= htmlspecialchars((string)($old['price'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="image" class="nature-label">Tải ảnh dụng cụ lên (Tùy chọn)</label>
                    <input type="file"
                           class="form-control rounded-4 py-2"
                           id="image"
                           name="image"
                           accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="col-md-6">
                    <label for="image_url" class="nature-label">Hoặc nhập URL ảnh (Cloudinary / ImgBB)</label>
                    <input type="text"
                           class="nature-input"
                           id="image_url"
                           name="image_url"
                           placeholder="https://... hoặc assets/img/tent-forest.png"
                           value="<?= htmlspecialchars($old['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="link" class="nature-label">Liên kết nơi mua / thông số chi tiết</label>
                <input type="url"
                       class="nature-input"
                       id="link"
                       name="link"
                       placeholder="https://..."
                       value="<?= htmlspecialchars($old['link'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="mb-4">
                <label for="description" class="nature-label">Mô tả dụng cụ</label>
                <textarea class="nature-textarea"
                          id="description"
                          name="description"
                          rows="4"
                          placeholder="Thông số kỹ thuật, ưu điểm nổi bật, lưu ý khi mang đi cắm trại / trekking..."><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="d-flex align-items-center gap-2 pt-2">
                <button type="submit" class="nature-btn-primary py-2 px-4" style="width: auto; height: 46px;">
                    <i class="bi bi-check-lg me-1"></i> Lưu dụng cụ
                </button>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products" class="nature-btn-secondary py-2 px-4 text-dark border-dark-subtle" style="height: 46px;">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

