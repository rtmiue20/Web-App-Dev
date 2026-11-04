<?php
$title = 'Quản lý Bài viết';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$currentPage = isset($currentPage) ? (int)$currentPage : 1;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$totalPosts = isset($totalPosts) ? (int)$totalPosts : count($posts ?? []);
$selectedStatus = $selectedStatus ?? 'all';
$selectedCategoryId = $selectedCategoryId ?? null;

// Xây dựng chuỗi query string phục vụ phân trang
$queryBase = 'status=' . urlencode($selectedStatus);
if (!empty($selectedCategoryId)) {
    $queryBase .= '&category=' . (int)$selectedCategoryId;
}

$defaultThumb = $baseUrl . '/assets/img/tent-forest.png';
?>
<div class="app-container" style="max-width: 1140px;">
    <!-- Breadcrumb -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size: 13px;">
                <li class="breadcrumb-item">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a>
                </li>
                <li class="breadcrumb-item active text-white" aria-current="page">Bài viết</li>
            </ol>
        </nav>
    </div>

    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">Quản lý Bài viết</h1>
            <p class="page-subtitle">Kiểm duyệt, ẩn hoặc xóa các bài đăng từ cộng đồng dã ngoại.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                <i class="bi bi-pencil-square me-1"></i> Viết bài mới
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="nature-btn-secondary px-3 py-2 text-white border-white-20" style="height: 44px;" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Xem diễn đàn
            </a>
        </div>
    </div>

    <!-- Status Filter Pills Toolbar (FR13) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div class="d-flex flex-wrap gap-2">
            <!-- Tab Tất cả -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts?status=all<?= $selectedCategoryId ? '&category=' . (int)$selectedCategoryId : '' ?>" 
               class="dock-pill <?= ($selectedStatus === 'all') ? 'active' : '' ?>">
                Tất cả <span class="badge rounded-pill bg-light text-dark ms-1" id="filterCountAll"><?= (int)($countAll ?? 0) ?></span>
            </a>

            <!-- Tab Đã duyệt / Công khai -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts?status=published<?= $selectedCategoryId ? '&category=' . (int)$selectedCategoryId : '' ?>" 
               class="dock-pill <?= ($selectedStatus === 'published') ? 'active' : '' ?>">
                <i class="bi bi-check-circle-fill text-success me-1"></i> Đã duyệt
                <span class="badge rounded-pill bg-success ms-1" id="filterCountPublished"><?= (int)($countPublished ?? 0) ?></span>
            </a>

            <!-- Tab Chờ duyệt -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts?status=pending<?= $selectedCategoryId ? '&category=' . (int)$selectedCategoryId : '' ?>" 
               class="dock-pill <?= ($selectedStatus === 'pending') ? 'active' : '' ?>">
                <i class="bi bi-hourglass-split text-warning me-1"></i> Chờ duyệt
                <span class="badge rounded-pill bg-warning text-dark ms-1" id="filterCountPending"><?= (int)($countPending ?? 0) ?></span>
            </a>

            <!-- Tab Từ chối -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts?status=rejected<?= $selectedCategoryId ? '&category=' . (int)$selectedCategoryId : '' ?>" 
               class="dock-pill <?= ($selectedStatus === 'rejected') ? 'active' : '' ?>">
                <i class="bi bi-x-circle-fill text-danger me-1"></i> Từ chối
                <span class="badge rounded-pill bg-danger ms-1" id="filterCountRejected"><?= (int)($countRejected ?? 0) ?></span>
            </a>

            <!-- Tab Đã ẩn -->
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts?status=hidden<?= $selectedCategoryId ? '&category=' . (int)$selectedCategoryId : '' ?>" 
               class="dock-pill <?= ($selectedStatus === 'hidden') ? 'active' : '' ?>">
                <i class="bi bi-eye-slash-fill text-secondary me-1"></i> Đã ẩn
                <span class="badge rounded-pill bg-secondary ms-1" id="filterCountHidden"><?= (int)($countHidden ?? 0) ?></span>
            </a>
        </div>

        <!-- Filter Dropdown theo chuyên mục -->
        <div class="d-flex align-items-center gap-2">
            <form method="get" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts" class="d-flex gap-2 m-0">
                <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES, 'UTF-8') ?>">
                <select name="category" class="form-select form-select-sm rounded-pill shadow-none" onchange="this.form.submit()" style="min-width: 180px;">
                    <option value="">Tất cả chuyên mục</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($selectedCategoryId == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Search Input Box -->
    <div class="nature-card mb-4" style="border-radius: 24px; padding: 1.25rem 1.75rem;">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="postSearchInput" class="form-control border-start-0 rounded-end-pill py-2 shadow-none" placeholder="Tìm nhanh theo tiêu đề hoặc tên tác giả...">
                </div>
            </div>
            <div class="col-12 col-md-6 text-md-end text-muted small">
                <span>Tổng cộng: <strong class="text-dark" id="displayPostCount"><?= $totalPosts ?></strong> bài viết</span>
            </div>
        </div>
    </div>

    <!-- Posts Data Table -->
    <div class="admin-table">
        <?php if (empty($posts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-x fs-1 text-muted d-block mb-3"></i>
                <h3 class="h5 fw-normal text-dark mb-1">Không tìm thấy bài viết nào</h3>
                <p class="text-muted small mb-3">Chưa có bài viết nào phù hợp với bộ lọc hiện tại.</p>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts" class="nature-btn-secondary px-4 py-2 d-inline-flex text-dark border-dark-subtle" style="width: auto; height: 42px;">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại bộ lọc
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="postsAdminTable">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 60px;">ID</th>
                            <th scope="col" style="width: 80px;">Ảnh bìa</th>
                            <th scope="col" style="min-width: 250px;">Tiêu đề & Tác giả</th>
                            <th scope="col" style="width: 150px;">Chuyên mục</th>
                            <th scope="col" class="text-center" style="width: 160px;">Trạng thái</th>
                            <th scope="col" class="text-center" style="width: 100px;">Tương tác</th>
                            <th scope="col" class="text-end" style="width: 170px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): 
                            $img = !empty($post['image_url']) 
                                ? (str_starts_with($post['image_url'], 'http') ? $post['image_url'] : $baseUrl . '/' . ltrim($post['image_url'], '/'))
                                : $defaultThumb;
                            $postStatus = $post['status'] ?? 'published';
                            $postTitle = $post['title'] ?? 'Không có tiêu đề';
                        ?>
                            <tr class="post-row">
                                <td class="fw-medium text-muted">#<?= (int)$post['id'] ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" 
                                         alt="<?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?>"
                                         class="rounded-3 object-fit-cover shadow-sm"
                                         style="width: 64px; height: 48px; border: 1px solid rgba(0,0,0,0.08);"
                                         loading="lazy">
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark post-title-text mb-1">
                                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>" 
                                           target="_blank"
                                           class="text-decoration-none text-dark hover-underline" 
                                           title="Xem bài viết: <?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars(mb_strimwidth($postTitle, 0, 60, '...'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </div>
                                    <div class="small text-muted post-author-text">
                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($post['username'] ?? 'Thành viên', ENT_QUOTES, 'UTF-8') ?>
                                        <span class="mx-1">&bull;</span>
                                        <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y H:i', strtotime($post['created_at'] ?? 'now')) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border text-secondary px-3 py-2 rounded-pill">
                                        <i class="bi bi-tag-fill me-1 opacity-75"></i><?= htmlspecialchars($post['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    // Thiết lập style và nhãn cho trạng thái hiện tại
                                    $badgeClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                                    $badgeIcon  = 'bi-eye-slash-fill';
                                    $badgeText  = 'Đã ẩn';

                                    if ($postStatus === 'published') {
                                        $badgeClass = 'bg-success-subtle text-success border-success-subtle';
                                        $badgeIcon  = 'bi-check-circle-fill';
                                        $badgeText  = 'Đã duyệt';
                                    } elseif ($postStatus === 'pending') {
                                        $badgeClass = 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
                                        $badgeIcon  = 'bi-hourglass-split';
                                        $badgeText  = 'Chờ duyệt';
                                    } elseif ($postStatus === 'rejected') {
                                        $badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
                                        $badgeIcon  = 'bi-x-circle-fill';
                                        $badgeText  = 'Từ chối';
                                    }
                                    ?>
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm dropdown-toggle rounded-pill px-3 py-1 fw-medium border shadow-none d-inline-flex align-items-center gap-1 post-status-btn <?= $badgeClass ?>" 
                                                type="button" 
                                                id="statusDropdown_<?= (int)$post['id'] ?>"
                                                data-bs-toggle="dropdown" 
                                                data-bs-popper-config='{"strategy":"fixed"}'
                                                aria-expanded="false"
                                                title="Nhấp để thay đổi trạng thái bài viết">
                                            <i class="bi <?= $badgeIcon ?> me-1 status-btn-icon"></i>
                                            <span class="status-btn-text"><?= $badgeText ?></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 rounded-4" 
                                            aria-labelledby="statusDropdown_<?= (int)$post['id'] ?>" 
                                            style="min-width: 200px; z-index: 1060;">
                                            <li class="dropdown-header text-uppercase small fw-bold px-3 py-1 text-muted" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                                                Chuyển trạng thái
                                            </li>

                                            <!-- 1. Chờ duyệt (Pending) -->
                                            <li>
                                                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts/<?= (int)$post['id'] ?>/status" class="status-change-form m-0" data-post-id="<?= (int)$post['id'] ?>">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="status" value="pending">
                                                    <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 <?= ($postStatus === 'pending') ? 'active' : '' ?>">
                                                        <span class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-hourglass-split text-warning"></i>
                                                            <span>Chờ duyệt (Pending)</span>
                                                        </span>
                                                        <?php if ($postStatus === 'pending'): ?>
                                                            <i class="bi bi-check2 fw-bold text-success status-check-icon"></i>
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            </li>

                                            <!-- 2. Đã duyệt (Approved) -->
                                            <li>
                                                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts/<?= (int)$post['id'] ?>/status" class="status-change-form m-0" data-post-id="<?= (int)$post['id'] ?>">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="status" value="published">
                                                    <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 <?= ($postStatus === 'published') ? 'active' : '' ?>">
                                                        <span class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-check-circle-fill text-success"></i>
                                                            <span>Đã duyệt (Approved)</span>
                                                        </span>
                                                        <?php if ($postStatus === 'published'): ?>
                                                            <i class="bi bi-check2 fw-bold text-success status-check-icon"></i>
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            </li>

                                            <!-- 3. Từ chối (Reject) -->
                                            <li>
                                                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts/<?= (int)$post['id'] ?>/status" class="status-change-form m-0" data-post-id="<?= (int)$post['id'] ?>">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 <?= ($postStatus === 'rejected') ? 'active' : '' ?>">
                                                        <span class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                                            <span>Từ chối (Reject)</span>
                                                        </span>
                                                        <?php if ($postStatus === 'rejected'): ?>
                                                            <i class="bi bi-check2 fw-bold text-success status-check-icon"></i>
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            </li>

                                            <!-- 4. Đã ẩn (Hidden) -->
                                            <li>
                                                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts/<?= (int)$post['id'] ?>/status" class="status-change-form m-0" data-post-id="<?= (int)$post['id'] ?>">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="status" value="hidden">
                                                    <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 <?= ($postStatus === 'hidden') ? 'active' : '' ?>">
                                                        <span class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-eye-slash-fill text-secondary"></i>
                                                            <span>Đã ẩn (Hidden)</span>
                                                        </span>
                                                        <?php if ($postStatus === 'hidden'): ?>
                                                            <i class="bi bi-check2 fw-bold text-success status-check-icon"></i>
                                                        <?php endif; ?>
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td class="text-center small text-muted">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <span title="Lượt thích"><i class="bi bi-heart-fill text-danger me-1"></i><?= (int)($post['likes'] ?? 0) ?></span>
                                        <span title="Bình luận"><i class="bi bi-chat-dots-fill text-primary me-1"></i><?= (int)($post['comments_count'] ?? 0) ?></span>
                                    </div>
                                </td>
                                <td class="text-end text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Nút Xem chi tiết -->
                                        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>" 
                                           target="_blank"
                                           class="admin-action-btn" 
                                           title="Xem bài viết trên diễn đàn">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>

                                        <!-- Nút Duyệt bài (Nếu chưa published) -->
                                        <?php if ($postStatus !== 'published'): ?>
                                            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts/<?= (int)$post['id'] ?>/approve" class="m-0 d-inline">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="admin-action-btn" style="color: #198754;" title="Duyệt và hiển thị bài viết">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Nút Ẩn bài (Nếu đang published) -->
                                        <?php if ($postStatus === 'published'): ?>
                                            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts/<?= (int)$post['id'] ?>/hide" class="m-0 d-inline">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="admin-action-btn" style="color: #ffc107;" title="Ẩn bài viết khỏi diễn đàn">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Nút Xóa bài viết (Mở modal xác nhận an toàn) -->
                                        <button type="button" 
                                                class="admin-action-btn btn-delete btn-open-delete-post-modal"
                                                data-id="<?= (int)$post['id'] ?>"
                                                data-title="<?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                title="Xóa vĩnh viễn bài viết">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang Bootstrap cho bảng Quản trị (FR13) -->
            <?php if ($totalPages > 1): ?>
                <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white">
                    <div class="small text-muted">
                        Trang <strong><?= $currentPage ?></strong> / <strong><?= $totalPages ?></strong> (Tổng số: <?= $totalPosts ?> bài viết)
                    </div>
                    <nav aria-label="Phân trang danh sách bài viết quản trị">
                        <ul class="pagination pagination-sm m-0">
                            <!-- Nút trước -->
                            <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts?<?= $queryBase ?>&page=<?= max(1, $currentPage - 1) ?>">
                                    &laquo; Trước
                                </a>
                            </li>

                            <!-- Các số trang -->
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?= ($p === $currentPage) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts?<?= $queryBase ?>&page=<?= $p ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Nút sau -->
                            <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts?<?= $queryBase ?>&page=<?= min($totalPages, $currentPage + 1) ?>">
                                    Sau &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Xác Nhận Xóa Bài Viết (FR13) -->
<div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 50%; background: rgba(220, 53, 69, 0.12); color: #dc3545;">
                        <i class="bi bi-trash3-fill fs-5"></i>
                    </span>
                    <h5 class="modal-title fw-normal mb-0" id="deletePostModalLabel" style="color: var(--color-ink);">Xác nhận xóa bài viết</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            
            <div class="modal-body px-4 py-3">
                <p class="text-secondary mb-3">
                    Bạn có chắc chắn muốn xóa bài viết: <strong id="modalPostTitle" class="text-dark"></strong> (ID: #<span id="modalPostId"></span>)?
                </p>
                <div class="alert alert-danger border-0 rounded-4 small mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Cảnh báo:</strong> Thao tác này sẽ xóa vĩnh viễn bài viết cùng toàn bộ ảnh bìa, bình luận và lượt thích liên quan. Hành động này không thể hoàn tác!
                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-2">
                <button type="button" class="nature-btn-secondary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px;">
                    Hủy bỏ
                </button>
                <form id="deletePostForm" method="post" action="" class="m-0">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-danger rounded-pill px-4" style="height: 42px; font-weight: 500;">
                        <i class="bi bi-trash3 me-1"></i> Xóa vĩnh viễn
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript tương tác (Không dùng lambda phức tạp, dễ đọc cho sinh viên) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var baseUrl = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
    
    // 1. Xử lý mở Modal Xóa bài viết
    var deleteModalEl = document.getElementById('deletePostModal');
    var deleteModal = null;
    if (deleteModalEl && typeof bootstrap !== 'undefined') {
        deleteModal = new bootstrap.Modal(deleteModalEl);
    }
    
    var deleteButtons = document.querySelectorAll('.btn-open-delete-post-modal');
    for (var i = 0; i < deleteButtons.length; i++) {
        deleteButtons[i].addEventListener('click', function () {
            var postId = this.getAttribute('data-id');
            var postTitle = this.getAttribute('data-title');
            
            document.getElementById('modalPostId').textContent = postId;
            document.getElementById('modalPostTitle').textContent = postTitle;
            document.getElementById('deletePostForm').action = baseUrl + '/admin/posts/' + postId + '/delete';
            
            if (deleteModal) {
                deleteModal.show();
            }
        });
    }

    // 2. Tìm kiếm nhanh trong bảng bài viết bằng JavaScript
    var searchInput = document.getElementById('postSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var filter = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('#postsAdminTable tbody tr.post-row');
            var matchCount = 0;

            for (var j = 0; j < rows.length; j++) {
                var titleText = rows[j].querySelector('.post-title-text');
                var authorText = rows[j].querySelector('.post-author-text');
                var content = (titleText ? titleText.textContent : '') + ' ' + (authorText ? authorText.textContent : '');
                
                if (content.toLowerCase().indexOf(filter) > -1) {
                    rows[j].style.display = '';
                    matchCount++;
                } else {
                    rows[j].style.display = 'none';
                }
            }

            var countEl = document.getElementById('displayPostCount');
            if (countEl) {
                countEl.textContent = matchCount;
            }
        });
    }

    // 3. Xử lý đổi trạng thái bài viết nhanh qua AJAX (Không dùng Lambda)
    var statusForms = document.querySelectorAll('.status-change-form');
    for (var k = 0; k < statusForms.length; k++) {
        statusForms[k].addEventListener('submit', function (e) {
            e.preventDefault();
            var form = this;
            var postId = form.getAttribute('data-post-id');
            var formData = new FormData(form);
            var newStatus = formData.get('status');

            // Hiển thị trạng thái đang xử lý trên nút
            var dropdownBtn = document.getElementById('statusDropdown_' + postId);
            if (!dropdownBtn) {
                form.submit();
                return;
            }

            var originalHtml = dropdownBtn.innerHTML;
            dropdownBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (res) {
                dropdownBtn.disabled = false;
                if (res.status === 'success') {
                    // Cập nhật giao diện nút dropdown theo trạng thái mới
                    var btnClasses = [
                        'bg-success-subtle', 'text-success', 'border-success-subtle',
                        'bg-warning-subtle', 'text-warning-emphasis', 'border-warning-subtle',
                        'bg-danger-subtle', 'text-danger', 'border-danger-subtle',
                        'bg-secondary-subtle', 'text-secondary', 'border-secondary-subtle'
                    ];
                    for (var c = 0; c < btnClasses.length; c++) {
                        dropdownBtn.classList.remove(btnClasses[c]);
                    }

                    var newIcon = 'bi-check-circle-fill';
                    var newText = 'Đã duyệt';
                    if (newStatus === 'published') {
                        dropdownBtn.classList.add('bg-success-subtle', 'text-success', 'border-success-subtle');
                        newIcon = 'bi-check-circle-fill';
                        newText = 'Đã duyệt';
                    } else if (newStatus === 'pending') {
                        dropdownBtn.classList.add('bg-warning-subtle', 'text-warning-emphasis', 'border-warning-subtle');
                        newIcon = 'bi-hourglass-split';
                        newText = 'Chờ duyệt';
                    } else if (newStatus === 'rejected') {
                        dropdownBtn.classList.add('bg-danger-subtle', 'text-danger', 'border-danger-subtle');
                        newIcon = 'bi-x-circle-fill';
                        newText = 'Từ chối';
                    } else if (newStatus === 'hidden') {
                        dropdownBtn.classList.add('bg-secondary-subtle', 'text-secondary', 'border-secondary-subtle');
                        newIcon = 'bi-eye-slash-fill';
                        newText = 'Đã ẩn';
                    }

                    dropdownBtn.innerHTML = '<i class="bi ' + newIcon + ' me-1 status-btn-icon"></i><span class="status-btn-text">' + newText + '</span>';

                    // Cập nhật trạng thái active và checkmark trong menu
                    var dropdownMenu = dropdownBtn.nextElementSibling;
                    if (dropdownMenu) {
                        var items = dropdownMenu.querySelectorAll('.dropdown-item');
                        for (var m = 0; m < items.length; m++) {
                            items[m].classList.remove('active');
                            var checkIcon = items[m].querySelector('.status-check-icon');
                            if (checkIcon) {
                                checkIcon.remove();
                            }
                        }
                        var activeBtn = form.querySelector('.dropdown-item');
                        if (activeBtn) {
                            activeBtn.classList.add('active');
                            activeBtn.insertAdjacentHTML('beforeend', '<i class="bi bi-check2 fw-bold text-success status-check-icon"></i>');
                        }
                    }

                    // Đóng dropdown menu qua Bootstrap API
                    if (typeof bootstrap !== 'undefined') {
                        var bsDropdown = bootstrap.Dropdown.getInstance(dropdownBtn);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }

                    // Cập nhật số lượng trên các tab bộ lọc nếu có trả về
                    if (res.counts) {
                        var elAll = document.getElementById('filterCountAll');
                        var elPub = document.getElementById('filterCountPublished');
                        var elPen = document.getElementById('filterCountPending');
                        var elRej = document.getElementById('filterCountRejected');
                        var elHid = document.getElementById('filterCountHidden');
                        if (elAll) elAll.textContent = res.counts.all;
                        if (elPub) elPub.textContent = res.counts.published;
                        if (elPen) elPen.textContent = res.counts.pending;
                        if (elRej) elRej.textContent = res.counts.rejected;
                        if (elHid) elHid.textContent = res.counts.hidden;
                    }
                } else {
                    alert(res.message || 'Có lỗi xảy ra khi cập nhật trạng thái.');
                }
            })
            .catch(function (err) {
                dropdownBtn.disabled = false;
                dropdownBtn.innerHTML = originalHtml;
                // Fallback: nếu lỗi fetch thì submit form bình thường
                form.submit();
            });
        });
    }
});
</script>
