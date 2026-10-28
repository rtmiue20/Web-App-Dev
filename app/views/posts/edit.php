<?php
$title = 'Chỉnh sửa bài viết';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

// Collect current images for thumbnail previews
$editImages = [];
if (!empty($post['images']) && is_array($post['images'])) {
    foreach ($post['images'] as $img) {
        $url = $img['image_url'] ?? '';
        if (!empty($url)) {
            $editImages[] = str_starts_with($url, 'http') ? $url : $baseUrl . '/' . ltrim($url, '/');
        }
    }
}
if (empty($editImages) && !empty($post['image_url'])) {
    $url = $post['image_url'];
    $editImages[] = str_starts_with($url, 'http') ? $url : $baseUrl . '/' . ltrim($url, '/');
}
?>
<div class="app-container" style="max-width: 800px;">
    <div class="mb-4">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$post['id'] ?>" class="nature-btn-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i> Quay lại chi tiết bài viết
        </a>
        <h1 class="page-title">Chỉnh sửa bài viết</h1>
        <p class="page-subtitle">Cập nhật thông tin, hình ảnh hoặc kinh nghiệm dã ngoại của bạn.</p>
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

            <!-- ── Images Section ──────────────────────────────────────── -->
            <div class="mb-4">
                <label class="nature-label">Ảnh bài viết</label>

                <?php if (!empty($editImages)): ?>
                    <!-- Current images thumbnails -->
                    <div class="mb-3">
                        <p class="text-muted small mb-2">
                            <i class="bi bi-images me-1"></i>
                            Ảnh hiện tại (<?= count($editImages) ?> ảnh) — tải lên ảnh mới sẽ <strong>thêm vào</strong> bên cạnh:
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($editImages as $imgSrc): ?>
                                <img src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Ảnh bài viết"
                                     style="width: 90px; height: 64px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(255,255,255,0.15); cursor: zoom-in;"
                                     onclick="window.open(this.src,'_blank')">
                            <?php endforeach; ?>
                        </div>
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
                            <i class="bi bi-tools me-1 text-success"></i>Dụng cụ &amp; Thiết bị sử dụng trong chuyến đi (Tools)
                        </label>
                        <div class="text-muted" style="font-size: 12px;">
                            Chọn những dụng cụ hoặc thiết bị bạn đã mang theo để chia sẻ kinh nghiệm cùng cộng đồng.
                        </div>
                    </div>
                    <span class="badge rounded-pill text-bg-light border px-3 py-1 text-muted" style="font-size: 11.5px;">
                        Đã chọn: <strong id="selectedToolsCount" class="text-success"><?= count($selectedProductIds ?? []) ?></strong> dụng cụ
                    </span>
                </div>

                <?php if (!empty($products)): ?>
                    <div class="rounded-4 p-3 border" style="background: rgba(0,0,0,0.02); border-color: rgba(0,0,0,0.12) !important;">
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
                        <div class="row row-cols-1 row-cols-md-2 g-2" id="toolsCheckboxGrid" style="max-height: 280px; overflow-y: auto; padding-right: 4px;">
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
                                    <label class="d-flex align-items-center gap-2 p-2 rounded-3 border bg-white h-100 position-relative" 
                                           style="cursor: pointer; transition: all 0.2s ease; border-color: rgba(0,0,0,0.08) !important;">
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
                                        <div class="min-w-0 flex-grow-1" style="font-size: 13px;">
                                            <div class="fw-semibold text-truncate text-dark" title="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>
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
function previewEditImages(files) {
    const grid = document.getElementById('editPreviewGrid');
    grid.innerHTML = '';
    if (!files || files.length === 0) return;
    Array.from(files).forEach((file, i) => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative; display:inline-block;';
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:90px;height:64px;object-fit:cover;border-radius:10px;border:2px solid var(--color-moss);';
            const badge = document.createElement('span');
            badge.textContent = (i + 1);
            badge.style.cssText = 'position:absolute;top:3px;right:3px;background:var(--color-moss);color:#fff;font-size:0.62rem;padding:1px 5px;border-radius:6px;';
            wrap.appendChild(img);
            wrap.appendChild(badge);
            grid.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });

    // Update dropzone text
    const dz = document.getElementById('editDropzone');
    if (dz) {
        const span = dz.querySelector('span');
        if (span) span.textContent = files.length + ' ảnh đã chọn — bấm để thay đổi';
    }
}

function handleEditDrop(e) {
    e.preventDefault();
    const dz = document.getElementById('editDropzone');
    if (dz) { dz.style.borderColor = 'rgba(255,255,255,0.25)'; dz.style.background = ''; }

    const input = document.getElementById('editImagesInput');
    if (!input || !e.dataTransfer?.files?.length) return;

    // Assign dropped files to input
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
    input.files = dt.files;
    previewEditImages(input.files);
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
        if (searchInput()) searchInput().value = document.getElementById('location')?.value || '';
        modal().style.display = 'flex';
        setTimeout(() => searchInput()?.focus(), 80);
    }

    function close() { modal().style.display = 'none'; }

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
                resultsBox().innerHTML = '<div class="map-spinner"><i class="bi bi-search me-1"></i>Không tìm thấy địa điểm. Thử từ khóa khác hoặc thêm tên quốc gia.</div>';
                return;
            }
            resultsBox().innerHTML = '';
            data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'map-result-item';
                const shortName = item.display_name.split(',')[0].trim();
                div.innerHTML = `<i class="bi bi-geo-alt-fill"></i><div><strong>${shortName}</strong><span>${item.display_name}</span></div>`;
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
        document.querySelectorAll('.map-result-item').forEach(e => e.classList.remove('selected'));
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

// Xử lý bộ chọn Dụng cụ (Tools Selection)
function updateSelectedToolsCounter() {
    var checkedBoxes = document.querySelectorAll('.tool-checkbox:checked');
    var badge = document.getElementById('selectedToolsCount');
    if (badge) {
        badge.textContent = checkedBoxes.length;
    }
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
}

document.addEventListener('DOMContentLoaded', function() {
    updateSelectedToolsCounter();
});
</script>

