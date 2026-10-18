<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$isLoggedIn = Session::has('user');
$writeUrl = $isLoggedIn ? $baseUrl . '/posts/create' : $baseUrl . '/login';

$spores = [
    ['left' => '58%', 'top' => '62%', 'size' => 3, 'duration' => 16, 'delay' => 0,  'x' => 24,  'opacity' => 0.55],
    ['left' => '66%', 'top' => '48%', 'size' => 2, 'duration' => 19, 'delay' => 3,  'x' => -18, 'opacity' => 0.40],
    ['left' => '72%', 'top' => '70%', 'size' => 4, 'duration' => 14, 'delay' => 6,  'x' => 30,  'opacity' => 0.50],
    ['left' => '80%', 'top' => '40%', 'size' => 2, 'duration' => 21, 'delay' => 2,  'x' => 12,  'opacity' => 0.35],
    ['left' => '52%', 'top' => '78%', 'size' => 3, 'duration' => 18, 'delay' => 9,  'x' => -26, 'opacity' => 0.45],
    ['left' => '62%', 'top' => '30%', 'size' => 2, 'duration' => 22, 'delay' => 5,  'x' => 16,  'opacity' => 0.30],
    ['left' => '86%', 'top' => '64%', 'size' => 3, 'duration' => 17, 'delay' => 11, 'x' => -14, 'opacity' => 0.40],
    ['left' => '44%', 'top' => '58%', 'size' => 2, 'duration' => 20, 'delay' => 7,  'x' => 20,  'opacity' => 0.30],
];

// Fallback topic cards
$featureCards = [
    [
        'label' => 'Chủ đề nổi bật',
        'title' => 'Cắm trại mùa mưa',
        'image' => $baseUrl . '/assets/img/tent-forest.png',
        'alt'   => 'Lều cắm trại màu xanh giữa khu rừng mưa',
        'depth' => '14px',
        'delay' => 700,
        'offset' => 'card-offset-1',
        'link'  => $baseUrl . '/posts',
    ],
    [
        'label' => 'Bài mới nhất',
        'title' => 'Sau cơn mưa trên đỉnh Tà Xùa',
        'image' => $baseUrl . '/assets/img/ta-xua-trek.png',
        'alt'   => 'Người leo núi trên sống lưng khủng long Tà Xùa giữa biển mây',
        'depth' => '20px',
        'delay' => 800,
        'offset' => 'card-offset-2',
        'link'  => $baseUrl . '/posts',
    ],
];
?>

<div class="hero-scene">
    <!-- Scene Background Elements -->
    <div class="scene-floor" aria-hidden="true"></div>
    <div class="scene-guide-line" style="left: 25%;" aria-hidden="true"></div>
    <div class="scene-guide-line" style="left: 50%;" aria-hidden="true"></div>
    <div class="scene-guide-line" style="left: 75%;" aria-hidden="true"></div>

    <!-- Drifting Spores -->
    <div class="parallax" style="position: absolute; inset: 0; pointer-events: none; --depth: -8px;" aria-hidden="true">
        <?php foreach ($spores as $s): ?>
            <span class="spore" style="
                left: <?= $s['left'] ?>;
                top: <?= $s['top'] ?>;
                width: <?= $s['size'] ?>px;
                height: <?= $s['size'] ?>px;
                --spore-duration: <?= $s['duration'] ?>s;
                --spore-delay: <?= $s['delay'] ?>s;
                --spore-x: <?= $s['x'] ?>px;
                --spore-opacity: <?= $s['opacity'] ?>;
            "></span>
        <?php endforeach; ?>
    </div>

    <!-- Giant Watermark Text -->
    <div class="scene-watermark" aria-hidden="true">
        DÃ NGOẠI
    </div>

    <!-- Floating Moss Form (Centerpiece) -->
    <div class="moss-container" aria-hidden="true">
        <div class="parallax" style="--depth: 34px;">
            <div class="moss-breathe">
                <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/moss-form.png" 
                     alt="" 
                     loading="eager"
                     decoding="async">
            </div>
        </div>
    </div>

    <!-- Main Foreground Content -->
    <main class="hero-content frame-in">
        <!-- Left: Headline, Description, CTA, Stats -->
        <section class="parallax" style="--depth: 6px; max-width: 580px;">
            <h1 class="headline">
                <span class="rise-in" style="--delay: 100ms;">Bước ra</span>
                <span class="rise-in" style="--delay: 200ms;">thiên nhiên,</span>
                <span class="rise-in" style="--delay: 300ms;">kể cho nhau nghe</span>
            </h1>

            <p class="lede rise-in" style="--delay: 400ms;">
                Nơi chia sẻ kinh nghiệm, địa điểm và mẹo hay cho người yêu dã ngoại.
            </p>

            <div class="rise-in" style="--delay: 500ms; margin-bottom: 2rem;">
                <a href="<?= htmlspecialchars($writeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-write iridescent-border">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                        <path d="m15 5 4 4"/>
                    </svg>
                    <span>Viết bài chia sẻ</span>
                </a>
            </div>

            <!-- Stats Counters -->
            <dl class="stats-group rise-in" style="--delay: 600ms;">
                <div class="stat-item">
                    <span class="stat-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 6h4"/>
                            <path d="M2 10h4"/>
                            <path d="M2 14h4"/>
                            <path d="M2 18h4"/>
                            <rect width="16" height="20" x="4" y="2" rx="2"/>
                            <path d="M9.5 8h5"/>
                            <path d="M9.5 12H16"/>
                            <path d="M9.5 16H14"/>
                        </svg>
                    </span>
                    <div>
                        <dt class="stat-label">Bài chia sẻ</dt>
                        <dd class="stat-value"><?= htmlspecialchars((string)($stats[0]['value'] ?? '128'), ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                </div>

                <span style="width: 1px; height: 32px; background: rgba(255, 255, 255, 0.12);" aria-hidden="true"></span>

                <div class="stat-item">
                    <span class="stat-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </span>
                    <div>
                        <dt class="stat-label">Thành viên</dt>
                        <dd class="stat-value"><?= htmlspecialchars((string)($stats[1]['value'] ?? '342'), ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                </div>
            </dl>
        </section>

        <!-- Right: Feature Cards (Cắm trại mùa mưa & Sau cơn mưa trên đỉnh Tà Xùa) -->
        <section class="feature-cards-grid" aria-label="Bài viết nổi bật">
            <?php foreach ($featureCards as $c): ?>
                <div class="parallax <?= $c['offset'] ?>" style="--depth: <?= $c['depth'] ?>;">
                    <div class="rise-in" style="--delay: <?= $c['delay'] ?>ms;">
                        <article class="paper-card">
                            <div class="card-header-meta">
                                <p class="card-meta-tag"><?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8') ?></p>
                                <h2 class="card-title">
                                    <a href="<?= htmlspecialchars($c['link'], ENT_QUOTES, 'UTF-8') ?>" 
                                       style="color: inherit; text-decoration: none;">
                                        <?= htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h2>
                            </div>
                            <div class="card-image-box">
                                <img src="<?= htmlspecialchars($c['image'], ENT_QUOTES, 'UTF-8') ?>" 
                                     alt="<?= htmlspecialchars($c['alt'], ENT_QUOTES, 'UTF-8') ?>"
                                     loading="lazy">
                                <a href="<?= htmlspecialchars($c['link'], ENT_QUOTES, 'UTF-8') ?>" 
                                   class="card-action-btn"
                                   aria-label="Xem bài viết: <?= htmlspecialchars($c['title'], ENT_QUOTES, 'UTF-8') ?>">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 7h10v10"/>
                                        <path d="M7 17 17 7"/>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
</div>