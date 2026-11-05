<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<style>
/* Tùy chỉnh Popup Bản đồ đẹp theo chuẩn Design System */
.custom-leaflet-popup .leaflet-popup-content-wrapper {
    background: #ffffff;
    color: #23261f;
    border-radius: 18px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 16px 36px -8px rgba(0, 0, 0, 0.4);
}
.custom-leaflet-popup .leaflet-popup-content {
    margin: 0;
    line-height: 1.45;
    min-width: 240px;
    max-width: 290px;
}
.custom-leaflet-popup .leaflet-popup-tip {
    background: #ffffff;
}
.place-popup-thumb {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}
.place-popup-body {
    padding: 12px 14px 14px;
}
.place-card-thumb {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 18px 18px 0 0;
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
        <div class="d-flex gap-2">
            <a class="nature-btn-secondary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/checklists" style="height: 42px; font-size: 13.5px;">
                <i class="bi bi-card-checklist"></i> Checklist
            </a>
            <a class="nature-btn-secondary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/trips" style="height: 42px; font-size: 13.5px;">
                <i class="bi bi-people-fill"></i> Nhóm đi chung
            </a>
            <a class="nature-btn-primary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/posts/create" style="width: auto; height: 42px; font-size: 13.5px;">
                <i class="bi bi-pin-map-fill"></i> Thêm địa điểm mới
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
            <div id="places-map" style="height: 460px; border-radius: 24px; z-index: 1; border: 1px solid rgba(255, 255, 255, 0.15);" aria-label="Bản đồ địa điểm dã ngoại tương tác"></div>
        </div>

        <!-- Places List Grid -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 fw-semibold mb-0 text-white">
                <i class="bi bi-pin-map-fill text-danger me-1"></i>Danh sách địa điểm nổi bật (<?= count($places) ?>)
            </h2>
            <span class="text-white-50 small">Nhấp để xem vị trí trên bản đồ</span>
        </div>

        <div class="row g-4">
            <?php foreach ($places as $place): ?>
                <?php
                $imgUrl = !empty($place['image_url']) ? upload_url($place['image_url']) : null;
                $rating = (float) $place['average_rating'];
                $rCount = (int) $place['review_count'];
                ?>
                <div class="col-12 col-md-6 col-lg-6">
                    <article class="nature-card h-100 d-flex flex-column p-0 overflow-hidden" style="border-radius: 28px;" id="place-card-<?= (int)$place['id'] ?>">
                        <?php if ($imgUrl): ?>
                            <img src="<?= $escape($imgUrl) ?>" alt="<?= $escape($place['title']) ?>" class="place-card-thumb">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center text-muted" style="height: 140px; background: rgba(0,0,0,0.05); border-radius: 28px 28px 0 0;">
                                <i class="bi bi-image fs-1 opacity-50"></i>
                            </div>
                        <?php endif; ?>

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

        <!-- Leaflet Script -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            var mapInstance = null;
            var markersMap = {};

            document.addEventListener('DOMContentLoaded', function() {
                var placesData = <?= json_encode($places, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
                var baseUrl = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;

                if (!document.getElementById('places-map')) return;

                mapInstance = L.map('places-map', {
                    scrollWheelZoom: true
                }).setView([16.0, 106.5], 6);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(mapInstance);

                var bounds = [];

                placesData.forEach(function(place) {
                    var lat = Number(place.lat);
                    var lng = Number(place.lng);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                    var marker = L.marker([lat, lng]).addTo(mapInstance);
                    markersMap[place.id] = marker;

                    // Custom HTML Popup Card
                    var imgHtml = place.image_url 
                        ? '<img src="' + baseUrl + '/uploads/' + encodeURIComponent(place.image_url) + '" class="place-popup-thumb" alt="' + encodeURIComponent(place.title) + '">'
                        : '';

                    var popupHtml = '<div class="custom-leaflet-popup">' +
                        imgHtml +
                        '<div class="place-popup-body">' +
                            '<div style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 1px; color: #9bbd55; font-weight: 600; margin-bottom: 2px;">' +
                                (place.category_name ? place.category_name : 'Địa điểm dã ngoại') +
                            '</div>' +
                            '<h4 style="font-size: 14.5px; font-weight: 600; color: #23261f; margin: 0 0 4px; line-height: 1.35;">' +
                                place.title +
                            '</h4>' +
                            '<div style="font-size: 12px; color: #6c757d; margin-bottom: 8px;">' +
                                '<i class="bi bi-geo-alt-fill text-danger me-1"></i>' + (place.location || 'Chưa có tên') +
                            '</div>' +
                            '<div style="display: flex; align-items: center; justify-content: space-between; padding-top: 6px; border-top: 1px solid #eee;">' +
                                '<span style="font-size: 12.5px; font-weight: 600; color: #d97706;">★ ' + Number(place.average_rating).toFixed(1) + ' <small style="color: #888; font-weight: normal;">(' + place.review_count + ')</small></span>' +
                                '<a href="' + baseUrl + '/posts/' + encodeURIComponent(place.id) + '" style="font-size: 12px; font-weight: 600; color: #2d3328; text-decoration: underline;">Xem bài viết →</a>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                    marker.bindPopup(popupHtml, {
                        className: 'custom-leaflet-popup',
                        maxWidth: 300,
                        offset: [0, -10]
                    });

                    bounds.push([lat, lng]);
                });

                if (bounds.length > 0) {
                    mapInstance.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
                }
            });

            function focusPlace(lat, lng, id) {
                if (!mapInstance) return;
                var mapEl = document.getElementById('places-map');
                if (mapEl) {
                    mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                mapInstance.flyTo([lat, lng], 13, { duration: 1.2 });
                if (markersMap[id]) {
                    setTimeout(function() {
                        markersMap[id].openPopup();
                    }, 1200);
                }
            }
        </script>
    <?php endif; ?>
</main>
