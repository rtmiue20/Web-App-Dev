<?php
$title = 'Chỉnh sửa bài viết';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

// Collect current images for thumbnail previews
$editImages = [];
if (!empty($post['images']) && is_array($post['images'])) {
    foreach ($post['images'] as $img) {
        $url = $img['image_url'] ?? '';
        if (!empty($url)) {
            $editImages[] = [
                'id' => (int)($img['id'] ?? 0),
                'url' => str_starts_with($url, 'http') ? $url : $baseUrl . '/' . ltrim($url, '/')
            ];
        }
    }
}
if (empty($editImages) && !empty($post['image_url'])) {
    $url = $post['image_url'];
    $editImages[] = [
        'id' => 0,
        'url' => str_starts_with($url, 'http') ? $url : $baseUrl . '/' . ltrim($url, '/')
    ];
}
?>
<div class="app-container" style="max-width: 800px;">
    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>" class="nature-btn-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i> Quay lại chi tiết bài viết
        </a>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" style="background: rgba(155, 189, 85, 0.18); color: var(--color-moss); border: 1px solid rgba(155, 189, 85, 0.35);">
                <i class="bi bi-pencil-square me-1"></i>Biên tập nội dung
            </span>
        </div>
        <h1 class="page-title mb-1">Chỉnh sửa bài viết</h1>
        <p class="page-subtitle mb-0">Cập nhật thông tin, hình ảnh hoặc kinh nghiệm dã ngoại của bạn.</p>
    </div>

    <div class="nature-card" style="border-radius: 36px;">
        <?php if (!empty($errors['form'])): ?>
            <div class="nature-alert nature-alert-error mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>/update" enctype="multipart/form-data">
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
                            <?php $currAct = $old['activity_type'] ?? $post['activity_type'] ?? ''; ?>
                            <select class="nature-input" id="activity_type" name="activity_type">
                                <option value="">-- Chọn loại hình --</option>
                                <option value="camping" <?= ($currAct === 'camping') ? 'selected' : '' ?>>Cắm trại (Camping)</option>
                                <option value="trekking" <?= ($currAct === 'trekking') ? 'selected' : '' ?>>Đi bộ đường dài (Trekking)</option>
                                <option value="leo_nui" <?= ($currAct === 'leo_nui') ? 'selected' : '' ?>>Leo núi (Hiking / Mountain)</option>
                                <option value="phuot" <?= ($currAct === 'phuot') ? 'selected' : '' ?>>Phượt đường dài</option>
                                <option value="da_ngoai" <?= ($currAct === 'da_ngoai') ? 'selected' : '' ?>>Dã ngoại ngoài trời</option>
                            </select>
                        </div>

                        <!-- Mức độ khó -->
                        <div class="col-12 col-md-4">
                            <label for="difficulty" class="nature-label">Mức độ khó</label>
                            <?php $currDiff = $old['difficulty'] ?? $post['difficulty'] ?? 'medium'; ?>
                            <select class="nature-input" id="difficulty" name="difficulty">
                                <option value="easy" <?= ($currDiff === 'easy') ? 'selected' : '' ?>>Dễ (Người mới, gia đình)</option>
                                <option value="medium" <?= ($currDiff === 'medium') ? 'selected' : '' ?>>Trung bình (Cần thể lực cơ bản)</option>
                                <option value="hard" <?= ($currDiff === 'hard') ? 'selected' : '' ?>>Khó (Đồi dốc, dài ngày)</option>
                                <option value="extreme" <?= ($currDiff === 'extreme') ? 'selected' : '' ?>>Thử thách cao (Chuyên nghiệp)</option>
                            </select>
                        </div>

                        <!-- Vùng miền / Địa điểm -->
                        <div class="col-12 col-md-4">
                            <label for="region" class="nature-label">Khu vực / Địa điểm</label>
                            <?php 
                            $standardRegions = ['Tây Bắc', 'Đông Bắc', 'Bắc Bộ', 'Bắc Trung Bộ', 'Nam Trung Bộ', 'Tây Nguyên', 'Đông Nam Bộ', 'Tây Nam Bộ', 'Đông Nam Á', 'Châu Á', 'Châu Âu', 'Châu Mỹ', 'Châu Úc & Khác'];
                            $currReg = $old['region'] ?? $post['region'] ?? ''; 
                            $isCustomReg = (!empty($currReg) && !in_array($currReg, $standardRegions, true)) || ($currReg === 'Quốc tế khác');
                            $customRegVal = $isCustomReg ? ($currReg === 'Quốc tế khác' ? ($old['custom_region'] ?? '') : $currReg) : '';
                            ?>
                            <select class="nature-input" id="region" name="region">
                                <option value="">-- Chọn khu vực --</option>
                                <optgroup label="Việt Nam">
                                    <option value="Tây Bắc" <?= ($currReg === 'Tây Bắc') ? 'selected' : '' ?>>Tây Bắc</option>
                                    <option value="Đông Bắc" <?= ($currReg === 'Đông Bắc') ? 'selected' : '' ?>>Đông Bắc</option>
                                    <option value="Bắc Bộ" <?= ($currReg === 'Bắc Bộ') ? 'selected' : '' ?>>Miền Bắc</option>
                                    <option value="Bắc Trung Bộ" <?= ($currReg === 'Bắc Trung Bộ') ? 'selected' : '' ?>>Bắc Trung Bộ</option>
                                    <option value="Nam Trung Bộ" <?= ($currReg === 'Nam Trung Bộ') ? 'selected' : '' ?>>Nam Trung Bộ</option>
                                    <option value="Tây Nguyên" <?= ($currReg === 'Tây Nguyên') ? 'selected' : '' ?>>Tây Nguyên</option>
                                    <option value="Đông Nam Bộ" <?= ($currReg === 'Đông Nam Bộ') ? 'selected' : '' ?>>Đông Nam Bộ</option>
                                    <option value="Tây Nam Bộ" <?= ($currReg === 'Tây Nam Bộ') ? 'selected' : '' ?>>Tây Nam Bộ</option>
                                </optgroup>
                                <optgroup label="Quốc tế (Nước ngoài)">
                                    <option value="Đông Nam Á" <?= ($currReg === 'Đông Nam Á') ? 'selected' : '' ?>>Đông Nam Á (Lào, Thái, Indo...)</option>
                                    <option value="Châu Á" <?= ($currReg === 'Châu Á') ? 'selected' : '' ?>>Châu Á (Nepal, Nhật, Hàn...)</option>
                                    <option value="Châu Âu" <?= ($currReg === 'Châu Âu') ? 'selected' : '' ?>>Châu Âu (Alps, Na Uy, Iceland...)</option>
                                    <option value="Châu Mỹ" <?= ($currReg === 'Châu Mỹ') ? 'selected' : '' ?>>Châu Mỹ (Patagonia, Rockies...)</option>
                                    <option value="Châu Úc & Khác" <?= ($currReg === 'Châu Úc & Khác') ? 'selected' : '' ?>>Châu Úc / Châu Phi</option>
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
                                   value="<?= htmlspecialchars((string)($old['route'] ?? $post['route'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <!-- Thời gian -->
                        <div class="col-12 col-md-3">
                            <label for="trip_duration" class="nature-label">Thời gian</label>
                            <input type="text" class="nature-input" id="trip_duration" name="trip_duration" 
                                   placeholder="Ví dụ: 2 ngày 1 đêm" 
                                   value="<?= htmlspecialchars((string)($old['trip_duration'] ?? $post['trip_duration'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <!-- Chi phí dự tính -->
                        <div class="col-12 col-md-4">
                            <label for="estimated_cost" class="nature-label">Chi phí dự tính</label>
                            <input type="text" class="nature-input" id="estimated_cost" name="estimated_cost" 
                                   placeholder="Ví dụ: 1.200.000 đ/người" 
                                   value="<?= htmlspecialchars((string)($old['estimated_cost'] ?? $post['estimated_cost'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <!-- Đồ cần mang -->
                    <div>
                        <label for="gear_needed" class="nature-label">Đồ cần mang (Checklist trang bị mang theo)</label>
                        <textarea class="nature-textarea" id="gear_needed" name="gear_needed" rows="2" 
                                  placeholder="Ví dụ: Lều 2 lớp chống mưa, túi ngủ nhiệt, giày trekking bám tốt, áo gió, đèn pin đội đầu, bình nước 2L, bộ sơ cứu y tế..."><?= htmlspecialchars((string)($old['gear_needed'] ?? $post['gear_needed'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ── Images Section ──────────────────────────────────────── -->
            <div class="mb-4">
                <label class="nature-label">Ảnh bài viết</label>

                <?php if (!empty($editImages)): ?>
                    <!-- Current images thumbnails -->
                    <div class="mb-3">
                        <p class="text-muted small mb-2">
                            <i class="bi bi-images me-1"></i>
                            Ảnh hiện tại (<?= count($editImages) ?> ảnh) — bấm biểu tượng thùng rác để gỡ bỏ ảnh:
                        </p>
                        <div class="d-flex flex-wrap gap-2" id="existingImagesList">
                            <?php foreach ($editImages as $item): ?>
                                <div class="position-relative" id="existImgCard_<?= $item['id'] ?>" style="width: 95px; height: 70px; border-radius: 12px; overflow: hidden; border: 2px solid rgba(255,255,255,0.18); background: #111; transition: all 0.2s ease;">
                                    <img src="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>"
                                         alt="Ảnh bài viết"
                                         style="width: 100%; height: 100%; object-fit: cover; cursor: zoom-in;"
                                         onclick="window.open(this.src,'_blank')">
                                    <?php if ($item['id'] > 0): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 22px; height: 22px; font-size: 11px; z-index: 5;"
                                                onclick="toggleDeleteExistingImg(<?= $item['id'] ?>)"
                                                title="Gỡ ảnh này khỏi bài viết">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="deletedImagesInputs"></div>
                    </div>
                <?php endif; ?>

                <!-- Multi-image dropzone -->
                <div id="editDropzone"
                     style="border: 2px dashed rgba(255,255,255,0.25); border-radius: 16px; padding: 1.5rem 1rem;
                            text-align: center; cursor: pointer; transition: border-color 0.2s, background 0.2s;"
                     onclick="document.getElementById('editImagesInput').click()"
                     ondragover="event.preventDefault(); this.style.borderColor='var(--color-moss)'; this.style.background='rgba(107,142,35,0.08)';"
                     ondragleave="this.style.borderColor='rgba(255,255,255,0.25)'; this.style.background='';"
                     ondrop="handleEditDrop(event)">
                    <i class="bi bi-cloud-arrow-up fs-3 d-block mb-2" style="color: rgba(255,255,255,0.45);"></i>
                    <span class="text-muted small">Kéo &amp; thả ảnh vào đây hoặc <strong>bấm để chọn</strong></span><br>
                    <span class="text-muted" style="font-size: 0.72rem;">JPG, PNG, WEBP — tối đa 2 MB mỗi ảnh</span>
                </div>

                <input type="file"
                       id="editImagesInput"
                       name="images[]"
                       multiple
                       accept="image/jpeg, image/png, image/webp"
                       style="display:none;"
                       onchange="previewEditImages(this.files)">

                <?php if (!empty($errors['images'])): ?>
                    <div class="text-danger small mt-1"><?= htmlspecialchars($errors['images'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <!-- New image previews -->
                <div id="editPreviewGrid" class="d-flex flex-wrap gap-2 mt-3"></div>
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
                        Tổng cộng: <strong id="selectedToolsCount" class="text-success"><?= count($selectedProductIds ?? []) ?></strong> dụng cụ
                    </span>
                </div>

                <!-- KHỐI 1: TỰ CHIA SẺ & CHỈNH SỬA CÔNG CỤ BẠN DÙNG (KÈM LINK MUA, GIÁ & ẢNH) -->
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

                    <!-- Dynamic container cho các dòng công cụ tự nhập hoặc đang có -->
                    <div id="customToolsContainer" class="d-flex flex-column gap-3 mt-3">
                        <?php if (!empty($tools)): ?>
                            <?php foreach ($tools as $t): 
                                $tId = (int)$t['id'];
                                $rawTImg = trim((string)($t['image_url'] ?? ''));
                                if ($rawTImg === '' || str_starts_with($rawTImg, 'bi-')) {
                                    $tImg = $baseUrl . '/assets/img/tent-forest.png';
                                } elseif (str_starts_with($rawTImg, 'http://') || str_starts_with($rawTImg, 'https://')) {
                                    $tImg = $rawTImg;
                                } else {
                                    $tImg = $baseUrl . '/' . ltrim($rawTImg, '/');
                                }
                            ?>
                                <div class="custom-tool-card p-3 rounded-4 border position-relative" style="background: #fafbfa; border-color: rgba(0,0,0,0.1); box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                    <input type="hidden" name="existing_tool_id[]" value="<?= $tId ?>">
                                    <input type="hidden" name="product_ids[]" value="<?= $tId ?>">
                                    <button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-2 p-1 remove-tool-btn" title="Xóa công cụ này khỏi bài viết" onclick="removeCustomToolRow(this)" style="font-size: 15px; line-height: 1;">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                    <div class="row g-2">
                                        <div class="col-12 col-md-7">
                                            <label class="form-label small fw-semibold text-dark mb-1">Tên dụng cụ / Thiết bị <span class="text-danger">*</span></label>
                                            <input type="text" name="existing_tool_name[]" value="<?= htmlspecialchars($t['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm rounded-3" required>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small fw-semibold text-dark mb-1">Giá tham khảo (VNĐ)</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="existing_tool_price[]" value="<?= (int)($t['price'] ?? 0) ?>" class="form-control form-control-sm rounded-start-3" min="0" step="1000">
                                                <span class="input-group-text bg-light text-muted rounded-end-3">đ</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-dark mb-1">
                                                <i class="bi bi-link-45deg text-success me-1"></i>Link nơi mua <span class="text-muted fw-normal">(Shopee, Lazada, Tiki, TikTok Shop, website shop...)</span>
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white border-end-0 text-success rounded-start-3"><i class="bi bi-bag-check"></i></span>
                                                <input type="url" name="existing_tool_link[]" value="<?= htmlspecialchars($t['link'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm border-start-0 rounded-end-3" placeholder="https://shopee.vn/... hoặc https://lazada.vn/... hoặc link shop">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-dark mb-1">
                                                <i class="bi bi-image text-success me-1"></i>Hình ảnh dụng cụ
                                            </label>
                                            <div class="d-flex align-items-center gap-3 p-2 rounded-3 border bg-white" style="border-color: rgba(0,0,0,0.08) !important;">
                                                <?php 
                                                    $rawImg = (string)($t['image_url'] ?? '');
                                                    if ($rawImg === '') {
                                                        $rawImg = 'assets/img/tent-forest.png';
                                                    }
                                                    $previewSrc = (strpos($rawImg, 'http') === 0 || strpos($rawImg, '/') === 0) ? $rawImg : '/' . ltrim($rawImg, '/');
                                                ?>
                                                <div class="position-relative flex-shrink-0">
                                                    <img src="<?= htmlspecialchars($previewSrc, ENT_QUOTES, 'UTF-8') ?>" 
                                                         alt="Ảnh dụng cụ" 
                                                         class="tool-preview-thumb rounded-3 border shadow-sm" 
                                                         style="width: 52px; height: 52px; object-fit: cover; background: #f0f4ec;">
                                                    <span class="badge bg-secondary position-absolute bottom-0 start-50 translate-middle-x status-badge" style="font-size: 9px; padding: 1px 4px; opacity: 0.9;">Hiện tại</span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="text-muted" style="font-size: 11.5px;">Chọn ảnh từ máy nếu muốn thay đổi <span class="fw-normal">(để trống sẽ tự động giữ nguyên ảnh hiện tại)</span></span>
                                                    </div>
                                                    <input type="file" 
                                                           name="existing_tool_file[]" 
                                                           class="form-control form-control-sm rounded-3" 
                                                           accept="image/*"
                                                           onchange="handleToolImageChange(this)">
                                                    <input type="hidden" name="existing_tool_image_url[]" value="<?= htmlspecialchars($t['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold text-dark mb-1">Kinh nghiệm / Đánh giá ngắn khi sử dụng</label>
                                            <input type="text" name="existing_tool_desc[]" value="<?= htmlspecialchars($t['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-12 mt-2 pt-2 border-top" style="border-color: rgba(0,0,0,0.06) !important;">
                                            <div class="form-check d-flex align-items-center gap-2 m-0">
                                                <input class="form-check-input mt-0" 
                                                       type="checkbox" 
                                                       <?= (!isset($t['is_shared']) || (int)$t['is_shared'] === 1) ? 'checked' : '' ?> 
                                                       style="cursor: pointer; width: 1.15em; height: 1.15em;"
                                                       onchange="this.nextElementSibling.value = this.checked ? '1' : '0'">
                                                <input type="hidden" name="existing_tool_is_shared[]" value="<?= (!isset($t['is_shared']) || (int)$t['is_shared'] === 1) ? '1' : '0' ?>">
                                                <label class="form-check-label small text-dark fw-medium" style="cursor: pointer; user-select: none;">
                                                    <i class="bi bi-share text-success me-1"></i>Chia sẻ dụng cụ này vào kho dụng cụ cộng đồng 
                                                    <span class="text-muted fw-normal" style="font-size: 11.5px;">(để những người đăng sau có thể chọn nhanh)</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Thông báo khi chưa có công cụ nào được thêm -->
                    <div id="emptyToolsNotice" class="text-center py-3 px-2 text-muted small mt-2" style="<?= !empty($tools) ? 'display:none;' : '' ?> background: rgba(0,0,0,0.015); border-radius: 12px; border: 1px dashed rgba(0,0,0,0.12);">
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
                                        <div class="admin-search-pill">
                                            <i class="bi bi-search admin-search-icon"></i>
                                            <input type="text" 
                                                   id="toolSearchInput" 
                                                   class="admin-search-input" 
                                                   style="padding-top: 0.45rem !important; padding-bottom: 0.45rem !important; font-size: 13px !important;"
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


            <!-- Submit buttons -->
            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="nature-btn-primary py-3 flex-grow-1">
                    <i class="bi bi-check-circle-fill me-2"></i> Lưu thay đổi
                </button>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>" class="nature-btn-secondary py-3 px-4 text-center">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
window.editDataTransfer = new DataTransfer();

function toggleDeleteExistingImg(imgId) {
    var card = document.getElementById('existImgCard_' + imgId);
    var container = document.getElementById('deletedImagesInputs');
    var existingInput = document.getElementById('delInput_' + imgId);
    if (existingInput) {
        existingInput.remove();
        if (card) {
            card.style.opacity = '1';
            card.style.filter = '';
            card.style.borderColor = 'rgba(255,255,255,0.18)';
        }
    } else {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'delete_image_ids[]';
        inp.value = imgId;
        inp.id = 'delInput_' + imgId;
        container.appendChild(inp);
        if (card) {
            card.style.opacity = '0.35';
            card.style.filter = 'grayscale(100%)';
            card.style.borderColor = '#dc3545';
        }
    }
}

function removeEditFile(idxToRemove) {
    var newDt = new DataTransfer();
    var curFiles = window.editDataTransfer.files;
    for (var i = 0; i < curFiles.length; i++) {
        if (i !== idxToRemove) {
            newDt.items.add(curFiles[i]);
        }
    }
    window.editDataTransfer = newDt;
    var input = document.getElementById('editImagesInput');
    if (input) {
        input.files = window.editDataTransfer.files;
    }
    renderEditPreviews();
}

function renderEditPreviews() {
    var grid = document.getElementById('editPreviewGrid');
    var files = window.editDataTransfer ? window.editDataTransfer.files : [];
    if (!grid) return;
    grid.innerHTML = '';
    if (!files || files.length === 0) {
        var dz = document.getElementById('editDropzone');
        if (dz) {
            var span = dz.querySelector('span');
            if (span) span.textContent = 'Kéo & thả ảnh vào đây hoặc bấm để chọn';
        }
        return;
    }

    Array.from(files).forEach(function(file, i) {
        if (!file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            var wrap = document.createElement('div');
            wrap.className = 'position-relative';
            wrap.style.cssText = 'width:95px;height:70px;border-radius:12px;overflow:hidden;border:2px solid var(--color-moss, #9bbd55);background:#111;';
            wrap.innerHTML = 
                '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">' +
                '<span style="position:absolute;bottom:3px;left:3px;background:rgba(0,0,0,0.7);color:#fff;font-size:9px;font-weight:700;padding:1px 5px;border-radius:6px;">#' + (i + 1) + '</span>' +
                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" ' +
                        'style="width:20px;height:20px;font-size:12px;line-height:1;box-shadow:0 2px 4px rgba(0,0,0,0.4);" ' +
                        'onclick="removeEditFile(' + i + ')" title="Bỏ chọn ảnh này">&times;</button>';
            grid.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });

    var dz = document.getElementById('editDropzone');
    if (dz) {
        var span = dz.querySelector('span');
        if (span) span.textContent = files.length + ' ảnh mới đã chọn — bấm để thêm tiếp';
    }
}

function previewEditImages(newFiles) {
    if (!newFiles || newFiles.length === 0) return;
    Array.from(newFiles).forEach(function(file) {
        if (!file.type.startsWith('image/')) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Ảnh "' + file.name + '" vượt quá 2MB.');
            return;
        }
        var isDup = false;
        for (var i = 0; i < window.editDataTransfer.files.length; i++) {
            var f = window.editDataTransfer.files[i];
            if (f.name === file.name && f.size === file.size && f.lastModified === file.lastModified) {
                isDup = true; break;
            }
        }
        if (!isDup) {
            window.editDataTransfer.items.add(file);
        }
    });
    var input = document.getElementById('editImagesInput');
    if (input) {
        input.files = window.editDataTransfer.files;
    }
    renderEditPreviews();
}

function handleEditDrop(e) {
    e.preventDefault();
    var dz = document.getElementById('editDropzone');
    if (dz) { dz.style.borderColor = 'rgba(255,255,255,0.25)'; dz.style.background = ''; }

    if (!e.dataTransfer || !e.dataTransfer.files || !e.dataTransfer.files.length) return;
    previewEditImages(e.dataTransfer.files);
}
</script>

<!-- ════════════════════════════════════════════════
     MAP PICKER MODAL (OpenStreetMap + Nominatim)
════════════════════════════════════════════════ -->
<div id="mapPickerModal" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
    <div style="background:#1a1a1a; border-radius:24px; width:min(640px,96vw); max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,0.6);">
        <div style="padding:1rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:10px;">
            <i class="bi bi-map-fill" style="color:var(--color-moss, #7aab3a); font-size:1.1rem;"></i>
            <strong style="color:#fff; flex:1;">Chọn địa điểm</strong>
            <button onclick="MapPicker.close()" style="background:none;border:none;color:rgba(255,255,255,0.6);font-size:1.2rem;cursor:pointer;padding:0;line-height:1;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div style="padding:0.85rem 1.25rem 0.5rem; border-bottom:none; display:flex; gap:8px;">
            <input type="text" id="mapSearchInput"
                   placeholder="Nhập tên địa điểm, ví dụ: Đồi Đa Phú, Tà Xùa, Núi Bà Đen..."
                   style="flex:1; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); border-radius:10px;
                          color:#fff; padding:0.5rem 0.85rem; font-size:0.92rem; outline:none;"
                   onkeydown="if(event.key==='Enter'){event.preventDefault();MapPicker.search();}">
            <button onclick="MapPicker.search()"
                    style="background:var(--color-moss,#7aab3a); border:none; border-radius:10px; color:#fff;
                           padding:0.5rem 1.1rem; cursor:pointer; font-size:0.88rem; white-space:nowrap;">
                <i class="bi bi-search me-1"></i> Tìm
            </button>
        </div>
        <div id="mapSearchResults" style="max-height:220px; overflow-y:auto; border-bottom:1px solid rgba(255,255,255,0.07);"></div>
        <div id="mapSelectedInfo" style="padding:0.65rem 1.25rem; background:rgba(122,171,58,0.08); display:none; align-items:center; gap:8px;">
            <i class="bi bi-geo-alt-fill" style="color:var(--color-moss,#7aab3a);"></i>
            <span id="mapSelectedText" style="color:#ccc; font-size:0.86rem; flex:1;"></span>
        </div>
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
/* ── Location input with inline map icon ── */
.loc-input-wrap {
    position: relative;
    display: block;
}
.loc-input-wrap .loc-input {
    padding-right: 44px !important;
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
// ══════════════════════════════════════════════════════════════
// MAP PICKER — Đa tầng Web Service Geocoding & Dã Ngoại Việt Nam
// ══════════════════════════════════════════════════════════════
const VIETNAM_OUTDOOR_SPOTS = [
    { name: "Đồi Đa Phú", detail: "Phường 7, TP. Đà Lạt, Lâm Đồng (Điểm cắm trại, săn mây)", lat: 11.9772, lng: 108.4116 },
    { name: "TP. Đà Lạt", detail: "Lâm Đồng (Thành phố ngàn hoa, trung tâm dã ngoại)", lat: 11.9404, lng: 108.4583 },
    { name: "Hồ Tuyền Lâm", detail: "Phường 4, TP. Đà Lạt, Lâm Đồng (Chèo SUP, cắm trại ven hồ)", lat: 11.8906, lng: 108.4312 },
    { name: "Đỉnh Langbiang", detail: "Huyện Lạc Dương, Lâm Đồng (Đỉnh Bà 2167m, săn mây)", lat: 12.0468, lng: 108.4419 },
    { name: "Đồi Cỏ Hồng Đan Kia", detail: "Huyện Lạc Dương, Lâm Đồng (Cắm trại đồi cỏ hồng)", lat: 12.0125, lng: 108.3842 },
    { name: "Đồi Thiên Phúc Đức", detail: "Phường 7, TP. Đà Lạt, Lâm Đồng (Cây thông cô đơn)", lat: 11.9688, lng: 108.4283 },
    { name: "Sống lưng khủng long Tà Xùa", detail: "Huyện Bắc Yên, Sơn La (Săn mây, trekking đỉnh gió)", lat: 21.2833, lng: 104.3167 },
    { name: "Đỉnh Núi Bà Đen", detail: "TP. Tây Ninh, Tây Ninh (Nóc nhà Nam Bộ 986m)", lat: 11.3736, lng: 106.1744 },
    { name: "Núi Chứa Chan", detail: "Huyện Xuân Lộc, Đồng Nai (Đệ nhị nóc nhà Nam Bộ 837m)", lat: 10.9575, lng: 107.3822 },
    { name: "Cung Trekking Tà Năng - Phan Dũng", detail: "Huyện Đức Trọng, Lâm Đồng & Tuy Phong, Bình Thuận", lat: 11.5333, lng: 108.4667 },
    { name: "Đỉnh Fansipan", detail: "Thị xã Sa Pa, Lào Cai (Nóc nhà Đông Dương 3143m)", lat: 22.3033, lng: 103.7750 },
    { name: "Đỉnh Lảo Thẩn", detail: "Xã Y Tý, Huyện Bát Xát, Lào Cai (Nóc nhà Y Tý săn mây 2860m)", lat: 22.6111, lng: 103.6289 },
    { name: "Đỉnh Ky Quan San (Bạch Mộc Lương Tử)", detail: "Huyện Bát Xát, Lào Cai (3046m)", lat: 22.5025, lng: 103.5886 },
    { name: "Đỉnh Tà Chì Nhù", detail: "Huyện Trạm Tấu, Yên Bái (Hoa chi pâu tím, biển mây 2979m)", lat: 21.5219, lng: 104.3831 },
    { name: "Đèo Mã Pí Lèng & Sông Nho Quế", detail: "Huyện Mèo Vạc & Đồng Văn, Hà Giang", lat: 23.2415, lng: 105.4119 },
    { name: "Hẻm Tu Sản", detail: "Sông Nho Quế, Mèo Vạc, Hà Giang (Chèo thuyền Kayak)", lat: 23.1625, lng: 105.4058 },
    { name: "Đỉnh Pha Luông", detail: "Huyện Mộc Châu, Sơn La (Đỉnh núi hùng vĩ 2000m)", lat: 20.7303, lng: 104.6067 },
    { name: "Đồi thông Bản Áng", detail: "Huyện Mộc Châu, Sơn La (Cắm trại rừng thông)", lat: 20.8306, lng: 104.6436 },
    { name: "Hồ Trị An", detail: "Huyện Vĩnh Cửu, Đồng Nai (Cắm trại ven hồ, câu cá)", lat: 11.1408, lng: 107.0306 },
    { name: "Hồ Dầu Tiếng", detail: "Huyện Dầu Tiếng, Bình Dương & Tây Ninh (Bãi cỏ ven hồ)", lat: 11.3256, lng: 106.3475 },
    { name: "Hồ Đồng Đò", detail: "Xã Minh Trí, Huyện Sóc Sơn, Hà Nội (Cắm trại ven hồ, chèo SUP)", lat: 21.3217, lng: 105.7761 },
    { name: "Núi Hàm Lợn", detail: "Huyện Sóc Sơn, Hà Nội (Nóc nhà Thủ đô, hồ Hàm Lợn)", lat: 21.3094, lng: 105.7889 },
    { name: "Vườn Quốc gia Ba Vì", detail: "Huyện Ba Vì, Hà Nội (Đỉnh Vua, Rừng thông)", lat: 21.0583, lng: 105.3667 },
    { name: "Núi Dinh", detail: "Thị xã Phú Mỹ, Bà Rịa - Vũng Tàu (Trekking suối Tiên, suối Đá)", lat: 10.5367, lng: 107.1267 },
    { name: "Mũi Né - Bàu Trắng", detail: "TP. Phan Thiết, Bình Thuận (Đồi cát trắng, cắm trại biển)", lat: 11.0647, lng: 108.4239 },
    { name: "Đảo Phú Quý", detail: "Huyện đảo Phú Quý, Bình Thuận (Cắm trại Bờ Kè, Vịnh Triều Dương)", lat: 10.5186, lng: 108.9406 },
    { name: "Vườn Quốc gia Cát Bà", detail: "Huyện Cát Hải, Hải Phòng (Đỉnh Ngự Lâm)", lat: 20.8000, lng: 106.9833 },
    { name: "Vườn Quốc gia Cúc Phương", detail: "Huyện Nho Quan, Ninh Bình (Rừng nguyên sinh)", lat: 20.3167, lng: 105.6000 },
    { name: "Vườn Quốc gia Cát Tiên", detail: "Huyện Tân Phú, Đồng Nai (Bàu Sấu, xem thú đêm)", lat: 11.4236, lng: 107.4292 },
    { name: "Cần Giờ", detail: "Huyện Cần Giờ, TP. Hồ Chí Minh (Rừng ngập mặn)", lat: 10.4114, lng: 106.9547 },
    { name: "Thác K50 (Hang Én)", detail: "Khu bảo tồn Kon Chư Răng, Gia Lai", lat: 14.5422, lng: 108.6186 },
    { name: "Thác Bản Giốc", detail: "Huyện Trùng Khánh, Cao Bằng", lat: 22.8542, lng: 106.7236 },
    { name: "Hồ Ba Bể", detail: "Huyện Ba Bể, Bắc Kạn", lat: 22.4167, lng: 105.6167 },
    { name: "Cao nguyên Đồng Cao", detail: "Huyện Sơn Động, Bắc Giang", lat: 21.4333, lng: 106.9167 },
    { name: "Cù Lao Câu", detail: "Huyện Tuy Phong, Bình Thuận", lat: 11.2333, lng: 108.8167 },
    { name: "Bình Lập - Bình Hưng", detail: "TP. Cam Ranh, Khánh Hòa", lat: 11.8333, lng: 109.1667 },
    { name: "Mũi Điện (Đại Lãnh)", detail: "Thị xã Đông Hòa, Phú Yên", lat: 12.8833, lng: 109.4567 }
];

const MapPicker = (function() {
    let selected = null;
    function modal() { return document.getElementById('mapPickerModal'); }
    function searchInput() { return document.getElementById('mapSearchInput'); }
    function resultsBox() { return document.getElementById('mapSearchResults'); }
    function selectedInfo() { return document.getElementById('mapSelectedInfo'); }
    function selectedText() { return document.getElementById('mapSelectedText'); }
    function confirmBtn() { return document.getElementById('mapConfirmBtn'); }

    function cleanTones(s) {
        return (s || '').normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .toLowerCase()
            .trim();
    }

    function renderItems(items) {
        if (!resultsBox()) return;
        resultsBox().innerHTML = '';
        if (!items || items.length === 0) {
            resultsBox().innerHTML = '<div class="map-spinner"><i class="bi bi-search me-1"></i>Không tìm thấy địa điểm. Thử từ khóa khác.</div>';
            return;
        }
        items.forEach(function(item) {
            const div = document.createElement('div');
            div.className = 'map-result-item';
            const badgeHtml = item.badge ? `<span class="badge text-bg-success ms-2" style="font-size:10px;font-weight:600;">${item.badge}</span>` : '';
            div.innerHTML = `<i class="bi bi-geo-alt-fill text-danger"></i><div><strong>${item.short_name}${badgeHtml}</strong><span>${item.display_name}</span></div>`;
            div.addEventListener('click', function() {
                selectResult(item, div);
            });
            resultsBox().appendChild(div);
        });
    }

    function open() {
        selected = null;
        if (selectedInfo()) selectedInfo().style.display = 'none';
        if (confirmBtn()) { confirmBtn().disabled = true; confirmBtn().style.opacity = '0.5'; }
        const curLoc = document.getElementById('location')?.value?.trim() || '';
        if (searchInput()) searchInput().value = curLoc;
        modal().style.display = 'flex';

        if (curLoc) {
            search();
        } else {
            const initialList = VIETNAM_OUTDOOR_SPOTS.slice(0, 8).map(function(s) {
                return {
                    short_name: s.name,
                    display_name: s.name + ', ' + s.detail,
                    lat: s.lat,
                    lon: s.lng,
                    badge: 'Gợi ý dã ngoại'
                };
            });
            renderItems(initialList);
        }

        setTimeout(function() {
            if (searchInput()) {
                searchInput().focus();
                searchInput().select();
            }
        }, 80);
    }

    function close() { modal().style.display = 'none'; }

    function quickSearch(name) {
        if (searchInput()) {
            searchInput().value = name;
            search();
        }
    }

    function useCustomName(name) {
        const locInput = document.getElementById('location');
        if (locInput) locInput.value = name;
        close();
    }

    async function search() {
        const q = searchInput()?.value?.trim();
        if (!q) return;
        resultsBox().innerHTML = '<div class="map-spinner"><i class="bi bi-hourglass-split me-1"></i>Đang tìm kiếm...</div>';
        if (selectedInfo()) selectedInfo().style.display = 'none';
        if (confirmBtn()) { confirmBtn().disabled = true; confirmBtn().style.opacity = '0.5'; }
        selected = null;

        const results = [];
        const seen = new Set();

        function addResult(item) {
            const key = (item.lat.toFixed(3) + ',' + item.lon.toFixed(3));
            if (seen.has(key)) return;
            seen.add(key);
            results.push(item);
        }

        const normQ = cleanTones(q);

        // Tầng 1: Danh mục địa điểm Dã ngoại Việt Nam
        if (Array.isArray(VIETNAM_OUTDOOR_SPOTS)) {
            VIETNAM_OUTDOOR_SPOTS.forEach(function(spot) {
                const normName = cleanTones(spot.name);
                const normDetail = cleanTones(spot.detail);
                if (normName.includes(normQ) || normDetail.includes(normQ) || normQ.includes(normName)) {
                    addResult({
                        short_name: spot.name,
                        display_name: spot.name + ', ' + spot.detail,
                        lat: parseFloat(spot.lat),
                        lon: parseFloat(spot.lng),
                        badge: 'Địa điểm dã ngoại'
                    });
                }
            });
        }

        // Tầng 2: Komoot Photon API (OSM)
        try {
            const photonUrl = 'https://photon.komoot.io/api/?q=' + encodeURIComponent(q) + '&limit=8';
            const res = await fetch(photonUrl, { method: 'GET', signal: AbortSignal.timeout(4500) });
            if (res.ok) {
                const data = await res.json();
                if (data && Array.isArray(data.features)) {
                    data.features.forEach(function(f) {
                        const p = f.properties || {};
                        const name = p.name || p.street || p.city || '';
                        if (!name) return;
                        const parts = [name, p.district, p.county, p.city, p.state, p.country].filter(Boolean);
                        const coords = f.geometry && f.geometry.coordinates;
                        if (coords && coords.length >= 2) {
                            addResult({
                                short_name: name,
                                display_name: parts.join(', '),
                                lat: parseFloat(coords[1]),
                                lon: parseFloat(coords[0])
                            });
                        }
                    });
                }
            }
        } catch (err) {
            console.warn('Photon geocoding warning:', err);
        }

        // Tầng 3: Open-Meteo Geocoding
        if (results.length < 5) {
            try {
                const meteoUrl = 'https://geocoding-api.open-meteo.com/v1/search?name=' + encodeURIComponent(q) + '&count=6&language=vi&format=json';
                const res = await fetch(meteoUrl, { method: 'GET', signal: AbortSignal.timeout(4000) });
                if (res.ok) {
                    const data = await res.json();
                    if (data && Array.isArray(data.results)) {
                        data.results.forEach(function(r) {
                            const parts = [r.name, r.admin2, r.admin1, r.country].filter(Boolean);
                            addResult({
                                short_name: r.name,
                                display_name: parts.join(', '),
                                lat: parseFloat(r.latitude),
                                lon: parseFloat(r.longitude)
                            });
                        });
                    }
                }
            } catch (err) {
                console.warn('Open-Meteo warning:', err);
            }
        }

        // Tầng 4: Nominatim
        if (results.length === 0) {
            try {
                const url = new URL('https://nominatim.openstreetmap.org/search');
                url.searchParams.set('format', 'json');
                url.searchParams.set('q', q);
                url.searchParams.set('limit', '6');
                url.searchParams.set('addressdetails', '1');
                url.searchParams.set('accept-language', 'vi,en');
                const res = await fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    signal: AbortSignal.timeout(3000)
                });
                if (res.ok) {
                    const data = await res.json();
                    if (Array.isArray(data)) {
                        data.forEach(function(item) {
                            addResult({
                                short_name: item.display_name.split(',')[0].trim(),
                                display_name: item.display_name,
                                lat: parseFloat(item.lat),
                                lon: parseFloat(item.lon)
                            });
                        });
                    }
                }
            } catch (err) {
                console.warn('Nominatim error:', err);
            }
        }

        if (results.length === 0) {
            const safeQ = q.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            const safeArg = q.replace(/'/g, "\\'");
            resultsBox().innerHTML = `
                <div class="p-3 text-center">
                    <i class="bi bi-geo-alt fs-3 text-success d-block mb-2"></i>
                    <div style="font-size:0.9rem;color:#fff;">Chưa tìm thấy tọa độ cho <strong>"${safeQ}"</strong>.</div>
                    <div class="small text-white-50 mt-1 mb-3">Bạn có thể tự gán tên này vào bài viết:</div>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5" onclick="MapPicker.useCustomName('${safeArg}')">
                        <i class="bi bi-check2-circle me-1"></i> Sử dụng tên "${safeQ}"
                    </button>
                </div>
            `;
            return;
        }

        renderItems(results);
    }

    function selectResult(item, el) {
        selected = {
            lat: parseFloat(item.lat),
            lng: parseFloat(item.lon),
            short_name: item.short_name || item.display_name.split(',').slice(0, 2).join(',').trim()
        };
        document.querySelectorAll('.map-result-item').forEach(function(e) {
            e.classList.remove('selected');
        });
        if (el) el.classList.add('selected');
        if (selectedInfo()) selectedInfo().style.display = 'flex';
        if (selectedText()) selectedText().textContent = `${selected.short_name}  ·  ${selected.lat.toFixed(5)}, ${selected.lng.toFixed(5)}`;
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

    document.getElementById('mapPickerModal')?.addEventListener('click', function(e) {
        if (e.target === this) close();
    });
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

// Thêm dòng công cụ tự nhập mới (kèm link mua Shopee, Lazada, giá, ảnh)
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
            '<div class="col-12">' +
                '<label class="form-label small fw-semibold text-dark mb-1">' +
                    '<i class="bi bi-image text-success me-1"></i>Hình ảnh dụng cụ <span class="text-muted fw-normal">(tải từ máy hoặc để trống dùng ảnh mặc định)</span>' +
                '</label>' +
                '<div class="d-flex align-items-center gap-3 p-2 rounded-3 border bg-white" style="border-color: rgba(0,0,0,0.08) !important;">' +
                    '<div class="position-relative flex-shrink-0">' +
                        '<img src="' + (imgVal ? (imgVal.indexOf('http') === 0 || imgVal.indexOf('/') === 0 ? imgVal : '/' + imgVal) : '/assets/img/tent-forest.png') + '" ' +
                             'alt="Xem trước" class="tool-preview-thumb rounded-3 border shadow-sm" style="width: 52px; height: 52px; object-fit: cover; background: #f0f4ec;">' +
                        '<span class="badge bg-secondary position-absolute bottom-0 start-50 translate-middle-x status-badge" style="font-size: 9px; padding: 1px 4px; opacity: 0.9;">' + (imgVal ? 'Đã có ảnh' : 'Mặc định') + '</span>' +
                    '</div>' +
                    '<div class="flex-grow-1">' +
                        '<input type="file" name="custom_tool_file[]" class="form-control form-control-sm rounded-3" accept="image/*" onchange="handleToolImageChange(this)">' +
                        '<input type="hidden" name="custom_tool_image_url[]" value="' + imgVal + '">' +
                    '</div>' +
                '</div>' +
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

function handleToolImageChange(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        var card = input.closest('.custom-tool-card') || input.parentElement;
        var thumb = card ? card.querySelector('.tool-preview-thumb') : null;
        var badge = card ? card.querySelector('.status-badge') : null;
        reader.onload = function(e) {
            if (thumb) {
                thumb.src = e.target.result;
            }
            if (badge) {
                badge.className = 'badge bg-success position-absolute bottom-0 start-50 translate-middle-x status-badge';
                badge.textContent = 'Ảnh mới';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
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

