<?php
$title = 'Thêm chuyên mục mới';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 680px;">
    <!-- Breadcrumb -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a></li>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="text-white-50 text-decoration-none">Chuyên mục</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Thêm mới</li>
            </ol>
        </nav>
    </div>

    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="nature-btn-secondary px-3 py-2 text-white border-white-20 d-inline-flex" style="height: 40px; font-size: 13px;">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="nature-card" style="border-radius: 36px; padding: 2.5rem 2rem;">
        <span class="nature-label mb-2"><i class="bi bi-folder-plus me-1"></i>FR11: Quản trị danh mục</span>
        <h1 class="h3 fw-normal mb-2" style="color: var(--color-ink);">Thêm chuyên mục mới</h1>
        <p class="small text-muted mb-4">Tạo chủ đề dã ngoại mới để người dùng có thể phân loại bài viết và tìm kiếm nhanh chóng.</p>

        <?php if (!empty($error)): ?>
            <div class="nature-alert nature-alert-error mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/store">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <!-- Tên chuyên mục -->
            <div class="mb-3">
                <label for="name" class="nature-label">Tên chuyên mục <span class="text-danger">*</span></label>
                <input type="text" 
                       class="nature-input" 
                       id="name" 
                       name="name" 
                       placeholder="Ví dụ: Cắm trại gia đình, Trekking rừng sâu, Đồ nghề sinh tồn..." 
                       value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       minlength="2"
                       maxlength="100"
                       required 
                       autofocus>
                <div class="form-text small text-muted">Từ 2 đến 100 ký tự. Tên chuyên mục không được trùng với mục đã có.</div>
            </div>

            <!-- Mô tả -->
            <div class="mb-4">
                <label for="description" class="nature-label">Mô tả ngắn</label>
                <textarea class="nature-textarea" 
                          id="description" 
                          name="description" 
                          rows="4" 
                          maxlength="255"
                          placeholder="Mô tả tóm tắt về loại bài viết, kinh nghiệm hoặc kỹ năng trong chủ đề này..."><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <div class="form-text small text-muted">Tối đa 255 ký tự. Giúp thành viên hiểu rõ chủ đề trước khi đăng bài.</div>
            </div>

            <!-- Action buttons -->
            <div class="d-flex align-items-center gap-2 pt-2">
                <button type="submit" class="nature-btn-primary py-2 px-4" style="width: auto; height: 46px;">
                    <i class="bi bi-check-lg me-1"></i> Lưu chuyên mục
                </button>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="nature-btn-secondary py-2 px-4 text-dark border-dark-subtle" style="height: 46px;">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>
