<?php
$title = 'Thêm sản phẩm mới';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container py-4 mt-4" style="max-width: 760px;">
    <!-- Breadcrumb Bootstrap -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products" class="text-white-50 text-decoration-none">Sản phẩm</a>
            </li>
            <li class="breadcrumb-item active text-white" aria-current="page">Thêm mới</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-lg rounded-4 bg-light text-dark">
        <div class="card-body p-4 p-md-5">
            <div class="mb-4">
                <span class="badge rounded-pill text-bg-success mb-2 px-3 py-2">
                    <i class="bi bi-box-seam me-1"></i>FR12: Quản trị sản phẩm
                </span>
                <h1 class="h3 fw-semibold mb-1 text-dark">Thêm sản phẩm / thiết bị mới</h1>
                <p class="text-muted small mb-0">Điền thông tin dụng cụ dã ngoại (hình ảnh, mô tả, giá và liên kết tham khảo).</p>
            </div>

            <?php if (!empty($errors['form'])): ?>
                <div class="alert alert-danger d-flex align-items-center rounded-3 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/store" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <!-- Tên sản phẩm -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control form-control-lg <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                           id="name"
                           name="name"
                           required
                           maxlength="150"
                           placeholder="Ví dụ: Lều cắm trại chống mưa 4 người"
                           value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['name'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Chuyên mục & Giá -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="category_id" class="form-label fw-semibold">Chuyên mục <span class="text-danger">*</span></label>
                        <select class="form-select <?= !empty($errors['category_id']) ? 'is-invalid' : '' ?>"
                                id="category_id"
                                name="category_id"
                                required>
                            <option value="">-- Chọn chuyên mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= (($old['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['category_id'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['category_id'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="price" class="form-label fw-semibold">Giá tham khảo (VNĐ) <span class="text-danger">*</span></label>
                        <div class="input-group has-validation">
                            <input type="number"
                                   class="form-control <?= !empty($errors['price']) ? 'is-invalid' : '' ?>"
                                   id="price"
                                   name="price"
                                   min="0"
                                   step="1000"
                                   required
                                   placeholder="Ví dụ: 650000"
                                   value="<?= htmlspecialchars($old['price'] ?? '0', ENT_QUOTES, 'UTF-8') ?>">
                            <span class="input-group-text">VNĐ</span>
                            <?php if (!empty($errors['price'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Liên kết sản phẩm (FR12 yêu cầu: ảnh, mô tả, liên kết) -->
                <div class="mb-3">
                    <label for="link" class="form-label fw-semibold">Liên kết tham khảo / mua sản phẩm</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                        <input type="url"
                               class="form-control <?= !empty($errors['link']) ? 'is-invalid' : '' ?>"
                               id="link"
                               name="link"
                               maxlength="255"
                               placeholder="https://example.com/san-pham-da-ngoai"
                               value="<?= htmlspecialchars($old['link'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (!empty($errors['link'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['link'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-text">Không bắt buộc. Nhập đường dẫn đầy đủ bắt đầu bằng <code>https://</code> hoặc <code>http://</code>.</div>
                </div>

                <!-- Ảnh sản phẩm -->
                <div class="mb-3">
                    <label for="image" class="form-label fw-semibold">Ảnh sản phẩm (JPG, PNG, WEBP - tối đa 2MB)</label>
                    <input type="file"
                           class="form-control <?= !empty($errors['image']) ? 'is-invalid' : '' ?>"
                           id="image"
                           name="image"
                           accept="image/png, image/jpeg, image/webp">
                    <?php if (!empty($errors['image'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Mô tả ngắn -->
                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Mô tả ngắn</label>
                    <textarea class="form-control <?= !empty($errors['description']) ? 'is-invalid' : '' ?>"
                              id="description"
                              name="description"
                              rows="3"
                              maxlength="255"
                              placeholder="Mô tả đặc điểm, công dụng của thiết bị dã ngoại..."><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (!empty($errors['description'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['description'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Nút thao tác -->
                <div class="d-flex align-items-center gap-2 pt-2">
                    <button type="submit" class="btn btn-success rounded-pill px-4 py-2">
                        <i class="bi bi-check-lg me-1"></i> Lưu sản phẩm
                    </button>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                        Hủy bỏ
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
