<?php
$title = $title ?? 'Hồ sơ người dùng';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$u = $targetUser ?? [];
$avatarUrl = upload_url($u['avatar_path'] ?? null);
$username = (string)($u['username'] ?? 'Thành viên');
$g = $gamification ?? [];
?>
<div class="app-container" style="max-width: 900px;">
    <!-- Profile Card Header -->
    <div class="nature-card mb-4" style="border-radius: 32px; padding: 2rem;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <?php if ($avatarUrl): ?>
                    <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" 
                         alt="Avatar" 
                         class="rounded-circle object-fit-cover shadow-sm" 
                         width="80" height="80">
                <?php else: ?>
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                         style="width: 80px; height: 80px; background: #2d3328; color: #fff; font-size: 1.8rem; font-weight: 600;">
                        <?= htmlspecialchars(mb_strtoupper(mb_substr($username, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h1 class="h3 fw-bold mb-0 text-dark"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></h1>
                        <?php
                        $targetRole = (string)($u['role_name'] ?? 'member');
                        $targetRoleBadge = class_exists('Auth') ? Auth::roleBadgeClass($targetRole) : 'text-bg-success';
                        $targetRoleLabel = class_exists('Auth') ? Auth::roleLabel($targetRole) : ucfirst($targetRole);
                        ?>
                        <span class="badge rounded-pill <?= htmlspecialchars($targetRoleBadge, ENT_QUOTES, 'UTF-8') ?> px-3 py-1" style="font-size: 11px;">
                            <?= htmlspecialchars($targetRoleLabel, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                    <!-- Badge Gamification -->
                    <div class="mt-2 d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill border shadow-sm" 
                         style="background: <?= htmlspecialchars($g['bg'] ?? 'rgba(0,0,0,0.05)', ENT_QUOTES, 'UTF-8') ?>; border-color: <?= htmlspecialchars($g['border'] ?? '#ccc', ENT_QUOTES, 'UTF-8') ?> !important; font-size: 13px; line-height: 1.4; color: <?= htmlspecialchars($g['color'] ?? '#333', ENT_QUOTES, 'UTF-8') ?>;">
                        <i class="bi <?= htmlspecialchars($g['icon'] ?? 'bi-award', ENT_QUOTES, 'UTF-8') ?> me-0.5"></i>
                        <span class="fw-bold"><?= htmlspecialchars($g['title'] ?? 'Tân thủ dã ngoại', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="opacity-75 ms-1">(Level <?= (int)($g['level'] ?? 1) ?>)</span>
                    </div>
                </div>
            </div>

            <!-- Follow / Edit Profile Button -->
            <div>
                <?php if (!empty($isOwnProfile)): ?>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/profile" class="nature-btn-secondary px-4 py-2 text-dark" style="height: 42px;">
                        <i class="bi bi-gear-fill me-1"></i> Quản lý hồ sơ của tôi
                    </a>
                <?php elseif (Auth::isMember()): ?>
                    <button type="button" 
                            id="btnFollowUser" 
                            class="nature-btn-primary px-4 py-2 follow-user-btn <?= !empty($isFollowing) ? 'following' : '' ?>" 
                            data-user-id="<?= (int)$u['id'] ?>"
                            onclick="ForumAjax.toggleFollowUser(<?= (int)$u['id'] ?>, this)"
                            style="width: auto; height: 42px; background: <?= !empty($isFollowing) ? '#6c757d' : 'var(--color-moss, #9bbd55)' ?>;">
                        <i class="bi <?= !empty($isFollowing) ? 'bi-check-lg' : 'bi-person-plus-fill' ?> me-1"></i>
                        <span class="follow-btn-text"><?= !empty($isFollowing) ? 'Đang theo dõi' : 'Theo dõi' ?></span>
                    </button>
                <?php elseif (!Auth::check()): ?>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="nature-btn-primary px-4 py-2" style="width: auto; height: 42px;">
                        <i class="bi bi-person-plus-fill me-1"></i> Theo dõi
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($u['bio'])): ?>
            <div class="p-3 rounded-3 mb-4" style="background: rgba(0,0,0,0.03); border-left: 3px solid var(--color-moss, #9bbd55);">
                <p class="mb-0 text-dark small fst-italic">"<?= nl2br(htmlspecialchars($u['bio'], ENT_QUOTES, 'UTF-8')) ?>"</p>
            </div>
        <?php endif; ?>

        <!-- Stats & Gamification Counters -->
        <div class="row g-3 text-center mb-3">
            <div class="col-3">
                <div class="p-2 rounded-3 bg-light border">
                    <div class="fs-4 fw-bold text-dark"><?= (int)($g['points'] ?? 0) ?></div>
                    <div class="text-muted small" style="font-size: 11px;">Điểm uy tín</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded-3 bg-light border">
                    <div class="fs-4 fw-bold text-dark"><?= count($posts ?? []) ?></div>
                    <div class="text-muted small" style="font-size: 11px;">Bài viết</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded-3 bg-light border">
                    <div class="fs-4 fw-bold text-dark" id="userFollowersCount"><?= (int)($followersCount ?? 0) ?></div>
                    <div class="text-muted small" style="font-size: 11px;">Người theo dõi</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded-3 bg-light border">
                    <div class="fs-4 fw-bold text-dark"><?= (int)($followingCount ?? 0) ?></div>
                    <div class="text-muted small" style="font-size: 11px;">Đang theo dõi</div>
                </div>
            </div>
        </div>

        <!-- Level Progress Bar -->
        <div class="mt-3">
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Tiến độ cấp độ: <?= (int)($g['points'] ?? 0) ?> / <?= (int)($g['next_tier_points'] ?? 100) ?> điểm</span>
                <span class="fw-semibold text-success"><?= (int)($g['progress_percent'] ?? 0) ?>%</span>
            </div>
            <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: <?= (int)($g['progress_percent'] ?? 0) ?>%;" 
                     aria-valuenow="<?= (int)($g['progress_percent'] ?? 0) ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100"></div>
            </div>
        </div>
    </div>

    <!-- Posts by this User -->
    <div class="nature-card p-4" style="border-radius: 32px;">
        <h2 class="h5 fw-bold text-dark mb-3">
            <i class="bi bi-collection-fill text-success me-2"></i>Bài viết của <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
        </h2>

        <?php if (empty($posts)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-1 opacity-50"></i>
                Thành viên này chưa đăng bài viết nào.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-3">
                <?php foreach ($posts as $p): 
                    $pId = (int)$p['id'];
                    $img = $p['image_url'] ? (str_starts_with($p['image_url'], 'http') ? $p['image_url'] : $baseUrl . '/' . ltrim($p['image_url'], '/')) : $baseUrl . '/assets/img/tent-forest.png';
                ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" 
                                 alt="<?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>" 
                                 class="card-img-top object-fit-cover" 
                                 style="height: 140px;">
                            <div class="card-body p-3 d-flex flex-column">
                                <span class="badge text-bg-light border mb-1 align-self-start" style="font-size: 11px;">
                                    <?= htmlspecialchars($p['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <h3 class="h6 card-title mb-2">
                                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= $pId ?>" class="text-decoration-none text-dark fw-bold text-truncate d-block">
                                        <?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h3>
                                <div class="mt-auto d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
                                    <span><i class="bi bi-caret-up-fill text-success me-1"></i><?= (int)($p['vote_score'] ?? 0) ?></span>
                                    <span><i class="bi bi-chat-dots me-1"></i><?= (int)($p['comments_count'] ?? 0) ?></span>
                                    <span><?= htmlspecialchars(date('d/m/Y', strtotime($p['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
