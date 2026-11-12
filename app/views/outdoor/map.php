<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!-- Leaflet CSS (jsdelivr CDN ổn định) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" crossorigin="">

<style>
/* ── Container bản đồ ── */
#places-map {
    height: 480px;
    border-radius: 24px;
    z-index: 1;
    border: 1px solid rgba(255, 255, 255, 0.15);
    overflow: hidden;
    background: #1a2217;
    box-shadow: 0 20px 48px -10px rgba(0, 0, 0, 0.60);
}

/* Đảm bảo các pane của Leaflet không bị ảnh hưởng bởi border-radius cha */
#places-map .leaflet-pane,
#places-map .leaflet-tile-pane,
#places-map .leaflet-tile-container {
    border-radius: 0 !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
}

/* ── Popup Bản đồ Liquid Glass & Nature ── */
.custom-leaflet-popup .leaflet-popup-content-wrapper {
    background: #ffffff;
    color: #23261f;
    border-radius: 20px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 20px 48px -8px rgba(0, 0, 0, 0.45), 0 4px 12px rgba(0, 0, 0, 0.20);
    border: 1px solid rgba(0, 0, 0, 0.06);
}
.custom-leaflet-popup .leaflet-popup-content {
    margin: 0;
    line-height: 1.45;
    min-width: 250px;
    max-width: 300px;
}
.custom-leaflet-popup .leaflet-popup-tip-container {
    margin-top: -1px;
}
.custom-leaflet-popup .leaflet-popup-tip {
    background: #ffffff;
    box-shadow: none;
}

/* ── Custom Moss Pin Marker ── */
.moss-map-pin {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    filter: drop-shadow(0 6px 10px rgba(0,0,0,0.45));
}
.moss-map-pin:hover {
    transform: scale(1.18) translateY(-4px);
}
.moss-map-pin svg {
    width: 36px;
    height: 36px;
}

.place-popup-thumb {
    width: 100%;
    height: 125px;
    object-fit: cover;
    display: block;
}
.place-card-thumb {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 28px 28px 0 0;
}

/* ── Leaflet controls styling (Zoom & Layer switcher) ── */
.leaflet-control-zoom,
.leaflet-control-layers {
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    border-radius: 14px !important;
    overflow: hidden !important;
    background: rgba(22, 32, 16, 0.85) !important;
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.40) !important;
    color: #ffffff !important;
}
.leaflet-control-zoom a {
    background: transparent !important;
    color: rgba(255, 255, 255, 0.90) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    font-weight: 500 !important;
    transition: background 0.15s ease !important;
}
.leaflet-control-zoom a:hover {
    background: rgba(155, 189, 85, 0.3) !important;
    color: #ffffff !important;
}
.leaflet-control-layers-toggle {
    filter: invert(1);
    opacity: 0.85;
}
.leaflet-control-layers-expanded {
    padding: 10px 14px !important;
    font-size: 13px !important;
}
.leaflet-control-layers-base label {
    margin-bottom: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ── Leaflet attribution ── */
.leaflet-control-attribution {
    background: rgba(14, 20, 11, 0.75) !important;
    backdrop-filter: blur(8px) !important;
    color: rgba(255, 255, 255, 0.65) !important;
    border-radius: 8px 0 0 0 !important;
    padding: 2px 8px !important;
    font-size: 10.5px !important;
}
.leaflet-control-attribution a {
    color: rgba(155, 189, 85, 0.90) !important;
    text-decoration: none;
}
</style>

<main class="app-container" style="max-width: 1140px;">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 page-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(155, 189, 85, 0.18); color: var(--color-moss); border: 1px solid rgba(155, 189, 85, 0.35);">
                    <i class="bi bi-geo-alt-fill me-1"></i>Bản đồ tương tác
                </span>
            </div>
            <h1 class="page-title mb-1">Bản đồ địa điểm dã ngoại</h1>
            <p class="page-subtitle mb-0">Khám phá các điểm cắm trại, cung đường trekking và tọa độ thiên nhiên được cộng đồng chia sẻ và đánh giá.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <?php if (class_exists('Auth') && Auth::check() && Auth::isGuest()): ?>
                <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1" style="background: rgba(255, 255, 255, 0.12); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(255, 255, 255, 0.22); font-size: 13px; font-weight: 500;">
                    <i class="bi bi-eye me-1"></i> Tài khoản Khách (Chỉ xem)
                </span>
            <?php else: ?>
                <a class="nature-btn-primary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/posts/create" style="width: auto; height: 42px; font-size: 13.5px;">
                    <i class="bi bi-pin-map-fill"></i> Thêm địa điểm mới
                </a>
            <?php endif; ?>
            <a class="nature-btn-secondary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/checklists" style="height: 42px; font-size: 13.5px;">
                <i class="bi bi-card-checklist"></i> Checklist
            </a>
            <a class="nature-btn-secondary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/trips" style="height: 42px; font-size: 13.5px;">
                <i class="bi bi-people-fill"></i> Nhóm đi chung
            </a>
        </div>
    </div>

    <?php if (empty($places)): ?>
        <div class="nature-card text-center py-5" style="border-radius: 28px;">
            <i class="bi bi-map fs-1 text-muted d-block mb-3"></i>
            <h3 class="h5 fw-normal mb-1" style="color: var(--color-ink);">Chưa có địa điểm trên bản đồ</h3>
            <p class="text-muted small mb-3">Hãy chia sẻ bài viết đầu tiên và thêm tọa độ GPS để ghim lên bản đồ dã ngoại.</p>
            <a href="<?= $escape($baseUrl) ?>/posts/create" class="nature-btn-primary px-4 py-2 d-inline-block" style="width: auto; height: 42px;">
                Đăng bài viết kèm tọa độ
            </a>
        </div>
    <?php else: ?>
        <!-- Map Container -->
        <div class="position-relative mb-4">
            <div id="places-map" aria-label="Bản đồ địa điểm dã ngoại tương tác"></div>
        </div>

        <?php
        $fallbackImages = [
            $baseUrl . '/assets/img/tent-forest.png',
            $baseUrl . '/assets/img/ta-xua-trek.png',
            $baseUrl . '/assets/img/moss-form.png',
        ];

        // Chuẩn hóa và gán display_image_url cho tất cả địa điểm để hiển thị đồng bộ trên thẻ và ghim bản đồ
        foreach ($places as $idx => &$pl) {
            $pl['display_image_url'] = !empty($pl['image_url'])
                ? upload_url($pl['image_url'])
                : $fallbackImages[$idx % count($fallbackImages)];
        }
        unset($pl);
        ?>

        <!-- Places List Grid -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 fw-semibold mb-0 text-white">
                <i class="bi bi-pin-map-fill text-danger me-1"></i>Danh sách địa điểm nổi bật (<?= $totalPlaces ?? count($places) ?>)
            </h2>
            <span class="text-white-50 small">Nhấp để xem vị trí trên bản đồ</span>
        </div>

        <div class="row g-4">
            <?php foreach ($places as $idx => $place): ?>
                <?php
                $cardFallback = $fallbackImages[$idx % count($fallbackImages)];
                $imgUrl = !empty($place['display_image_url']) ? $place['display_image_url'] : $cardFallback;
                $rating = (float) $place['average_rating'];
                $rCount = (int) $place['review_count'];
                ?>
                <div class="col-12 col-md-6 col-lg-6">
                    <article class="nature-card h-100 d-flex flex-column p-0 overflow-hidden" style="border-radius: 28px;" id="place-card-<?= (int)$place['id'] ?>">
                        <img src="<?= $escape($imgUrl) ?>" alt="<?= $escape($place['title']) ?>" class="place-card-thumb" loading="lazy" onerror="this.onerror=null;this.src='<?= $escape($cardFallback) ?>';">

                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <?php if (!empty($place['category_name'])): ?>
                                    <span class="badge text-bg-success" style="font-size: 11px;">
                                        <?= $escape($place['category_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>

                                <span class="badge text-bg-light border" style="font-size: 12px; color: #d97706;">
                                    ★ <?= number_format($rating, 1) ?> <span class="text-muted fw-normal">(<?= $rCount ?>)</span>
                                </span>
                            </div>

                            <h3 class="h5 fw-semibold mb-1">
                                <a href="<?= $escape($baseUrl) ?>/posts/<?= (int) $place['id'] ?>" style="color: var(--color-ink); text-decoration: none;">
                                    <?= $escape($place['title']) ?>
                                </a>
                            </h3>

                            <p class="small text-muted mb-3">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= $escape($place['location'] ?: 'Chưa đặt tên địa điểm') ?>
                            </p>

                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1"
                                        onclick="focusPlace(<?= (float)$place['lat'] ?>, <?= (float)$place['lng'] ?>, <?= (int)$place['id'] ?>)" style="font-size: 12.5px;">
                                    <i class="bi bi-crosshair"></i> Xem trên bản đồ
                                </button>
                                <a href="<?= $escape($baseUrl) ?>/posts/<?= (int) $place['id'] ?>" class="nature-btn-secondary py-1 px-3 d-inline-flex align-items-center gap-1" style="font-size: 12.5px; height: 34px;">
                                    Xem bài viết <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Thanh phân trang Bản đồ địa điểm (Đồng bộ chuẩn Nature Glass giống Dụng cụ) -->
        <?php if (($totalPlaces ?? 0) > 0): ?>
            <nav aria-label="Điều hướng phân trang địa điểm" class="mt-5 mb-4">
                <ul class="pagination forum-pagination justify-content-center flex-wrap">
                    <!-- Nút trang trước -->
                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= $escape($baseUrl) ?>/places?page=<?= max(1, $currentPage - 1) ?>" 
                           tabindex="<?= ($currentPage <= 1) ? '-1' : '0' ?>"
                           aria-disabled="<?= ($currentPage <= 1) ? 'true' : 'false' ?>">
                            <i class="bi bi-chevron-left me-1"></i> Trước
                        </a>
                    </li>

                    <!-- Danh sách số trang -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                            <a class="page-link rounded-pill" 
                               href="<?= $escape($baseUrl) ?>/places?page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Nút trang sau -->
                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= $escape($baseUrl) ?>/places?page=<?= min($totalPages, $currentPage + 1) ?>" 
                           tabindex="<?= ($currentPage >= $totalPages) ? '-1' : '0' ?>"
                           aria-disabled="<?= ($currentPage >= $totalPages) ? 'true' : 'false' ?>">
                            Sau <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                </ul>

                <div class="text-center text-white-50 small mt-2">
                    Trang <?= $currentPage ?> / <?= $totalPages ?> (Hiển thị <?= count($places) ?> địa điểm / trang • Tổng số <?= $totalPlaces ?> địa điểm)
                </div>
            </nav>
        <?php endif; ?>

        <!-- Leaflet Script (jsdelivr CDN) -->
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            var mapInstance = null;
            var markersMap  = {};

            document.addEventListener('DOMContentLoaded', function () {
                var placesData = <?= json_encode($allPlaces ?? $places, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
                var baseUrl    = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;

                var mapEl = document.getElementById('places-map');
                if (!mapEl) return;

                // Cấu hình CDN fallback cho Leaflet default images nếu cần
                L.Icon.Default.imagePath = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/';

                /* ── Khởi tạo Leaflet Map ── */
                mapInstance = L.map('places-map', {
                    scrollWheelZoom: true,
                    zoomControl: true
                }).setView([16.0, 106.5], 6);

                /* ── Các lớp bản đồ 100% MIỄN PHÍ, KHÔNG CẦN API KEY, KHÔNG WATERMARK ── */
                var googleTerrain = L.tileLayer(
                    'https://mt{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}',
                    {
                        maxZoom: 20,
                        subdomains: ['0', '1', '2', '3'],
                        attribution: '&copy; Google Maps (Địa hình Dã ngoại)'
                    }
                );

                var googleRoadmap = L.tileLayer(
                    'https://mt{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
                    {
                        maxZoom: 20,
                        subdomains: ['0', '1', '2', '3'],
                        attribution: '&copy; Google Maps'
                    }
                );

                var googleSatellite = L.tileLayer(
                    'https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
                    {
                        maxZoom: 20,
                        subdomains: ['0', '1', '2', '3'],
                        attribution: '&copy; Google Maps (Vệ tinh)'
                    }
                );

                var esriTopo = L.tileLayer(
                    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
                    {
                        maxZoom: 19,
                        attribution: '&copy; Esri World Topo'
                    }
                );

                // Mặc định nạp Bản đồ Địa hình Dã ngoại (Google Terrain - rõ núi rừng, sông suối, tiếng Việt 100%)
                googleTerrain.addTo(mapInstance);

                // Thêm bộ chọn Layer (Layer Switcher) ở góc phải
                var baseMaps = {
                    "⛰️ Địa hình Dã ngoại (Mặc định)": googleTerrain,
                    "🗺️ Bản đồ Đường phố": googleRoadmap,
                    "🌲 Vệ tinh Rừng núi": googleSatellite,
                    "🧭 Bản đồ Topo (Esri)": esriTopo
                };
                L.control.layers(baseMaps, null, { position: 'topright' }).addTo(mapInstance);

                /* ── Custom SVG Pin Marker (xanh rêu dã ngoại với biểu tượng lều trại) ── */
                function createMossMarker(lat, lng) {
                    var svgIcon = L.divIcon({
                        className: '',
                        html: '<div class="moss-map-pin">'
                            + '<svg viewBox="0 0 36 44" fill="none" xmlns="http://www.w3.org/2000/svg">'
                            + '<path d="M18 0C8.058 0 0 8.058 0 18C0 28.5 18 44 18 44C18 44 36 28.5 36 18C36 8.058 27.942 0 18 0Z" fill="url(#mossGrad)"/>'
                            + '<circle cx="18" cy="18" r="14" fill="#1b2416"/>'
                            + '<path d="M18 10L24 22H12L18 10Z" fill="#9bbd55"/>'
                            + '<path d="M18 10L14 22H22L18 10Z" fill="#b9dc70"/>'
                            + '<defs>'
                            + '<linearGradient id="mossGrad" x1="0" y1="0" x2="36" y2="44" gradientUnits="userSpaceOnUse">'
                            + '<stop stop-color="#9bbd55"/>'
                            + '<stop offset="1" stop-color="#4f7a22"/>'
                            + '</linearGradient>'
                            + '</defs>'
                            + '</svg>'
                            + '</div>',
                        iconSize: [36, 44],
                        iconAnchor: [18, 42],
                        popupAnchor: [0, -38]
                    });
                    return L.marker([lat, lng], { icon: svgIcon });
                }

                /* ── Vẽ Markers và Popups ── */
                var bounds = [];
                var defaultFallback = (baseUrl ? baseUrl : '') + '/assets/img/tent-forest.png';

                placesData.forEach(function (place) {
                    var lat = Number(place.lat);
                    var lng = Number(place.lng);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                    var marker = createMossMarker(lat, lng).addTo(mapInstance);
                    markersMap[place.id] = marker;

                    // Popup Content - URL chuẩn xác từ display_image_url kèm fallback và onerror an toàn
                    var popupImgUrl = place.display_image_url
                        ? place.display_image_url
                        : (place.image_url ? (place.image_url.startsWith('http') ? place.image_url : (baseUrl ? baseUrl : '') + '/' + place.image_url.replace(/^\/+/, '')) : defaultFallback);

                    var safeTitle = (place.title || '').replace(/"/g, '&quot;');
                    var imgHtml = '<img src="' + popupImgUrl + '" class="place-popup-thumb" alt="' + safeTitle + '" loading="lazy" onerror="this.onerror=null;this.src=\'' + defaultFallback + '\';">';

                    var starsHtml = '';
                    var r = Number(place.average_rating) || 0;
                    for (var i = 1; i <= 5; i++) {
                        starsHtml += '<span style="color:' + (i <= Math.round(r) ? '#f59e0b' : '#d1d5db') + ';font-size:12px;">★</span>';
                    }

                    var popupHtml =
                        '<div>' +
                            imgHtml +
                            '<div style="padding:12px 14px 14px;">' +
                                '<div style="font-size:10px;text-transform:uppercase;letter-spacing:1.2px;color:#9bbd55;font-weight:700;margin-bottom:4px;">' +
                                    (place.category_name ? place.category_name : 'Địa điểm dã ngoại') +
                                '</div>' +
                                '<h4 style="font-size:14px;font-weight:600;color:#1a2415;margin:0 0 4px;line-height:1.35;">' +
                                    place.title +
                                '</h4>' +
                                '<div style="font-size:11.5px;color:#6b7280;margin-bottom:8px;display:flex;align-items:center;gap:4px;">' +
                                    '📍 ' + (place.location || 'Chưa có tên vị trí') +
                                '</div>' +
                                '<div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #e5e7eb;">' +
                                    '<span style="font-size:12px;display:flex;align-items:center;gap:3px;">' +
                                        starsHtml +
                                        '<span style="color:#374151;font-size:11px;margin-left:3px;">' + r.toFixed(1) + ' (' + place.review_count + ')</span>' +
                                    '</span>' +
                                    '<a href="' + baseUrl + '/posts/' + encodeURIComponent(place.id) + '" ' +
                                       'style="font-size:11.5px;font-weight:600;color:#fff;background:#2d3a24;padding:4px 12px;border-radius:999px;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background=\'#9bbd55\'" onmouseout="this.style.background=\'#2d3a24\'">' +
                                        'Xem bài viết →' +
                                    '</a>' +
                                '</div>' +
                            '</div>' +
                        '</div>';

                    marker.bindPopup(popupHtml, {
                        className: 'custom-leaflet-popup',
                        maxWidth: 290,
                        offset: [0, -6]
                    });

                    bounds.push([lat, lng]);
                });

                /* ── Fit Bounds căn vừa các ghim ── */
                if (bounds.length === 1) {
                    mapInstance.setView(bounds[0], 12);
                } else if (bounds.length > 1) {
                    mapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
                }

                /* ── Kích hoạt invalidateSize sau khi DOM hoàn tất render ── */
                setTimeout(function () {
                    mapInstance.invalidateSize();
                }, 200);
            });

            /* ── Di chuyển mượt tới địa điểm khi nhấp nút Xem trên bản đồ ── */
            function focusPlace(lat, lng, id) {
                if (!mapInstance) return;
                var mapEl = document.getElementById('places-map');
                if (mapEl) {
                    mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                mapInstance.flyTo([lat, lng], 13, { duration: 1.2, easeLinearity: 0.5 });
                if (markersMap[id]) {
                    setTimeout(function () {
                        markersMap[id].openPopup();
                    }, 1250);
                }
            }
        </script>
    <?php endif; ?>
</main>
