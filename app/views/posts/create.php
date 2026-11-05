<?php
$title = 'Viết bài chia sẻ';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 800px;">
    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="nature-btn-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h1 class="page-title">Viết bài chia sẻ</h1>
        <p class="page-subtitle">Kể lại hành trình, kinh nghiệm cắm trại hay mẹo sinh tồn của bạn.</p>
    </div>

    <div class="nature-card" style="border-radius: 36px;">
        <?php if (!empty($errors['form'])): ?>
            <div class="nature-alert nature-alert-error mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

            <!-- Post Title -->
            <div class="mb-3">
                <label for="title" class="nature-label">Tiêu đề bài viết</label>
                <input class="nature-input" 
                       id="title" 
                       name="title" 
                       type="text" 
                       required 
                       placeholder="Ví dụ: Lịch trình 2 ngày 1 đêm săn mây Tà Xùa"
                       value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($errors['title'])): ?>
                    <div class="text-danger small mt-1"><?= htmlspecialchars($errors['title'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-3">
                <!-- Category Select -->
                <div class="col-md-6">
                    <label for="category_id" class="nature-label">Chuyên mục</label>
                    <select class="nature-input" id="category_id" name="category_id" required>
                        <option value="">-- Chọn chuyên mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= (($old['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['category_id'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['category_id'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Location with Map Picker -->
                <div class="col-md-6">
                    <label for="location" class="nature-label">Địa điểm</label>
                    <div class="loc-input-wrap">
                        <input class="nature-input loc-input"
                               id="location" 
                               name="location" 
                               type="text" 
                               placeholder="Ví dụ: Tà Xùa, Sơn La"
                               value="<?= htmlspecialchars($old['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="button"
                                class="loc-map-btn"
                                onclick="MapPicker.open()"
                                title="Chọn vị trí trên bản đồ">
                            <i class="bi bi-map-fill"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <!-- Lat & Lng -->
                <div class="col-md-6">
                    <label for="lat" class="nature-label">Vĩ độ (Lat) <span class="text-muted small fw-normal">(để xem thời tiết &amp; bản đồ)</span></label>
                    <input class="nature-input" 
                           id="lat" 
                           name="lat" 
                           type="number" 
                           step="any"
                           placeholder="Ví dụ: 21.28"
                           value="<?= htmlspecialchars((string)($old['lat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['lat'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['lat'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="lng" class="nature-label">Kinh độ (Lng)</label>
                    <input class="nature-input" 
                           id="lng" 
                           name="lng" 
                           type="number" 
                           step="any"
                           placeholder="Ví dụ: 104.31"
                           value="<?= htmlspecialchars((string)($old['lng'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['lng'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['lng'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bài viết dạng "Chuyến đi" (Trip Info: Cung đường, thời gian, chi phí, độ khó, vùng miền, đồ cần mang) -->
            <div class="card border-0 rounded-4 mb-4" style="background: rgba(155, 189, 85, 0.08); border: 1px solid rgba(155, 189, 85, 0.3) !important;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; background: var(--color-moss, #9bbd55); color: #1c2219;">
                            <i class="bi bi-signpost-2-fill"></i>
                        </span>
                        <div>
                            <h3 class="h6 fw-bold mb-0 text-dark">Thông tin hành trình "Chuyến đi" (Trip Details)</h3>
                            <span class="text-muted small" style="font-size: 11.5px;">Cung cấp các thông số thực tế để giúp cộng đồng dễ dàng lọc và chuẩn bị chuyến đi</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Loại hình -->
                        <div class="col-12 col-md-4">
                            <label for="activity_type" class="nature-label">Loại hình dã ngoại</label>
                            <select class="nature-input" id="activity_type" name="activity_type">
                                <option value="">-- Chọn loại hình --</option>
                                <option value="camping" <?= (($old['activity_type'] ?? '') === 'camping') ? 'selected' : '' ?>>Cắm trại (Camping)</option>
                                <option value="trekking" <?= (($old['activity_type'] ?? '') === 'trekking') ? 'selected' : '' ?>>Đi bộ đường dài (Trekking)</option>
                                <option value="leo_nui" <?= (($old['activity_type'] ?? '') === 'leo_nui') ? 'selected' : '' ?>>Leo núi (Hiking / Mountain)</option>
                                <option value="phuot" <?= (($old['activity_type'] ?? '') === 'phuot') ? 'selected' : '' ?>>Phượt đường dài</option>
                                <option value="da_ngoai" <?= (($old['activity_type'] ?? '') === 'da_ngoai') ? 'selected' : '' ?>>Dã ngoại ngoài trời</option>
                            </select>
                        </div>

                        <!-- Mức độ khó -->
                        <div class="col-12 col-md-4">
                            <label for="difficulty" class="nature-label">Mức độ khó</label>
                            <select class="nature-input" id="difficulty" name="difficulty">
                                <option value="easy" <?= (($old['difficulty'] ?? '') === 'easy') ? 'selected' : '' ?>>Dễ (Người mới, gia đình)</option>
                                <option value="medium" <?= (($old['difficulty'] ?? 'medium') === 'medium') ? 'selected' : '' ?>>Trung bình (Cần thể lực cơ bản)</option>
                                <option value="hard" <?= (($old['difficulty'] ?? '') === 'hard') ? 'selected' : '' ?>>Khó (Đồi dốc, dài ngày)</option>
                                <option value="extreme" <?= (($old['difficulty'] ?? '') === 'extreme') ? 'selected' : '' ?>>Thử thách cao (Chuyên nghiệp)</option>
                            </select>
                        </div>

                        <!-- Vùng miền / Địa điểm -->
                        <div class="col-12 col-md-4">
                            <label for="region" class="nature-label">Khu vực / Địa điểm</label>
                            <?php
                            $standardRegions = ['Tây Bắc', 'Đông Bắc', 'Bắc Bộ', 'Bắc Trung Bộ', 'Nam Trung Bộ', 'Tây Nguyên', 'Đông Nam Bộ', 'Tây Nam Bộ', 'Đông Nam Á', 'Châu Á', 'Châu Âu', 'Châu Mỹ', 'Châu Úc & Khác'];
                            $oldRegion = $old['region'] ?? '';
                            $isCustomReg = (!empty($oldRegion) && !in_array($oldRegion, $standardRegions, true)) || ($oldRegion === 'Quốc tế khác');
                            $customRegVal = $isCustomReg ? ($oldRegion === 'Quốc tế khác' ? ($old['custom_region'] ?? '') : $oldRegion) : '';
                            ?>
                            <select class="nature-input" id="region" name="region">
                                <option value="">-- Chọn khu vực --</option>
                                <optgroup label="Việt Nam">
                                    <option value="Tây Bắc" <?= ($oldRegion === 'Tây Bắc') ? 'selected' : '' ?>>Tây Bắc</option>
                                    <option value="Đông Bắc" <?= ($oldRegion === 'Đông Bắc') ? 'selected' : '' ?>>Đông Bắc</option>
                                    <option value="Bắc Bộ" <?= ($oldRegion === 'Bắc Bộ') ? 'selected' : '' ?>>Miền Bắc</option>
                                    <option value="Bắc Trung Bộ" <?= ($oldRegion === 'Bắc Trung Bộ') ? 'selected' : '' ?>>Bắc Trung Bộ</option>
                                    <option value="Nam Trung Bộ" <?= ($oldRegion === 'Nam Trung Bộ') ? 'selected' : '' ?>>Nam Trung Bộ</option>
                                    <option value="Tây Nguyên" <?= ($oldRegion === 'Tây Nguyên') ? 'selected' : '' ?>>Tây Nguyên</option>
                                    <option value="Đông Nam Bộ" <?= ($oldRegion === 'Đông Nam Bộ') ? 'selected' : '' ?>>Đông Nam Bộ</option>
                                    <option value="Tây Nam Bộ" <?= ($oldRegion === 'Tây Nam Bộ') ? 'selected' : '' ?>>Tây Nam Bộ</option>
                                </optgroup>
                                <optgroup label="Quốc tế (Nước ngoài)">
                                    <option value="Đông Nam Á" <?= ($oldRegion === 'Đông Nam Á') ? 'selected' : '' ?>>Đông Nam Á (Lào, Thái, Indo...)</option>
                                    <option value="Châu Á" <?= ($oldRegion === 'Châu Á') ? 'selected' : '' ?>>Châu Á (Nepal, Nhật, Hàn...)</option>
                                    <option value="Châu Âu" <?= ($oldRegion === 'Châu Âu') ? 'selected' : '' ?>>Châu Âu (Alps, Na Uy, Iceland...)</option>
                                    <option value="Châu Mỹ" <?= ($oldRegion === 'Châu Mỹ') ? 'selected' : '' ?>>Châu Mỹ (Patagonia, Rockies...)</option>
                                    <option value="Châu Úc & Khác" <?= ($oldRegion === 'Châu Úc & Khác') ? 'selected' : '' ?>>Châu Úc / Châu Phi</option>
                                    <option value="Quốc tế khác" <?= $isCustomReg ? 'selected' : '' ?>>Quốc tế khác (Tự nhập địa điểm)</option>
                                </optgroup>
                            </select>
                            <div id="custom_region_wrapper" class="mt-2" style="display: <?= $isCustomReg ? 'block' : 'none' ?>;">
                                <input type="text" 
                                       class="nature-input" 
                                       id="custom_region" 
                                       name="custom_region" 
                                       placeholder="Nhập tên quốc gia / địa điểm (VD: Nepal, Nhật Bản, Thụy Sĩ...)" 
                                       value="<?= htmlspecialchars($customRegVal, ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Cung đường -->
                        <div class="col-12 col-md-5">
                            <label for="route" class="nature-label">Cung đường di chuyển</label>
                            <input type="text" class="nature-input" id="route" name="route" 
                                   placeholder="Ví dụ: Hà Nội - Đèo Đá Trắng - Tà Xùa (220km)" 
                                   value="<?= htmlspecialchars($old['route'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <!-- Thời gian -->
                        <div class="col-12 col-md-3">
                            <label for="trip_duration" class="nature-label">Thời gian</label>
                            <input type="text" class="nature-input" id="trip_duration" name="trip_duration" 
                                   placeholder="Ví dụ: 2 ngày 1 đêm" 
                                   value="<?= htmlspecialchars($old['trip_duration'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <!-- Chi phí dự tính -->
                        <div class="col-12 col-md-4">
                            <label for="estimated_cost" class="nature-label">Chi phí dự tính</label>
                            <input type="text" class="nature-input" id="estimated_cost" name="estimated_cost" 
                                   placeholder="Ví dụ: 1.200.000 đ/người" 
                                   value="<?= htmlspecialchars($old['estimated_cost'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <!-- Đồ cần mang -->
                    <div>
                        <label for="gear_needed" class="nature-label">Đồ cần mang (Checklist trang bị mang theo)</label>
                        <textarea class="nature-textarea" id="gear_needed" name="gear_needed" rows="2" 
                                  placeholder="Ví dụ: Lều 2 lớp chống mưa, túi ngủ nhiệt, giày trekking bám tốt, áo gió, đèn pin đội đầu, bình nước 2L, bộ sơ cứu y tế..."><?= htmlspecialchars($old['gear_needed'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Multiple Images Upload -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="nature-label mb-0"><i class="bi bi-images me-1"></i>Hình ảnh bài viết (hỗ trợ chọn hoặc tải lên nhiều ảnh)</label>
                    <span class="text-muted small">JPG, PNG, WEBP &lt; 2MB/ảnh</span>
                </div>
                
                <div class="upload-dropzone p-3 text-center rounded-4 border border-2 border-dashed" 
                     id="imageDropzone" 
                     style="background: rgba(0,0,0,0.02); border-color: rgba(0,0,0,0.15) !important; cursor: pointer; transition: all 0.2s ease;">
                    <i class="bi bi-cloud-arrow-up fs-2 text-muted d-block mb-1"></i>
                    <div class="fw-medium text-dark small mb-1">Nhấn để chọn một hoặc nhiều ảnh, hoặc kéo thả vào đây</div>
                    <div class="text-muted" style="font-size: 11px;">Bạn có thể chọn cùng lúc nhiều ảnh để tạo album chuyển động (slideshow) khi xem bài viết</div>
                    <input class="d-none" 
                           id="imagesInput" 
                           name="images[]" 
                           type="file" 
                           multiple
                           accept="image/png, image/jpeg, image/webp">
                </div>

                <!-- Preview container -->
                <div class="row g-2 mt-2 d-none" id="imagePreviewContainer"></div>
                                <div class="small text-muted mt-1 d-none" id="imagePreviewCount"></div>
                <div class="mt-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-muted rounded-start-pill ps-3">
                            <i class="bi bi-cloud-check-fill text-primary me-1"></i>Link ảnh Cloud (ImgBB / Cloudinary)
                        </span>
                        <input type="text"
                               name="cloud_image_url"
                               id="cloud_image_url"
                               class="form-control rounded-end-pill"
                               placeholder="Dán link ảnh từ ImgBB / Cloudinary (hoặc bấm tải ảnh ở khung trên để tự động upload)...">
                    </div>
                </div>

                <?php if (!empty($errors['image']) || !empty($errors['images'])): ?>
                    <div class="text-danger small mt-1"><?= htmlspecialchars($errors['image'] ?? $errors['images'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <!-- Post Content -->
            <div class="mb-4">
                <label for="content" class="nature-label">Nội dung bài viết</label>
                <textarea class="nature-textarea" 
                          id="content" 
                          name="content" 
                          rows="8" 
                          required 
                          placeholder="Chia sẻ chi tiết lịch trình, cung đường, đồ chuẩn bị, chi phí, hoặc các lưu ý an toàn..."><?= htmlspecialchars($old['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (!empty($errors['content'])): ?>
                    <div class="text-danger small mt-1"><?= htmlspecialchars($errors['content'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <!-- Tools & Gear Selector (Dụng cụ & Trang thiết bị chuyến đi) -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <div>
                        <label class="nature-label mb-0">
                            <i class="bi bi-tools me-1 text-success"></i>Dụng cụ &amp; Thiết bị sử dụng trong chuyến đi (Tools &amp; Gear)
                        </label>
                        <div class="text-muted" style="font-size: 12px;">
                            Chia sẻ những công cụ hữu dụng bạn đã sử dụng kèm link mua (Shopee, Lazada, Tiki, web shop...) và giá để người xem tham khảo &amp; mua sắm.
                        </div>
                    </div>
                    <span class="badge rounded-pill text-bg-light border px-3 py-1 text-muted" style="font-size: 11.5px;">
                        Tổng cộng: <strong id="selectedToolsCount" class="text-success">0</strong> dụng cụ
                    </span>
                </div>

                <!-- KHỐI 1: TỰ CHIA SẺ CÔNG CỤ BẠN DÙNG (KÈM LINK MUA, GIÁ & ẢNH) -->
                <div class="rounded-4 p-3 mb-3 border bg-white shadow-sm" style="border-color: rgba(46, 125, 50, 0.25) !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1.5" style="font-size: 13px; font-weight: 600;">
                                <i class="bi bi-bag-check-fill me-1.5"></i>Dụng cụ bạn sử dụng
                            </span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-sm" onclick="addCustomToolRow()" style="font-size: 12.5px; font-weight: 500;">
                            <i class="bi bi-plus-circle-fill"></i> Thêm công cụ bạn dùng
                        </button>
                    </div>

                    <!-- Dynamic container cho các dòng công cụ tự nhập -->
                    <div id="customToolsContainer" class="d-flex flex-column gap-3 mt-3"></div>

                    <!-- Thông báo khi chưa có công cụ nào được thêm -->
                    <div id="emptyToolsNotice" class="text-center py-3 px-2 text-muted small mt-2" style="background: rgba(0,0,0,0.015); border-radius: 12px; border: 1px dashed rgba(0,0,0,0.12);">
                        <i class="bi bi-bag-heart text-success fs-3 d-block mb-1"></i>
                        <span>Bạn chưa thêm công cụ cá nhân nào. Hãy bấm <strong>"Thêm công cụ bạn dùng"</strong> ở trên để chia sẻ bếp, lều, balo, đèn pin... kèm link mua Shopee, Lazada!</span>
                    </div>
                </div>

                <!-- KHỐI 2: HOẶC CHỌN THÊM TỪ KHO DỤNG CỤ CÓ SẴN CỦA HỆ THỐNG (COLLAPSIBLE) -->
                <?php if (!empty($products)): ?>
                    <style>
                        .tool-card-label { overflow: hidden; max-width: 100%; }
                        .tool-title-marquee { position: relative; width: 100%; max-width: 100%; overflow: hidden; white-space: nowrap; line-height: 1.35; }
                        .tool-title-marquee.has-overflow {
                            mask-image: linear-gradient(to right, #000 0%, #000 calc(100% - 16px), transparent 100%);
                            -webkit-mask-image: linear-gradient(to right, #000 0%, #000 calc(100% - 16px), transparent 100%);
                        }
                        .tool-title-inner { display: inline-block; white-space: nowrap; }
                        .tool-title-marquee.has-overflow .tool-title-inner {
                            will-change: transform;
                            animation: toolTextMarquee var(--marquee-duration, 8s) ease-in-out infinite;
                        }
                        .tool-item label:hover .tool-title-inner,
                        .tool-item label:focus-within .tool-title-inner {
                            animation-play-state: paused;
                        }
                        @keyframes toolTextMarquee {
                            0%, 20% { transform: translateX(0); }
                            68%, 82% { transform: translateX(var(--marquee-distance, -80px)); }
                            94%, 100% { transform: translateX(0); }
                        }
                    </style>
                    <div class="accordion border-0" id="accordionExistingTools">
                        <div class="accordion-item rounded-4 border overflow-hidden" style="border-color: rgba(0,0,0,0.1) !important;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2.5 px-3 bg-light text-dark fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExistingTools" aria-expanded="false" style="font-size: 13px;">
                                    <i class="bi bi-collection me-2 text-muted"></i> Hoặc chọn thêm từ kho dụng cụ cộng đồng sẵn có (<?= count($products) ?> sản phẩm)
                                </button>
                            </h2>
                            <div id="collapseExistingTools" class="accordion-collapse collapse" data-bs-parent="#accordionExistingTools">
                                <div class="accordion-body p-3" style="background: rgba(0,0,0,0.015);">
                                    <!-- Filter search box -->
                                    <div class="mb-3">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                                            <input type="text" 
                                                   id="toolSearchInput" 
                                                   class="form-control form-control-sm border-start-0 rounded-end-pill" 
                                                   placeholder="Tìm nhanh dụng cụ (ví dụ: lều, balo, bếp, gậy leo núi, đèn pin...)" 
                                                   oninput="filterToolsList(this.value)">
                                        </div>
                                    </div>

                                    <!-- Checkbox grid -->
                                    <div class="row row-cols-1 row-cols-md-2 g-2" id="toolsCheckboxGrid" style="max-height: 260px; overflow-y: auto; padding-right: 4px;">
                                        <?php foreach ($products as $prod):
                                            $pId = (int)$prod['id'];
                                            $isChecked = in_array($pId, $selectedProductIds ?? [], true);
                                            $rawToolImg = trim((string)($prod['image_url'] ?? ''));
                                            if ($rawToolImg === '' || str_starts_with($rawToolImg, 'bi-')) {
                                                $toolImg = $baseUrl . '/assets/img/tent-forest.png';
                                            } elseif (str_starts_with($rawToolImg, 'http://') || str_starts_with($rawToolImg, 'https://')) {
                                                $toolImg = $rawToolImg;
                                            } else {
                                                $toolImg = $baseUrl . '/' . ltrim($rawToolImg, '/');
                                            }
                                        ?>
                                            <div class="col tool-item" data-name="<?= htmlspecialchars(mb_strtolower($prod['name']), ENT_QUOTES, 'UTF-8') ?>">
                                                <label class="d-flex align-items-center gap-2 p-2 rounded-3 border bg-white h-100 position-relative tool-card-label" 
                                                       style="cursor: pointer; transition: all 0.2s ease; border-color: rgba(0,0,0,0.08) !important; overflow: hidden;">
                                                    <input type="checkbox" 
                                                           name="product_ids[]" 
                                                           value="<?= $pId ?>" 
                                                           class="form-check-input mt-0 tool-checkbox flex-shrink-0" 
                                                           style="cursor: pointer;"
                                                           <?= $isChecked ? 'checked' : '' ?>
                                                           onchange="updateSelectedToolsCounter()">
                                                    <img src="<?= htmlspecialchars($toolImg, ENT_QUOTES, 'UTF-8') ?>" 
                                                         alt="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>" 
                                                         class="rounded-2 object-fit-cover flex-shrink-0" 
                                                         style="width: 44px; height: 44px; border: 1px solid rgba(0,0,0,0.06);">
                                                    <div class="min-w-0 flex-grow-1 overflow-hidden" style="font-size: 13px;">
                                                        <div class="tool-title-marquee fw-semibold text-dark" title="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <span class="tool-title-inner"><?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 11px;">
                                                            <span><?= htmlspecialchars($prod['category_name'] ?? 'Dụng cụ', ENT_QUOTES, 'UTF-8') ?></span>
                                                            <span class="text-success fw-bold"><?= number_format((float)($prod['price'] ?? 0), 0, ',', '.') ?> đ</span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>


            <!-- Submit -->
            <button type="submit" class="nature-btn-primary py-3">
                <i class="bi bi-send-fill me-2"></i> Xuất bản bài viết
            </button>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════
     MAP PICKER MODAL (OpenStreetMap + Nominatim)
════════════════════════════════════════════════ -->
<div id="mapPickerModal" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
    <div style="background:#1a1a1a; border-radius:24px; width:min(640px,96vw); max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,0.6);">
        <!-- Modal Header -->
        <div style="padding:1rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:10px;">
            <i class="bi bi-map-fill" style="color:var(--color-moss, #7aab3a); font-size:1.1rem;"></i>
            <strong style="color:#fff; flex:1;">Chọn địa điểm</strong>
            <button onclick="MapPicker.close()" style="background:none;border:none;color:rgba(255,255,255,0.6);font-size:1.2rem;cursor:pointer;padding:0;line-height:1;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Search bar -->
        <div style="padding:0.85rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.07); display:flex; gap:8px;">
            <input type="text" id="mapSearchInput"
                   placeholder="Nhập tên địa điểm, ví dụ: Đỉnh Fansipan..."
                   style="flex:1; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); border-radius:10px;
                          color:#fff; padding:0.5rem 0.85rem; font-size:0.92rem; outline:none;"
                   onkeydown="if(event.key==='Enter'){event.preventDefault();MapPicker.search();}">
            <button onclick="MapPicker.search()"
                    style="background:var(--color-moss,#7aab3a); border:none; border-radius:10px; color:#fff;
                           padding:0.5rem 1.1rem; cursor:pointer; font-size:0.88rem; white-space:nowrap;">
                <i class="bi bi-search me-1"></i> Tìm
            </button>
        </div>

        <!-- Search results -->
        <div id="mapSearchResults" style="max-height:220px; overflow-y:auto; border-bottom:1px solid rgba(255,255,255,0.07);"></div>

        <!-- Selected info -->
        <div id="mapSelectedInfo" style="padding:0.65rem 1.25rem; background:rgba(122,171,58,0.08); display:none; align-items:center; gap:8px;">
            <i class="bi bi-geo-alt-fill" style="color:var(--color-moss,#7aab3a);"></i>
            <span id="mapSelectedText" style="color:#ccc; font-size:0.86rem; flex:1;"></span>
        </div>

        <!-- Footer -->
        <div style="padding:0.85rem 1.25rem; display:flex; justify-content:flex-end; gap:8px;">
            <button onclick="MapPicker.close()"
                    style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); border-radius:10px;
                           color:#ccc; padding:0.5rem 1.1rem; cursor:pointer;">
                Hủy
            </button>
            <button id="mapConfirmBtn" onclick="MapPicker.confirm()" disabled
                    style="background:var(--color-moss,#7aab3a); border:none; border-radius:10px; color:#fff;
                           padding:0.5rem 1.25rem; cursor:pointer; opacity:0.5;">
                <i class="bi bi-check-lg me-1"></i> Xác nhận
            </button>
        </div>
    </div>
</div>

<style>
/* ── Location input with inline icon ── */
.loc-input-wrap {
    position: relative;
    display: block;
}
.loc-input-wrap .loc-input {
    padding-right: 44px !important; /* room for the icon */
}
.loc-map-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--color-moss, #7aab3a);
    font-size: 1.05rem;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 8px;
    line-height: 1;
    transition: color 0.18s, background 0.18s, transform 0.15s;
    z-index: 5;
}
.loc-map-btn:hover {
    color: #4d7a1f;
    background: rgba(122,171,58,0.12);
    transform: translateY(-50%) scale(1.15);
}

/* ── Map result items ── */
.map-result-item {
    padding: 0.65rem 1.25rem;
    cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: background 0.15s;
    color: #ccc;
    font-size: 0.86rem;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}
.map-result-item:hover { background: rgba(255,255,255,0.07); color: #fff; }
.map-result-item.selected { background: rgba(122,171,58,0.15); }
.map-result-item i { color: var(--color-moss,#7aab3a); margin-top:2px; flex-shrink:0; }
.map-result-item strong { display:block; font-size:0.9rem; color:#fff; }
.map-result-item span { font-size: 0.78rem; color: #999; }
.map-spinner { padding:1rem; text-align:center; color:rgba(255,255,255,0.45); font-size:0.85rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Image dropzone (existing logic) ──────────────────────────
    const dropzone = document.getElementById('imageDropzone');
    const input    = document.getElementById('imagesInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const countText = document.getElementById('imagePreviewCount');

    if (dropzone && input) {
        dropzone.addEventListener('click', function() {
            input.click();
        });

        ['dragenter', 'dragover'].forEach(function(ev) {
            dropzone.addEventListener(ev, function(e) {
                e.preventDefault(); e.stopPropagation();
                dropzone.style.background   = 'rgba(155, 189, 85, 0.15)';
                dropzone.style.borderColor  = 'var(--color-moss, #9bbd55)';
            });
        });
        ['dragleave', 'drop'].forEach(function(ev) {
            dropzone.addEventListener(ev, function(e) {
                e.preventDefault(); e.stopPropagation();
                dropzone.style.background  = 'rgba(0,0,0,0.02)';
                dropzone.style.borderColor = 'rgba(0,0,0,0.15)';
            });
        });
        dropzone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                input.files = dt.files;
                renderPreviews(dt.files);
            }
        });
        input.addEventListener('change', function() {
            renderPreviews(input.files);
        });

        function renderPreviews(files) {
            if (!files || files.length === 0) {
                previewContainer.classList.add('d-none');
                countText.classList.add('d-none');
                previewContainer.innerHTML = '';
                return;
            }
            previewContainer.innerHTML = '';
            previewContainer.classList.remove('d-none');
            countText.classList.remove('d-none');
            countText.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i>Đã chọn <strong>${files.length}</strong> hình ảnh cho bài viết này.`;
            Array.from(files).forEach(function(file, index) {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-4 col-sm-3 col-md-2';
                    col.innerHTML = `
                        <div class="position-relative rounded-3 overflow-hidden border shadow-sm" style="height: 75px; background: #000;">
                            <img src="${e.target.result}" alt="Preview ${index+1}" style="width:100%;height:100%;object-fit:cover;">
                            <span class="badge bg-dark bg-opacity-75 position-absolute bottom-0 start-0 m-1" style="font-size:9px;">#${index+1}</span>
                        </div>`;
                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        }
    }
});

// ══════════════════════════════════════════════════════════════
// MAP PICKER — OpenStreetMap Nominatim (no API key needed)
// ══════════════════════════════════════════════════════════════
const MapPicker = (function() {
    let selected = null;

    function modal() { return document.getElementById('mapPickerModal'); }
    function searchInput() { return document.getElementById('mapSearchInput'); }
    function resultsBox() { return document.getElementById('mapSearchResults'); }
    function selectedInfo() { return document.getElementById('mapSelectedInfo'); }
    function selectedText() { return document.getElementById('mapSelectedText'); }
    function confirmBtn() { return document.getElementById('mapConfirmBtn'); }

    function open() {
        selected = null;
        if (resultsBox()) resultsBox().innerHTML = '';
        if (selectedInfo()) selectedInfo().style.display = 'none';
        if (confirmBtn()) { confirmBtn().disabled = true; confirmBtn().style.opacity = '0.5'; }
        if (searchInput()) {
            searchInput().value = document.getElementById('location')?.value || '';
        }
        modal().style.display = 'flex';
        setTimeout(function() {
            if (searchInput()) searchInput().focus();
        }, 80);
    }

    function close() {
        modal().style.display = 'none';
    }

    async function search() {
        const q = searchInput()?.value?.trim();
        if (!q) return;

        resultsBox().innerHTML = '<div class="map-spinner"><i class="bi bi-hourglass-split me-1"></i>Đang tìm kiếm...</div>';
        if (selectedInfo()) selectedInfo().style.display = 'none';
        if (confirmBtn()) { confirmBtn().disabled = true; confirmBtn().style.opacity = '0.5'; }
        selected = null;

        try {
            // Nominatim public API — no key required, add email for fair-use policy
            const url = new URL('https://nominatim.openstreetmap.org/search');
            url.searchParams.set('format', 'json');
            url.searchParams.set('q', q);
            url.searchParams.set('limit', '8');
            url.searchParams.set('addressdetails', '1');
            url.searchParams.set('accept-language', 'vi,en');

            const res = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                signal: AbortSignal.timeout(8000)
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            if (!data || data.length === 0) {
                resultsBox().innerHTML = '<div class="map-spinner"><i class="bi bi-search me-1"></i>Không tìm thấy địa điểm. Thử từ khóa khác hoặc thêm tên quốc gia.</div>';
                return;
            }

            resultsBox().innerHTML = '';
            data.forEach(function(item) {
                const div = document.createElement('div');
                div.className = 'map-result-item';
                const shortName = item.display_name.split(',')[0].trim();
                const address   = item.display_name;
                div.innerHTML = `<i class="bi bi-geo-alt-fill"></i><div><strong>${shortName}</strong><span>${address}</span></div>`;
                div.addEventListener('click', function() {
                    selectResult(item, div);
                });
                resultsBox().appendChild(div);
            });
        } catch (err) {
            console.error('Nominatim error:', err);
            if (err.name === 'TimeoutError') {
                resultsBox().innerHTML = '<div class="map-spinner" style="color:#f99;">Yêu cầu quá thời gian. Kiểm tra kết nối mạng và thử lại.</div>';
            } else {
                resultsBox().innerHTML = `<div class="map-spinner" style="color:#f99;">
                    <i class="bi bi-wifi-off me-1"></i>Không thể kết nối đến dịch vụ bản đồ.<br>
                    <small class="d-block mt-1 opacity-75">Bạn có thể nhập tọa độ thủ công vào ô Vĩ độ / Kinh độ bên dưới.</small>
                </div>`;
            }
        }
    }

    function selectResult(item, el) {
        selected = {
            lat: parseFloat(item.lat),
            lng: parseFloat(item.lon),
            short_name: item.display_name.split(',').slice(0, 2).join(',').trim()
        };

        // Highlight selected row
        document.querySelectorAll('.map-result-item').forEach(function(e) {
            e.classList.remove('selected');
        });
        if (el) el.classList.add('selected');

        if (selectedInfo()) selectedInfo().style.display = 'flex';
        if (selectedText()) selectedText().textContent =
            `${selected.short_name}  ·  ${selected.lat.toFixed(5)}, ${selected.lng.toFixed(5)}`;
        if (confirmBtn()) { confirmBtn().disabled = false; confirmBtn().style.opacity = '1'; }
    }

    function confirm() {
        if (!selected) return;
        const locInput = document.getElementById('location');
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        if (locInput) locInput.value = selected.short_name;
        if (latInput) latInput.value = selected.lat.toFixed(7);
        if (lngInput) lngInput.value = selected.lng.toFixed(7);
        close();
    }

    // Close on backdrop click
    document.getElementById('mapPickerModal')?.addEventListener('click', function(e) {
        if (e.target === this) close();
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal()?.style.display === 'flex') close();
    });

    return { open, close, search, confirm };
})();

// Xử lý bộ chọn Dụng cụ (Tools Selection) & Chạy chữ Marquee
function updateSelectedToolsCounter() {
    var checkedBoxes = document.querySelectorAll('.tool-checkbox:checked');
    var customCards = document.querySelectorAll('#customToolsContainer .custom-tool-card');
    var badge = document.getElementById('selectedToolsCount');
    if (badge) {
        badge.textContent = checkedBoxes.length + customCards.length;
    }
}

// Thêm dòng công cụ tự nhập (kèm link mua Shopee, Lazada, giá, ảnh)
function addCustomToolRow(initialData) {
    var data = initialData || {};
    var container = document.getElementById('customToolsContainer');
    var emptyNotice = document.getElementById('emptyToolsNotice');
    if (!container) return;

    if (emptyNotice) {
        emptyNotice.style.display = 'none';
    }

    var card = document.createElement('div');
    card.className = 'custom-tool-card p-3 rounded-4 border position-relative';
    card.style.background = '#fafbfa';
    card.style.borderColor = 'rgba(0,0,0,0.1)';
    card.style.boxShadow = '0 2px 8px rgba(0,0,0,0.03)';

    var nameVal = data.name ? escapeHtml(data.name) : '';
    var priceVal = (data.price !== undefined && data.price !== null) ? escapeHtml(data.price) : '';
    var linkVal = data.link ? escapeHtml(data.link) : '';
    var descVal = data.description ? escapeHtml(data.description) : '';
    var imgVal = data.image_url ? escapeHtml(data.image_url) : '';

    var html = '' +
        '<button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-2 p-1 remove-tool-btn" title="Xóa công cụ này" onclick="removeCustomToolRow(this)" style="font-size: 15px; line-height: 1;">' +
            '<i class="bi bi-trash3-fill"></i>' +
        '</button>' +
        '<div class="row g-2">' +
            '<div class="col-12 col-md-7">' +
                '<label class="form-label small fw-semibold text-dark mb-1">Tên dụng cụ / Thiết bị <span class="text-danger">*</span></label>' +
                '<input type="text" name="custom_tool_name[]" value="' + nameVal + '" class="form-control form-control-sm rounded-3" placeholder="VD: Bếp ga dã ngoại Fire-Maple FMS-105" required>' +
            '</div>' +
            '<div class="col-12 col-md-5">' +
                '<label class="form-label small fw-semibold text-dark mb-1">Giá tham khảo (VNĐ)</label>' +
                '<div class="input-group input-group-sm">' +
                    '<input type="number" name="custom_tool_price[]" value="' + priceVal + '" class="form-control form-control-sm rounded-start-3" placeholder="VD: 350000" min="0" step="1000">' +
                    '<span class="input-group-text bg-light text-muted rounded-end-3">đ</span>' +
                '</div>' +
            '</div>' +
            '<div class="col-12">' +
                '<label class="form-label small fw-semibold text-dark mb-1">' +
                    '<i class="bi bi-link-45deg text-success me-1"></i>Link nơi mua <span class="text-muted fw-normal">(Shopee, Lazada, Tiki, TikTok Shop, website shop...)</span>' +
                '</label>' +
                '<div class="input-group input-group-sm">' +
                    '<span class="input-group-text bg-white border-end-0 text-success rounded-start-3"><i class="bi bi-bag-check"></i></span>' +
                    '<input type="url" name="custom_tool_link[]" value="' + linkVal + '" class="form-control form-control-sm border-start-0 rounded-end-3" placeholder="https://shopee.vn/... hoặc https://lazada.vn/... hoặc link web shop">' +
                '</div>' +
            '</div>' +
            '<div class="col-12 col-md-6">' +
                '<label class="form-label small fw-semibold text-dark mb-1">Tải ảnh lên (từ máy)</label>' +
                '<input type="file" name="custom_tool_file[]" class="form-control form-control-sm rounded-3" accept="image/*">' +
            '</div>' +
            '<div class="col-12 col-md-6">' +
                '<label class="form-label small fw-semibold text-dark mb-1">Hoặc dán URL ảnh</label>' +
                '<input type="url" name="custom_tool_image_url[]" value="' + imgVal + '" class="form-control form-control-sm rounded-3" placeholder="https://domain.com/anh-dung-cu.jpg">' +
            '</div>' +
            '<div class="col-12">' +
                '<label class="form-label small fw-semibold text-dark mb-1">Kinh nghiệm / Đánh giá ngắn khi sử dụng</label>' +
                '<input type="text" name="custom_tool_desc[]" value="' + descVal + '" class="form-control form-control-sm rounded-3" placeholder="VD: Lửa rất mạnh, cản gió tốt, đun sôi 1L nước trong 3 phút, gập gọn trong lòng bàn tay.">' +
            '</div>' +
            '<div class="col-12 mt-2 pt-2 border-top" style="border-color: rgba(0,0,0,0.06) !important;">' +
                '<div class="form-check d-flex align-items-center gap-2 m-0">' +
                    '<input class="form-check-input mt-0" type="checkbox" ' + (data.is_shared === 0 ? '' : 'checked') + ' style="cursor: pointer; width: 1.15em; height: 1.15em;" onchange="this.nextElementSibling.value = this.checked ? \'1\' : \'0\'">' +
                    '<input type="hidden" name="custom_tool_is_shared[]" value="' + (data.is_shared === 0 ? '0' : '1') + '">' +
                    '<label class="form-check-label small text-dark fw-medium" style="cursor: pointer; user-select: none;">' +
                        '<i class="bi bi-share text-success me-1"></i>Chia sẻ dụng cụ này vào kho dụng cụ cộng đồng ' +
                        '<span class="text-muted fw-normal" style="font-size: 11.5px;">(để những người đăng sau có thể chọn nhanh)</span>' +
                    '</label>' +
                '</div>' +
            '</div>' +
        '</div>';

    card.innerHTML = html;
    container.appendChild(card);
    updateSelectedToolsCounter();
}

function removeCustomToolRow(btn) {
    var card = btn.closest('.custom-tool-card');
    if (card) {
        card.remove();
    }
    var container = document.getElementById('customToolsContainer');
    var emptyNotice = document.getElementById('emptyToolsNotice');
    if (container && emptyNotice) {
        var remaining = container.querySelectorAll('.custom-tool-card');
        if (remaining.length === 0) {
            emptyNotice.style.display = '';
        }
    }
    updateSelectedToolsCounter();
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function initToolMarquees() {
    var marquees = document.querySelectorAll('.tool-title-marquee');
    if (!marquees.length) return;

    marquees.forEach(function(marquee) {
        var inner = marquee.querySelector('.tool-title-inner');
        if (!inner) return;

        marquee.classList.remove('has-overflow');
        var overflow = inner.scrollWidth - marquee.clientWidth;
        if (overflow > 2) {
            marquee.classList.add('has-overflow');
            var distance = overflow + 10;
            var duration = Math.min(14, Math.max(6, Math.round(distance / 22) + 3));
            inner.style.setProperty('--marquee-distance', '-' + distance + 'px');
            inner.style.setProperty('--marquee-duration', duration + 's');
        } else {
            marquee.classList.remove('has-overflow');
            inner.style.removeProperty('--marquee-distance');
            inner.style.removeProperty('--marquee-duration');
        }
    });
}

function filterToolsList(keyword) {
    var query = (keyword || '').toLowerCase().trim();
    var items = document.querySelectorAll('#toolsCheckboxGrid .tool-item');
    items.forEach(function(item) {
        var name = item.getAttribute('data-name') || '';
        if (query === '' || name.indexOf(query) !== -1) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
    setTimeout(initToolMarquees, 50);
}

document.addEventListener('DOMContentLoaded', function() {
    updateSelectedToolsCounter();
    initToolMarquees();
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(initToolMarquees);
    }

    var regionSelect = document.getElementById('region');
    var customRegionWrapper = document.getElementById('custom_region_wrapper');
    var customRegionInput = document.getElementById('custom_region');
    if (regionSelect && customRegionWrapper && customRegionInput) {
        regionSelect.addEventListener('change', function () {
            if (this.value === 'Quốc tế khác') {
                customRegionWrapper.style.display = 'block';
                customRegionInput.focus();
            } else {
                customRegionWrapper.style.display = 'none';
            }
        });
    }
});

window.addEventListener('load', initToolMarquees);
window.addEventListener('resize', function() {
    clearTimeout(window._marqueeResizeTimer);
    window._marqueeResizeTimer = setTimeout(initToolMarquees, 150);
});
</script>

