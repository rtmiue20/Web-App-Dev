<?php
$title = 'Hồ sơ cá nhân & Thành tựu';
$avatarUrl = upload_url($user['avatar_path'] ?? null);
$username = (string) ($user['username'] ?? '');
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$g = $gamification ?? [];
$myPosts = $myPosts ?? [];
$bookmarks = $bookmarks ?? [];
$followingUsers = $followingUsers ?? [];
$followingCategories = $followingCategories ?? [];
?>
<div class="app-container" style="max-width: 900px;">
    <!-- Profile & Gamification Summary Card -->
    <div class="nature-card mb-4" style="border-radius: 36px; padding: 2rem;">
        <?php if (!empty($errors['form'])): ?>
            <div class="nature-alert nature-alert-error mb-4" role="alert">
                <?= e($errors['form']) ?>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="Ảnh đại diện của <?= e($username) ?>"
                         class="rounded-circle object-fit-cover shadow-sm" width="80" height="80">
                <?php else: ?>
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                         style="width: 80px; height: 80px; background: #2d3328; color: #fff; font-size: 1.8rem; font-weight: 600;">
                        <?= e(mb_strtoupper(mb_substr($username !== '' ? $username : 'U', 0, 1))) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h1 class="h3 fw-normal mb-0" style="color: var(--color-ink);"><?= e($username) ?></h1>
                        <?php
                        $currentRole = class_exists('Auth') ? Auth::role($user) : ($user['role_name'] ?? 'member');
                        $roleLabel = class_exists('Auth') ? Auth::roleLabel($currentRole) : $currentRole;
                        $roleBadge = class_exists('Auth') ? Auth::roleBadgeClass($currentRole) : 'text-bg-success';
                        ?>
                        <span class="badge <?= e($roleBadge) ?> px-3 py-1 rounded-pill" style="font-size: 11px;"><?= e($roleLabel) ?></span>
                    </div>
                    <!-- Badge Gamification -->
                    <div class="mt-1 d-inline-flex align-items-center gap-1 px-2.5 py-1 rounded-pill border" 
                         style="background: <?= htmlspecialchars($g['bg'] ?? 'rgba(0,0,0,0.05)', ENT_QUOTES, 'UTF-8') ?>; border-color: <?= htmlspecialchars($g['border'] ?? '#ccc', ENT_QUOTES, 'UTF-8') ?> !important; font-size: 12px; color: <?= htmlspecialchars($g['color'] ?? '#333', ENT_QUOTES, 'UTF-8') ?>;">
                        <i class="bi <?= htmlspecialchars($g['icon'] ?? 'bi-award', ENT_QUOTES, 'UTF-8') ?>"></i>
                        <span class="fw-bold"><?= htmlspecialchars($g['title'] ?? 'Tân thủ dã ngoại', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="opacity-75">(Cấp <?= (int)($g['level'] ?? 1) ?>)</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?= e(base_url('/posts/create')) ?>" class="nature-btn-primary px-3 py-2" style="width: auto; height: 42px;">
                    <i class="bi bi-pencil-fill me-1"></i> Viết bài mới
                </a>
                <a href="<?= e(base_url('/users/' . (int)$user['id'])) ?>" class="nature-btn-secondary px-3 py-2 text-dark" style="height: 42px;" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Xem hồ sơ công khai
                </a>
            </div>
        </div>

        <?php if (!empty($user['bio'])): ?>
            <div class="p-3 rounded-3 mb-4" style="background: rgba(0,0,0,0.03); border-left: 3px solid var(--color-moss, #9bbd55);">
                <span class="nature-label d-block mb-1">Giới thiệu bản thân</span>
                <p class="mb-0 text-dark small"><?= nl2br(e($user['bio'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Gamification Stats Counters -->
        <div class="row g-3 text-center mb-3">
            <div class="col-6 col-md-3">
                <div class="p-2.5 rounded-3 bg-light border">
                    <div class="fs-4 fw-bold text-dark"><?= (int)($g['points'] ?? 0) ?></div>
                    <div class="text-muted small" style="font-size: 11px;">Điểm uy tín (Reputation)</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2.5 rounded-3 bg-light border">
                    <div class="fs-4 fw-bold text-dark"><?= count($myPosts) ?></div>
                    <div class="text-muted small" style="font-size: 11px;">Bài viết đã đăng</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2.5 rounded-3 bg-light border">
                    <div class="fs-4 fw-bold text-dark"><?= (int)($followersCount ?? 0) ?></div>
                    <div class="text-muted small" style="font-size: 11px;">Người theo dõi (Followers)</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-2.5 rounded-3 bg-light border">
                    <div class="fs-4 fw-bold text-dark"><?= count($bookmarks) ?></div>
                    <div class="text-muted small" style="font-size: 11px;">Bài viết đã lưu</div>
                </div>
            </div>
        </div>

        <!-- Level Progress Bar -->
        <div class="mt-3">
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Tiến độ thăng hạng: <strong><?= (int)($g['points'] ?? 0) ?></strong> / <?= (int)($g['next_tier_points'] ?? 100) ?> điểm</span>
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

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills justify-content-center gap-2 mb-4" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="tab-edit-tab" data-bs-toggle="pill" data-bs-target="#tab-edit" type="button" role="tab">
                <i class="bi bi-person-gear me-1"></i> Hồ sơ &amp; Mật khẩu
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="tab-posts-tab" data-bs-toggle="pill" data-bs-target="#tab-posts" type="button" role="tab">
                <i class="bi bi-file-earmark-text me-1"></i> Bài viết của tôi (<?= count($myPosts) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="tab-bookmarks-tab" data-bs-toggle="pill" data-bs-target="#tab-bookmarks" type="button" role="tab">
                <i class="bi bi-bookmark-heart-fill me-1"></i> Đã lưu (<?= count($bookmarks) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="tab-following-tab" data-bs-toggle="pill" data-bs-target="#tab-following" type="button" role="tab">
                <i class="bi bi-people me-1"></i> Đang theo dõi (<?= count($followingUsers) + count($followingCategories) ?>)
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="profileTabsContent">
        <!-- TAB 1: Edit Profile & Password -->
        <div class="tab-pane fade show active" id="tab-edit" role="tabpanel">
            <div class="nature-card mb-4" style="border-radius: 36px; padding: 2rem;">
                <h2 class="h4 mb-3">Chỉnh sửa hồ sơ</h2>
                <form method="post" action="<?= e(base_url('/profile')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="username" class="nature-label">Tên người dùng</label>
                        <input class="nature-input" id="username" name="username" type="text" minlength="3" maxlength="50"
                               required value="<?= e($old['username'] ?? $username) ?>">
                        <?php if (!empty($errors['username'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="nature-label">Giới thiệu (tối đa 500 ký tự)</label>
                        <textarea class="nature-textarea" id="bio" name="bio" rows="4" maxlength="500"><?= e($old['bio'] ?? $user['bio'] ?? '') ?></textarea>
                        <?php if (!empty($errors['bio'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['bio']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="avatar" class="nature-label">Ảnh đại diện (JPG, PNG hoặc WEBP, tối đa 2MB)</label>
                        <input class="nature-input" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp">
                        <?php if (!empty($errors['avatar'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['avatar']) ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                        Lưu hồ sơ
                    </button>
                </form>
            </div>

            <div class="nature-card" style="border-radius: 36px; padding: 2rem;">
                <h2 class="h4 mb-3">Đổi mật khẩu</h2>
                <?php if (!empty($errors['password_form'])): ?>
                    <div class="nature-alert nature-alert-error mb-3" role="alert"><?= e($errors['password_form']) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= e(base_url('/profile/password')) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="current_password" class="nature-label">Mật khẩu hiện tại</label>
                        <input class="nature-input" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                        <?php if (!empty($errors['current_password'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['current_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="nature-label">Mật khẩu mới</label>
                        <input class="nature-input" id="new_password" name="new_password" type="password" minlength="8" autocomplete="new-password" required>
                        <?php if (!empty($errors['new_password'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['new_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="nature-label">Xác nhận mật khẩu mới</label>
                        <input class="nature-input" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                        <?php if (!empty($errors['password_confirmation'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['password_confirmation']) ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                        Cập nhật mật khẩu
                    </button>
                </form>
            </div>
        </div>

        <!-- TAB 2: My Posts -->
        <div class="tab-pane fade" id="tab-posts" role="tabpanel">
            <div class="nature-card p-4" style="border-radius: 32px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-bold text-dark mb-0">Bài viết của bạn</h2>
                    <a href="<?= e(base_url('/posts/create')) ?>" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="bi bi-pencil-fill me-1"></i> Viết bài mới
                    </a>
                </div>
                <?php if (empty($myPosts)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        Bạn chưa đăng bài viết nào. Hãy chia sẻ trải nghiệm đầu tiên nhé!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tiêu đề</th>
                                    <th>Chuyên mục</th>
                                    <th>Trạng thái</th>
                                    <th>Lượt vote</th>
                                    <th>Thời gian</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myPosts as $mp): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= e(base_url('/posts/' . (int)$mp['id'])) ?>" class="fw-semibold text-dark text-decoration-none">
                                                <?= htmlspecialchars($mp['title'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </td>
                                        <td><span class="badge text-bg-light border"><?= htmlspecialchars($mp['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td>
                                            <?php if ($mp['status'] === 'published'): ?>
                                                <span class="badge text-bg-success">Công khai</span>
                                            <?php elseif ($mp['status'] === 'pending'): ?>
                                                <span class="badge text-bg-warning text-dark">Chờ duyệt</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary"><?= htmlspecialchars($mp['status'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><i class="bi bi-caret-up-fill text-success me-1"></i><?= (int)($mp['vote_score'] ?? 0) ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars(date('d/m/Y', strtotime($mp['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-end">
                                            <a href="<?= e(base_url('/posts/' . (int)$mp['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-secondary" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB 3: Bookmarks -->
        <div class="tab-pane fade" id="tab-bookmarks" role="tabpanel">
            <div class="nature-card p-4" style="border-radius: 32px;">
                <h2 class="h5 fw-bold text-dark mb-3"><i class="bi bi-bookmark-heart-fill text-danger me-2"></i>Bài viết bạn đã lưu (Bookmarks)</h2>
                <?php if (empty($bookmarks)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bookmark-x fs-1 d-block mb-2 opacity-50"></i>
                        Bạn chưa lưu bài viết nào. Hãy bấm biểu tượng Bookmark trên các bài viết hay để đọc lại sau!
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <?php foreach ($bookmarks as $bm): 
                            $bmImg = !empty($bm['image_url']) ? (str_starts_with($bm['image_url'], 'http') ? $bm['image_url'] : $baseUrl . '/' . ltrim($bm['image_url'], '/')) : $baseUrl . '/assets/img/tent-forest.png';
                        ?>
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                    <img src="<?= htmlspecialchars($bmImg, ENT_QUOTES, 'UTF-8') ?>" 
                                         alt="<?= htmlspecialchars($bm['title'], ENT_QUOTES, 'UTF-8') ?>" 
                                         class="card-img-top object-fit-cover" 
                                         style="height: 130px;">
                                    <div class="card-body p-3 d-flex flex-column">
                                        <span class="badge text-bg-light border mb-1 align-self-start" style="font-size: 11px;">
                                            <?= htmlspecialchars($bm['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <h3 class="h6 card-title mb-2">
                                            <a href="<?= e(base_url('/posts/' . (int)$bm['id'])) ?>" class="text-decoration-none text-dark fw-bold text-truncate d-block">
                                                <?= htmlspecialchars($bm['title'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </h3>
                                        <div class="mt-auto d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
                                            <span>Bởi: <?= htmlspecialchars($bm['username'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <button type="button" 
                                                    class="btn btn-sm btn-link text-danger p-0 text-decoration-none"
                                                    onclick="ForumAjax.toggleBookmark(<?= (int)$bm['id'] ?>, this); this.closest('.col').remove();"
                                                    title="Bỏ lưu bài viết">
                                                <i class="bi bi-bookmark-x-fill"></i> Bỏ lưu
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB 4: Following Users & Categories -->
        <div class="tab-pane fade" id="tab-following" role="tabpanel">
            <div class="nature-card p-4 mb-4" style="border-radius: 32px;">
                <h2 class="h5 fw-bold text-dark mb-3"><i class="bi bi-people-fill text-primary me-2"></i>Thành viên đang theo dõi</h2>
                <?php if (empty($followingUsers)): ?>
                    <div class="text-center py-4 text-muted small">
                        Bạn chưa theo dõi thành viên nào. Hãy ghé thăm trang cá nhân của các phượt thủ để bấm theo dõi!
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <?php foreach ($followingUsers as $fu): 
                            $fuAvatar = upload_url($fu['avatar_path'] ?? null);
                        ?>
                            <div class="col">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-light border">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($fuAvatar): ?>
                                            <img src="<?= e($fuAvatar) ?>" alt="" class="rounded-circle object-fit-cover" width="46" height="46">
                                        <?php else: ?>
                                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-dark text-white fw-bold" style="width: 46px; height: 46px;">
                                                <?= htmlspecialchars(mb_strtoupper(mb_substr($fu['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?= e(base_url('/users/' . (int)$fu['id'])) ?>" class="fw-bold text-dark text-decoration-none">
                                                <?= htmlspecialchars($fu['username'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <div class="text-muted small text-truncate" style="max-width: 180px; font-size: 11.5px;">
                                                <?= htmlspecialchars($fu['bio'] ?? 'Thành viên diễn đàn', ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                            onclick="ForumAjax.toggleFollowUser(<?= (int)$fu['id'] ?>, this); this.closest('.col').remove();">
                                        Bỏ theo dõi
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="nature-card p-4" style="border-radius: 32px;">
                <h2 class="h5 fw-bold text-dark mb-3"><i class="bi bi-tag-fill text-success me-2"></i>Chuyên mục đang theo dõi</h2>
                <?php if (empty($followingCategories)): ?>
                    <div class="text-center py-4 text-muted small">
                        Bạn chưa theo dõi chuyên mục nào.
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($followingCategories as $fc): ?>
                            <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-light border">
                                <a href="<?= e(base_url('/posts?category=' . (int)$fc['id'])) ?>" class="text-dark fw-semibold text-decoration-none small">
                                    <i class="bi bi-compass me-1 text-success"></i><?= htmlspecialchars($fc['name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <button type="button" 
                                        class="btn-close" 
                                        style="font-size: 9px; cursor: pointer;"
                                        onclick="ForumAjax.toggleFollowCategory(<?= (int)$fc['id'] ?>, this); this.closest('.d-inline-flex').remove();"
                                        title="Bỏ theo dõi"></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
