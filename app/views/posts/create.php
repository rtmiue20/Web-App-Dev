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

                <!-- Location -->
                <div class="col-md-6">
                    <label for="location" class="nature-label">Địa điểm / Tọa độ</label>
                    <input class="nature-input" 
                           id="location" 
                           name="location" 
                           type="text" 
                           placeholder="Ví dụ: Tà Xùa, Sơn La"
                           value="<?= htmlspecialchars($old['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <!-- Image Upload -->
            <div class="mb-3">
                <label for="image" class="nature-label">Ảnh bìa bài viết (JPG, PNG, WEBP &lt; 2MB)</label>
                <input class="nature-input" 
                       id="image" 
                       name="image" 
                       type="file" 
                       accept="image/png, image/jpeg, image/webp" 
                       style="padding-top: 0.55rem;">
                <?php if (!empty($errors['image'])): ?>
                    <div class="text-danger small mt-1"><?= htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8') ?></div>
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

            <!-- Submit -->
            <button type="submit" class="nature-btn-primary py-3">
                <i class="bi bi-send-fill me-2"></i> Xuất bản bài viết
            </button>
        </form>
    </div>
</div>
