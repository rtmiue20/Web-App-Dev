<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$hasCoordinates = $trip['lat'] !== null && $trip['lng'] !== null;
$isLoggedIn = Session::has('user');
$spotsLeft = max(0, (int) $trip['max_participants'] - (int) $trip['participant_count']);
$hasStarted = strtotime($trip['starts_at']) <= time();
?>
<main class="app-container" style="max-width: 900px;">
    <p class="mb-3"><a class="nature-btn-secondary" href="<?= $escape($baseUrl) ?>/trips">← Danh sách chuyến đi</a></p>
    <article class="nature-card p-4 p-md-5">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <h1 class="h2"><?= $escape($trip['title']) ?></h1>
            <span class="badge text-bg-success align-self-start">
                <?= (int) $trip['participant_count'] ?>/<?= (int) $trip['max_participants'] ?> người
            </span>
        </div>
        <p><i class="bi bi-geo-alt me-1"></i><?= $escape($trip['location']) ?></p>
        <p><i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y H:i', strtotime($trip['starts_at'])) ?></p>
        <p class="text-muted">Người tổ chức: <?= $escape($trip['username']) ?></p>
        <div class="my-4"><?= nl2br($escape($trip['description'])) ?></div>

        <?php if ($isLoggedIn): ?>
            <?php if ($hasStarted): ?>
                <div class="alert alert-secondary">Chuyến đi đã bắt đầu, không thể đăng ký thêm.</div>
            <?php elseif ($joined): ?>
                <div class="alert alert-success">Bạn đã đăng ký chuyến đi này.</div>
            <?php elseif ($spotsLeft === 0): ?>
                <div class="alert alert-warning">Chuyến đi đã đủ số người.</div>
            <?php else: ?>
                <form method="post" action="<?= $escape($baseUrl) ?>/trips/<?= (int) $trip['id'] ?>/join">
                    <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                    <button class="nature-btn-primary" type="submit">Đăng ký tham gia (còn <?= $spotsLeft ?> chỗ)</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <a class="nature-btn-primary" href="<?= $escape($baseUrl) ?>/login">Đăng nhập để tham gia</a>
        <?php endif; ?>
    </article>

    <section class="nature-card p-4 mt-4">
        <h2 class="h5">Cảnh báo an toàn theo thời tiết</h2>
        <?php if ($hasCoordinates): ?>
            <p id="trip-weather-info" class="mb-2">Đang tải thời tiết tại <?= $escape($trip['location']) ?>...</p>
            <div id="trip-weather-warning" class="d-none" role="status"></div>
            <p class="small text-muted mb-0">Thời tiết có thể thay đổi; hãy kiểm tra dự báo địa phương trước khi khởi hành.</p>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    ForumAjax.fetchWeather(
                        <?= json_encode((string) $trip['lat']) ?>,
                        <?= json_encode((string) $trip['lng']) ?>,
                        document.getElementById('trip-weather-info'),
                        document.getElementById('trip-weather-warning'),
                        <?= json_encode(date('Y-m-d', strtotime($trip['starts_at']))) ?>
                    );
                });
            </script>
            <p class="small text-muted mb-0">Dự báo chỉ có trong 16 ngày tới; dữ liệu mưa không thay thế cảnh báo lũ/đường đi từ cơ quan địa phương.</p>
        <?php else: ?>
            <p class="text-muted mb-0">Người tổ chức chưa thêm tọa độ nên chưa thể tải cảnh báo thời tiết cho địa điểm này.</p>
        <?php endif; ?>
    </section>
</main>
