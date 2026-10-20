<?php
$title = 'Chỉnh sửa chuyên mục';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 680px;">
    <!-- Breadcrumb -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a></li>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="text-white-50 text-decoration-none">Chuyên mục</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Sửa (#<?= (int)($category['id'] ?? 0) ?>)</li>
            </ol>
        </nav>
    </div>

    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="nature-btn-secondary px-3 py-2 text-white border-white-20 d-inline-flex" style="height: 40px; font-size: 13px;">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="nature-card" style="border-radius: 36px; padding: 2.5rem 2rem;">
        <span class="nature-label mb-2"><i class="bi bi-pencil-square me-1"></i>FR11: Chỉnh sửa danh mục</span>
        <h1 class="h3 fw-normal mb-2" style="color: var(--color-ink);">Cập nhật chuyên mục</h1>
        <p class="small text-muted mb-4">Thay đổi tên hoặc mô tả cho chuyên mục <strong>#<?= (int)($category['id'] ?? 0) ?></strong>.</p>

        <?php if (!empty($error)): ?>
            <div class="nature-alert nature-alert-error mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/update/<?= (int)($category['id'] ?? 0) ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <!-- Tên chuyên mục -->
            <div class="mb-3">
                <label for="name" class="nature-label">Tên chuyên mục <span class="text-danger">*</span></label>
                <input type="text" 
                       class="nature-input" 
                       id="name" 
                       name="name" 
                       value="<?= htmlspecialchars($old['name'] ?? ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       minlength="2"
                       maxlength="100"
                       required 
                       autofocus>
                <div class="form-text small text-muted">Từ 2 đến 100 ký tự. Không được trùng lặp với chuyên mục khác.</div>
            </div>

            <!-- Mô tả -->
            <div class="mb-4">
                <label for="description" class="nature-label">Mô tả ngắn</label>
                <textarea class="nature-textarea" 
                          id="description" 
                          name="description" 
                          rows="4" 
                          maxlength="255"><?= htmlspecialchars($old['description'] ?? ($category['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                <div class="form-text small text-muted">Tối đa 255 ký tự.</div>
            </div>

            <!-- Action buttons -->
            <div class="d-flex align-items-center gap-2 pt-2">
                <button type="submit" class="nature-btn-primary py-2 px-4" style="width: auto; height: 46px;">
                    <i class="bi bi-save me-1"></i> Lưu thay đổi
                </button>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="nature-btn-secondary py-2 px-4 text-dark border-dark-subtle" style="height: 46px;">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>
