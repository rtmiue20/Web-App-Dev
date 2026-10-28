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
        dropzone.addEventListener('click', () => input.click());

        ['dragenter', 'dragover'].forEach(ev => {
            dropzone.addEventListener(ev, (e) => {
                e.preventDefault(); e.stopPropagation();
                dropzone.style.background   = 'rgba(155, 189, 85, 0.15)';
                dropzone.style.borderColor  = 'var(--color-moss, #9bbd55)';
            });
        });
        ['dragleave', 'drop'].forEach(ev => {
            dropzone.addEventListener(ev, (e) => {
                e.preventDefault(); e.stopPropagation();
                dropzone.style.background  = 'rgba(0,0,0,0.02)';
                dropzone.style.borderColor = 'rgba(0,0,0,0.15)';
            });
        });
        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                input.files = dt.files;
                renderPreviews(dt.files);
            }
        });
        input.addEventListener('change', () => renderPreviews(input.files));

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
            Array.from(files).forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
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
const MapPicker = (() => {
    let selected = null;

    const modal        = () => document.getElementById('mapPickerModal');
    const searchInput  = () => document.getElementById('mapSearchInput');
    const resultsBox   = () => document.getElementById('mapSearchResults');
    const selectedInfo = () => document.getElementById('mapSelectedInfo');
    const selectedText = () => document.getElementById('mapSelectedText');
    const confirmBtn   = () => document.getElementById('mapConfirmBtn');

    function open() {
        selected = null;
        if (resultsBox()) resultsBox().innerHTML = '';
        if (selectedInfo()) selectedInfo().style.display = 'none';
        if (confirmBtn()) { confirmBtn().disabled = true; confirmBtn().style.opacity = '0.5'; }
        if (searchInput()) {
            searchInput().value = document.getElementById('location')?.value || '';
        }
        modal().style.display = 'flex';
        setTimeout(() => searchInput()?.focus(), 80);
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
            data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'map-result-item';
                const shortName = item.display_name.split(',')[0].trim();
                const address   = item.display_name;
                div.innerHTML = `<i class="bi bi-geo-alt-fill"></i><div><strong>${shortName}</strong><span>${address}</span></div>`;
                div.addEventListener('click', () => selectResult(item, div));
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
        document.querySelectorAll('.map-result-item').forEach(e => e.classList.remove('selected'));
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
</script>
