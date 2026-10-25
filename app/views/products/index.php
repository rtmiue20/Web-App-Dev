<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$defaultImg = $baseUrl . '/assets/img/tent-forest.png';
?>
<div class="container py-5 mt-5">
    <!-- Header trang Thiết bị (FR09 - Vương) -->
    <div class="row align-items-center justify-content-between mb-4 g-3">
        <div class="col-12 col-md-8">
            <span class="badge rounded-pill text-bg-success mb-2 px-3 py-2">
                <i class="bi bi-compass me-1"></i>FR09: Gợi ý trang thiết bị
            </span>
            <h1 class="display-6 fw-normal text-white mb-1">Dụng cụ &amp; Thiết bị Dã ngoại</h1>
            <p class="text-white-50 mb-0">Lựa chọn trang bị phù hợp theo từng chuyên mục cho hành trình khám phá thiên nhiên của bạn.</p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
            <span class="badge rounded-pill text-bg-dark border border-secondary px-3 py-2">
                <i class="bi bi-box-seam me-1"></i> Hiện có: <strong><?= count($products ?? []) ?></strong> thiết bị
            </span>
        </div>
    </div>

    <!-- Bộ lọc theo chuyên mục bằng Bootstrap Nav Pills (FR09) -->
    <ul class="nav nav-pills flex-wrap gap-2 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
        <li class="nav-item">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products"
               class="btn rounded-pill px-4 <?= empty($selectedCategoryId) ? 'btn-light fw-semibold text-dark' : 'btn-outline-light' ?>">
                <i class="bi bi-grid-fill me-1"></i> Tất cả
            </a>
        </li>
        <?php foreach ($categories as $cat): ?>
            <li class="nav-item">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products?category=<?= (int)$cat['id'] ?>"
                   class="btn rounded-pill px-4 <?= (($selectedCategoryId ?? null) == $cat['id']) ? 'btn-light fw-semibold text-dark' : 'btn-outline-light' ?>">
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Danh sách sản phẩm bằng Bootstrap Grid & Card -->
    <?php if (empty($products)): ?>
        <div class="card border-0 shadow-sm rounded-4 bg-light text-dark text-center py-5 my-3">
            <div class="card-body py-4">
                <i class="bi bi-box-seam display-4 text-secondary d-block mb-3"></i>
                <h2 class="h5 fw-semibold mb-2">Chưa có thiết bị nào trong chuyên mục này</h2>
                <p class="text-muted small mb-3">Hãy thử chọn một chuyên mục khác hoặc xem toàn bộ danh sách thiết bị.</p>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/products" class="btn btn-dark rounded-pill px-4">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Xem tất cả thiết bị
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
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
                <div class="col">
                    <div class="card h-100 border-0 shadow rounded-4 overflow-hidden bg-light text-dark">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                 class="card-img-top object-fit-cover"
                                 style="height: 210px;"
                                 loading="lazy">
                            <?php if (!empty($product['category_name'])): ?>
                                <span class="badge rounded-pill text-bg-dark bg-opacity-75 position-absolute top-0 start-0 m-3 px-3 py-2">
                                    <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body d-flex flex-column p-4">
                            <h2 class="card-title h5 fw-semibold mb-2 text-dark">
                                <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h2>
                            <p class="card-text text-secondary small flex-grow-1 mb-3">
                                <?= htmlspecialchars($product['description'] ?? 'Chưa có mô tả chi tiết cho thiết bị này.', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                                <div>
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Giá tham khảo</span>
                                    <span class="fs-5 fw-bold text-success">
                                        <?= number_format((float)($product['price'] ?? 0), 0, ',', '.') ?> đ
                                    </span>
                                </div>

                                <?php if ($productLink !== ''): ?>
                                    <a href="<?= htmlspecialchars($productLink, ENT_QUOTES, 'UTF-8') ?>"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                        Tham khảo <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3 py-2">
                                        Gợi ý dã ngoại
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
