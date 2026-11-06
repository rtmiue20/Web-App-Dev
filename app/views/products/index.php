<?php
$title = 'Dụng cụ & Trang thiết bị dã ngoại';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$defaultImg = $baseUrl . '/assets/img/tent-forest.png';
$currentPage = isset($currentPage) ? (int)$currentPage : 1;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$totalProducts = isset($totalProducts) ? (int)$totalProducts : count($products ?? []);
$totalItems = $totalProducts;
$catQuery = !empty($selectedCategoryId) ? 'category=' . (int)$selectedCategoryId . '&' : '';

$isLoggedIn = Auth::check();
$currentUserId = Auth::id();
$isAdmin = Auth::isAdmin();
$csrfToken = Csrf::token();
?>
<div class="app-container">
    <!-- Header trang Dụng cụ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center page-header gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="nature-badge" style="background: rgba(155, 189, 85, 0.15); color: #fff; border-color: rgba(155, 189, 85, 0.4);">
                    <i class="bi bi-tools me-1"></i>Dụng cụ &amp; Thiết bị
                </span>
                <span class="badge rounded-pill text-bg-dark border border-secondary border-opacity-25 px-3 py-1 text-white-50">
                    <i class="bi bi-box-seam me-1"></i><?= $totalItems ?> sản phẩm
                </span>
            </div>
            <h1 class="page-title mb-1">Dụng cụ &amp; Thiết bị dã ngoại</h1>
            <p class="page-subtitle mb-0">Các công cụ, trang bị sinh tồn và đồ cắm trại được cộng đồng chia sẻ, tin dùng trong các chuyến đi.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ($isLoggedIn): ?>
                <button type="button" class="nature-btn-primary px-4 py-2" style="width: auto; height: 46px;" data-bs-toggle="modal" data-bs-target="#modalAddProduct">
                    <i class="bi bi-plus-circle-fill me-2"></i> Chia sẻ dụng cụ mới
                </button>
            <?php else: ?>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-primary px-4 py-2" style="width: auto; height: 46px;">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Đăng nhập để chia sẻ
                </a>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-secondary px-4 py-2 text-white border-white-50" style="width: auto; height: 46px; background: rgba(255,255,255,0.08);">
                <i class="bi bi-pencil-fill me-2"></i> Viết bài trải nghiệm
            </a>
        </div>
    </div>

    <!-- Category Filter Bar (Phân mục) -->
    <div class="category-slidebar-container mb-4 pb-3" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" 
               class="dock-pill <?= empty($selectedCategoryId) ? 'active' : '' ?>">
                <i class="bi bi-grid-fill me-1"></i> Tất cả dụng cụ
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products?category=<?= (int)$cat['id'] ?>" 
                   class="dock-pill <?= ($selectedCategoryId ?? null) == $cat['id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Grid danh sách sản phẩm / dụng cụ -->
    <?php if (empty($products)): ?>
        <div class="nature-card text-center py-5 my-4" style="border-radius: 32px;">
            <i class="bi bi-box-seam display-3 text-muted d-block mb-3"></i>
            <h2 class="h5 fw-normal mb-2 text-dark">Chưa có dụng cụ nào trong danh mục này</h2>
            <p class="text-muted small mb-4">Hãy chọn một danh mục khác hoặc là người đầu tiên chia sẻ dụng cụ hữu ích của bạn!</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" class="nature-btn-secondary px-4 py-2 text-dark border-dark-subtle d-inline-flex" style="width: auto;">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Xem tất cả dụng cụ
                </a>
                <?php if ($isLoggedIn): ?>
                    <button type="button" class="nature-btn-primary px-4 py-2" style="width: auto;" data-bs-toggle="modal" data-bs-target="#modalAddProduct">
                        <i class="bi bi-plus-circle me-1"></i> Thêm dụng cụ ngay
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($products as $prod):
                $rawImg = trim((string)($prod['image_url'] ?? ''));
                if ($rawImg === '' || str_starts_with($rawImg, 'bi-')) {
                    $img = $defaultImg;
                } elseif (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) {
                    $img = $rawImg;
                } else {
                    $img = $baseUrl . '/' . ltrim($rawImg, '/');
                }
                $prodLink = trim((string)($prod['link'] ?? ''));

                // Quyền quản lý: Người chia sẻ hoặc Admin
                $toolUserId = (int)($prod['user_id'] ?? 0);
                $isOwner = $isLoggedIn && ($toolUserId === (int)$currentUserId);
                $canManage = $isLoggedIn && ($isAdmin || $isOwner);

                // Thông tin người chia sẻ
                $sharerName = !empty($prod['author_username']) ? $prod['author_username'] : 'Ban quản trị';
                $sharerAvatar = !empty($prod['author_avatar']) ? ($baseUrl . '/' . ltrim($prod['author_avatar'], '/')) : ($baseUrl . '/assets/img/celtic-logo.png');
                $sharerIsAdmin = (!empty($prod['author_role']) && $prod['author_role'] === 'admin');
            ?>
                <div class="col">
                    <div class="nature-card h-100 d-flex flex-column overflow-hidden p-0 position-relative" style="border-radius: 28px; transition: transform 0.25s ease, box-shadow 0.25s ease;">
                        
                        <!-- Ảnh sản phẩm -->
                        <div class="position-relative overflow-hidden" style="height: 220px; background: rgba(0,0,0,0.05);">
                            <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>"
                                 class="w-100 h-100 object-fit-cover"
                                 loading="lazy"
                                 style="transition: transform 0.4s ease;">

                            <!-- Badge Chuyên mục -->
                            <?php if (!empty($prod['category_name'])): ?>
                                <span class="badge rounded-pill position-absolute top-0 start-0 m-3 px-3 py-2 text-bg-dark bg-opacity-75 border border-secondary border-opacity-25" style="backdrop-filter: blur(8px); z-index: 2;">
                                    <i class="bi bi-tag-fill me-1 text-warning"></i><?= htmlspecialchars($prod['category_name'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>

                            <!-- Nút thao tác Sửa / Xóa cho Người chia sẻ & Admin -->
                            <?php if ($canManage): ?>
                                <div class="position-absolute top-0 end-0 m-3 d-flex gap-1" style="z-index: 3;">
                                    <button type="button" 
                                            class="btn btn-sm btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center btn-edit-tool" 
                                            style="width: 34px; height: 34px; background: rgba(255,255,255,0.92); backdrop-filter: blur(8px); border: 1px solid rgba(0,0,0,0.1);"
                                            title="Chỉnh sửa dụng cụ"
                                            data-product="<?= htmlspecialchars(json_encode($prod, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-pencil-fill text-dark" style="font-size: 13px;"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger rounded-circle shadow-sm d-flex align-items-center justify-content-center btn-delete-tool" 
                                            style="width: 34px; height: 34px; background: rgba(220, 53, 69, 0.92); backdrop-filter: blur(8px); border: none;"
                                            title="Xóa dụng cụ"
                                            data-id="<?= (int)$prod['id'] ?>"
                                            data-name="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-trash-fill text-white" style="font-size: 13px;"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Thân card -->
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <h2 class="h5 fw-semibold mb-2" style="color: var(--color-ink); line-height: 1.4;">
                                <?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>

                            <!-- Người chia sẻ -->
                            <div class="d-flex align-items-center gap-2 mb-3 text-muted" style="font-size: 12.5px;">
                                <img src="<?= htmlspecialchars($sharerAvatar, ENT_QUOTES, 'UTF-8') ?>" 
                                     alt="<?= htmlspecialchars($sharerName, ENT_QUOTES, 'UTF-8') ?>" 
                                     class="rounded-circle border" 
                                     style="width: 22px; height: 22px; object-fit: cover;">
                                <span class="text-truncate">
                                    Chia sẻ bởi <strong class="text-dark"><?= htmlspecialchars($sharerName, ENT_QUOTES, 'UTF-8') ?></strong>
                                </span>
                                <?php if ($sharerIsAdmin): ?>
                                    <span class="badge text-bg-success rounded-pill px-2 py-0" style="font-size: 10px;">BQT</span>
                                <?php endif; ?>
                            </div>

                            <p class="small text-muted mb-4 flex-grow-1" style="line-height: 1.6;">
                                <?= htmlspecialchars($prod['description'] ?? 'Dụng cụ hỗ trợ cho các chuyến dã ngoại, cắm trại và khám phá thiên nhiên an toàn.', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <!-- Chân card: Giá tham khảo & Nút mua -->
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto" style="border-color: rgba(0, 0, 0, 0.08) !important;">
                                <div>
                                    <span class="d-block text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Giá tham khảo</span>
                                    <span class="fs-5 fw-bold" style="color: #2e7d32;">
                                        <?= number_format((float)($prod['price'] ?? 0), 0, ',', '.') ?> <small class="fw-normal" style="font-size: 13px;">đ</small>
                                    </span>
                                </div>

                                <div>
                                    <?php if ($prodLink !== ''): 
                                        $linkLower = mb_strtolower($prodLink);
                                        $btnName = 'Xem nơi mua';
                                        $btnIcon = 'bi-box-arrow-up-right';
                                        $btnStyle = 'background: #2e7d32; color: #fff; border-color: #2e7d32;';

                                        if (strpos($linkLower, 'shopee') !== false || strpos($linkLower, 'shp.ee') !== false) {
                                            $btnName = 'Mua tại Shopee';
                                            $btnIcon = 'bi-bag-fill';
                                            $btnStyle = 'background: #ee4d2d; color: #fff; border-color: #ee4d2d;';
                                        } elseif (strpos($linkLower, 'lazada') !== false || strpos($linkLower, 'laz.vn') !== false) {
                                            $btnName = 'Mua tại Lazada';
                                            $btnIcon = 'bi-cart4';
                                            $btnStyle = 'background: #0f146d; color: #fff; border-color: #0f146d;';
                                        } elseif (strpos($linkLower, 'tiki.vn') !== false || strpos($linkLower, 'ti.ki') !== false) {
                                            $btnName = 'Mua tại Tiki';
                                            $btnIcon = 'bi-shop';
                                            $btnStyle = 'background: #1a94ff; color: #fff; border-color: #1a94ff;';
                                        } elseif (strpos($linkLower, 'tiktok.com') !== false) {
                                            $btnName = 'TikTok Shop';
                                            $btnIcon = 'bi-tiktok';
                                            $btnStyle = 'background: #111111; color: #fff; border-color: #111111;';
                                        }
                                    ?>
                                        <a href="<?= htmlspecialchars($prodLink, ENT_QUOTES, 'UTF-8') ?>" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="btn btn-sm rounded-pill py-1 px-3 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm text-nowrap"
                                           style="<?= $btnStyle ?> font-size: 12px;"
                                           title="Xem nơi mua hoặc đặt mua trực tiếp">
                                            <i class="bi <?= $btnIcon ?>"></i>
                                            <span><?= $btnName ?></span>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge rounded-pill text-bg-light border text-muted px-3 py-2" style="font-size: 12px;">
                                            <i class="bi bi-check2-circle me-1 text-success"></i>Đồ dùng khuyên mang
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Thanh phân trang dụng cụ (Hiển thị 6 dụng cụ / trang) -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Điều hướng phân trang dụng cụ" class="mt-5 mb-4">
                <ul class="pagination forum-pagination justify-content-center flex-wrap">
                    <!-- Nút trang trước -->
                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products?<?= $catQuery ?>page=<?= max(1, $currentPage - 1) ?>" 
                           tabindex="<?= ($currentPage <= 1) ? '-1' : '0' ?>"
                           aria-disabled="<?= ($currentPage <= 1) ? 'true' : 'false' ?>">
                            <i class="bi bi-chevron-left me-1"></i> Trước
                        </a>
                    </li>

                    <!-- Danh sách số trang -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                            <a class="page-link rounded-pill" 
                               href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products?<?= $catQuery ?>page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Nút trang sau -->
                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products?<?= $catQuery ?>page=<?= min($totalPages, $currentPage + 1) ?>" 
                           tabindex="<?= ($currentPage >= $totalPages) ? '-1' : '0' ?>"
                           aria-disabled="<?= ($currentPage >= $totalPages) ? 'true' : 'false' ?>">
                            Sau <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                </ul>

                <div class="text-center text-white-50 small mt-2">
                    Trang <?= $currentPage ?> / <?= $totalPages ?> (Hiển thị 6 dụng cụ / trang • Tổng số <?= $totalProducts ?> dụng cụ)
                </div>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ========================================== -->
<!-- MODAL 1: THÊM DỤNG CỤ MỚI VÀO KHO CỘNG ĐỒNG -->
<!-- ========================================== -->
<div class="modal fade" id="modalAddProduct" tabindex="-1" aria-labelledby="modalAddProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden; background: #ffffff;">
            <div class="modal-header border-0 pb-0 pt-4 px-4" style="background: transparent;">
                <div>
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(155, 189, 85, 0.2); color: #3c6114; border: 1px solid rgba(155, 189, 85, 0.45); font-weight: 600;">
                        <i class="bi bi-plus-circle me-1"></i>Chia sẻ dụng cụ
                    </span>
                    <h5 class="modal-title fw-bold fs-4" id="modalAddProductLabel" style="color: #23261f;">Thêm dụng cụ vào kho cộng đồng</h5>
                    <p class="small mb-0 text-muted">Chia sẻ các trang bị hữu ích bạn từng sử dụng để mọi người cùng tham khảo.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            
            <form action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products/store" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="modal-body p-4" style="background: #ffffff;">
                    <div class="row g-3">
                        <!-- Tên dụng cụ -->
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Tên dụng cụ / Thiết bị <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control rounded-3" 
                                   placeholder="VD: Lều cắm trại 2 lớp Naturehike Cloud Up 2" 
                                   required>
                        </div>

                        <!-- Chuyên mục -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Phân mục dã ngoại</label>
                            <select name="category_id" class="form-select rounded-3">
                                <option value="">-- Chọn phân mục phù hợp --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Giá tham khảo -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Giá tham khảo (VNĐ)</label>
                            <div class="input-group">
                                <input type="number" 
                                       name="price" 
                                       class="form-control" 
                                       placeholder="VD: 450000" 
                                       min="0" 
                                       step="1000">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>

                        <!-- Link nơi mua -->
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Link nơi mua (Shopee, Lazada, web shop...)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="text" 
                                       name="link" 
                                       class="form-control" 
                                       placeholder="https://shopee.vn/... hoặc link web shop">
                            </div>
                        </div>

                        <!-- Tải ảnh lên -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Tải ảnh lên (từ máy)</label>
                            <input type="file" 
                                   name="image" 
                                   class="form-control rounded-3" 
                                   accept="image/*">
                        </div>

                        <!-- Hoặc dán URL ảnh -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Hoặc dán URL ảnh trực tiếp</label>
                            <input type="url" 
                                   name="image_url" 
                                   class="form-control rounded-3" 
                                   placeholder="https://domain.com/anh-dung-cu.jpg">
                        </div>

                        <!-- Kinh nghiệm / Đánh giá ngắn -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Kinh nghiệm / Đánh giá ngắn khi sử dụng</label>
                            <textarea name="description" 
                                      rows="3" 
                                      class="form-control rounded-3" 
                                      placeholder="VD: Chống mưa tốt, khung nhôm nhẹ chịu gió mạnh, phù hợp cho 2 người đi săn mây..."></textarea>
                        </div>

                        <!-- Checkbox chia sẻ -->
                        <div class="col-12">
                            <div class="form-check form-switch p-0 d-flex align-items-center gap-2">
                                <input class="form-check-input ms-0" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="add_is_shared" 
                                       name="is_shared" 
                                       value="1" 
                                       checked>
                                <label class="form-check-label small fw-medium" for="add_is_shared" style="color: #23261f;">
                                    Chia sẻ công khai vào kho dụng cụ cộng đồng cho mọi người cùng xem và dùng lại
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4" style="background: transparent;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto;">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Chia sẻ dụng cụ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: CHỈNH SỬA DỤNG CỤ (AUTHOR & ADMIN) -->
<!-- ========================================== -->
<div class="modal fade" id="modalEditProduct" tabindex="-1" aria-labelledby="modalEditProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden; background: #ffffff;">
            <div class="modal-header border-0 pb-0 pt-4 px-4" style="background: transparent;">
                <div>
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(56, 189, 248, 0.15); color: #0284c7; border: 1px solid rgba(56, 189, 248, 0.35); font-weight: 600;">
                        <i class="bi bi-pencil-square me-1"></i>Cập nhật thông tin
                    </span>
                    <h5 class="modal-title fw-bold fs-4" id="modalEditProductLabel" style="color: #23261f;">Chỉnh sửa dụng cụ dã ngoại</h5>
                    <p class="small mb-0 text-muted">Cập nhật hình ảnh, giá bán, link mua sắm và nhận xét kinh nghiệm sử dụng.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            
            <form action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products/update" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="edit_product_id" value="">
                
                <div class="modal-body p-4" style="background: #ffffff;">
                    <div class="row g-3">
                        <!-- Tên dụng cụ -->
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Tên dụng cụ / Thiết bị <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="edit_product_name"
                                   class="form-control rounded-3" 
                                   required>
                        </div>

                        <!-- Chuyên mục -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Phân mục dã ngoại</label>
                            <select name="category_id" id="edit_product_category" class="form-select rounded-3">
                                <option value="">-- Chọn phân mục phù hợp --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Giá tham khảo -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Giá tham khảo (VNĐ)</label>
                            <div class="input-group">
                                <input type="number" 
                                       name="price" 
                                       id="edit_product_price"
                                       class="form-control" 
                                       min="0" 
                                       step="1000">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>

                        <!-- Link nơi mua -->
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Link nơi mua (Shopee, Lazada, web shop...)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="text" 
                                       name="link" 
                                       id="edit_product_link"
                                       class="form-control" 
                                       placeholder="https://shopee.vn/...">
                            </div>
                        </div>

                        <!-- Tải ảnh mới lên -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Tải ảnh mới (từ máy)</label>
                            <input type="file" 
                                   name="image" 
                                   class="form-control rounded-3" 
                                   accept="image/*">
                            <div class="form-text text-muted" style="font-size: 11px;">Để trống nếu muốn giữ nguyên ảnh hiện tại.</div>
                        </div>

                        <!-- Hoặc đổi URL ảnh -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Hoặc cập nhật URL ảnh</label>
                            <input type="url" 
                                   name="image_url" 
                                   id="edit_product_image_url"
                                   class="form-control rounded-3" 
                                   placeholder="https://domain.com/anh-dung-cu.jpg">
                        </div>

                        <!-- Kinh nghiệm / Đánh giá ngắn -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small" style="color: #23261f;">Kinh nghiệm / Đánh giá ngắn khi sử dụng</label>
                            <textarea name="description" 
                                      id="edit_product_desc" 
                                      rows="3" 
                                      class="form-control rounded-3"></textarea>
                        </div>

                        <!-- Checkbox chia sẻ -->
                        <div class="col-12">
                            <div class="form-check form-switch p-0 d-flex align-items-center gap-2">
                                <input class="form-check-input ms-0" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="edit_product_is_shared" 
                                       name="is_shared" 
                                       value="1">
                                <label class="form-check-label small fw-medium" for="edit_product_is_shared" style="color: #23261f;">
                                    Chia sẻ công khai vào kho dụng cụ cộng đồng
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4" style="background: transparent;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto;">
                        <i class="bi bi-check-circle me-1"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: XÁC NHẬN XÓA DỤNG CỤ              -->
<!-- ========================================== -->
<div class="modal fade" id="modalDeleteProduct" tabindex="-1" aria-labelledby="modalDeleteProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden; background: #ffffff;">
            <div class="modal-header border-0 pb-0 pt-4 px-4" style="background: transparent;">
                <div>
                    <span class="badge rounded-pill px-3 py-1 mb-2" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.35); font-weight: 600;">
                        <i class="bi bi-exclamation-triangle me-1"></i>Xác nhận xóa
                    </span>
                    <h5 class="modal-title fw-bold fs-5" id="modalDeleteProductLabel" style="color: #23261f;">Xóa dụng cụ khỏi kho?</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            
            <form action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products/delete" method="POST">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="delete_product_id" value="">

                <div class="modal-body p-4" style="color: #4a4f44; background: #ffffff;">
                    Bạn có chắc chắn muốn xóa dụng cụ <strong id="delete_product_name_display" style="color: #23261f;"></strong> khỏi kho dụng cụ cộng đồng không? Hành động này sẽ gỡ dụng cụ này khỏi danh mục chung.
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4" style="background: transparent;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-trash-fill me-1"></i> Xác nhận xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript điều khiển modal Sửa & Xóa (Không dùng arrow function =>) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModalEl = document.getElementById('modalEditProduct');
    var deleteModalEl = document.getElementById('modalDeleteProduct');
    var editModal = (editModalEl && typeof bootstrap !== 'undefined') ? new bootstrap.Modal(editModalEl) : null;
    var deleteModal = (deleteModalEl && typeof bootstrap !== 'undefined') ? new bootstrap.Modal(deleteModalEl) : null;

    // Bắt sự kiện click nút Sửa
    var editButtons = document.querySelectorAll('.btn-edit-tool');
    for (var i = 0; i < editButtons.length; i++) {
        editButtons[i].addEventListener('click', function (e) {
            e.preventDefault();
            var rawData = this.getAttribute('data-product');
            if (!rawData) {
                return;
            }
            try {
                var prod = JSON.parse(rawData);
                var idInput = document.getElementById('edit_product_id');
                var nameInput = document.getElementById('edit_product_name');
                var catSelect = document.getElementById('edit_product_category');
                var priceInput = document.getElementById('edit_product_price');
                var linkInput = document.getElementById('edit_product_link');
                var descInput = document.getElementById('edit_product_desc');
                var imgUrlInput = document.getElementById('edit_product_image_url');
                var isSharedCheck = document.getElementById('edit_product_is_shared');

                if (idInput) idInput.value = prod.id || '';
                if (nameInput) nameInput.value = prod.name || '';
                if (catSelect) catSelect.value = prod.category_id || '';
                if (priceInput) priceInput.value = prod.price || '';
                if (linkInput) linkInput.value = prod.link || '';
                if (descInput) descInput.value = prod.description || '';
                if (imgUrlInput) {
                    var curImg = prod.image_url || '';
                    if (curImg.indexOf('http://') === 0 || curImg.indexOf('https://') === 0) {
                        imgUrlInput.value = curImg;
                    } else {
                        imgUrlInput.value = '';
                    }
                }
                if (isSharedCheck) {
                    isSharedCheck.checked = (parseInt(prod.is_shared, 10) === 1 || prod.is_shared === true);
                }

                if (editModal) {
                    editModal.show();
                }
            } catch (err) {
                console.error('Lỗi phân tích dữ liệu dụng cụ:', err);
            }
        });
    }

    // Bắt sự kiện click nút Xóa
    var deleteButtons = document.querySelectorAll('.btn-delete-tool');
    for (var j = 0; j < deleteButtons.length; j++) {
        deleteButtons[j].addEventListener('click', function (e) {
            e.preventDefault();
            var pId = this.getAttribute('data-id');
            var pName = this.getAttribute('data-name');
            var idInput = document.getElementById('delete_product_id');
            var nameDisplay = document.getElementById('delete_product_name_display');

            if (idInput) idInput.value = pId || '';
            if (nameDisplay) nameDisplay.textContent = pName ? ('"' + pName + '"') : 'dụng cụ này';

            if (deleteModal) {
                deleteModal.show();
            }
        });
    }
});
</script>
