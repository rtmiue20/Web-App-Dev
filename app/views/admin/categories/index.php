<?php
$title = 'Quản lý Chuyên mục';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 1100px;">
    <!-- Breadcrumb & Header -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Chuyên mục</li>
            </ol>
        </nav>
    </div>

    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">Quản lý Chuyên mục</h1>
            <p class="page-subtitle">Thêm, sửa thông tin hoặc xóa các danh mục chủ đề trên diễn đàn dã ngoại.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                <i class="bi bi-plus-lg me-1"></i> Thêm chuyên mục mới
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="nature-card mb-4" style="border-radius: 24px; padding: 1.25rem 1.75rem;">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="categorySearchInput" class="form-control border-start-0 rounded-end-pill py-2 shadow-none" placeholder="Tìm kiếm nhanh theo tên chuyên mục...">
                </div>
            </div>
            <div class="col-12 col-md-6 text-md-end text-muted small">
                <span>Tổng cộng: <strong class="text-dark" id="totalCategoryCount"><?= count($categories ?? []) ?></strong> chuyên mục</span>
            </div>
        </div>
    </div>

    <!-- Categories Data Table -->
    <div class="admin-table">
        <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="bi bi-folder-x fs-1 text-muted d-block mb-3"></i>
                <h3 class="h5 fw-normal text-dark mb-1">Chưa có chuyên mục nào</h3>
                <p class="text-muted small mb-3">Hãy tạo chuyên mục đầu tiên để các thành viên bắt đầu đăng bài viết.</p>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/create" class="nature-btn-primary px-4 py-2 d-inline-flex" style="width: auto; height: 42px;">
                    <i class="bi bi-plus-circle me-1"></i> Tạo chuyên mục
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="categoriesTable">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 70px;">ID</th>
                            <th scope="col" style="min-width: 180px;">Tên chuyên mục</th>
                            <th scope="col">Mô tả</th>
                            <th scope="col" class="text-center" style="width: 140px;">Số bài viết</th>
                            <th scope="col" class="text-end" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): 
                            $postCount = (int)($cat['post_count'] ?? 0);
                        ?>
                            <tr class="category-row">
                                <td class="fw-medium text-muted">#<?= (int)$cat['id'] ?></td>
                                <td>
                                    <div class="fw-semibold text-dark category-name">
                                        <i class="bi bi-tag-fill text-success me-2 opacity-75"></i>
                                        <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="small text-muted d-md-none">
                                        <?= htmlspecialchars(mb_strimwidth($cat['description'] ?? '', 0, 45, '...'), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="text-secondary small d-none d-md-table-cell category-desc">
                                    <?= htmlspecialchars($cat['description'] ?? 'Chưa có mô tả chi tiết', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill <?= $postCount > 0 ? 'text-bg-success' : 'text-bg-light border text-muted' ?> px-3 py-2">
                                        <?= $postCount ?> bài
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <!-- Nút Sửa -->
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/edit/<?= (int)$cat['id'] ?>" 
                                       class="admin-action-btn btn-edit me-1" 
                                       title="Chỉnh sửa chuyên mục">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Nút Xóa (Có xác nhận) -->
                                    <button type="button" 
                                            class="admin-action-btn btn-delete btn-open-delete-modal"
                                            data-id="<?= (int)$cat['id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-count="<?= $postCount ?>"
                                            title="Xóa chuyên mục">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Xác Nhận Xóa Chuyên Mục (FR11 Requirement) -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 50%; background: rgba(220, 53, 69, 0.12); color: #dc3545;">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </span>
                    <h5 class="modal-title fw-normal mb-0" id="deleteConfirmModalLabel" style="color: var(--color-ink);">Xác nhận xóa chuyên mục</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            
            <div class="modal-body px-4 py-3">
                <p class="text-secondary mb-2">
                    Bạn có chắc chắn muốn xóa chuyên mục: <strong id="deleteCategoryName" class="text-dark"></strong> (ID: #<span id="deleteCategoryId"></span>)?
                </p>
                <div id="deleteWarningBox" class="alert alert-warning border-0 rounded-4 d-none small mb-0">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <strong>Lưu ý:</strong> Chuyên mục này đang có <span id="deletePostCount" class="fw-bold"></span> bài viết. Do quy định an toàn dữ liệu, bạn không thể xóa chuyên mục khi vẫn còn bài viết bên trong.
                </div>
                <div id="deleteSafeNotice" class="text-muted small">
                    Hành động này sẽ xóa hoàn toàn danh mục khỏi hệ thống và không thể hoàn tác.
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteCategoryForm" method="post" action="">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" id="btnConfirmDelete" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash3 me-1"></i> Đồng ý xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = window.BASE_URL || '';
    const modalElement = document.getElementById('deleteConfirmModal');
    const deleteModal = modalElement ? new bootstrap.Modal(modalElement) : null;
    const form = document.getElementById('deleteCategoryForm');
    const nameSpan = document.getElementById('deleteCategoryName');
    const idSpan = document.getElementById('deleteCategoryId');
    const warningBox = document.getElementById('deleteWarningBox');
    const safeNotice = document.getElementById('deleteSafeNotice');
    const countSpan = document.getElementById('deletePostCount');
    const btnConfirm = document.getElementById('btnConfirmDelete');

    // Mở modal xác nhận khi bấm nút xóa
    document.querySelectorAll('.btn-open-delete-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const postCount = parseInt(this.getAttribute('data-count') || '0', 10);

            if (form && nameSpan && idSpan) {
                form.action = baseUrl + '/admin/categories/delete/' + id;
                nameSpan.textContent = name;
                idSpan.textContent = id;

                if (postCount > 0) {
                    warningBox.classList.remove('d-none');
                    safeNotice.classList.add('d-none');
                    countSpan.textContent = postCount;
                    btnConfirm.setAttribute('disabled', 'disabled');
                    btnConfirm.classList.add('opacity-50');
                    btnConfirm.title = 'Vui lòng xóa hoặc di chuyển các bài viết trước khi xóa danh mục này';
                } else {
                    warningBox.classList.add('d-none');
                    safeNotice.classList.remove('d-none');
                    btnConfirm.removeAttribute('disabled');
                    btnConfirm.classList.remove('opacity-50');
                    btnConfirm.title = '';
                }

                if (deleteModal) {
                    deleteModal.show();
                }
            }
        });
    });

    // Tìm kiếm tức thời trên client-side
    const searchInput = document.getElementById('categorySearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#categoriesTable tbody tr.category-row');
            let visibleCount = 0;

            rows.forEach(function(row) {
                const nameText = row.querySelector('.category-name')?.textContent.toLowerCase() || '';
                const descText = row.querySelector('.category-desc')?.textContent.toLowerCase() || '';
                if (nameText.includes(keyword) || descText.includes(keyword)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const countEl = document.getElementById('totalCategoryCount');
            if (countEl) {
                countEl.textContent = visibleCount;
            }
        });
    }
});
</script>
