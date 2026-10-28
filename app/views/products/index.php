<?php
$title = 'Dụng cụ & Trang thiết bị dã ngoại';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$defaultImg = $baseUrl . '/assets/img/tent-forest.png';
$totalItems = count($products ?? []);
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
            <p class="page-subtitle mb-0">Các công cụ, trang bị sinh tồn và đồ cắm trại được cộng đồng tin dùng trong các chuyến đi.</p>
        </div>
        <div>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 46px;">
                <i class="bi bi-pencil-fill me-2"></i> Chia sẻ chuyến đi &amp; Dụng cụ
            </a>
        </div>
    </div>

    <!-- Category Filter Bar -->
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
            <p class="text-muted small mb-4">Hãy chọn một danh mục khác hoặc xem toàn bộ danh mục sản phẩm dã ngoại.</p>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" class="nature-btn-secondary px-4 py-2 text-dark border-dark-subtle d-inline-flex" style="width: auto;">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Xem tất cả dụng cụ
            </a>
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
            ?>
                <div class="col">
                    <div class="nature-card h-100 d-flex flex-column overflow-hidden p-0" style="border-radius: 28px; transition: transform 0.25s ease, box-shadow 0.25s ease;">
                        <!-- Ảnh sản phẩm -->
                        <div class="position-relative overflow-hidden" style="height: 220px; background: rgba(0,0,0,0.05);">
                            <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>"
                                 class="w-100 h-100 object-fit-cover"
                                 loading="lazy"
                                 style="transition: transform 0.4s ease;">
                            <?php if (!empty($prod['category_name'])): ?>
                                <span class="badge rounded-pill position-absolute top-0 start-0 m-3 px-3 py-2 text-bg-dark bg-opacity-75 border border-secondary border-opacity-25" style="backdrop-filter: blur(8px);">
                                    <i class="bi bi-tag-fill me-1 text-warning"></i><?= htmlspecialchars($prod['category_name'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Thân card -->
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <h2 class="h5 fw-semibold mb-2" style="color: var(--color-ink); line-height: 1.4;">
                                <?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>

                            <p class="small text-muted mb-4 flex-grow-1" style="line-height: 1.6;">
                                <?= htmlspecialchars($prod['description'] ?? 'Dụng cụ hỗ trợ cho các chuyến dã ngoại, cắm trại và khám phá thiên nhiên an toàn.', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <!-- Chân card: Giá tham khảo & Nút hành động -->
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto" style="border-color: rgba(0, 0, 0, 0.08) !important;">
                                <div>
                                    <span class="d-block text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Giá tham khảo</span>
                                    <span class="fs-5 fw-bold" style="color: #2e7d32;">
                                        <?= number_format((float)($prod['price'] ?? 0), 0, ',', '.') ?> <small class="fw-normal" style="font-size: 13px;">đ</small>
                                    </span>
                                </div>

                                <div>
                                    <?php if ($prodLink !== ''): ?>
                                        <a href="<?= htmlspecialchars($prodLink, ENT_QUOTES, 'UTF-8') ?>" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="nature-btn-secondary py-1 px-3 text-dark border-dark-subtle d-inline-flex align-items-center gap-1"
                                           style="font-size: 12.5px;"
                                           title="Xem nơi mua hoặc thông số kỹ thuật chi tiết">
                                            <span>Xem nơi mua</span>
                                            <i class="bi bi-box-arrow-up-right" style="font-size: 11px;"></i>
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
    <?php endif; ?>
</div>
