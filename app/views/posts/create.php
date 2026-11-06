<?php
$title = 'Tạo bài viết chia sẻ';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$currentUser = Session::get('user');
$userName = is_array($currentUser) ? ($currentUser['username'] ?? 'Bạn') : 'Bạn';
$userAvatar = is_array($currentUser) && !empty($currentUser['avatar_path']) ? upload_url($currentUser['avatar_path']) : null;
$escape = static function($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
?>
<style>
/* ══════════════════════════════════════════════════════════════
   FACEBOOK-STYLE POST COMPOSER STYLES
══════════════════════════════════════════════════════════════ */
.fb-composer-wrapper {
    max-width: 680px;
    margin: 0 auto;
}

.fb-composer-card {
    background: #ffffff;
    border-radius: 28px;
    padding: 1.5rem 1.75rem 1.75rem;
    box-shadow: 0 24px 60px -15px rgba(0, 0, 0, 0.45);
    color: var(--color-ink, #23261f);
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

/* Author Row */
.fb-author-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.07);
}

.fb-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    background: #9bbd55;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.fb-author-info {
    flex-grow: 1;
    min-width: 0;
}

.fb-author-name {
    font-size: 15.5px;
    font-weight: 600;
    color: #1a1a1a;
    line-height: 1.25;
    margin-bottom: 4px;
}

/* Category Pill Selector */
.fb-category-pill-select {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #eef2eb;
    border: 1px solid #d4dfce;
    border-radius: 20px;
    padding: 3px 12px 3px 10px;
    font-size: 12.5px;
    font-weight: 500;
    color: #385a21;
    cursor: pointer;
    outline: none;
    transition: all 0.2s ease;
}

.fb-category-pill-select:focus,
.fb-category-pill-select:hover {
    background: #e3ebd9;
    border-color: #9bbd55;
}

/* Inputs */
.fb-title-input {
    width: 100%;
    border: none;
    border-bottom: 1.5px solid #eef0eb;
    padding: 0.6rem 0 0.85rem;
    font-size: 1.15rem;
    font-weight: 600;
    color: #1c2219;
    outline: none;
    background: transparent;
    transition: border-color 0.2s;
    margin-bottom: 0.85rem;
}

.fb-title-input:focus {
    border-color: #9bbd55;
}

.fb-title-input::placeholder {
    color: #9aa095;
    font-weight: 400;
}

.fb-content-textarea {
    width: 100%;
    border: none;
    padding: 0.5rem 0;
    font-size: 16px;
    line-height: 1.6;
    color: #23261f;
    outline: none;
    background: transparent;
    resize: vertical;
    min-height: 140px;
    font-family: inherit;
}

.fb-content-textarea::placeholder {
    color: #9aa095;
    font-size: 16px;
    font-weight: 300;
}

/* Attached Badges / Chips */
.fb-chips-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 0.75rem;
    margin-bottom: 1rem;
}

.fb-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.18s ease;
    user-select: none;
    border: 1px solid transparent;
}

.fb-chip-image {
    background: #e8f5e9;
    color: #2e7d32;
    border-color: #c8e6c9;
}
.fb-chip-location {
    background: #ffebee;
    color: #c62828;
    border-color: #ffcdd2;
}
.fb-chip-trip {
    background: #e3f2fd;
    color: #1565c0;
    border-color: #bbdefb;
}
.fb-chip-tools {
    background: #fff8e1;
    color: #f57f17;
    border-color: #ffecb3;
}

.fb-chip:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
}

.fb-chip-close {
    background: none;
    border: none;
    padding: 0;
    font-size: 14px;
    line-height: 1;
    margin-left: 2px;
    color: inherit;
    opacity: 0.65;
    cursor: pointer;
}
.fb-chip-close:hover {
    opacity: 1;
}

/* Image Thumbnails Strip */
.fb-thumbs-strip {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 4px 0 10px;
    margin-bottom: 0.85rem;
}
.fb-thumb-item {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

/* "Thêm vào bài viết của bạn" Toolbar Box */
.fb-composer-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-radius: 18px;
    border: 1px solid #e3e7df;
    background: #fbfcf9;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.fb-toolbar-title {
    font-size: 13.5px;
    font-weight: 600;
    color: #2e3328;
}

.fb-tool-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.fb-tool-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #4b5246;
}

.fb-tool-btn:hover {
    background: #ebf1e6;
    transform: scale(1.08);
}

.fb-tool-btn.active {
    background: #e2edd9;
}

/* Submit Button */
.fb-submit-btn {
    width: 100%;
    height: 48px;
    border-radius: 14px;
    font-weight: 600;
    font-size: 15.5px;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 8px 24px -6px rgba(155, 189, 85, 0.5);
}

/* ══════════════════════════════════════════════════════════════
   LIQUID GLASS MODALS FOR POST COMPOSER (Nature Dark Edition)
   ══════════════════════════════════════════════════════════════ */
.modal-content.fb-modal-box {
    background: rgba(14, 20, 11, 0.95) !important;
    backdrop-filter: blur(40px) saturate(220%) !important;
    -webkit-backdrop-filter: blur(40px) saturate(220%) !important;
    border: 1px solid rgba(255, 255, 255, 0.16) !important;
    border-top-color: rgba(255, 255, 255, 0.32) !important;
    border-radius: 28px !important;
    box-shadow: 0 36px 90px -15px rgba(0, 0, 0, 0.8), 0 1px 0 rgba(255, 255, 255, 0.18) inset !important;
    overflow: hidden;
    color: #ffffff !important;
}

.fb-modal-box .modal-header {
    background: rgba(255, 255, 255, 0.03) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    padding: 1.15rem 1.5rem !important;
    position: relative;
}

.fb-modal-box .modal-title {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    letter-spacing: -0.2px;
}

.fb-modal-box .modal-body {
    padding: 1.5rem !important;
    max-height: 75vh;
    overflow-y: auto;
    color: #ffffff !important;
}

.fb-modal-box .modal-footer {
    background: rgba(0, 0, 0, 0.22) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    padding: 0.95rem 1.5rem !important;
}

/* Close button - Crisp white */
.fb-modal-box .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%) !important;
    opacity: 0.8 !important;
    transition: opacity 0.2s ease, transform 0.2s ease !important;
}
.fb-modal-box .btn-close:hover {
    opacity: 1 !important;
    transform: scale(1.1) rotate(90deg) !important;
}

/* Labels inside modal */
.fb-modal-box .nature-label,
.fb-modal-box .form-label {
    color: rgba(255, 255, 255, 0.88) !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    margin-bottom: 0.45rem;
}

/* Inputs and Selects inside modal */
.fb-modal-box .nature-input,
.fb-modal-box .nature-textarea,
.fb-modal-box .form-control,
.fb-modal-box .form-select {
    background: rgba(255, 255, 255, 0.08) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
    border-radius: 14px !important;
    font-size: 14.5px !important;
    transition: all 0.2s ease;
}

.fb-modal-box .nature-input:focus,
.fb-modal-box .nature-textarea:focus,
.fb-modal-box .form-control:focus,
.fb-modal-box .form-select:focus {
    background: rgba(255, 255, 255, 0.13) !important;
    border-color: #9bbd55 !important;
    box-shadow: 0 0 0 3px rgba(155, 189, 85, 0.28) !important;
    color: #ffffff !important;
}

.fb-modal-box .nature-input::placeholder,
.fb-modal-box .nature-textarea::placeholder,
.fb-modal-box .form-control::placeholder {
    color: rgba(255, 255, 255, 0.45) !important;
}

.fb-modal-box select option,
.fb-modal-box select optgroup {
    background: #182215 !important;
    color: #ffffff !important;
}

/* Accordion inside modal */
.fb-modal-box .accordion-item {
    background: rgba(255, 255, 255, 0.04) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 18px !important;
    overflow: hidden;
}

.fb-modal-box .accordion-button {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: none !important;
}
.fb-modal-box .accordion-button:not(.collapsed) {
    background: rgba(155, 189, 85, 0.15) !important;
    color: #c4e482 !important;
}
.fb-modal-box .accordion-button::after {
    filter: invert(1) brightness(150%) !important;
}

/* Tool Item Marquee Animation */
.tool-name-marquee-container {
    overflow: hidden;
    white-space: nowrap;
    position: relative;
    width: 100%;
}
.tool-name-marquee-text {
    display: inline-block;
    white-space: nowrap;
    animation: toolMarqueeScroll 12s linear infinite;
    will-change: transform;
}
.tool-item label:hover .tool-name-marquee-text {
    animation-play-state: paused;
}
@keyframes toolMarqueeScroll {
    0% { transform: translateX(0); }
    20% { transform: translateX(0); }
    80% { transform: translateX(calc(-100% + 160px)); }
    100% { transform: translateX(0); }
}
</style>

<div class="app-container fb-composer-wrapper">
    <!-- Top Return Nav -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="<?= $escape($baseUrl) ?>/posts" class="nature-btn-secondary px-3 py-1.5 d-inline-flex align-items-center gap-1.5" style="height: 38px; font-size: 13px;">
            <i class="bi bi-arrow-left"></i> Quay lại diễn đàn
        </a>
        <span class="badge" style="background: rgba(155, 189, 85, 0.15); color: #c4e482; border: 1px solid rgba(155, 189, 85, 0.3);">
            <i class="bi bi-pencil-square me-1"></i>Viết bài mới
        </span>
    </div>

    <!-- Main Post Composer Form (Bao gồm các modal bên trong) -->
    <form method="post" action="<?= $escape($baseUrl) ?>/posts" enctype="multipart/form-data" id="postCreateForm">
        <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">

        <?php if (!empty($errors['form'])): ?>
            <div class="alert alert-danger rounded-4 mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span><?= $escape($errors['form']) ?></span>
            </div>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════════════════════
             FACEBOOK COMPOSER CARD (Giao diện chính tinh gọn)
        ══════════════════════════════════════════════════════════ -->
        <div class="fb-composer-card">
            <!-- Author & Category Selector Row -->
            <div class="fb-author-row">
                <?php if ($userAvatar): ?>
                    <img src="<?= $escape($userAvatar) ?>" alt="<?= $escape($userName) ?>" class="fb-avatar">
                <?php else: ?>
                    <div class="fb-avatar"><?= strtoupper(mb_substr($userName, 0, 1)) ?></div>
                <?php endif; ?>

                <div class="fb-author-info">
                    <div class="fb-author-name"><?= $escape($userName) ?></div>
                    <div class="d-flex align-items-center gap-2">
                        <select class="fb-category-pill-select" id="category_id" name="category_id" required>
                            <option value="">📁 Chọn chuyên mục *</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= (($old['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                    <?= $escape($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="badge rounded-pill text-bg-light border text-muted" style="font-size: 11px;">
                            <i class="bi bi-globe-asia-australia me-1"></i>Công khai
                        </span>
                    </div>
                </div>
            </div>
            <?php if (!empty($errors['category_id'])): ?>
                <div class="text-danger small mb-2"><i class="bi bi-exclamation-circle me-1"></i><?= $escape($errors['category_id']) ?></div>
            <?php endif; ?>

            <!-- Post Title (Field chính 1) -->
            <div>
                <input class="fb-title-input" 
                       id="title" 
                       name="title" 
                       type="text" 
                       required 
                       placeholder="Tiêu đề bài chia sẻ dã ngoại của bạn..."
                       value="<?= $escape($old['title'] ?? '') ?>">
                <?php if (!empty($errors['title'])): ?>
                    <div class="text-danger small mb-2"><i class="bi bi-exclamation-circle me-1"></i><?= $escape($errors['title']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Post Content (Field chính 2) -->
            <div>
                <textarea class="fb-content-textarea" 
                          id="content" 
                          name="content" 
                          rows="5" 
                          required 
                          placeholder="<?= $escape($userName) ?> ơi, bạn muốn chia sẻ kinh nghiệm, lịch trình hay câu chuyện dã ngoại gì hôm nay?..."><?= $escape($old['content'] ?? '') ?></textarea>
                <?php if (!empty($errors['content'])): ?>
                    <div class="text-danger small mb-2"><i class="bi bi-exclamation-circle me-1"></i><?= $escape($errors['content']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Image Thumbnails Strip (hiện nhanh khi đã chọn ảnh) -->
            <div id="fbThumbsStrip" class="fb-thumbs-strip d-none"></div>

            <!-- Attached Feature Badges / Chips (Hiện các tính năng phụ đã cài đặt) -->
            <div id="fbAttachedChips" class="fb-chips-container d-none">
                <!-- Location Chip -->
                <span id="chipLocation" class="fb-chip fb-chip-location d-none" onclick="openModal('modalLocation')">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span id="chipLocationText">Địa điểm</span>
                    <button type="button" class="fb-chip-close" onclick="event.stopPropagation(); clearLocation();">&times;</button>
                </span>

                <!-- Trip Info Chip -->
                <span id="chipTrip" class="fb-chip fb-chip-trip d-none" onclick="openModal('modalTripInfo')">
                    <i class="bi bi-compass-fill"></i>
                    <span id="chipTripText">Hành trình chuyến đi</span>
                    <button type="button" class="fb-chip-close" onclick="event.stopPropagation(); clearTripInfo();">&times;</button>
                </span>

                <!-- Tools & Gear Chip -->
                <span id="chipTools" class="fb-chip fb-chip-tools d-none" onclick="openModal('modalToolsGear')">
                    <i class="bi bi-tools"></i>
                    <span id="chipToolsText">0 Dụng cụ</span>
                    <button type="button" class="fb-chip-close" onclick="event.stopPropagation(); clearTools();">&times;</button>
                </span>

                <!-- Photos Chip -->
                <span id="chipImages" class="fb-chip fb-chip-image d-none" onclick="openModal('modalImages')">
                    <i class="bi bi-images"></i>
                    <span id="chipImagesText">0 ảnh</span>
                    <button type="button" class="fb-chip-close" onclick="event.stopPropagation(); clearImages();">&times;</button>
                </span>
            </div>

            <!-- Error Alerts for Auxiliary Modals if any -->
            <?php if (!empty($errors['image']) || !empty($errors['images'])): ?>
                <div class="text-danger small mb-2"><i class="bi bi-images me-1"></i><?= $escape($errors['image'] ?? $errors['images']) ?></div>
            <?php endif; ?>
            <?php if (!empty($errors['lat']) || !empty($errors['lng'])): ?>
                <div class="text-danger small mb-2"><i class="bi bi-geo-alt me-1"></i><?= $escape($errors['lat'] ?? $errors['lng']) ?></div>
            <?php endif; ?>

            <!-- ══════════════════════════════════════════════════════════
                 FACEBOOK COMPOSER TOOLBAR ("Thêm vào bài viết của bạn")
            ══════════════════════════════════════════════════════════ -->
            <div class="fb-composer-toolbar">
                <span class="fb-toolbar-title">Thêm vào bài viết của bạn</span>
                <div class="fb-tool-actions">
                    <!-- Nút 1: Hình ảnh / Video -->
                    <button type="button" class="fb-tool-btn" data-bs-toggle="modal" data-bs-target="#modalImages" title="Thêm hình ảnh / Album ảnh">
                        <i class="bi bi-images text-success"></i>
                    </button>
                    <!-- Nút 2: Check-in Địa điểm & Bản đồ GPS -->
                    <button type="button" class="fb-tool-btn" data-bs-toggle="modal" data-bs-target="#modalLocation" title="Check-in Địa điểm & Tọa độ bản đồ">
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                    </button>
                    <!-- Nút 3: Chi tiết hành trình dã ngoại -->
                    <button type="button" class="fb-tool-btn" data-bs-toggle="modal" data-bs-target="#modalTripInfo" title="Thông tin hành trình (Độ khó, cung đường, chi phí...)">
                        <i class="bi bi-compass-fill text-primary"></i>
                    </button>
                    <!-- Nút 4: Dụng cụ & Trang thiết bị -->
                    <button type="button" class="fb-tool-btn" data-bs-toggle="modal" data-bs-target="#modalToolsGear" title="Dụng cụ & Trang thiết bị sử dụng">
                        <i class="bi bi-tools text-warning"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button (Nút xuất bản phong cách FB) -->
            <button type="submit" class="nature-btn-primary fb-submit-btn">
                <i class="bi bi-send-fill"></i> Xuất bản bài viết
            </button>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             MODAL 1: HÌNH ẢNH BÀI VIẾT (#modalImages)
        ══════════════════════════════════════════════════════════ -->
        <div class="modal fade" id="modalImages" tabindex="-1" aria-labelledby="modalImagesLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content fb-modal-box">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalImagesLabel">
                            <i class="bi bi-images text-success me-2"></i>Thêm hình ảnh bài viết
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Dropzone -->
                        <div class="upload-dropzone p-4 text-center rounded-4 border border-2 border-dashed" 
                             id="imageDropzone" 
                             style="background: rgba(255, 255, 255, 0.04); border-color: rgba(155, 189, 85, 0.45) !important; cursor: pointer; transition: all 0.2s ease;">
                            <i class="bi bi-cloud-arrow-up-fill fs-1 d-block mb-2" style="color: #9bbd55;"></i>
                            <div class="fw-semibold text-white mb-1">Nhấn để chọn ảnh, hoặc kéo thả file vào đây</div>
                            <div class="small" style="color: rgba(255, 255, 255, 0.7);">Hỗ trợ JPG, PNG, WEBP &lt; 2MB/ảnh. Có thể chọn cùng lúc nhiều ảnh để tạo Album ảnh.</div>
                            <input class="d-none" id="imagesInput" name="images[]" type="file" multiple accept="image/png, image/jpeg, image/webp">
                        </div>

                        <!-- Previews -->
                        <div class="row g-2 mt-3 d-none" id="imagePreviewContainer"></div>
                        <div class="small mt-2 d-none" id="imagePreviewCount" style="color: rgba(255, 255, 255, 0.85);"></div>

                        <!-- Cloud URL Input -->
                        <div class="mt-4 pt-3" style="border-top: 1px solid rgba(255, 255, 255, 0.1) !important;">
                            <label class="form-label small fw-semibold text-white mb-1">
                                <i class="bi bi-link-45deg me-1" style="color: #9bbd55;"></i>Hoặc dán Link ảnh Cloud (ImgBB / Cloudinary)
                            </label>
                            <input type="text"
                                   name="cloud_image_url"
                                   id="cloud_image_url"
                                   class="nature-input"
                                   style="height: 42px; font-size: 13.5px;"
                                   placeholder="Dán link ảnh từ ImgBB / Cloudinary..."
                                   value="<?= $escape($old['cloud_image_url'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="nature-btn-primary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px;">
                            <i class="bi bi-check-lg me-1"></i> Xong
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             MODAL 2: ĐỊA ĐIỂM & BẢN ĐỒ GPS (#modalLocation)
        ══════════════════════════════════════════════════════════ -->
        <div class="modal fade" id="modalLocation" tabindex="-1" aria-labelledby="modalLocationLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content fb-modal-box">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLocationLabel">
                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>Check-in Địa điểm & Bản đồ
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Location Name with Map Picker Button -->
                        <div class="mb-3">
                            <label for="location" class="nature-label">Tên địa điểm dã ngoại</label>
                            <div class="loc-input-wrap position-relative">
                                <input class="nature-input loc-input"
                                       id="location" 
                                       name="location" 
                                       type="text" 
                                       placeholder="Ví dụ: Tà Xùa, Sơn La hoặc Hồ Trị An, Đồng Nai..."
                                       value="<?= $escape($old['location'] ?? '') ?>"
                                       oninput="updateAllChips()">
                                <button type="button"
                                        class="loc-map-btn"
                                        onclick="MapPicker.open()"
                                        title="Mở bản đồ tìm kiếm vị trí">
                                    <i class="bi bi-map-fill"></i>
                                </button>
                            </div>
                            <small class="mt-1 d-block" style="color: rgba(255, 255, 255, 0.65);">Bấm vào biểu tượng bản đồ bên phải ô nhập để tìm kiếm nhanh tọa độ.</small>
                        </div>

                        <!-- Coordinates Lat / Lng -->
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="lat" class="nature-label">Vĩ độ (Lat)</label>
                                <input class="nature-input" 
                                       id="lat" 
                                       name="lat" 
                                       type="number" 
                                       step="any"
                                       placeholder="VD: 21.2833"
                                       value="<?= $escape((string)($old['lat'] ?? '')) ?>"
                                       oninput="updateAllChips()">
                            </div>
                            <div class="col-6">
                                <label for="lng" class="nature-label">Kinh độ (Lng)</label>
                                <input class="nature-input" 
                                       id="lng" 
                                       name="lng" 
                                       type="number" 
                                       step="any"
                                       placeholder="VD: 104.3167"
                                       value="<?= $escape((string)($old['lng'] ?? '')) ?>"
                                       oninput="updateAllChips()">
                            </div>
                        </div>
                        <div class="rounded-3 mt-3 p-3 small" style="background: rgba(155, 189, 85, 0.12); border: 1px solid rgba(155, 189, 85, 0.3); color: #d6e8b5; line-height: 1.5;">
                            <i class="bi bi-info-circle-fill me-1" style="color: #9bbd55;"></i>Khi có tọa độ GPS, hệ thống sẽ tự động ghim vị trí lên <strong class="text-white">Bản đồ địa điểm</strong> và kích hoạt <strong class="text-white">Dự báo thời tiết 4 ngày tới</strong> cho người đọc!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="nature-btn-primary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px;">
                            <i class="bi bi-check-lg me-1"></i> Xác nhận địa điểm
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════
                 MAP PICKER MODAL (OpenStreetMap + Nominatim)
                 Đặt trong #modalLocation để tránh xung đột FocusTrap của Bootstrap
            ══════════════════════════════════════════════════════════ -->
            <div id="mapPickerModal" style="display:none; position:fixed; inset:0; z-index:1070; background:rgba(0,0,0,0.72); align-items:center; justify-content:center;">
                <div style="background:#182215; border:1px solid rgba(255,255,255,0.18); border-radius:24px; width:min(640px,96vw); max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 32px 80px rgba(0,0,0,0.8);">
                    <!-- Modal Header -->
                    <div style="padding:1rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:10px;">
                        <i class="bi bi-map-fill" style="color:#9bbd55; font-size:1.15rem;"></i>
                        <strong style="color:#fff; flex:1; font-size:1.05rem;">Chọn địa điểm trên bản đồ</strong>
                        <button type="button" onclick="MapPicker.close()" style="background:none;border:none;color:rgba(255,255,255,0.75);font-size:1.25rem;cursor:pointer;padding:0;line-height:1;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Search bar -->
                    <div style="padding:0.85rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; gap:8px;">
                        <input type="text" id="mapSearchInput"
                               placeholder="Nhập tên địa điểm, ví dụ: Đỉnh Fansipan, Tà Xùa, Núi Bà Đen..."
                               style="flex:1; background:rgba(255,255,255,0.09); border:1px solid rgba(255,255,255,0.2); border-radius:12px;
                                      color:#fff; padding:0.55rem 0.95rem; font-size:0.92rem; outline:none;"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();MapPicker.search();}">
                        <button type="button" onclick="MapPicker.search()"
                                style="background:#9bbd55; border:none; border-radius:12px; color:#182215; font-weight:600;
                                       padding:0.55rem 1.2rem; cursor:pointer; font-size:0.9rem; white-space:nowrap; display:inline-flex; align-items:center; gap:4px;">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                    </div>

                    <!-- Search results -->
                    <div id="mapSearchResults" style="max-height:240px; overflow-y:auto; border-bottom:1px solid rgba(255,255,255,0.08);"></div>

                    <!-- Selected info -->
                    <div id="mapSelectedInfo" style="padding:0.75rem 1.25rem; background:rgba(155,189,85,0.12); border-bottom:1px solid rgba(155,189,85,0.2); display:none; align-items:center; gap:8px;">
                        <i class="bi bi-geo-alt-fill" style="color:#9bbd55;"></i>
                        <span id="mapSelectedText" style="color:#d6e8b5; font-size:0.88rem; flex:1;"></span>
                    </div>

                    <!-- Footer -->
                    <div style="padding:0.85rem 1.25rem; display:flex; justify-content:flex-end; gap:8px; background:rgba(0,0,0,0.2);">
                        <button type="button" onclick="MapPicker.close()"
                                style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.18); border-radius:12px;
                                       color:#eee; padding:0.5rem 1.2rem; cursor:pointer; font-size:0.9rem;">
                            Hủy
                        </button>
                        <button type="button" id="mapConfirmBtn" onclick="MapPicker.confirm()" disabled
                                style="background:#9bbd55; border:none; border-radius:12px; color:#182215; font-weight:600;
                                       padding:0.5rem 1.35rem; cursor:pointer; opacity:0.5; font-size:0.9rem;">
                            <i class="bi bi-check-lg me-1"></i> Xác nhận
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             MODAL 3: THÔNG TIN HÀNH TRÌNH CHUYẾN ĐI (#modalTripInfo)
        ══════════════════════════════════════════════════════════ -->
        <div class="modal fade" id="modalTripInfo" tabindex="-1" aria-labelledby="modalTripInfoLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content fb-modal-box">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTripInfoLabel">
                            <i class="bi bi-compass-fill text-primary me-2"></i>Thông tin hành trình "Chuyến đi" (Trip Details)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small mb-3" style="color: rgba(255, 255, 255, 0.7); font-size: 13.5px;">Cung cấp các thông số thực tế để giúp cộng đồng dễ dàng lọc bài viết và chuẩn bị hành trang.</p>

                        <div class="row g-3 mb-3">
                            <!-- Loại hình -->
                            <div class="col-12 col-md-4">
                                <label for="activity_type" class="nature-label">Loại hình dã ngoại</label>
                                <select class="nature-input" id="activity_type" name="activity_type" onchange="updateAllChips()">
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
                                <select class="nature-input" id="difficulty" name="difficulty" onchange="updateAllChips()">
                                    <option value="easy" <?= (($old['difficulty'] ?? '') === 'easy') ? 'selected' : '' ?>>Dễ (Người mới, gia đình)</option>
                                    <option value="medium" <?= (($old['difficulty'] ?? 'medium') === 'medium') ? 'selected' : '' ?>>Trung bình (Cần thể lực cơ bản)</option>
                                    <option value="hard" <?= (($old['difficulty'] ?? '') === 'hard') ? 'selected' : '' ?>>Khó (Đồi dốc, dài ngày)</option>
                                    <option value="extreme" <?= (($old['difficulty'] ?? '') === 'extreme') ? 'selected' : '' ?>>Thử thách cao (Chuyên nghiệp)</option>
                                </select>
                            </div>

                            <!-- Vùng miền -->
                            <div class="col-12 col-md-4">
                                <label for="region" class="nature-label">Khu vực / Vùng miền</label>
                                <?php
                                $standardRegions = ['Tây Bắc', 'Đông Bắc', 'Bắc Bộ', 'Bắc Trung Bộ', 'Nam Trung Bộ', 'Tây Nguyên', 'Đông Nam Bộ', 'Tây Nam Bộ', 'Đông Nam Á', 'Châu Á', 'Châu Âu', 'Châu Mỹ', 'Châu Úc & Khác'];
                                $oldRegion = $old['region'] ?? '';
                                $isCustomReg = (!empty($oldRegion) && !in_array($oldRegion, $standardRegions, true)) || ($oldRegion === 'Quốc tế khác');
                                $customRegVal = $isCustomReg ? ($oldRegion === 'Quốc tế khác' ? ($old['custom_region'] ?? '') : $oldRegion) : '';
                                ?>
                                <select class="nature-input" id="region" name="region" onchange="updateAllChips()">
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
                                        <option value="Đông Nam Á" <?= ($oldRegion === 'Đông Nam Á') ? 'selected' : '' ?>>Đông Nam Á</option>
                                        <option value="Châu Á" <?= ($oldRegion === 'Châu Á') ? 'selected' : '' ?>>Châu Á</option>
                                        <option value="Châu Âu" <?= ($oldRegion === 'Châu Âu') ? 'selected' : '' ?>>Châu Âu</option>
                                        <option value="Châu Mỹ" <?= ($oldRegion === 'Châu Mỹ') ? 'selected' : '' ?>>Châu Mỹ</option>
                                        <option value="Châu Úc & Khác" <?= ($oldRegion === 'Châu Úc & Khác') ? 'selected' : '' ?>>Châu Úc / Châu Phi</option>
                                        <option value="Quốc tế khác" <?= $isCustomReg ? 'selected' : '' ?>>Quốc tế khác</option>
                                    </optgroup>
                                </select>
                                <div id="custom_region_wrapper" class="mt-2" style="display: <?= $isCustomReg ? 'block' : 'none' ?>;">
                                    <input type="text" class="nature-input" id="custom_region" name="custom_region" 
                                           placeholder="Nhập tên quốc gia / địa điểm..." 
                                           value="<?= $escape($customRegVal) ?>" oninput="updateAllChips()">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-5">
                                <label for="route" class="nature-label">Cung đường di chuyển</label>
                                <input type="text" class="nature-input" id="route" name="route" 
                                       placeholder="VD: Hà Nội - Đèo Đá Trắng - Tà Xùa" 
                                       value="<?= $escape($old['route'] ?? '') ?>" oninput="updateAllChips()">
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="trip_duration" class="nature-label">Thời gian</label>
                                <input type="text" class="nature-input" id="trip_duration" name="trip_duration" 
                                       placeholder="VD: 2 ngày 1 đêm" 
                                       value="<?= $escape($old['trip_duration'] ?? '') ?>" oninput="updateAllChips()">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="estimated_cost" class="nature-label">Chi phí dự tính</label>
                                <input type="text" class="nature-input" id="estimated_cost" name="estimated_cost" 
                                       placeholder="VD: 1.200.000 đ/người" 
                                       value="<?= $escape($old['estimated_cost'] ?? '') ?>" oninput="updateAllChips()">
                            </div>
                        </div>

                        <div>
                            <label for="gear_needed" class="nature-label">Đồ cần mang (Checklist trang bị mang theo)</label>
                            <textarea class="nature-textarea" id="gear_needed" name="gear_needed" rows="3" 
                                      placeholder="VD: Lều 2 lớp, túi ngủ nhiệt, giày bám tốt, áo gió, đèn pin đội đầu, bình nước 2L..."
                                      oninput="updateAllChips()"><?= $escape($old['gear_needed'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="nature-btn-primary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px;">
                            <i class="bi bi-check-lg me-1"></i> Lưu thông tin hành trình
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             MODAL 4: DỤNG CỤ & TRANG THIẾT BỊ (#modalToolsGear)
        ══════════════════════════════════════════════════════════ -->
        <div class="modal fade" id="modalToolsGear" tabindex="-1" aria-labelledby="modalToolsGearLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content fb-modal-box">
                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title mb-0" id="modalToolsGearLabel">
                                <i class="bi bi-tools text-warning me-2"></i>Dụng cụ & Trang thiết bị chuyến đi
                            </h5>
                            <span class="badge rounded-pill text-white" id="modalToolsCountBadge" style="background: #9bbd55; font-size: 11px;">0</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <!-- KHỐI 1: TỰ NHẬP CÔNG CỤ CÁ NHÂN -->
                        <div class="p-3 mb-3 rounded-4 border" style="background: rgba(255, 255, 255, 0.04); border-color: rgba(255, 255, 255, 0.12) !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                <span class="fw-semibold text-white small">
                                    <i class="bi bi-bag-check-fill me-1" style="color: #9bbd55;"></i>Thêm dụng cụ cá nhân bạn dùng
                                </span>
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 shadow-sm" onclick="addCustomToolRow()" style="font-size: 12px; font-weight: 600; background: rgba(155, 189, 85, 0.2); border: 1px solid #9bbd55; color: #c4e482;">
                                    <i class="bi bi-plus-circle-fill"></i> Thêm món đồ
                                </button>
                            </div>
                            <div id="customToolsContainer" class="d-flex flex-column gap-3 mt-2"></div>
                            <div id="emptyToolsNotice" class="text-center py-3 small" style="background: rgba(255, 255, 255, 0.02); border-radius: 14px; border: 1px dashed rgba(255, 255, 255, 0.16); color: rgba(255, 255, 255, 0.72); padding: 1rem;">
                                <span>Chưa có món đồ cá nhân nào. Hãy bấm <strong class="text-white">"Thêm món đồ"</strong> để chia sẻ lều, balo, bếp... kèm link mua Shopee/Lazada!</span>
                            </div>
                        </div>

                        <!-- KHỐI 2: CHỌN TỪ KHO DỤNG CỤ CỘNG ĐỒNG CÓ SẴN -->
                        <?php if (!empty($products)): ?>
                            <div class="accordion border-0" id="accordionExistingTools">
                                <div class="accordion-item rounded-4 border overflow-hidden" style="background: rgba(255, 255, 255, 0.04); border-color: rgba(255, 255, 255, 0.12) !important;">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2.5 px-3 fw-semibold text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExistingTools" aria-expanded="false" style="font-size: 13px; background: rgba(255, 255, 255, 0.05); color: #ffffff !important; box-shadow: none;">
                                            <i class="bi bi-collection me-2" style="color: #9bbd55;"></i> Hoặc chọn từ kho dụng cụ cộng đồng sẵn có (<?= count($products) ?> sản phẩm)
                                        </button>
                                    </h2>
                                    <div id="collapseExistingTools" class="accordion-collapse collapse" data-bs-parent="#accordionExistingTools">
                                        <div class="accordion-body p-3">
                                            <div class="mb-2">
                                                <input type="text" id="toolSearchInput" class="form-control form-control-sm rounded-pill" 
                                                       style="background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.18); color: #ffffff;"
                                                       placeholder="Tìm nhanh lều, balo, bếp, đèn pin..." oninput="filterToolsList(this.value)">
                                            </div>
                                            <div class="row row-cols-1 row-cols-md-2 g-2" id="toolsCheckboxGrid" style="max-height: 220px; overflow-y: auto;">
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
                                                    <div class="col tool-item" data-name="<?= $escape(mb_strtolower($prod['name'])) ?>">
                                                        <label class="d-flex align-items-center gap-2 p-2 rounded-3 border h-100 position-relative" style="cursor: pointer; background: rgba(255, 255, 255, 0.06); border-color: rgba(255, 255, 255, 0.12) !important; transition: all 0.2s ease;">
                                                            <input type="checkbox" name="product_ids[]" value="<?= $pId ?>" 
                                                                   class="form-check-input mt-0 tool-checkbox flex-shrink-0" 
                                                                   <?= $isChecked ? 'checked' : '' ?> onchange="updateSelectedToolsCounter()">
                                                            <img src="<?= $escape($toolImg) ?>" alt="<?= $escape($prod['name']) ?>" class="rounded-2 object-fit-cover flex-shrink-0" style="width: 38px; height: 38px;">
                                                            <div class="min-w-0 flex-grow-1" style="font-size: 12.5px; overflow: hidden;">
                                                                <div class="tool-name-marquee-container">
                                                                    <div class="fw-semibold text-white tool-name-marquee-text" title="<?= $escape($prod['name']) ?>"><?= $escape($prod['name']) ?></div>
                                                                </div>
                                                                <div class="fw-bold small" style="color: #9bbd55;"><?= number_format((float)($prod['price'] ?? 0), 0, ',', '.') ?> đ</div>
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
                    <div class="modal-footer">
                        <button type="button" class="nature-btn-primary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px;">
                            <i class="bi bi-check-lg me-1"></i> Lưu danh sách dụng cụ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
/* Location button in input */
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
    font-size: 1.15rem;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 8px;
    line-height: 1;
    transition: all 0.2s;
    z-index: 5;
}
.loc-map-btn:hover {
    color: #4d7a1f;
    background: rgba(122,171,58,0.15);
    transform: translateY(-50%) scale(1.15);
}

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
// FACEBOOK COMPOSER INTERACTION ENGINE & CHIPS SYNCHRONIZER
// ══════════════════════════════════════════════════════════════

function openModal(modalId) {
    var modalEl = document.getElementById(modalId);
    if (modalEl && window.bootstrap) {
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
}

// Cập nhật tất cả các chip trạng thái trên giao diện chính
function updateAllChips() {
    var container = document.getElementById('fbAttachedChips');
    if (!container) return;

    var anyChipVisible = false;

    // 1. Location Chip
    var locInput = document.getElementById('location');
    var latInput = document.getElementById('lat');
    var lngInput = document.getElementById('lng');
    var chipLoc = document.getElementById('chipLocation');
    var chipLocText = document.getElementById('chipLocationText');
    var locVal = (locInput?.value || '').trim();
    var latVal = (latInput?.value || '').trim();
    var lngVal = (lngInput?.value || '').trim();

    if (locVal !== '' || (latVal !== '' && lngVal !== '')) {
        chipLoc.classList.remove('d-none');
        chipLocText.textContent = locVal ? locVal : 'Tọa độ GPS (' + latVal + ', ' + lngVal + ')';
        anyChipVisible = true;
    } else {
        chipLoc.classList.add('d-none');
    }

    // 2. Trip Info Chip
    var actType = (document.getElementById('activity_type')?.value || '').trim();
    var diff = (document.getElementById('difficulty')?.value || '').trim();
    var reg = (document.getElementById('region')?.value || '').trim();
    var route = (document.getElementById('route')?.value || '').trim();
    var chipTrip = document.getElementById('chipTrip');
    var chipTripText = document.getElementById('chipTripText');

    if (actType !== '' || route !== '' || (reg !== '' && reg !== 'Quốc tế khác')) {
        chipTrip.classList.remove('d-none');
        var labels = [];
        if (actType) labels.push(actType.toUpperCase());
        if (reg) labels.push(reg);
        if (route) labels.push(route.length > 20 ? route.substring(0, 18) + '…' : route);
        chipTripText.textContent = labels.length ? labels.join(' · ') : 'Chi tiết hành trình';
        anyChipVisible = true;
    } else {
        chipTrip.classList.add('d-none');
    }

    // 3. Tools Chip
    var checkedTools = document.querySelectorAll('.tool-checkbox:checked').length;
    var customTools = document.querySelectorAll('#customToolsContainer .custom-tool-card').length;
    var totalTools = checkedTools + customTools;
    var chipTools = document.getElementById('chipTools');
    var chipToolsText = document.getElementById('chipToolsText');
    var modalCountBadge = document.getElementById('modalToolsCountBadge');
    if (modalCountBadge) modalCountBadge.textContent = totalTools;

    if (totalTools > 0) {
        chipTools.classList.remove('d-none');
        chipToolsText.textContent = totalTools + ' dụng cụ & thiết bị';
        anyChipVisible = true;
    } else {
        chipTools.classList.add('d-none');
    }

    // 4. Images Chip
    var imagesInput = document.getElementById('imagesInput');
    var cloudUrlInput = document.getElementById('cloud_image_url');
    var numFiles = imagesInput?.files?.length || 0;
    var hasCloudUrl = (cloudUrlInput?.value || '').trim() !== '';
    var chipImages = document.getElementById('chipImages');
    var chipImagesText = document.getElementById('chipImagesText');

    if (numFiles > 0 || hasCloudUrl) {
        chipImages.classList.remove('d-none');
        chipImagesText.textContent = numFiles > 0 ? numFiles + ' ảnh đã chọn' : 'Link ảnh Cloud';
        anyChipVisible = true;
    } else {
        chipImages.classList.add('d-none');
    }

    if (anyChipVisible) {
        container.classList.remove('d-none');
    } else {
        container.classList.add('d-none');
    }
}

// Xóa địa điểm
function clearLocation() {
    var locInput = document.getElementById('location');
    var latInput = document.getElementById('lat');
    var lngInput = document.getElementById('lng');
    if (locInput) locInput.value = '';
    if (latInput) latInput.value = '';
    if (lngInput) lngInput.value = '';
    updateAllChips();
}

// Xóa thông tin hành trình
function clearTripInfo() {
    ['activity_type', 'difficulty', 'region', 'custom_region', 'route', 'trip_duration', 'estimated_cost', 'gear_needed'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    var customWrapper = document.getElementById('custom_region_wrapper');
    if (customWrapper) customWrapper.style.display = 'none';
    updateAllChips();
}

// Xóa dụng cụ
function clearTools() {
    document.querySelectorAll('.tool-checkbox:checked').forEach(function(cb) {
        cb.checked = false;
    });
    var customContainer = document.getElementById('customToolsContainer');
    if (customContainer) customContainer.innerHTML = '';
    var emptyNotice = document.getElementById('emptyToolsNotice');
    if (emptyNotice) emptyNotice.style.display = '';
    updateAllChips();
}

// Xóa ảnh
function clearImages() {
    var imagesInput = document.getElementById('imagesInput');
    var cloudInput = document.getElementById('cloud_image_url');
    var prevBox = document.getElementById('imagePreviewContainer');
    var countBox = document.getElementById('imagePreviewCount');
    var thumbsStrip = document.getElementById('fbThumbsStrip');
    if (imagesInput) imagesInput.value = '';
    if (cloudInput) cloudInput.value = '';
    if (prevBox) { prevBox.innerHTML = ''; prevBox.classList.add('d-none'); }
    if (countBox) countBox.classList.add('d-none');
    if (thumbsStrip) { thumbsStrip.innerHTML = ''; thumbsStrip.classList.add('d-none'); }
    updateAllChips();
}

// ══════════════════════════════════════════════════════════════
// MAP PICKER — OpenStreetMap Nominatim
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

        // Tạm thời vô hiệu hóa FocusTrap của Bootstrap Modal nếu đang mở
        var locModal = document.getElementById('modalLocation');
        if (locModal && window.bootstrap) {
            var modalInst = bootstrap.Modal.getInstance(locModal);
            if (modalInst && modalInst._focustrap && typeof modalInst._focustrap.deactivate === 'function') {
                modalInst._focustrap.deactivate();
            }
        }

        if (modal()) modal().style.display = 'flex';
        setTimeout(function() {
            if (searchInput()) {
                searchInput().focus();
                searchInput().select();
            }
        }, 80);
    }

    function close() {
        if (modal()) modal().style.display = 'none';

        // Kích hoạt lại FocusTrap của Bootstrap Modal
        var locModal = document.getElementById('modalLocation');
        if (locModal && window.bootstrap) {
            var modalInst = bootstrap.Modal.getInstance(locModal);
            if (modalInst && modalInst._focustrap && typeof modalInst._focustrap.activate === 'function') {
                modalInst._focustrap.activate();
            }
        }

        var locInput = document.getElementById('location');
        if (locInput) {
            setTimeout(function() { locInput.focus(); }, 60);
        }
    }

    async function search() {
        const q = searchInput()?.value?.trim();
        if (!q) return;

        resultsBox().innerHTML = '<div class="map-spinner"><i class="bi bi-hourglass-split me-1"></i>Đang tìm kiếm...</div>';
        if (selectedInfo()) selectedInfo().style.display = 'none';
        if (confirmBtn()) { confirmBtn().disabled = true; confirmBtn().style.opacity = '0.5'; }
        selected = null;

        try {
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
                resultsBox().innerHTML = '<div class="map-spinner"><i class="bi bi-search me-1"></i>Không tìm thấy địa điểm. Thử từ khóa khác.</div>';
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
            resultsBox().innerHTML = `<div class="map-spinner" style="color:#f99;">
                <i class="bi bi-wifi-off me-1"></i>Không thể kết nối dịch vụ bản đồ.<br>
                <small class="d-block mt-1 opacity-75">Bạn có thể tự gõ tên và tọa độ thủ công.</small>
            </div>`;
        }
    }

    function selectResult(item, el) {
        selected = {
            lat: parseFloat(item.lat),
            lng: parseFloat(item.lon),
            short_name: item.display_name.split(',').slice(0, 2).join(',').trim()
        };

        document.querySelectorAll('.map-result-item').forEach(function(e) {
            e.classList.remove('selected');
        });
        if (el) el.classList.add('selected');

        if (selectedInfo()) selectedInfo().style.display = 'flex';
        if (selectedText()) selectedText().textContent =
            `${selected.short_name} · ${selected.lat.toFixed(5)}, ${selected.lng.toFixed(5)}`;
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
        updateAllChips();
    }

    var mapModalEl = document.getElementById('mapPickerModal');
    if (mapModalEl) {
        mapModalEl.addEventListener('click', function(e) {
            if (e.target === this) close();
        });
        mapModalEl.addEventListener('focusin', function(e) {
            e.stopPropagation();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal()?.style.display === 'flex') close();
    });

    return { open, close, search, confirm };
})();

// ══════════════════════════════════════════════════════════════
// TOOLS & GEAR SELECTION LOGIC
// ══════════════════════════════════════════════════════════════
function updateSelectedToolsCounter() {
    updateAllChips();
}

function addCustomToolRow(initialData) {
    var data = initialData || {};
    var container = document.getElementById('customToolsContainer');
    var emptyNotice = document.getElementById('emptyToolsNotice');
    if (!container) return;

    if (emptyNotice) emptyNotice.style.display = 'none';

    var card = document.createElement('div');
    card.className = 'custom-tool-card p-3 rounded-4 border position-relative';
    card.style.background = 'rgba(255, 255, 255, 0.07)';
    card.style.borderColor = 'rgba(255, 255, 255, 0.15)';
    card.style.boxShadow = '0 6px 20px rgba(0, 0, 0, 0.25)';

    var nameVal = data.name ? escapeHtml(data.name) : '';
    var priceVal = (data.price !== undefined && data.price !== null) ? escapeHtml(data.price) : '';
    var linkVal = data.link ? escapeHtml(data.link) : '';
    var descVal = data.description ? escapeHtml(data.description) : '';
    var imgVal = data.image_url ? escapeHtml(data.image_url) : '';

    var html = '' +
        '<button type="button" class="btn btn-sm text-danger position-absolute top-0 end-0 m-2 p-1 remove-tool-btn" title="Xóa món này" onclick="removeCustomToolRow(this)" style="font-size: 15px; line-height: 1;">' +
            '<i class="bi bi-trash3-fill"></i>' +
        '</button>' +
        '<div class="row g-2">' +
            '<div class="col-12 col-md-7">' +
                '<label class="form-label small fw-semibold text-white mb-1">Tên dụng cụ / Thiết bị <span class="text-danger">*</span></label>' +
                '<input type="text" name="custom_tool_name[]" value="' + nameVal + '" class="form-control form-control-sm rounded-3" placeholder="VD: Bếp ga dã ngoại Fire-Maple" required oninput="updateAllChips()">' +
            '</div>' +
            '<div class="col-12 col-md-5">' +
                '<label class="form-label small fw-semibold text-white mb-1">Giá tham khảo (VNĐ)</label>' +
                '<div class="input-group input-group-sm">' +
                    '<input type="number" name="custom_tool_price[]" value="' + priceVal + '" class="form-control form-control-sm rounded-start-3" placeholder="VD: 350000" min="0" step="1000">' +
                    '<span class="input-group-text rounded-end-3" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.18); color: rgba(255, 255, 255, 0.85);">đ</span>' +
                '</div>' +
            '</div>' +
            '<div class="col-12">' +
                '<label class="form-label small fw-semibold text-white mb-1"><i class="bi bi-link-45deg me-1" style="color: #9bbd55;"></i>Link mua (Shopee / Lazada...)</label>' +
                '<input type="url" name="custom_tool_link[]" value="' + linkVal + '" class="form-control form-control-sm rounded-3" placeholder="https://shopee.vn/...">' +
            '</div>' +
            '<div class="col-12 col-md-6">' +
                '<label class="form-label small fw-semibold text-white mb-1">Tải ảnh lên</label>' +
                '<input type="file" name="custom_tool_file[]" class="form-control form-control-sm rounded-3" accept="image/*">' +
            '</div>' +
            '<div class="col-12 col-md-6">' +
                '<label class="form-label small fw-semibold text-white mb-1">Hoặc dán URL ảnh</label>' +
                '<input type="url" name="custom_tool_image_url[]" value="' + imgVal + '" class="form-control form-control-sm rounded-3" placeholder="https://domain.com/anh.jpg">' +
            '</div>' +
            '<div class="col-12">' +
                '<label class="form-label small fw-semibold text-white mb-1">Đánh giá ngắn khi sử dụng</label>' +
                '<input type="text" name="custom_tool_desc[]" value="' + descVal + '" class="form-control form-control-sm rounded-3" placeholder="VD: Lửa mạnh, cản gió tốt, gập gọn nhẹ...">' +
            '</div>' +
            '<div class="col-12 mt-2 pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.1) !important;">' +
                '<div class="form-check d-flex align-items-center gap-2 m-0">' +
                    '<input class="form-check-input mt-0" type="checkbox" ' + (data.is_shared === 0 ? '' : 'checked') + ' style="cursor: pointer; width: 1.15em; height: 1.15em;" onchange="this.nextElementSibling.value = this.checked ? \'1\' : \'0\'">' +
                    '<input type="hidden" name="custom_tool_is_shared[]" value="' + (data.is_shared === 0 ? '0' : '1') + '">' +
                    '<label class="form-check-label small text-white fw-medium" style="cursor: pointer; user-select: none;">' +
                        '<i class="bi bi-share me-1" style="color: #9bbd55;"></i>Chia sẻ vào kho dụng cụ cộng đồng' +
                    '</label>' +
                '</div>' +
            '</div>' +
        '</div>';

    card.innerHTML = html;
    container.appendChild(card);
    updateAllChips();
}

function removeCustomToolRow(btn) {
    var card = btn.closest('.custom-tool-card');
    if (card) card.remove();
    var container = document.getElementById('customToolsContainer');
    var emptyNotice = document.getElementById('emptyToolsNotice');
    if (container && emptyNotice) {
        if (container.querySelectorAll('.custom-tool-card').length === 0) {
            emptyNotice.style.display = '';
        }
    }
    updateAllChips();
}

function filterToolsList(keyword) {
    var query = (keyword || '').toLowerCase().trim();
    document.querySelectorAll('#toolsCheckboxGrid .tool-item').forEach(function(item) {
        var name = item.getAttribute('data-name') || '';
        item.style.display = (query === '' || name.indexOf(query) !== -1) ? '' : 'none';
    });
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

// ══════════════════════════════════════════════════════════════
// IMAGE DROPZONE & PREVIEWS
// ══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('imageDropzone');
    const input    = document.getElementById('imagesInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const countText = document.getElementById('imagePreviewCount');
    const thumbsStrip = document.getElementById('fbThumbsStrip');

    if (dropzone && input) {
        dropzone.addEventListener('click', function() { input.click(); });

        ['dragenter', 'dragover'].forEach(function(ev) {
            dropzone.addEventListener(ev, function(e) {
                e.preventDefault(); e.stopPropagation();
                dropzone.style.background = 'rgba(155, 189, 85, 0.15)';
                dropzone.style.borderColor = '#9bbd55';
            });
        });
        ['dragleave', 'drop'].forEach(function(ev) {
            dropzone.addEventListener(ev, function(e) {
                e.preventDefault(); e.stopPropagation();
                dropzone.style.background = 'rgba(255, 255, 255, 0.04)';
                dropzone.style.borderColor = 'rgba(155, 189, 85, 0.45)';
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
                thumbsStrip.classList.add('d-none');
                thumbsStrip.innerHTML = '';
                updateAllChips();
                return;
            }
            previewContainer.innerHTML = '';
            previewContainer.classList.remove('d-none');
            countText.classList.remove('d-none');
            countText.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i>Đã chọn <strong>${files.length}</strong> hình ảnh cho bài viết này.`;

            thumbsStrip.innerHTML = '';
            thumbsStrip.classList.remove('d-none');

            Array.from(files).forEach(function(file, index) {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Previews in modal
                    const col = document.createElement('div');
                    col.className = 'col-4 col-sm-3 col-md-2';
                    col.innerHTML = `
                        <div class="position-relative rounded-3 overflow-hidden border shadow-sm" style="height: 75px; background: #000;">
                            <img src="${e.target.result}" alt="Preview ${index+1}" style="width:100%;height:100%;object-fit:cover;">
                            <span class="badge bg-dark bg-opacity-75 position-absolute bottom-0 start-0 m-1" style="font-size:9px;">#${index+1}</span>
                        </div>`;
                    previewContainer.appendChild(col);

                    // Thumbnail in main post box
                    const thumbImg = document.createElement('img');
                    thumbImg.src = e.target.result;
                    thumbImg.className = 'fb-thumb-item';
                    thumbImg.alt = `Thumb ${index+1}`;
                    thumbImg.onclick = function() { openModal('modalImages'); };
                    thumbsStrip.appendChild(thumbImg);
                };
                reader.readAsDataURL(file);
            });
            updateAllChips();
        }
    }

    // Region dropdown handler
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
            updateAllChips();
        });
    }

    // Modal close listeners to update chips
    ['modalImages', 'modalLocation', 'modalTripInfo', 'modalToolsGear'].forEach(function(mId) {
        var mEl = document.getElementById(mId);
        if (mEl) {
            mEl.addEventListener('hidden.bs.modal', function() {
                updateAllChips();
            });
        }
    });

    // Cloud url input listener
    var cloudUrlInput = document.getElementById('cloud_image_url');
    if (cloudUrlInput) {
        cloudUrlInput.addEventListener('input', updateAllChips);
    }

    // Initialize chips on first load (e.g. from $old)
    updateAllChips();
});
</script>
