<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<main class="app-container" style="max-width: 1100px;">
    <div class="mb-4">
        <p class="text-uppercase text-white-50 small mb-1">Địa điểm cộng đồng chia sẻ</p>
        <h1 class="text-white h2">Bản đồ địa điểm phổ biến</h1>
        <p class="text-white-50">Địa điểm lấy từ bài viết đã có tọa độ. Điểm đánh giá được tính từ review của thành viên.</p>
    </div>

    <?php if ($places === []): ?>
        <div class="nature-card p-4">Chưa có địa điểm trên bản đồ. Hãy thêm tọa độ khi đăng bài viết về một địa điểm.</div>
    <?php else: ?>
        <div id="places-map" class="mb-4" style="height: 430px; border-radius: 20px; z-index: 1;" aria-label="Bản đồ địa điểm dã ngoại"></div>
        <section class="row g-3">
            <?php foreach ($places as $place): ?>
                <article class="col-md-6">
                    <div class="nature-card p-4 h-100">
                        <h2 class="h5"><a href="<?= $escape($baseUrl) ?>/posts/<?= (int) $place['id'] ?>"><?= $escape($place['title']) ?></a></h2>
                        <p class="mb-1"><?= $escape($place['location'] ?: 'Chưa đặt tên địa điểm') ?></p>
                        <p class="mb-0">★ <?= number_format((float) $place['average_rating'], 1) ?>
                            <span class="text-muted">(<?= (int) $place['review_count'] ?> đánh giá)</span>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var places = <?= json_encode($places, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
                var map = L.map('places-map').setView([16.2, 106.0], 5);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                var bounds = [];
                places.forEach(function(place) {
                    var lat = Number(place.lat);
                    var lng = Number(place.lng);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
                    var marker = L.marker([lat, lng]).addTo(map);
                    var popup = document.createElement('div');
                    var title = document.createElement('strong');
                    title.textContent = place.title;
                    var rating = document.createElement('div');
                    rating.textContent = '★ ' + place.average_rating + ' (' + place.review_count + ' đánh giá)';
                    var link = document.createElement('a');
                    link.href = (window.BASE_URL || '') + '/posts/' + encodeURIComponent(place.id);
                    link.textContent = 'Xem địa điểm';
                    popup.append(title, rating, link);
                    marker.bindPopup(popup);
                    bounds.push([lat, lng]);
                });
                if (bounds.length > 0) map.fitBounds(bounds, {padding: [24, 24]});
            });
        </script>
    <?php endif; ?>
</main>
