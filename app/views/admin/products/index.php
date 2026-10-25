<?php
$title = 'Quản lý Sản phẩm';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$defaultImg = $baseUrl . '/assets/img/tent-forest.png';
?>
<div class="container py-4 mt-4" style="max-width: 1140px;">
    <!-- Breadcrumb Bootstrap -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a>
            </li>
            <li class="breadcrumb-item active text-white" aria-current="page">Sản phẩm</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h2 fw-normal text-white mb-1">Quản lý Sản phẩm &amp; Thiết bị</h1>
            <p class="text-white-50 mb-0">Thêm mới, chỉnh sửa hoặc xóa các dụng cụ dã ngoại gợi ý trên diễn đàn (FR12).</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/create" class="btn btn-success rounded-pill px-4 py-2">
                <i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" class="btn btn-outline-light rounded-pill px-3 py-2" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Xem trang thiết bị
            </a>
        </div>
    </div>

    <!-- Bộ lọc theo chuyên mục (Bootstrap Card + Form Select) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light text-dark">
        <div class="card-body p-3 px-4">
            <form method="get" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products" class="row g-3 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                            <i class="bi bi-funnel"></i>
                        </span>
                        <select name="category" class="form-select border-start-0 rounded-end-pill shadow-none" onchange="this.form.submit()">
                            <option value="">Tất cả chuyên mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= (($selectedCategoryId ?? null) == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-7 text-md-end text-muted small">
                    Tổng cộng: <strong class="text-dark"><?= count($products ?? []) ?></strong> sản phẩm
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng danh sách sản phẩm (Bootstrap Table) -->
    <div class="card border-0 shadow rounded-4 overflow-hidden bg-light text-dark">
        <?php if (empty($products)): ?>
            <div class="card-body text-center py-5">
                <i class="bi bi-box-seam display-4 text-muted d-block mb-3"></i>
                <h3 class="h5 fw-normal text-dark mb-1">Chưa có sản phẩm nào</h3>
                <p class="text-muted small mb-3">Hãy thêm sản phẩm dã ngoại đầu tiên vào hệ thống.</p>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/create" class="btn btn-dark rounded-pill px-4">
                    <i class="bi bi-plus-circle me-1"></i> Thêm sản phẩm ngay
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4" style="width: 70px;">ID</th>
                            <th scope="col" style="width: 95px;">Ảnh</th>
                            <th scope="col">Tên sản phẩm &amp; Mô tả</th>
                            <th scope="col" style="width: 160px;">Chuyên mục</th>
                            <th scope="col" class="text-end" style="width: 140px;">Giá tham khảo</th>
                            <th scope="col" class="text-center" style="width: 110px;">Liên kết</th>
                            <th scope="col" class="text-end pe-4" style="width: 130px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product):
                            $rawImg = trim((string)($product['image_url'] ?? ''));
                            if ($rawImg === '' || str_starts_with($rawImg, 'bi-')) {
                                $img = $defaultImg;
                            } elseif (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) {
                                $img = $rawImg;
                            } else {
                                $img = $baseUrl . '/' . ltrim($rawImg, '/');
                            }
                            $productLink = trim((string)($product['link'] ?? ''));
                        ?>
                            <tr>
                                <td class="ps-4 text-muted fw-medium">#<?= (int)$product['id'] ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                                         alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                         class="rounded-3 border object-fit-cover"
                                         style="width: 68px; height: 50px;"
                                         loading="lazy">
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if (!empty($product['description'])): ?>
                                        <div class="text-muted small text-truncate" style="max-width: 320px;">
                                            <?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2">
                                        <?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    <?= number_format((float)($product['price'] ?? 0), 0, ',', '.') ?> đ
                                </td>
                                <td class="text-center">
                                    <?php if ($productLink !== ''): ?>
                                        <a href="<?= htmlspecialchars($productLink, ENT_QUOTES, 'UTF-8') ?>"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1"
                                           title="<?= htmlspecialchars($productLink, ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="bi bi-link-45deg"></i> Mở link
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/products/<?= (int)$product['id'] ?>/edit"
                                       class="btn btn-sm btn-outline-primary rounded-pill me-1"
                                       title="Chỉnh sửa sản phẩm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger rounded-pill btn-open-delete-product-modal"
                                            data-id="<?= (int)$product['id'] ?>"
                                            data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                            title="Xóa sản phẩm">
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

<!-- Modal Bootstrap xác nhận xóa sản phẩm -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 text-dark">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-semibold" id="deleteProductModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Xác nhận xóa sản phẩm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body px-4">
                <p class="text-secondary mb-3">
                    Bạn có chắc chắn muốn xóa sản phẩm <strong id="modalProductName" class="text-dark"></strong>?
                </p>
                <div class="alert alert-danger border-0 rounded-3 small mb-0">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    File ảnh của sản phẩm cũng sẽ bị xóa khỏi máy chủ. Hành động này không thể hoàn tác.
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteProductForm" method="post" action="" class="m-0">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash3 me-1"></i> Xóa vĩnh viễn
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
var productAdminBaseUrl = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
var deleteProductModalInstance = null;

function handleOpenDeleteProductModal(event) {
    var button = event.currentTarget;
    var id = button.getAttribute('data-id');
    var name = button.getAttribute('data-name');

    document.getElementById('modalProductName').textContent = name;
    document.getElementById('deleteProductForm').action = productAdminBaseUrl + '/admin/products/' + id + '/delete';

    if (deleteProductModalInstance) {
        deleteProductModalInstance.show();
    }
}

function initDeleteProductModal() {
    var modalEl = document.getElementById('deleteProductModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        deleteProductModalInstance = new bootstrap.Modal(modalEl);
    }

    var buttons = document.querySelectorAll('.btn-open-delete-product-modal');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', handleOpenDeleteProductModal);
    }
}

document.addEventListener('DOMContentLoaded', initDeleteProductModal);
</script>
