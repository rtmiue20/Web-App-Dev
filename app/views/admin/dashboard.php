<?php
$title = 'Bảng điều khiển Quản trị';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$totalPostsSafe = max(1, (int)($postCount ?? 0));
?>
<div class="app-container" style="max-width: 1080px;">
    <!-- Welcome Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="nature-badge mb-2" style="background: rgba(155, 189, 85, 0.15); color: #c4e482; border-color: rgba(155, 189, 85, 0.3);">
                <i class="bi bi-shield-check me-1"></i>Khu vực Quản trị · Stored Procedures &amp; SQL Views
            </span>
            <h1 class="page-title">Bảng điều khiển</h1>
            <p class="page-subtitle">Xin chào <strong><?= htmlspecialchars($user['username'] ?? 'Quản trị viên', ENT_QUOTES, 'UTF-8') ?></strong>! Dữ liệu thống kê bên dưới được tổng hợp thời gian thực qua các Stored Procedures &amp; SQL Views.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/create" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                <i class="bi bi-plus-circle me-1"></i> Thêm chuyên mục
            </a>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" class="nature-btn-secondary px-3 py-2 text-white border-white-20" style="height: 44px;" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Xem diễn đàn
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards (từ sp_dashboard_stats) -->
    <div class="row g-4 mb-4">
        <!-- Chuyên mục -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="nature-card h-100" style="border-radius: 28px; padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="nature-label mb-0">Chuyên mục</span>
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%; background: rgba(155, 189, 85, 0.15); color: var(--color-moss);">
                        <i class="bi bi-tags-fill fs-5"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="display-6 fw-normal" style="color: var(--color-ink);"><?= (int)($categoryCount ?? 0) ?></span>
                    <span class="text-muted small">danh mục</span>
                </div>
                <div class="pt-2 border-top">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="text-decoration-none small fw-medium" style="color: var(--color-ink);">
                        Quản lý chuyên mục &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Bài viết -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="nature-card h-100" style="border-radius: 28px; padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="nature-label mb-0">Bài viết</span>
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%; background: rgba(13, 110, 253, 0.12); color: #0d6efd;">
                        <i class="bi bi-file-earmark-text-fill fs-5"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <span class="display-6 fw-normal" style="color: var(--color-ink);"><?= (int)($postCount ?? 0) ?></span>
                    <span class="text-muted small">bài đăng</span>
                </div>
                <div class="d-flex gap-1 mb-2">
                    <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size: 10.5px;">
                        <?= (int)($publishedPosts ?? 0) ?> đã duyệt
                    </span>
                    <?php if (!empty($pendingPosts)): ?>
                        <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 10.5px;">
                            <?= (int)$pendingPosts ?> chờ duyệt
                        </span>
                    <?php endif; ?>
                </div>
                <div class="pt-2 border-top">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts" class="text-decoration-none small fw-medium" style="color: var(--color-ink);">
                        Kiểm duyệt bài viết &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Bình luận & Thảo luận -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="nature-card h-100" style="border-radius: 28px; padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="nature-label mb-0">Bình luận</span>
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%; background: rgba(32, 201, 151, 0.15); color: #198754;">
                        <i class="bi bi-chat-dots-fill fs-5"></i>
                    </span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="display-6 fw-normal" style="color: var(--color-ink);"><?= (int)($commentCount ?? 0) ?></span>
                    <span class="text-muted small">lượt thảo luận</span>
                </div>
                <div class="pt-2 border-top">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts" class="text-decoration-none small fw-medium" style="color: var(--color-ink);">
                        Xem luồng thảo luận &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Thành viên -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="nature-card h-100 position-relative" style="border-radius: 28px; padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="nature-label mb-0">Thành viên</span>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="d-inline-flex align-items-center justify-content-center text-decoration-none" style="width: 42px; height: 42px; border-radius: 50%; background: rgba(255, 193, 7, 0.15); color: #ffb703;" title="Xem danh sách thành viên">
                        <i class="bi bi-people-fill fs-5"></i>
                    </a>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="display-6 fw-normal" style="color: var(--color-ink);"><?= (int)($userCount ?? 0) ?></span>
                    <span class="text-muted small">tài khoản</span>
                </div>
                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="text-decoration-none small fw-medium" style="color: var(--color-ink);">
                        Quản lý thành viên &rarr;
                    </a>
                    <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 10.5px;">
                        <i class="bi bi-key me-1"></i>RBAC
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- BIỂU ĐỒ CHART.JS: NGƯỜI DÙNG MỚI & BÀI VIẾT MỚI THEO NGÀY (sp_stats_daily_activity qua AJAX JSON) -->
    <?php
        $chartInitial = [
            'days'         => count($dailyActivity ?? []),
            'labels'       => array_column($dailyActivity ?? [], 'date_label'),
            'new_users'    => array_column($dailyActivity ?? [], 'new_users'),
            'new_posts'    => array_column($dailyActivity ?? [], 'new_posts'),
            'new_comments' => array_column($dailyActivity ?? [], 'new_comments'),
        ];
        $jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
    ?>
    <div class="nature-card mb-4" style="border-radius: 32px; padding: 2rem;">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <h2 class="h5 fw-normal mb-0" style="color: var(--color-ink);">
                        <i class="bi bi-graph-up-arrow text-success me-1"></i>Hoạt động theo ngày
                    </h2>
                    <span class="badge rounded-pill text-bg-light border text-muted" style="font-size: 11px;">
                        <code>CALL sp_stats_daily_activity(<span id="chartDaysLabel"><?= (int)$chartInitial['days'] ?></span>)</code>
                    </span>
                </div>
                <p class="small text-muted mb-0">Số người dùng đăng ký mới và bài viết mới mỗi ngày — Chart.js, dữ liệu JSON từ <code>/api/stats/daily</code> (AJAX).</p>
            </div>
            <div class="btn-group btn-group-sm" role="group" aria-label="Khoảng thời gian thống kê" id="chartRangeGroup">
                <button type="button" class="btn btn-outline-success active" data-days="7">7 ngày</button>
                <button type="button" class="btn btn-outline-success" data-days="14">14 ngày</button>
                <button type="button" class="btn btn-outline-success" data-days="30">30 ngày</button>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge text-bg-primary-subtle text-primary-emphasis border border-primary-subtle px-3 py-2">
                <i class="bi bi-person-plus-fill me-1"></i>Người dùng mới: <strong id="chartTotalUsers">0</strong>
            </span>
            <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2">
                <i class="bi bi-file-earmark-plus-fill me-1"></i>Bài viết mới: <strong id="chartTotalPosts">0</strong>
            </span>
            <span class="badge text-bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2">
                <i class="bi bi-chat-dots-fill me-1"></i>Bình luận mới: <strong id="chartTotalComments">0</strong>
            </span>
        </div>

        <div class="position-relative" style="height: 320px;">
            <canvas id="dailyActivityChart" role="img" aria-label="Biểu đồ người dùng mới và bài viết mới theo ngày"></canvas>
            <div id="chartMessage" class="position-absolute top-50 start-50 translate-middle text-muted small text-center" style="display: none;"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        var BASE_URL = <?= json_encode($baseUrl, $jsonFlags | JSON_UNESCAPED_SLASHES) ?>;
        var initialData = <?= json_encode($chartInitial, $jsonFlags) ?>;
        var canvas = document.getElementById('dailyActivityChart');
        var messageEl = document.getElementById('chartMessage');
        var rangeGroup = document.getElementById('chartRangeGroup');
        var chart = null;

        function showMessage(text) {
            if (!messageEl) return;
            messageEl.textContent = text || '';
            messageEl.style.display = text ? 'block' : 'none';
        }

        function sum(arr) {
            return (arr || []).reduce(function (a, b) { return a + Number(b || 0); }, 0);
        }

        function updateSummary(data) {
            document.getElementById('chartTotalUsers').textContent = sum(data.new_users);
            document.getElementById('chartTotalPosts').textContent = sum(data.new_posts);
            document.getElementById('chartTotalComments').textContent = sum(data.new_comments);
            document.getElementById('chartDaysLabel').textContent = (data.labels || []).length;
        }

        function buildChart(data) {
            if (typeof window.Chart === 'undefined' || !canvas) {
                showMessage('Không tải được thư viện Chart.js (kiểm tra kết nối Internet).');
                return;
            }
            chart = new window.Chart(canvas.getContext('2d'), {
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Bài viết mới',
                            data: data.new_posts,
                            backgroundColor: 'rgba(47, 94, 46, 0.65)',
                            borderColor: '#2f5e2e',
                            borderWidth: 1,
                            borderRadius: 6,
                            order: 2
                        },
                        {
                            type: 'line',
                            label: 'Người dùng mới',
                            data: data.new_users,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.15)',
                            pointBackgroundColor: '#0d6efd',
                            tension: 0.3,
                            fill: true,
                            order: 1
                        },
                        {
                            type: 'line',
                            label: 'Bình luận mới',
                            data: data.new_comments,
                            borderColor: '#20c997',
                            borderDash: [5, 4],
                            pointBackgroundColor: '#20c997',
                            tension: 0.3,
                            hidden: true,
                            order: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: { title: function (items) { return 'Ngày ' + items[0].label; } } }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 }, title: { display: true, text: 'Số lượng' } }
                    }
                }
            });
        }

        function applyData(data) {
            updateSummary(data);
            var allZero = sum(data.new_users) + sum(data.new_posts) + sum(data.new_comments) === 0;
            showMessage(allZero ? 'Chưa có hoạt động nào trong khoảng thời gian này.' : '');
            if (!chart) {
                buildChart(data);
                return;
            }
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.new_posts;
            chart.data.datasets[1].data = data.new_users;
            chart.data.datasets[2].data = data.new_comments;
            chart.update();
        }

        function loadRange(days) {
            fetch(BASE_URL + '/api/stats/daily?days=' + encodeURIComponent(days), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json().then(function (json) { return { ok: res.ok, json: json }; }); })
                .then(function (r) {
                    if (!r.ok || !r.json || !r.json.success) {
                        throw new Error((r.json && r.json.message) || 'Không tải được dữ liệu thống kê.');
                    }
                    applyData(r.json.data);
                })
                .catch(function (err) { showMessage(err.message); });
        }

        if (rangeGroup) {
            rangeGroup.addEventListener('click', function (e) {
                var btn = e.target.closest('button[data-days]');
                if (!btn) return;
                rangeGroup.querySelectorAll('button').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                loadRange(btn.getAttribute('data-days'));
            });
        }

        applyData(initialData);
    })();
    </script>

    <!-- KHỐI 1: THỐNG KÊ SỐ BÀI VIẾT THEO DANH MỤC (sp_stats_posts_by_category & vw_category_summary) -->
    <div class="nature-card mb-4" style="border-radius: 32px; padding: 2rem;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h2 class="h5 fw-normal mb-0" style="color: var(--color-ink);">Thống kê Số bài viết theo Danh mục</h2>
                    <span class="badge rounded-pill text-bg-light border text-muted" style="font-size: 11px;">
                        <code>CALL sp_stats_posts_by_category()</code>
                    </span>
                </div>
                <p class="small text-muted mb-0">Tổng hợp từ SQL View <code>vw_category_summary</code> — phân bổ số bài đã duyệt, chờ duyệt và tỷ trọng từng chuyên mục.</p>
            </div>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories" class="nature-btn-secondary py-1 px-3 text-dark border-dark-subtle" style="font-size: 13px;">
                Quản lý toàn bộ <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (empty($categoryStats)): ?>
            <p class="text-muted text-center py-4 mb-0">Chưa có chuyên mục nào trong hệ thống.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 60px;">ID</th>
                            <th scope="col">Tên chuyên mục</th>
                            <th scope="col">Tỷ trọng bài viết</th>
                            <th scope="col" class="text-center" style="width: 120px;">Đã duyệt</th>
                            <th scope="col" class="text-center" style="width: 120px;">Tổng số bài</th>
                            <th scope="col" class="text-end" style="width: 110px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categoryStats as $cat):
                            $catPostCount = (int)($cat['total_posts'] ?? $cat['post_count'] ?? 0);
                            $catPubCount  = (int)($cat['published_count'] ?? $catPostCount);
                            $percent      = min(100, round(($catPostCount / $totalPostsSafe) * 100));
                        ?>
                            <tr>
                                <td class="fw-medium text-muted">#<?= (int)$cat['id'] ?></td>
                                <td>
                                    <div class="fw-medium text-dark">
                                        <i class="bi bi-folder2-open text-success me-2"></i>
                                        <?= htmlspecialchars($cat['name'] ?? $cat['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars(mb_strimwidth($cat['description'] ?? 'Chưa có mô tả', 0, 55, '...'), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td style="min-width: 160px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 7px; background: rgba(0,0,0,0.06);">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="small text-muted" style="min-width: 36px;"><?= $percent ?>%</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">
                                        <?= $catPubCount ?> bài
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge text-bg-light border fw-semibold">
                                        <?= $catPostCount ?> bài
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/categories/edit/<?= (int)$cat['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0 me-1" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts?category=<?= (int)$cat['id'] ?>" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0" title="Xem bài trong danh mục">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- KHỐI 2 & 3: TOP BÀI VIẾT NỔI BẬT (sp_stats_top_posts) & TOP THÀNH VIÊN ĐÓNG GÓP (sp_stats_top_users) -->
    <div class="row g-4 mb-4">
        <!-- Top Bài viết nổi bật nhất -->
        <div class="col-12 col-lg-7">
            <div class="nature-card h-100" style="border-radius: 32px; padding: 1.75rem;">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h2 class="h5 fw-normal mb-0" style="color: var(--color-ink);">
                                <i class="bi bi-trophy-fill text-warning me-1"></i>Top Bài viết nổi bật
                            </h2>
                            <span class="badge rounded-pill text-bg-light border text-muted" style="font-size: 10.5px;">
                                <code>CALL sp_stats_top_posts(5)</code>
                            </span>
                        </div>
                        <p class="small text-muted mb-0">Xếp hạng từ View <code>vw_post_details</code> theo điểm Vote &amp; Bình luận.</p>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts" class="small text-decoration-none fw-medium" style="color: var(--color-ink);">
                        Tất cả bài &rarr;
                    </a>
                </div>

                <?php if (empty($topPosts)): ?>
                    <p class="text-muted text-center py-4 mb-0">Chưa có bài viết nào được xuất bản.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 42px;">#</th>
                                    <th>Bài viết</th>
                                    <th class="text-center">Vote</th>
                                    <th class="text-center">Bình luận</th>
                                    <th class="text-center">Điểm Hot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topPosts as $idx => $tp): ?>
                                    <tr>
                                        <td>
                                            <span class="badge rounded-pill <?= $idx === 0 ? 'text-bg-warning' : ($idx === 1 ? 'text-bg-secondary' : 'text-bg-light border') ?>">
                                                <?= $idx + 1 ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$tp['id'] ?>" class="fw-medium text-dark text-decoration-none d-block text-truncate" style="max-width: 240px;">
                                                <?= htmlspecialchars($tp['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <div class="small text-muted">
                                                <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($tp['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                                <span class="mx-1">·</span>
                                                <span><?= htmlspecialchars($tp['category_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">
                                                <i class="bi bi-caret-up-fill"></i> <?= (int)($tp['vote_score'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-bg-light border">
                                                <i class="bi bi-chat-dots me-1"></i><?= (int)($tp['comments_count'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold text-success">
                                            <?= (int)($tp['hot_score'] ?? 0) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Thành viên tích cực nhất -->
        <div class="col-12 col-lg-5">
            <div class="nature-card h-100" style="border-radius: 32px; padding: 1.75rem;">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h2 class="h5 fw-normal mb-0" style="color: var(--color-ink);">
                                <i class="bi bi-award-fill text-success me-1"></i>Top Thành viên
                            </h2>
                            <span class="badge rounded-pill text-bg-light border text-muted" style="font-size: 10.5px;">
                                <code>CALL sp_stats_top_users(5)</code>
                            </span>
                        </div>
                        <p class="small text-muted mb-0">Tổng hợp từ SQL View <code>vw_user_summary</code>.</p>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="small text-decoration-none fw-medium" style="color: var(--color-ink);">
                        Quản lý &rarr;
                    </a>
                </div>

                <?php if (empty($topUsers)): ?>
                    <p class="text-muted text-center py-4 mb-0">Chưa có dữ liệu thành viên.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Thành viên</th>
                                    <th class="text-center">Số bài</th>
                                    <th class="text-center">Bình luận</th>
                                    <th class="text-center">Karma</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topUsers as $uRow): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/users/<?= (int)$uRow['id'] ?>" class="fw-medium text-dark text-decoration-none d-block">
                                                <?= htmlspecialchars($uRow['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <span class="badge rounded-pill text-bg-light border text-muted" style="font-size: 10px;">
                                                <?= htmlspecialchars(strtoupper((string)($uRow['role_name'] ?? 'member')), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold">
                                            <?= (int)($uRow['total_posts'] ?? 0) ?>
                                        </td>
                                        <td class="text-center text-muted">
                                            <?= (int)($uRow['total_comments'] ?? 0) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">
                                                +<?= (int)($uRow['total_karma'] ?? 0) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
