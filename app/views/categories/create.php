<?php
$title = 'Thêm danh mục mới';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 680px;">
    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories" class="nature-btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh mục
        </a>
    </div>

    <div class="nature-card" style="border-radius: 36px;">
        <p class="nature-label">Quản trị nội dung</p>
        <h1 class="h3 fw-normal mb-2" style="color: var(--color-ink);">Thêm chuyên mục mới</h1>
        <p class="small text-muted mb-4">Tạo một chủ đề mới để người dùng phân loại và tìm kiếm bài viết dễ dàng hơn.</p>

        <div id="alertMessage"></div>

        <form id="addCategoryForm">
            <div class="mb-3">
                <label for="name" class="nature-label">Tên danh mục <span class="text-danger">*</span></label>
                <input type="text" class="nature-input" id="name" name="name" placeholder="Ví dụ: Cắm trại, Trekking, Phượt..." required>
            </div>

            <div class="mb-4">
                <label for="description" class="nature-label">Mô tả chi tiết</label>
                <textarea class="nature-textarea" id="description" name="description" rows="4" placeholder="Mô tả ngắn về chủ đề này..."></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="nature-btn-primary py-2 px-4" style="width: auto; height: 46px;">
                    <i class="bi bi-check-lg me-1"></i> Lưu chuyên mục
                </button>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/categories" class="nature-btn-secondary py-2 px-4 text-dark border-dark-subtle" style="height: 46px;">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addCategoryForm');
    const alertBox = document.getElementById('alertMessage');
    const baseUrl = window.BASE_URL || '';

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            try {
                const res = await fetch(baseUrl + '/categories/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString()
                });
                const data = await res.json();
                if (data.status === 'success') {
                    alertBox.innerHTML = '<div class="nature-alert nature-alert-success mb-3"><i class="bi bi-check-circle-fill me-2"></i>' + data.message + '</div>';
                    form.reset();
                } else {
                    alertBox.innerHTML = '<div class="nature-alert nature-alert-error mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>' + (data.message || 'Có lỗi xảy ra') + '</div>';
                }
            } catch (err) {
                alertBox.innerHTML = '<div class="nature-alert nature-alert-error mb-3">Lỗi kết nối máy chủ.</div>';
            }
        });
    }
});
</script>