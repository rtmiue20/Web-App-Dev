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

/* Image Thumbnails Strip & Preview Cards */
.fb-thumbs-strip {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow-x: auto;
    padding: 6px 2px 12px;
    margin-bottom: 0.85rem;
    scrollbar-width: thin;
}
.fb-thumb-card {
    position: relative;
    width: 82px;
    height: 82px;
    border-radius: 14px;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    border: 2px solid rgba(155, 189, 85, 0.4);
    background: #1a1e17;
}
.fb-thumb-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    cursor: pointer;
}
.fb-thumb-del {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.9);
    color: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    z-index: 5;
    transition: transform 0.15s ease;
}
.fb-thumb-del:hover {
    transform: scale(1.15);
    background: #dc3545;
}
.fb-thumb-idx {
    position: absolute;
    bottom: 4px;
    left: 4px;
    background: rgba(0,0,0,0.7);
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 8px;
    pointer-events: none;
}
.fb-thumb-add-btn {
    width: 82px;
    height: 82px;
    border-radius: 14px;
    border: 2px dashed #9bbd55;
    background: #f7f9f4;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    color: #5c8424;
    font-size: 11px;
    font-weight: 600;
    gap: 4px;
    transition: all 0.2s ease;
}
.fb-thumb-add-btn:hover {
    background: #eef4e6;
    border-color: #5c8424;
    transform: translateY(-2px);
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
   NATURE WHITE CARD MODALS FOR POST COMPOSER
   Đồng bộ 100% với Design System Đồ án nhóm (Thẻ trắng cao cấp)
   ══════════════════════════════════════════════════════════════ */
.modal-content.fb-modal-box {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border: 1px solid rgba(0, 0, 0, 0.08) !important;
    border-radius: 28px !important;
    box-shadow: 0 24px 60px -12px rgba(20, 24, 16, 0.25) !important;
    overflow: hidden;
    color: #23261f !important;
}

.fb-modal-box .modal-header {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border-bottom: 1px solid #edf0e8 !important;
    padding: 1.15rem 1.5rem !important;
    position: relative;
}

.fb-modal-box .modal-title {
    font-size: 1.15rem !important;
    font-weight: 700 !important;
    color: #23261f !important;
    letter-spacing: -0.2px;
}

.fb-modal-box .modal-body {
    padding: 1.5rem !important;
    max-height: 75vh;
    overflow-y: auto;
    color: #23261f !important;
    background: #ffffff !important;
    background-color: #ffffff !important;
}

.fb-modal-box .modal-footer {
    background: #fafbfa !important;
    background-color: #fafbfa !important;
    border-top: 1px solid #edf0e8 !important;
    padding: 0.95rem 1.5rem !important;
}

/* Close button - Crisp standard dark */
.fb-modal-box .btn-close {
    filter: none !important;
    opacity: 0.6 !important;
    transition: opacity 0.2s ease, transform 0.2s ease !important;
}
.fb-modal-box .btn-close:hover {
    opacity: 1 !important;
    transform: scale(1.1) rotate(90deg) !important;
}

/* Labels inside modal */
.fb-modal-box .nature-label,
.fb-modal-box .form-label,
.fb-modal-box label {
    color: #23261f !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    letter-spacing: 0.2px;
    margin-bottom: 0.45rem;
    display: inline-block;
}

/* Inputs and Selects inside modal */
.fb-modal-box .nature-input,
.fb-modal-box .nature-textarea,
.fb-modal-box .form-control,
.fb-modal-box .form-select,
.fb-modal-box input,
.fb-modal-box textarea,
.fb-modal-box select {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border: 1px solid #dcdfd6 !important;
    color: #23261f !important;
    border-radius: 14px !important;
    font-size: 14px !important;
    transition: all 0.2s ease;
}

.fb-modal-box .nature-input:focus,
.fb-modal-box .nature-textarea:focus,
.fb-modal-box .form-control:focus,
.fb-modal-box .form-select:focus,
.fb-modal-box input:focus,
.fb-modal-box textarea:focus,
.fb-modal-box select:focus {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border-color: #9bbd55 !important;
    box-shadow: 0 0 0 3px rgba(155, 189, 85, 0.25) !important;
    color: #23261f !important;
}

.fb-modal-box .nature-input::placeholder,
.fb-modal-box .nature-textarea::placeholder,
.fb-modal-box .form-control::placeholder,
.fb-modal-box input::placeholder,
.fb-modal-box textarea::placeholder {
    color: #8f9488 !important;
    opacity: 0.85 !important;
}

.fb-modal-box select option,
.fb-modal-box select optgroup {
    background: #ffffff !important;
    color: #23261f !important;
}

/* Accordion inside modal */
.fb-modal-box .accordion-item {
    background: #ffffff !important;
    border: 1px solid #e3e7df !important;
    border-radius: 18px !important;
    overflow: hidden;
}

.fb-modal-box .accordion-button {
    background: #f7f9f4 !important;
    color: #23261f !important;
    border: none !important;
    box-shadow: none !important;
}
.fb-modal-box .accordion-button:not(.collapsed) {
    background: #eef4e6 !important;
    color: #2e441b !important;
}
.fb-modal-box .accordion-button::after {
    filter: none !important;
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
    <form method="post" action="<?= $escape($baseUrl) ?>/posts" enctype="multipart/form-data" id="postCreateForm" novalidate>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Dropzone -->
                        <div class="upload-dropzone p-4 text-center rounded-4 border border-2 border-dashed" 
                             id="imageDropzone" 
                             style="background: #f7f9f4; border-color: #9bbd55 !important; cursor: pointer; transition: all 0.2s ease;">
                            <i class="bi bi-cloud-arrow-up-fill fs-1 d-block mb-2" style="color: #7aab3a;"></i>
                            <div class="fw-semibold text-dark mb-1" style="font-size: 15px;">Nhấn để chọn ảnh, hoặc kéo thả file vào đây</div>
                            <div class="small mb-2" style="color: #686d62;">Hỗ trợ JPG, PNG, WEBP &lt; 2MB/ảnh. Có thể chọn cùng lúc nhiều ảnh hoặc nhấn thêm nhiều lần để tạo Album ảnh dã ngoại.</div>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" onclick="document.getElementById('imagesInput').click()">
                                <i class="bi bi-plus-circle me-1"></i>Chọn thêm hình ảnh từ máy
                            </button>
                            <input class="d-none" id="imagesInput" name="images[]" type="file" multiple accept="image/png, image/jpeg, image/webp">
                        </div>

                        <!-- Previews -->
                        <div class="row g-2 mt-3 d-none" id="imagePreviewContainer"></div>
                        <div class="small mt-2 d-none" id="imagePreviewCount" style="color: #4a4f44; font-weight: 600;"></div>

                        <!-- Cloud URL Input -->
                        <div class="mt-4 pt-3" style="border-top: 1px solid #edf0e8 !important;">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                <i class="bi bi-link-45deg me-1" style="color: #7aab3a;"></i>Hoặc dán Link ảnh Cloud (ImgBB / Cloudinary / Unsplash)
                            </label>
                            <input type="text"
                                   name="cloud_image_url"
                                   id="cloud_image_url"
                                   class="nature-input"
                                   style="height: 44px; font-size: 13.5px;"
                                   placeholder="https://i.ibb.co/... hoặc dán link ảnh trực tuyến..."
                                   value="<?= $escape($old['cloud_image_url'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <button type="button" class="nature-btn-secondary px-3 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px; font-size: 13.5px;">
                            Đóng
                        </button>
                        <button type="button" class="nature-btn-primary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px; font-size: 13.5px;">
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
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
                                       oninput="updateAllChips()"
                                       style="height: 44px; padding-right: 48px;">
                                <button type="button"
                                        class="loc-map-btn"
                                        onclick="MapPicker.open()"
                                        title="Mở bản đồ tìm kiếm vị trí"
                                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #eef4e6; border: 1px solid #c8dcb0; color: #5c8424; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                    <i class="bi bi-map-fill"></i>
                                </button>
                            </div>
                            <small class="mt-1 d-block" style="color: #686d62; font-size: 12.5px;">Bấm vào biểu tượng bản đồ bên phải ô nhập để tìm kiếm nhanh tọa độ.</small>
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
                                       oninput="updateAllChips()"
                                       style="height: 44px;">
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
                                       oninput="updateAllChips()"
                                       style="height: 44px;">
                            </div>
                        </div>
                        <div class="rounded-4 mt-3 p-3 small" style="background: #f1f7ea; border: 1px solid rgba(155, 189, 85, 0.45); color: #2e441b; line-height: 1.55;">
                            <i class="bi bi-info-circle-fill me-1" style="color: #7aab3a; font-size: 15px;"></i>Khi có tọa độ GPS, hệ thống sẽ tự động ghim vị trí lên <strong style="color: #1a2a0e;">Bản đồ địa điểm</strong> và kích hoạt <strong style="color: #1a2a0e;">Dự báo thời tiết 4 ngày tới</strong> cho người đọc!
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <button type="button" class="nature-btn-secondary px-3 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px; font-size: 13.5px;">
                            Đóng
                        </button>
                        <button type="button" class="nature-btn-primary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px; font-size: 13.5px;">
                            <i class="bi bi-check-lg me-1"></i> Xác nhận địa điểm
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════
                 MAP PICKER MODAL (OpenStreetMap + Nominatim)
                 Đặt trong #modalLocation để tránh xung đột FocusTrap của Bootstrap
            ══════════════════════════════════════════════════════════ -->
            <div id="mapPickerModal" style="display:none; position:fixed; inset:0; z-index:1070; background:rgba(0,0,0,0.6); backdrop-filter: blur(4px); align-items:center; justify-content:center;">
                <div style="background:#ffffff; border:1px solid rgba(0,0,0,0.1); border-radius:24px; width:min(640px,96vw); max-height:90vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 32px 80px rgba(0,0,0,0.35);">
                    <!-- Modal Header -->
                    <div style="padding:1.1rem 1.35rem; border-bottom:1px solid #edf0e8; display:flex; align-items:center; gap:10px; background:#fafbfa;">
                        <i class="bi bi-map-fill" style="color:#7aab3a; font-size:1.15rem;"></i>
                        <strong style="color:#23261f; flex:1; font-size:1.05rem;">Chọn địa điểm trên bản đồ</strong>
                        <button type="button" onclick="MapPicker.close()" class="btn-close" style="opacity:0.6; cursor:pointer;" aria-label="Đóng"></button>
                    </div>

                    <!-- Search bar -->
                    <div style="padding:0.85rem 1.25rem 0.5rem; border-bottom:none; display:flex; gap:8px; background:#ffffff;">
                        <input type="text" id="mapSearchInput"
                               placeholder="Nhập tên địa điểm, ví dụ: Đồi Đa Phú, Tà Xùa, Núi Bà Đen..."
                               style="flex:1; background:#ffffff; border:1px solid #dcdfd6; border-radius:12px;
                                      color:#23261f; padding:0.6rem 0.95rem; font-size:0.92rem; outline:none;"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();MapPicker.search();}">
                        <button type="button" onclick="MapPicker.search()"
                                style="background:#9bbd55; border:none; border-radius:12px; color:#1e2b18; font-weight:600;
                                       padding:0.6rem 1.25rem; cursor:pointer; font-size:0.9rem; white-space:nowrap; display:inline-flex; align-items:center; gap:4px; box-shadow: 0 2px 8px rgba(155,189,85,0.35);">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                    </div>

                    <!-- Search results -->
                    <div id="mapSearchResults" style="max-height:240px; overflow-y:auto; border-bottom:1px solid #edf0e8; background:#ffffff;"></div>

                    <!-- Selected info -->
                    <div id="mapSelectedInfo" style="padding:0.75rem 1.25rem; background:#f1f7ea; border-bottom:1px solid rgba(155,189,85,0.3); display:none; align-items:center; gap:8px;">
                        <i class="bi bi-geo-alt-fill" style="color:#7aab3a;"></i>
                        <span id="mapSelectedText" style="color:#2e441b; font-size:0.88rem; flex:1; font-weight:500;"></span>
                    </div>

                    <!-- Footer -->
                    <div style="padding:0.85rem 1.25rem; display:flex; justify-content:flex-end; gap:8px; background:#fafbfa;">
                        <button type="button" onclick="MapPicker.close()"
                                class="nature-btn-secondary px-3 py-1.5"
                                style="border-radius:12px; font-size:0.9rem;">
                            Hủy
                        </button>
                        <button type="button" id="mapConfirmBtn" onclick="MapPicker.confirm()" disabled
                                style="background:#9bbd55; border:none; border-radius:12px; color:#1e2b18; font-weight:600;
                                       padding:0.5rem 1.35rem; cursor:pointer; opacity:0.5; font-size:0.9rem; box-shadow: 0 2px 8px rgba(155,189,85,0.3);">
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small mb-3" style="color: #686d62; font-size: 13.5px;">Cung cấp các thông số thực tế để giúp cộng đồng dễ dàng lọc bài viết và chuẩn bị hành trang.</p>

                        <div class="row g-3 mb-3">
                            <!-- Loại hình -->
                            <div class="col-12 col-md-4">
                                <label for="activity_type" class="nature-label">Loại hình dã ngoại</label>
                                <select class="nature-input" id="activity_type" name="activity_type" onchange="updateAllChips()" style="height: 44px;">
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
                                <select class="nature-input" id="difficulty" name="difficulty" onchange="updateAllChips()" style="height: 44px;">
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
                                <select class="nature-input" id="region" name="region" onchange="updateAllChips()" style="height: 44px;">
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
                                           value="<?= $escape($customRegVal) ?>" oninput="updateAllChips()"
                                           style="height: 44px;">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-5">
                                <label for="route" class="nature-label">Cung đường di chuyển</label>
                                <input type="text" class="nature-input" id="route" name="route" 
                                       placeholder="VD: Hà Nội - Đèo Đá Trắng - Tà Xùa" 
                                       value="<?= $escape($old['route'] ?? '') ?>" oninput="updateAllChips()"
                                       style="height: 44px;">
                            </div>
                            <div class="col-12 col-md-3">
                                <label for="trip_duration" class="nature-label">Thời gian</label>
                                <input type="text" class="nature-input" id="trip_duration" name="trip_duration" 
                                       placeholder="VD: 2 ngày 1 đêm" 
                                       value="<?= $escape($old['trip_duration'] ?? '') ?>" oninput="updateAllChips()"
                                       style="height: 44px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="estimated_cost" class="nature-label">Chi phí dự tính</label>
                                <input type="text" class="nature-input" id="estimated_cost" name="estimated_cost" 
                                       placeholder="VD: 1.200.000 đ/người" 
                                       value="<?= $escape($old['estimated_cost'] ?? '') ?>" oninput="updateAllChips()"
                                       style="height: 44px;">
                            </div>
                        </div>

                        <div>
                            <label for="gear_needed" class="nature-label">Đồ cần mang (Checklist trang bị mang theo)</label>
                            <textarea class="nature-textarea" id="gear_needed" name="gear_needed" rows="3" 
                                      placeholder="VD: Lều 2 lớp, túi ngủ nhiệt, giày bám tốt, áo gió, đèn pin đội đầu, bình nước 2L..."
                                      oninput="updateAllChips()"><?= $escape($old['gear_needed'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <button type="button" class="nature-btn-secondary px-3 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px; font-size: 13.5px;">
                            Đóng
                        </button>
                        <button type="button" class="nature-btn-primary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px; font-size: 13.5px;">
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
                            <span class="badge rounded-pill" id="modalToolsCountBadge" style="background: #eef4e6; color: #43691a; border: 1px solid #c5deb0; font-size: 11.5px; font-weight: 700;">0</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <!-- KHỐI 1: TỰ NHẬP CÔNG CỤ CÁ NHÂN -->
                        <div class="p-3 mb-3 rounded-4 border" style="background: #f8faf5; border-color: #e3e7df !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                <span class="fw-semibold small" style="color: #23261f; font-size: 13.5px;">
                                    <i class="bi bi-bag-check-fill me-1" style="color: #7aab3a;"></i>Thêm dụng cụ cá nhân bạn dùng
                                </span>
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1 shadow-sm" onclick="addCustomToolRow()" style="font-size: 12.5px; font-weight: 600; background: #eef4e6; border: 1px solid #9bbd55; color: #3b5c1c;">
                                    <i class="bi bi-plus-circle-fill"></i> Thêm món đồ
                                </button>
                            </div>
                            <div id="customToolsContainer" class="d-flex flex-column gap-3 mt-2"></div>
                            <div id="emptyToolsNotice" class="text-center py-3 small" style="background: #ffffff; border-radius: 14px; border: 1px dashed #dcdfd6; color: #686d62; padding: 1rem;">
                                <span>Chưa có món đồ cá nhân nào. Hãy bấm <strong style="color: #23261f;">"Thêm món đồ"</strong> để chia sẻ lều, balo, bếp... kèm link mua Shopee/Lazada!</span>
                            </div>
                        </div>

                        <!-- KHỐI 2: CHỌN TỪ KHO DỤNG CỤ CỘNG ĐỒNG CÓ SẴN -->
                        <?php if (!empty($products)): ?>
                            <div class="accordion border-0" id="accordionExistingTools">
                                <div class="accordion-item rounded-4 border overflow-hidden" style="background: #ffffff; border-color: #e3e7df !important;">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2.5 px-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExistingTools" aria-expanded="false" style="font-size: 13.5px; background: #f7f9f4; color: #23261f !important; box-shadow: none;">
                                            <i class="bi bi-collection me-2" style="color: #7aab3a;"></i> Hoặc chọn từ kho dụng cụ cộng đồng sẵn có (<?= count($products) ?> sản phẩm)
                                        </button>
                                    </h2>
                                    <div id="collapseExistingTools" class="accordion-collapse collapse" data-bs-parent="#accordionExistingTools">
                                        <div class="accordion-body p-3">
                                            <div class="mb-2">
                                                <input type="text" id="toolSearchInput" class="form-control form-control-sm rounded-pill" 
                                                       style="background: #ffffff; border-color: #dcdfd6; color: #23261f;"
                                                       placeholder="Tìm nhanh lều, balo, bếp, đèn pin..." oninput="filterToolsList(this.value)">
                                            </div>
                                            <div class="row row-cols-1 row-cols-md-2 g-2" id="toolsCheckboxGrid" style="max-height: 240px; overflow-y: auto;">
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
                                                        <label class="d-flex align-items-center gap-2 p-2 rounded-3 border h-100 position-relative" style="cursor: pointer; background: #ffffff; border-color: #e3e7df !important; transition: all 0.2s ease;">
                                                            <input type="checkbox" name="product_ids[]" value="<?= $pId ?>" 
                                                                   class="form-check-input mt-0 tool-checkbox flex-shrink-0" 
                                                                   <?= $isChecked ? 'checked' : '' ?> onchange="updateSelectedToolsCounter()">
                                                            <img src="<?= $escape($toolImg) ?>" alt="<?= $escape($prod['name']) ?>" class="rounded-2 object-fit-cover flex-shrink-0" style="width: 38px; height: 38px;">
                                                            <div class="min-w-0 flex-grow-1" style="font-size: 12.5px; overflow: hidden;">
                                                                <div class="tool-name-marquee-container">
                                                                    <div class="fw-semibold tool-name-marquee-text" style="color: #23261f;" title="<?= $escape($prod['name']) ?>"><?= $escape($prod['name']) ?></div>
                                                                </div>
                                                                <div class="fw-bold small" style="color: #5c8424;"><?= number_format((float)($prod['price'] ?? 0), 0, ',', '.') ?> đ</div>
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
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <button type="button" class="nature-btn-secondary px-3 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px; font-size: 13.5px;">
                            Đóng
                        </button>
                        <button type="button" class="nature-btn-primary px-4 py-2" data-bs-dismiss="modal" style="width: auto; height: 42px; font-size: 13.5px;">
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
    padding: 0.7rem 1.25rem;
    cursor: pointer;
    border-bottom: 1px solid #edf0e8;
    transition: background 0.15s;
    color: #475569;
    font-size: 0.88rem;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: #ffffff;
}
.map-result-item:hover { background: rgba(155, 189, 85, 0.12); color: #1e293b; }
.map-result-item.selected { background: rgba(155, 189, 85, 0.22); border-left: 3px solid #7aab3a; }
.map-result-item i { color: #7aab3a; margin-top: 3px; flex-shrink: 0; font-size: 1rem; }
.map-result-item strong { display: block; font-size: 0.92rem; color: #1e293b; font-weight: 600; }
.map-result-item span { font-size: 0.8rem; color: #64748b; line-height: 1.35; display: block; margin-top: 2px; }
.map-spinner { padding: 1.25rem; text-align: center; color: #64748b; font-size: 0.88rem; }
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
    if (window.composerDataTransfer) {
        window.composerDataTransfer = new DataTransfer();
    }
    if (imagesInput) {
        imagesInput.value = '';
        try {
            if (window.composerDataTransfer) imagesInput.files = window.composerDataTransfer.files;
        } catch (e) {}
    }
    if (cloudInput) cloudInput.value = '';
    if (prevBox) { prevBox.innerHTML = ''; prevBox.classList.add('d-none'); }
    if (countBox) countBox.classList.add('d-none');
    if (thumbsStrip) { thumbsStrip.innerHTML = ''; thumbsStrip.classList.add('d-none'); }
    updateAllChips();
}

// ══════════════════════════════════════════════════════════════
// MAP PICKER — Đa tầng Web Service Geocoding & Dã Ngoại Việt Nam
// Tự động chuyển đổi Photon (OpenStreetMap) -> Open-Meteo -> Nominatim -> Offline Spots
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
        if (searchInput()) {
            searchInput().value = curLoc;
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

        // Hiển thị ngay các địa điểm gợi ý ban đầu để người dùng chọn nhanh không cần gõ
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
        if (typeof updateAllChips === 'function') updateAllChips();
    }

    async function search() {
        const q = searchInput()?.value?.trim();
        if (!q) return;

        resultsBox().innerHTML = '<div class="map-spinner"><i class="bi bi-hourglass-split me-1"></i>Đang tìm kiếm địa điểm...</div>';
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

        // Tầng 1: Danh mục địa điểm Dã ngoại Việt Nam tích hợp sẵn (Tức thì, 0ms, không phụ thuộc mạng)
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

        // Tầng 2: Web Service Komoot Photon API (Dữ liệu OpenStreetMap toàn cầu, miễn phí, hỗ trợ CORS, siêu tốc)
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
                        const displayName = parts.join(', ');
                        const coords = f.geometry && f.geometry.coordinates;
                        if (coords && coords.length >= 2) {
                            addResult({
                                short_name: name,
                                display_name: displayName,
                                lat: parseFloat(coords[1]),
                                lon: parseFloat(coords[0])
                            });
                        }
                    });
                }
            }
        } catch (err) {
            console.warn('Photon geocoding fallback warning:', err);
        }

        // Tầng 3: Web Service Open-Meteo Geocoding (Tỉnh thành / Quận huyện Việt Nam)
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
                console.warn('Open-Meteo geocoding warning:', err);
            }
        }

        // Tầng 4: Nominatim OpenStreetMap (Nếu mạng nội hạt phân giải được)
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
                console.warn('Nominatim fallback warning:', err);
            }
        }

        // Tầng 5: Nếu không có kết quả từ bất kỳ nguồn nào: Cho phép người dùng xác nhận tên tùy chỉnh
        if (results.length === 0) {
            const safeQ = q.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            const safeArg = q.replace(/'/g, "\\'");
            resultsBox().innerHTML = `
                <div class="p-4 text-center">
                    <i class="bi bi-geo-alt fs-2 text-success d-block mb-2"></i>
                    <div style="font-size:0.92rem;color:#1e293b;">Chưa có tọa độ tự động cho <strong>"${safeQ}"</strong>.</div>
                    <div class="small text-muted mt-1 mb-3">Bạn có thể gán tên này vào bài viết và nhập tọa độ thủ công:</div>
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
        if (typeof updateAllChips === 'function') updateAllChips();
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
    card.style.background = '#ffffff';
    card.style.borderColor = '#e0e4da';
    card.style.boxShadow = '0 4px 14px rgba(0, 0, 0, 0.05)';

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
                '<label class="form-label small fw-semibold text-dark mb-1">Tên dụng cụ / Thiết bị <span class="text-danger">*</span></label>' +
                '<input type="text" name="custom_tool_name[]" value="' + nameVal + '" class="form-control form-control-sm rounded-3" placeholder="VD: Bếp ga dã ngoại Fire-Maple" oninput="updateAllChips()">' +
            '</div>' +
            '<div class="col-12 col-md-5">' +
                '<label class="form-label small fw-semibold text-dark mb-1">Giá tham khảo (VNĐ)</label>' +
                '<div class="input-group input-group-sm">' +
                    '<input type="number" name="custom_tool_price[]" value="' + priceVal + '" class="form-control form-control-sm rounded-start-3" placeholder="VD: 350000" min="0" step="1000">' +
                    '<span class="input-group-text rounded-end-3" style="background: #f4f6f0; border: 1px solid #dcdfd6; color: #4a4f44; font-weight: 600;">đ</span>' +
                '</div>' +
            '</div>' +
            '<div class="col-12">' +
                '<label class="form-label small fw-semibold text-dark mb-1"><i class="bi bi-link-45deg me-1" style="color: #7aab3a;"></i>Link mua (Shopee / Lazada...)</label>' +
                '<input type="text" inputmode="url" name="custom_tool_link[]" value="' + linkVal + '" class="form-control form-control-sm rounded-3" placeholder="https://shopee.vn/...">' +
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
                '<label class="form-label small fw-semibold text-dark mb-1">Đánh giá ngắn khi sử dụng</label>' +
                '<input type="text" name="custom_tool_desc[]" value="' + descVal + '" class="form-control form-control-sm rounded-3" placeholder="VD: Lửa mạnh, cản gió tốt, gập gọn nhẹ...">' +
            '</div>' +
            '<div class="col-12 mt-2 pt-2 border-top" style="border-color: #edf0e8 !important;">' +
                '<div class="form-check d-flex align-items-center gap-2 m-0">' +
                    '<input class="form-check-input mt-0" type="checkbox" ' + (data.is_shared === 0 ? '' : 'checked') + ' style="cursor: pointer; width: 1.15em; height: 1.15em;" onchange="this.nextElementSibling.value = this.checked ? \'1\' : \'0\'">' +
                    '<input type="hidden" name="custom_tool_is_shared[]" value="' + (data.is_shared === 0 ? '0' : '1') + '">' +
                    '<label class="form-check-label small text-dark fw-medium" style="cursor: pointer; user-select: none;">' +
                        '<i class="bi bi-share me-1" style="color: #7aab3a;"></i>Chia sẻ vào kho dụng cụ cộng đồng' +
                    '</label>' +
                '</div>' +
            '</div>' +
        '</div>';

    card.innerHTML = html;
    container.appendChild(card);
    updateAllChips();
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
// IMAGE DROPZONE & MULTI-IMAGE PREVIEWS (Instagram / Facebook Style)
// ══════════════════════════════════════════════════════════════
window.composerDataTransfer = new DataTransfer();

window.removeComposerFile = function(indexToRemove) {
    if (!window.composerDataTransfer) return;
    var newDt = new DataTransfer();
    var currentFiles = window.composerDataTransfer.files;
    for (var i = 0; i < currentFiles.length; i++) {
        if (i !== indexToRemove) {
            newDt.items.add(currentFiles[i]);
        }
    }
    window.composerDataTransfer = newDt;
    var input = document.getElementById('imagesInput');
    if (input) {
        input.files = window.composerDataTransfer.files;
    }
    window.renderComposerPreviews();
};

document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('imageDropzone');
    const input    = document.getElementById('imagesInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const countText = document.getElementById('imagePreviewCount');
    const thumbsStrip = document.getElementById('fbThumbsStrip');

    if (dropzone && input) {
        dropzone.addEventListener('click', function(e) {
            if (e.target.tagName !== 'BUTTON') {
                input.click();
            }
        });

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

        function handleNewFiles(fileList) {
            if (!fileList || fileList.length === 0) return;
            Array.from(fileList).forEach(function(file) {
                if (!file.type.startsWith('image/')) return;
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ảnh "' + file.name + '" vượt quá 2MB. Vui lòng chọn ảnh nhỏ hơn.');
                    return;
                }
                // Tránh thêm ảnh trùng lặp y hệt
                var isDup = false;
                for (var i = 0; i < window.composerDataTransfer.files.length; i++) {
                    var f = window.composerDataTransfer.files[i];
                    if (f.name === file.name && f.size === file.size && f.lastModified === file.lastModified) {
                        isDup = true;
                        break;
                    }
                }
                if (!isDup) {
                    window.composerDataTransfer.items.add(file);
                }
            });
            input.files = window.composerDataTransfer.files;
            window.renderComposerPreviews();
        }

        dropzone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                handleNewFiles(dt.files);
            }
        });

        input.addEventListener('change', function() {
            if (input.files && input.files.length > 0) {
                handleNewFiles(input.files);
            }
        });

        window.renderComposerPreviews = function() {
            var files = window.composerDataTransfer ? window.composerDataTransfer.files : [];
            var numFiles = files.length;

            if (numFiles === 0) {
                if (previewContainer) {
                    previewContainer.classList.add('d-none');
                    previewContainer.innerHTML = '';
                }
                if (countText) {
                    countText.classList.add('d-none');
                    countText.innerHTML = '';
                }
                if (thumbsStrip) {
                    thumbsStrip.classList.add('d-none');
                    thumbsStrip.innerHTML = '';
                }
                updateAllChips();
                return;
            }

            if (previewContainer) {
                previewContainer.innerHTML = '';
                previewContainer.classList.remove('d-none');
            }
            if (countText) {
                countText.classList.remove('d-none');
                countText.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i>Đã chọn <strong>' + numFiles + '</strong> hình ảnh cho bài viết này.';
            }
            if (thumbsStrip) {
                thumbsStrip.innerHTML = '';
                thumbsStrip.classList.remove('d-none');
            }

            Array.from(files).forEach(function(file, index) {
                if (!file.type.startsWith('image/')) return;
                var reader = new FileReader();
                reader.onload = function(e) {
                    var dataUrl = e.target.result;

                    // Preview trong Modal
                    if (previewContainer) {
                        var col = document.createElement('div');
                        col.className = 'col-6 col-sm-4 col-md-3';
                        col.innerHTML = 
                            '<div class="position-relative rounded-3 overflow-hidden border shadow-sm" style="height: 90px; background: #000;">' +
                                '<img src="' + dataUrl + '" alt="Ảnh ' + (index+1) + '" style="width:100%;height:100%;object-fit:cover;">' +
                                '<span class="badge bg-dark bg-opacity-75 position-absolute bottom-0 start-0 m-1" style="font-size:10px;">#' + (index+1) + '</span>' +
                                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" ' +
                                        'style="width:22px;height:22px;font-size:13px;line-height:1;box-shadow:0 2px 5px rgba(0,0,0,0.4);" ' +
                                        'onclick="removeComposerFile(' + index + ')" title="Xóa ảnh này">&times;</button>' +
                            '</div>';
                        previewContainer.appendChild(col);
                    }

                    // Preview trong Composer Card chính
                    if (thumbsStrip) {
                        var card = document.createElement('div');
                        card.className = 'fb-thumb-card';
                        card.innerHTML = 
                            '<img src="' + dataUrl + '" alt="Thumb ' + (index+1) + '" onclick="openModal(\'modalImages\')">' +
                            '<span class="fb-thumb-idx">#' + (index+1) + '</span>' +
                            '<button type="button" class="fb-thumb-del" onclick="event.stopPropagation(); removeComposerFile(' + index + ');" title="Xóa ảnh này">&times;</button>';
                        thumbsStrip.appendChild(card);

                        // Nếu là item cuối cùng, thêm nút "+ Thêm ảnh"
                        if (index === numFiles - 1) {
                            var addBtn = document.createElement('div');
                            addBtn.className = 'fb-thumb-add-btn';
                            addBtn.title = 'Chọn thêm ảnh từ máy';
                            addBtn.onclick = function() { input.click(); };
                            addBtn.innerHTML = '<i class="bi bi-plus-lg fs-5"></i><span>Thêm ảnh</span>';
                            thumbsStrip.appendChild(addBtn);
                        }
                    }
                };
                reader.readAsDataURL(file);
            });

            updateAllChips();
        };
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

    // Client-side submit validation: check required fields and focus nicely
    var createForm = document.getElementById('postCreateForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            var catSelect = document.getElementById('category_id');
            var titleInput = document.getElementById('title');
            var contentInput = document.getElementById('content');

            if (!catSelect || !catSelect.value || catSelect.value === '0') {
                e.preventDefault();
                alert('Vui lòng chọn chuyên mục cho bài viết của bạn.');
                if (catSelect) {
                    catSelect.focus();
                    catSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }

            if (!titleInput || titleInput.value.trim().length < 5) {
                e.preventDefault();
                alert('Tiêu đề bài viết phải có ít nhất 5 ký tự.');
                if (titleInput) {
                    titleInput.focus();
                    titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }

            if (!contentInput || contentInput.value.trim().length < 10) {
                e.preventDefault();
                alert('Nội dung bài viết phải có ít nhất 10 ký tự.');
                if (contentInput) {
                    contentInput.focus();
                    contentInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });
    }

    // Initialize chips on first load (e.g. from $old)
    updateAllChips();
});
</script>
