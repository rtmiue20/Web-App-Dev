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
$activities = $activities ?? [];
$activeTab = $activeTab ?? 'edit';
?>
<!-- Cropper.js CSS (Local vendor with jsdelivr fallback) -->
<link rel="stylesheet" href="<?= e($baseUrl) ?>/assets/vendor/cropperjs/cropper.min.css">
<style>
/* ── Avatar Crop & Preview Styling ── */
.avatar-cropper-area {
    width: 100%;
    height: 330px;
    background: #141b12;
    border-radius: 18px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(0, 0, 0, 0.12);
}
.avatar-cropper-area img {
    max-width: 100%;
    display: block;
}
/* Mask tròn mềm mại hiển thị khung cắt avatar */
.avatar-cropper-area .cropper-view-box,
.avatar-cropper-area .cropper-face {
    border-radius: 50% !important;
}
.avatar-cropper-area .cropper-view-box {
    outline: 2.5px solid #9bbd55 !important;
    outline-color: rgba(155, 189, 85, 0.95) !important;
    box-shadow: 0 0 0 1000px rgba(10, 14, 9, 0.65);
}
.avatar-cropper-area .cropper-dashed {
    border-color: rgba(255, 255, 255, 0.35) !important;
}
.avatar-cropper-area .cropper-center {
    display: none !important;
}
.avatar-live-preview {
    border-radius: 50% !important;
    overflow: hidden !important;
    border: 2.5px solid var(--color-moss, #9bbd55);
    background: #1e261a;
    display: inline-block;
}
.avatar-preview-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.avatar-preview-hover:hover {
    transform: scale(1.04);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
}

/* ── Profile Tabs Dock Styling (Segmented Glass Pill Bar) ── */
.profile-tabs-wrapper {
    display: flex !important;
    justify-content: center !important;
    margin-top: 0.5rem !important;
    margin-bottom: 2.25rem !important;
    width: 100% !important;
    padding: 0 0.5rem;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    -webkit-overflow-scrolling: touch;
}
.profile-tabs-wrapper::-webkit-scrollbar {
    display: none;
}
.profile-nav-dock {
    display: flex !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    background: rgba(20, 30, 18, 0.82) !important;
    backdrop-filter: blur(24px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    border-radius: 999px !important;
    padding: 8px 10px !important;
    width: 100% !important;
    max-width: 860px !important;
    margin: 0 auto !important;
    box-shadow: 0 14px 40px rgba(0, 0, 0, 0.35), 0 2px 10px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.18) !important;
}
.profile-nav-dock .nav-item {
    margin: 0 !important;
    flex: 1 1 0 !important;
    display: flex !important;
    min-width: 0 !important;
}
.profile-nav-dock .nav-link {
    width: 100% !important;
    height: 48px !important;
    min-height: 48px !important;
    color: rgba(255, 255, 255, 0.86) !important;
    background: transparent !important;
    border: 1px solid transparent !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    padding: 0 14px !important;
    border-radius: 999px !important;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    white-space: nowrap !important;
    text-decoration: none !important;
}
.profile-nav-dock .nav-link i {
    font-size: 16px !important;
    opacity: 0.92;
    transition: transform 0.2s ease;
}
.profile-nav-dock .nav-link:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.12) !important;
    transform: translateY(-1px) !important;
}
.profile-nav-dock .nav-link:hover i {
    transform: scale(1.12);
}
.profile-nav-dock .nav-link.active {
    background: var(--color-moss, #9bbd55) !important;
    color: #121810 !important;
    font-weight: 700 !important;
    box-shadow: 0 6px 20px rgba(155, 189, 85, 0.52), inset 0 1px 0 rgba(255, 255, 255, 0.45) !important;
    border-color: rgba(255, 255, 255, 0.35) !important;
}
.profile-nav-dock .nav-link.active i {
    color: #121810 !important;
    opacity: 1 !important;
}
.profile-nav-dock .nav-link.active .badge-pending-pill {
    background: rgba(18, 24, 16, 0.22) !important;
    color: #121810 !important;
}
.profile-nav-dock .nav-link:not(.active) .badge-pending-pill {
    background: #f59e0b !important;
    color: #1c1917 !important;
}
@media (max-width: 860px) {
    .profile-tabs-wrapper {
        justify-content: flex-start !important;
        padding: 0 1rem !important;
    }
    .profile-nav-dock {
        width: max-content !important;
        max-width: none !important;
        border-radius: 999px !important;
        gap: 5px !important;
        padding: 6px 8px !important;
    }
    .profile-nav-dock .nav-item {
        flex: 0 0 auto !important;
    }
    .profile-nav-dock .nav-link {
        width: auto !important;
        font-size: 13.5px !important;
        height: 44px !important;
        min-height: 44px !important;
        padding: 0 16px !important;
    }
}
</style>
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
                    <div class="mt-2 d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill border shadow-sm" 
                         style="background: <?= htmlspecialchars($g['bg'] ?? 'rgba(0,0,0,0.05)', ENT_QUOTES, 'UTF-8') ?>; border-color: <?= htmlspecialchars($g['border'] ?? '#ccc', ENT_QUOTES, 'UTF-8') ?> !important; font-size: 13px; line-height: 1.4; color: <?= htmlspecialchars($g['color'] ?? '#333', ENT_QUOTES, 'UTF-8') ?>;">
                        <i class="bi <?= htmlspecialchars($g['icon'] ?? 'bi-award', ENT_QUOTES, 'UTF-8') ?> me-0.5"></i>
                        <span class="fw-bold"><?= htmlspecialchars($g['title'] ?? 'Tân thủ dã ngoại', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="opacity-75 ms-1">(Cấp <?= (int)($g['level'] ?? 1) ?>)</span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <?php if (class_exists('Auth') && Auth::isMember($user)): ?>
                    <a href="<?= e(base_url('/posts/create')) ?>" class="nature-btn-primary px-3 py-2" style="width: auto; height: 42px;">
                        <i class="bi bi-pencil-fill me-1"></i> Viết bài mới
                    </a>
                <?php endif; ?>
                <a href="<?= e(base_url('/users/' . (int)$user['id'])) ?>" class="nature-btn-secondary px-3 py-2 text-dark" style="height: 42px;" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Xem hồ sơ công khai
                </a>
            </div>
        </div>

        <?php if (class_exists('Auth') && Auth::isGuest($user)): ?>
            <div class="d-flex align-items-center gap-2 rounded-4 mb-4 p-3" style="background: rgba(155, 189, 85, 0.12); border: 1px solid rgba(155, 189, 85, 0.30);">
                <i class="bi bi-eye-fill fs-5" style="color: var(--color-moss);"></i>
                <div class="small text-dark">
                    <strong>Chế độ Khách (Guest — Chỉ xem):</strong> Tài khoản của bạn hiện đang được phân quyền chỉ xem nội dung trên diễn đàn. Bạn vẫn có thể cập nhật hồ sơ cá nhân tại đây.
                </div>
            </div>
        <?php endif; ?>

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

    <?php
    $myPendingCount = 0;
    if (!empty($myPosts)) {
        foreach ($myPosts as $mp) {
            if (($mp['status'] ?? '') === 'pending') {
                $myPendingCount++;
            }
        }
    }
    ?>

    <?php if ($myPendingCount > 0): ?>
        <!-- Banner thông báo bài viết đang chờ duyệt (Giao diện tinh gọn, chuyên nghiệp) -->
        <div class="nature-card mb-4 p-4 shadow-sm" 
             style="border-radius: 28px; background: #ffffff; border: 1.5px solid rgba(245, 158, 11, 0.45); box-shadow: 0 8px 30px rgba(0,0,0,0.06) !important;">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-auto d-flex align-items-center justify-content-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 52px; height: 52px; background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 24px;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <div class="col-12 col-md">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <h3 class="h6 mb-0 fw-bold" style="color: #1e293b; font-size: 16px;">
                            Bạn có <?= $myPendingCount ?> bài viết đang chờ Ban Quản Trị phê duyệt
                        </h3>
                        <span class="badge rounded-pill" style="background: rgba(245, 158, 11, 0.18); color: #b45309; font-size: 11px; font-weight: 600;">
                            Hàng đợi kiểm duyệt
                        </span>
                    </div>
                    <p class="text-muted small mb-0" style="line-height: 1.5;">
                        Bài viết đã được lưu an toàn trong hệ thống. Ban Quản Trị sẽ xem xét và xuất bản công khai sớm nhất có thể.
                    </p>
                </div>
                <div class="col-12 col-md-auto text-md-end">
                    <button type="button" class="nature-btn-secondary px-3.5 py-2 text-nowrap d-inline-flex align-items-center gap-1.5" 
                            style="height: 42px; font-size: 13.5px; width: auto; color: #1e293b; border-color: rgba(0,0,0,0.15);"
                            onclick="switchToPostsTab()">
                        <i class="bi bi-file-earmark-text text-success"></i> Xem bài viết chờ duyệt
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs (Segmented Glass Dock Bar) -->
    <div class="profile-tabs-wrapper">
        <ul class="nav nav-pills profile-nav-dock" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'edit') ? 'active' : '' ?>" id="tab-edit-tab" data-bs-toggle="pill" data-bs-target="#tab-edit" type="button" role="tab">
                    <i class="bi bi-person-gear me-2"></i> Hồ sơ &amp; Mật khẩu
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'posts') ? 'active' : '' ?>" id="tab-posts-tab" data-bs-toggle="pill" data-bs-target="#tab-posts" type="button" role="tab">
                    <i class="bi bi-file-earmark-text me-2"></i> Bài viết (<?= count($myPosts) ?>)
                    <?php if ($myPendingCount > 0): ?>
                        <span class="badge rounded-pill ms-1.5 badge-pending-pill px-2 py-0.5" style="font-size: 10px; font-weight: 700;"><?= $myPendingCount ?> chờ</span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'bookmarks') ? 'active' : '' ?>" id="tab-bookmarks-tab" data-bs-toggle="pill" data-bs-target="#tab-bookmarks" type="button" role="tab">
                    <i class="bi bi-bookmark-heart-fill me-2"></i> Đã lưu (<?= count($bookmarks) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'following') ? 'active' : '' ?>" id="tab-following-tab" data-bs-toggle="pill" data-bs-target="#tab-following" type="button" role="tab">
                    <i class="bi bi-people me-2"></i> Theo dõi (<?= count($followingUsers) + count($followingCategories) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= ($activeTab === 'activity') ? 'active' : '' ?>" id="tab-activity-tab" data-bs-toggle="pill" data-bs-target="#tab-activity" type="button" role="tab">
                    <i class="bi bi-clock-history me-2"></i> Hoạt động (<?= count($activities) ?>)
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" id="profileTabsContent">
        <!-- TAB 1: Edit Profile & Password -->
        <div class="tab-pane fade <?= ($activeTab === 'edit') ? 'show active' : '' ?>" id="tab-edit" role="tabpanel">
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
                    <!-- Ảnh đại diện với tính năng Cắt & Căn chỉnh khung tròn -->
                    <div class="mb-4 p-3.5 rounded-4 bg-light border" style="border-color: rgba(0, 0, 0, 0.08) !important;">
                        <label class="nature-label mb-2.5 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-person-bounding-box me-1.5 text-success"></i>Ảnh đại diện diễn đàn</span>
                            <span class="badge text-bg-light border text-muted fw-normal" style="font-size: 11px;">Tỉ lệ chuẩn 1:1 tròn</span>
                        </label>

                        <div class="d-flex align-items-center gap-3.5 flex-wrap">
                            <!-- Avatar Preview in Form -->
                            <div class="position-relative avatar-preview-hover" style="width: 88px; height: 88px; flex-shrink: 0;">
                                <div id="avatar-preview-wrap" class="rounded-circle overflow-hidden shadow-sm position-relative"
                                     style="width: 88px; height: 88px; background: #232d1e; border: 3px solid var(--color-moss, #9bbd55); cursor: pointer;"
                                     onclick="triggerAvatarSelect()" title="Nhấp để chọn và căn chỉnh ảnh đại diện mới">
                                    <?php if ($avatarUrl): ?>
                                        <img id="avatar-form-preview" src="<?= e($avatarUrl) ?>" alt="Ảnh đại diện"
                                             class="w-100 h-100 object-fit-cover d-block">
                                        <div id="avatar-form-placeholder" class="d-none w-100 h-100 align-items-center justify-content-center text-white fw-bold fs-3">
                                            <?= e(mb_strtoupper(mb_substr($username !== '' ? $username : 'U', 0, 1))) ?>
                                        </div>
                                    <?php else: ?>
                                        <img id="avatar-form-preview" src="" alt="Ảnh đại diện"
                                             class="w-100 h-100 object-fit-cover" style="display: none;">
                                        <div id="avatar-form-placeholder" class="d-flex w-100 h-100 align-items-center justify-content-center text-white fw-bold fs-3">
                                            <?= e(mb_strtoupper(mb_substr($username !== '' ? $username : 'U', 0, 1))) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="position-absolute bottom-0 start-0 w-100 text-center py-0.5"
                                         style="background: rgba(0, 0, 0, 0.65); color: #fff; font-size: 11px;">
                                        <i class="bi bi-camera-fill"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons & Instructions -->
                            <div class="flex-grow-1" style="min-width: 220px;">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1.5">
                                    <button type="button" class="nature-btn-secondary px-3 py-1.5 d-inline-flex align-items-center gap-1.5"
                                            onclick="triggerAvatarSelect()" style="font-size: 13px; height: 38px; width: auto;">
                                        <i class="bi bi-cloud-arrow-up-fill text-success"></i> Chọn ảnh đại diện
                                    </button>
                                    <button type="button" id="btn-recrop-avatar" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1"
                                            onclick="reopenCropModal()" style="display: none; font-size: 13px; height: 38px;">
                                        <i class="bi bi-crop"></i> Căn chỉnh lại
                                    </button>
                                </div>

                                <div id="avatar-crop-status" class="mb-1" style="display: none;"></div>

                                <small class="text-muted d-block" style="font-size: 12px; line-height: 1.45;">
                                    <i class="bi bi-info-circle me-1"></i>Hỗ trợ JPG, PNG hoặc WEBP (tối đa 2MB). Bạn có thể xoay, thu phóng và căn chỉnh khung tròn trước khi lưu.
                                </small>
                            </div>
                        </div>

                        <!-- Hidden Inputs -->
                        <input class="d-none" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" onchange="handleAvatarFileSelect(this)">
                        <input type="hidden" name="avatar_cropped" id="avatar_cropped" value="">

                        <?php if (!empty($errors['avatar'])): ?>
                            <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle-fill me-1"></i><?= e($errors['avatar']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-end pt-2">
                        <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                            Lưu hồ sơ
                        </button>
                    </div>
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
                        <div style="position: relative;">
                            <input class="nature-input" 
                                   style="padding-right: 3rem;" 
                                   id="current_password" 
                                   name="current_password" 
                                   type="password" 
                                   autocomplete="current-password" 
                                   required 
                                   placeholder="••••••••">
                            <button type="button" 
                                    class="btn-toggle-password" 
                                    data-target="current_password"
                                    onclick="togglePasswordVisibility('current_password', this)"
                                    aria-label="Hiện hoặc ẩn mật khẩu"
                                    style="position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #8A8D83; cursor: pointer; padding: 0.4rem; display: flex; align-items: center; justify-content: center; z-index: 2;">
                                <!-- Eye Open -->
                                <svg class="icon-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <!-- Eye Closed -->
                                <svg class="icon-eye-closed" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                        <?php if (!empty($errors['current_password'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['current_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="nature-label">Mật khẩu mới</label>
                        <div style="position: relative;">
                            <input class="nature-input" 
                                   style="padding-right: 3rem;" 
                                   id="new_password" 
                                   name="new_password" 
                                   type="password" 
                                   minlength="8" 
                                   autocomplete="new-password" 
                                   required 
                                   placeholder="••••••••">
                            <button type="button" 
                                    class="btn-toggle-password" 
                                    data-target="new_password"
                                    onclick="togglePasswordVisibility('new_password', this)"
                                    aria-label="Hiện hoặc ẩn mật khẩu"
                                    style="position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #8A8D83; cursor: pointer; padding: 0.4rem; display: flex; align-items: center; justify-content: center; z-index: 2;">
                                <!-- Eye Open -->
                                <svg class="icon-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <!-- Eye Closed -->
                                <svg class="icon-eye-closed" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                        <?php if (!empty($errors['new_password'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['new_password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="nature-label">Xác nhận mật khẩu mới</label>
                        <div style="position: relative;">
                            <input class="nature-input" 
                                   style="padding-right: 3rem;" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   type="password" 
                                   minlength="8" 
                                   autocomplete="new-password" 
                                   required 
                                   placeholder="••••••••">
                            <button type="button" 
                                    class="btn-toggle-password" 
                                    data-target="password_confirmation"
                                    onclick="togglePasswordVisibility('password_confirmation', this)"
                                    aria-label="Hiện hoặc ẩn mật khẩu"
                                    style="position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #8A8D83; cursor: pointer; padding: 0.4rem; display: flex; align-items: center; justify-content: center; z-index: 2;">
                                <!-- Eye Open -->
                                <svg class="icon-eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <!-- Eye Closed -->
                                <svg class="icon-eye-closed" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                        <?php if (!empty($errors['password_confirmation'])): ?>
                            <div class="text-danger small mt-1"><?= e($errors['password_confirmation']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-end pt-2">
                        <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto; height: 44px;">
                            Cập nhật mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 2: My Posts -->
        <div class="tab-pane fade <?= ($activeTab === 'posts') ? 'show active' : '' ?>" id="tab-posts" role="tabpanel">
            <div class="nature-card p-4" style="border-radius: 32px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-bold text-dark mb-0">Bài viết của bạn</h2>
                    <a href="<?= e(base_url('/posts/create')) ?>" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="bi bi-pencil-fill me-1"></i> Viết bài mới
                    </a>
                </div>
                <?php if ($myPendingCount > 0): ?>
                    <div class="alert d-flex align-items-center gap-3 rounded-4 mb-3 p-3 shadow-sm border" 
                         style="background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.3) !important; color: #78350f;" role="alert">
                        <i class="bi bi-hourglass-split fs-4 text-warning flex-shrink-0"></i>
                        <div class="small">
                            <strong>Bạn có <?= $myPendingCount ?> bài viết đang chờ Ban Quản Trị duyệt.</strong> Các bài viết này chỉ hiển thị với bạn và Quản trị viên. Bạn có thể xem trước, chỉnh sửa nội dung hoặc bấm biểu tượng thùng rác (<i class="bi bi-trash3 text-danger"></i>) để hủy bỏ bài viết nếu bạn đổi ý không muốn đăng nữa.
                        </div>
                    </div>
                <?php endif; ?>
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
                                <?php foreach ($myPosts as $mp): 
                                    $isRowPending = (($mp['status'] ?? '') === 'pending');
                                ?>
                                    <tr <?= $isRowPending ? 'style="background-color: rgba(245, 158, 11, 0.08);"' : '' ?>>
                                        <td>
                                            <a href="<?= e(base_url('/posts/' . (int)$mp['id'])) ?>" class="fw-semibold text-dark text-decoration-none">
                                                <?= htmlspecialchars($mp['title'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                            <?php if ($isRowPending): ?>
                                                <span class="d-block small text-muted">Chỉ bạn và BQT thấy bài viết này</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge text-bg-light border"><?= htmlspecialchars($mp['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td>
                                            <?php if ($mp['status'] === 'published'): ?>
                                                <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Công khai</span>
                                            <?php elseif ($mp['status'] === 'pending'): ?>
                                                <span class="badge text-bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Chờ duyệt</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary"><?= htmlspecialchars($mp['status'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><i class="bi bi-caret-up-fill text-success me-1"></i><?= (int)($mp['vote_score'] ?? 0) ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars(date('d/m/Y', strtotime($mp['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-end">
                                            <div class="d-inline-flex align-items-center gap-1.5">
                                                <a href="<?= e(base_url('/posts/' . (int)$mp['id'])) ?>" 
                                                   class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center" 
                                                   style="width: 32px; height: 32px; border-radius: 8px;" 
                                                   title="Xem trước bài viết">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= e(base_url('/posts/' . (int)$mp['id'] . '/edit')) ?>" 
                                                   class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center" 
                                                   style="width: 32px; height: 32px; border-radius: 8px;" 
                                                   title="Sửa nội dung">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="post" 
                                                      action="<?= e(base_url('/posts/' . (int)$mp['id'] . '/delete')) ?>" 
                                                      onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn <?= $isRowPending ? 'hủy bỏ và xóa bài viết đang chờ duyệt' : 'xóa bài viết' ?> “<?= e(addslashes($mp['title'])) ?>” không? Hành động này không thể hoàn tác.', 'Xác nhận xóa', '<?= $isRowPending ? 'Hủy bài viết chờ duyệt?' : 'Xóa bài viết?' ?>');" 
                                                      class="d-inline m-0 p-0">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="redirect" value="profile">
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center" 
                                                            style="width: 32px; height: 32px; border-radius: 8px;" 
                                                            title="<?= $isRowPending ? 'Hủy bỏ bài viết chờ duyệt' : 'Xóa bài viết' ?>">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            </div>
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
        <div class="tab-pane fade <?= ($activeTab === 'bookmarks') ? 'show active' : '' ?>" id="tab-bookmarks" role="tabpanel">
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
        <div class="tab-pane fade <?= ($activeTab === 'following') ? 'show active' : '' ?>" id="tab-following" role="tabpanel">
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

        <!-- TAB 5: Activity History (Nhật ký hoạt động cá nhân) -->
        <div class="tab-pane fade <?= ($activeTab === 'activity') ? 'show active' : '' ?>" id="tab-activity" role="tabpanel">
            <div class="nature-card p-4" style="border-radius: 32px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge rounded-pill px-3 py-1" style="background: rgba(155, 189, 85, 0.18); color: var(--color-moss, #6f8e32); border: 1px solid rgba(155, 189, 85, 0.35); font-size: 11.5px; font-weight: 600;">
                                <i class="bi bi-clock-history me-1"></i>Nhật ký hoạt động cá nhân
                            </span>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-0">Lịch sử hoạt động của bạn</h2>
                        <p class="text-muted small mb-0 mt-1">Ghi lại toàn bộ hành trình đăng ký chuyến đi, bài viết, bình luận, checklist và tương tác trên diễn đàn.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge text-bg-light border text-muted px-3 py-2 rounded-pill small">
                            Tổng số: <strong id="activityTotalCount"><?= count($activities) ?></strong> sự kiện
                        </span>
                    </div>
                </div>

                <!-- Bộ lọc nhanh loại hoạt động -->
                <div class="d-flex flex-wrap gap-1.5 mb-3" id="activityFilterGroup">
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold active-act-filter" data-filter="all" onclick="filterActivities('all', this)" style="background: #233d2c; color: #fff; font-size: 12px;">Tất cả (<?= count($activities) ?>)</button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary" data-filter="trip" onclick="filterActivities('trip', this)" style="background: rgba(0,0,0,0.05); font-size: 12px;">Đi chung</button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary" data-filter="post" onclick="filterActivities('post', this)" style="background: rgba(0,0,0,0.05); font-size: 12px;">Bài viết</button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary" data-filter="comment" onclick="filterActivities('comment', this)" style="background: rgba(0,0,0,0.05); font-size: 12px;">Bình luận</button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary" data-filter="interaction" onclick="filterActivities('interaction', this)" style="background: rgba(0,0,0,0.05); font-size: 12px;">Tương tác &amp; Khác</button>
                </div>

                <?php if (empty($activities)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-50"></i>
                        Bạn chưa có hoạt động nào được ghi nhận. Hãy tham gia thảo luận hoặc đăng ký chuyến đi!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0" id="activityTable">
                            <thead class="table-light" style="font-size: 13px;">
                                <tr>
                                    <th style="min-width: 140px;">Thời gian</th>
                                    <th style="min-width: 125px;">Phân loại</th>
                                    <th>Nội dung chi tiết hoạt động</th>
                                    <th class="text-end" style="min-width: 110px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $act): 
                                    $actType = (string)($act['type'] ?? '');
                                    $actTid = (int)($act['target_id'] ?? 0);
                                    
                                    // Xác định nhóm phân loại lọc
                                    $filterGroup = 'interaction';
                                    if (in_array($actType, ['join_trip', 'leave_trip', 'create_trip', 'trip_new_member'], true)) {
                                        $filterGroup = 'trip';
                                    } elseif (in_array($actType, ['create_post', 'post_approved', 'post_rejected'], true)) {
                                        $filterGroup = 'post';
                                    } elseif (in_array($actType, ['comment_post', 'reply_comment'], true)) {
                                        $filterGroup = 'comment';
                                    }

                                    // Xác định đường dẫn chi tiết
                                    $actUrl = '';
                                    if ($actTid > 0) {
                                        if ($filterGroup === 'trip') {
                                            $actUrl = $baseUrl . '/trips/' . $actTid;
                                        } elseif ($filterGroup === 'post' || $filterGroup === 'comment' || in_array($actType, ['like_post', 'bookmark_post'], true)) {
                                            $actUrl = $baseUrl . '/posts/' . $actTid;
                                        } elseif ($actType === 'create_checklist') {
                                            $actUrl = $baseUrl . '/checklists';
                                        } elseif ($actType === 'review_place') {
                                            $actUrl = $baseUrl . '/places';
                                        } elseif ($actType === 'follow_user') {
                                            $actUrl = $baseUrl . '/users/' . $actTid;
                                        } elseif ($actType === 'follow_category') {
                                            $actUrl = $baseUrl . '/posts?category=' . $actTid;
                                        }
                                    }

                                    // Huy hiệu & Icon phân loại
                                    $typeBadgeHtml = '';
                                    switch ($actType) {
                                        case 'join_trip':
                                            $typeBadgeHtml = '<span class="badge text-bg-success rounded-pill px-2.5 py-1"><i class="bi bi-person-check-fill me-1"></i>Tham gia</span>';
                                            break;
                                        case 'leave_trip':
                                            $typeBadgeHtml = '<span class="badge text-bg-warning rounded-pill px-2.5 py-1"><i class="bi bi-box-arrow-left me-1"></i>Hủy chuyến</span>';
                                            break;
                                        case 'create_trip':
                                            $typeBadgeHtml = '<span class="badge text-bg-info text-dark rounded-pill px-2.5 py-1"><i class="bi bi-compass-fill me-1"></i>Tạo chuyến</span>';
                                            break;
                                        case 'trip_new_member':
                                            $typeBadgeHtml = '<span class="badge text-bg-info text-dark rounded-pill px-2.5 py-1"><i class="bi bi-people-fill me-1"></i>Thành viên</span>';
                                            break;
                                        case 'create_post':
                                            $typeBadgeHtml = '<span class="badge text-bg-success rounded-pill px-2.5 py-1"><i class="bi bi-file-earmark-text me-1"></i>Bài viết</span>';
                                            break;
                                        case 'post_approved':
                                            $typeBadgeHtml = '<span class="badge text-bg-success rounded-pill px-2.5 py-1"><i class="bi bi-patch-check-fill me-1"></i>Đã duyệt</span>';
                                            break;
                                        case 'post_rejected':
                                            $typeBadgeHtml = '<span class="badge text-bg-danger rounded-pill px-2.5 py-1"><i class="bi bi-x-circle-fill me-1"></i>Bị từ chối</span>';
                                            break;
                                        case 'comment_post':
                                        case 'reply_comment':
                                            $typeBadgeHtml = '<span class="badge text-bg-primary rounded-pill px-2.5 py-1"><i class="bi bi-chat-dots-fill me-1"></i>Bình luận</span>';
                                            break;
                                        case 'like_post':
                                            $typeBadgeHtml = '<span class="badge rounded-pill px-2.5 py-1" style="background: rgba(239, 68, 68, 0.15); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3);"><i class="bi bi-hand-thumbs-up-fill me-1"></i>Thích bài</span>';
                                            break;
                                        case 'bookmark_post':
                                            $typeBadgeHtml = '<span class="badge rounded-pill px-2.5 py-1" style="background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);"><i class="bi bi-bookmark-star-fill me-1"></i>Lưu bài</span>';
                                            break;
                                        case 'create_checklist':
                                            $typeBadgeHtml = '<span class="badge rounded-pill px-2.5 py-1" style="background: rgba(180, 83, 9, 0.15); color: #b45309; border: 1px solid rgba(180, 83, 9, 0.3);"><i class="bi bi-card-checklist me-1"></i>Checklist</span>';
                                            break;
                                        case 'review_place':
                                            $typeBadgeHtml = '<span class="badge text-bg-warning rounded-pill px-2.5 py-1"><i class="bi bi-star-fill me-1"></i>Đánh giá</span>';
                                            break;
                                        case 'follow_user':
                                        case 'follow_category':
                                            $typeBadgeHtml = '<span class="badge rounded-pill px-2.5 py-1" style="background: rgba(147, 51, 234, 0.15); color: #7e22ce; border: 1px solid rgba(147, 51, 234, 0.3);"><i class="bi bi-people me-1"></i>Theo dõi</span>';
                                            break;
                                        case 'update_profile':
                                            $typeBadgeHtml = '<span class="badge text-bg-secondary rounded-pill px-2.5 py-1"><i class="bi bi-person-gear me-1"></i>Hồ sơ</span>';
                                            break;
                                        default:
                                            $typeBadgeHtml = '<span class="badge text-bg-light border rounded-pill px-2.5 py-1"><i class="bi bi-bell-fill me-1"></i>Hệ thống</span>';
                                            break;
                                    }
                                ?>
                                    <tr class="activity-row" data-group="<?= htmlspecialchars($filterGroup, ENT_QUOTES, 'UTF-8') ?>">
                                        <td class="text-nowrap" style="font-size: 13px;">
                                            <div class="fw-semibold text-dark">
                                                <i class="bi bi-clock me-1 text-muted"></i><?= htmlspecialchars(date('d/m/Y', strtotime($act['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="small text-muted" style="font-size: 11.5px;">
                                                <?= htmlspecialchars(date('H:i:s', strtotime($act['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?= $typeBadgeHtml ?>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark" style="font-size: 13.5px; line-height: 1.5;">
                                                <?= htmlspecialchars($act['content'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <?php if (!empty($act['actor_username']) && $act['actor_username'] !== 'Hệ thống' && (int)($act['actor_id'] ?? 0) !== (int)($user['id'] ?? 0)): ?>
                                                <div class="small text-muted mt-0.5" style="font-size: 11px;">
                                                    Bởi: <strong><?= htmlspecialchars($act['actor_username'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($actUrl !== ''): ?>
                                                <a href="<?= htmlspecialchars($actUrl, ENT_QUOTES, 'UTF-8') ?>" 
                                                   class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-nowrap d-inline-flex align-items-center gap-1" 
                                                   style="font-size: 12px; height: 30px;">
                                                    <i class="bi bi-arrow-up-right"></i> Xem
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Khung nút Xem thêm tiến trình hoạt động -->
                    <div id="activityLoadMoreContainer" class="text-center pt-3 pb-2 border-top mt-2" style="display: none;">
                        <button id="btnLoadMoreActivities" type="button" 
                                class="btn btn-outline-success rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 fw-semibold shadow-sm" 
                                onclick="loadMoreActivities()" 
                                style="font-size: 13.5px; height: 42px;">
                            <i class="bi bi-chevron-down"></i> Xem thêm (<span id="actRemainingCount">0</span> hoạt động tiếp theo)
                        </button>
                        <div id="actAllShownMsg" class="d-inline-flex align-items-center gap-1.5 text-muted small py-2 px-3.5 rounded-pill bg-light border" style="display: none;">
                            <i class="bi bi-check2-all text-success fs-6"></i> Đã hiển thị toàn bộ <strong id="actShownAllCount">0</strong> hoạt động
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         MODAL CĂN CHỈNH KHUNG ẢNH ĐẠI DIỆN (Cropper.js)
    ══════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="avatarCropModal" tabindex="-1" aria-labelledby="avatarCropModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 640px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; background: #ffffff; color: #23261f; overflow: hidden;">
                <!-- Header -->
                <div class="modal-header border-bottom px-4 pt-4 pb-3" style="background: #fafbfa; border-color: rgba(35, 38, 31, 0.08) !important;">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1"
                                  style="background: rgba(155, 189, 85, 0.18); color: var(--color-moss, #6f8e32); border: 1px solid rgba(155, 189, 85, 0.35); font-size: 11px; font-weight: 600;">
                                <i class="bi bi-crop"></i> Căn chỉnh khung tròn
                            </span>
                        </div>
                        <h5 class="modal-title fw-bold mb-0" id="avatarCropModalLabel" style="color: #1e241b; font-size: 1.15rem;">
                            Căn chỉnh ảnh đại diện
                        </h5>
                    </div>
                    <button type="button" class="btn-close shadow-none" onclick="closeAvatarCropModal()" aria-label="Đóng"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-arrows-move me-1 text-success"></i>Kéo ảnh để di chuyển vị trí, cuộn chuột hoặc dùng thanh trượt để phóng to/thu nhỏ vào góc đẹp nhất:
                    </p>

                    <div class="row g-3 align-items-center mb-3">
                        <!-- Vùng Cropper chính -->
                        <div class="col-12 col-md-8">
                            <div class="avatar-cropper-area shadow-inner">
                                <img id="avatar-crop-image" src="" alt="Ảnh căn chỉnh">
                            </div>
                        </div>

                        <!-- Vùng Xem trước trực tiếp (Live Preview) -->
                        <div class="col-12 col-md-4 text-center">
                            <span class="nature-label d-block mb-2" style="font-size: 12px;">Xem trước hiển thị</span>
                            <div class="d-flex flex-column align-items-center gap-2">
                                <!-- Preview lớn 96px -->
                                <div class="avatar-live-preview shadow-sm" style="width: 96px; height: 96px;"></div>
                                <span class="text-muted" style="font-size: 11px;">Hồ sơ cá nhân</span>

                                <!-- Preview nhỏ 40px -->
                                <div class="avatar-live-preview shadow-sm mt-1" style="width: 40px; height: 40px;"></div>
                                <span class="text-muted" style="font-size: 11px;">Thanh Menu &amp; Bình luận</span>
                            </div>
                        </div>
                    </div>

                    <!-- Thanh công cụ căn chỉnh (Zoom, Rotate, Reset) -->
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap p-2.5 rounded-4 bg-light border">
                        <!-- Thu phóng -->
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-light border rounded-circle" style="width: 32px; height: 32px; padding: 0;"
                                    onclick="zoomCropper(-0.1)" title="Thu nhỏ">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="range" class="form-range" id="avatar-zoom-slider" min="0.2" max="3" step="0.05" value="1"
                                   style="width: 110px;" oninput="onZoomSliderChange(this.value)" title="Thanh trượt phóng to / thu nhỏ">
                            <button type="button" class="btn btn-sm btn-light border rounded-circle" style="width: 32px; height: 32px; padding: 0;"
                                    onclick="zoomCropper(0.1)" title="Phóng to">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>

                        <!-- Xoay ảnh -->
                        <div class="d-flex align-items-center gap-1.5">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1"
                                    onclick="rotateCropper(-90)" title="Xoay trái 90°">
                                <i class="bi bi-arrow-counterclockwise"></i> <span class="small d-none d-sm-inline">Trái</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1"
                                    onclick="rotateCropper(90)" title="Xoay phải 90°">
                                <i class="bi bi-arrow-clockwise"></i> <span class="small d-none d-sm-inline">Phải</span>
                            </button>
                        </div>

                        <!-- Đặt lại -->
                        <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none small"
                                onclick="resetCropper()" title="Khôi phục trạng thái ban đầu">
                            <i class="bi bi-arrow-repeat me-1"></i>Đặt lại
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer px-4 py-3 d-flex justify-content-between align-items-center" style="background: #fafbfa; border-color: rgba(35, 38, 31, 0.08) !important;">
                    <button type="button" class="nature-btn-secondary px-3.5 py-2" onclick="closeAvatarCropModal()" style="width: auto; height: 42px; font-size: 13.5px;">
                        Hủy bỏ
                    </button>
                    <button type="button" class="nature-btn-primary px-4 py-2 d-inline-flex align-items-center gap-1.5" onclick="confirmAvatarCrop()" style="width: auto; height: 42px; font-size: 13.5px;">
                        <i class="bi bi-check2-circle fs-5"></i> Xác nhận &amp; Cắt ảnh
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cropper.js JavaScript (Local vendor with jsdelivr fallback) -->
<script src="<?= e($baseUrl) ?>/assets/vendor/cropperjs/cropper.min.js"></script>
<script>
if (typeof Cropper === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"><\/script>');
}
</script>
<script>
var cropperInstance = null;
var currentAvatarRawUrl = null;

function triggerAvatarSelect() {
    var fileInput = document.getElementById('avatar');
    if (fileInput) {
        fileInput.click();
    }
}

function handleAvatarFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];

    var validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Vui lòng chọn file ảnh hợp lệ (JPG, PNG hoặc WEBP).');
        input.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        alert('Dung lượng ảnh vượt quá 2MB. Vui lòng chọn ảnh nhỏ hơn.');
        input.value = '';
        return;
    }

    var reader = new FileReader();
    reader.onload = function(e) {
        currentAvatarRawUrl = e.target.result;
        openAvatarCropModal(currentAvatarRawUrl);
    };
    reader.readAsDataURL(file);
}

function reopenCropModal() {
    if (currentAvatarRawUrl) {
        openAvatarCropModal(currentAvatarRawUrl);
    } else {
        triggerAvatarSelect();
    }
}

function openAvatarCropModal(imageSrc) {
    var modalEl = document.getElementById('avatarCropModal');
    if (!modalEl) return;

    var imageEl = document.getElementById('avatar-crop-image');
    if (!imageEl) return;

    imageEl.src = imageSrc;

    if (window.bootstrap && bootstrap.Modal) {
        var modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInst.show();
    } else {
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
    }
}

function closeAvatarCropModal() {
    var modalEl = document.getElementById('avatarCropModal');
    if (!modalEl) return;

    if (window.bootstrap && bootstrap.Modal) {
        var modalInst = bootstrap.Modal.getInstance(modalEl);
        if (modalInst) modalInst.hide();
    } else {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
    }

    if (cropperInstance) {
        cropperInstance.destroy();
        cropperInstance = null;
    }
}

function initCropperInstance() {
    var imageEl = document.getElementById('avatar-crop-image');
    if (!imageEl) return;

    if (cropperInstance) {
        cropperInstance.destroy();
        cropperInstance = null;
    }

    if (typeof Cropper !== 'undefined') {
        cropperInstance = new Cropper(imageEl, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.85,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            preview: '.avatar-live-preview',
            ready: function() {
                var zoomSlider = document.getElementById('avatar-zoom-slider');
                if (zoomSlider) zoomSlider.value = 1;
            }
        });
    }
}

function zoomCropper(delta) {
    if (cropperInstance) {
        cropperInstance.zoom(delta);
    }
}

function onZoomSliderChange(val) {
    if (cropperInstance) {
        cropperInstance.zoomTo(parseFloat(val));
    }
}

function rotateCropper(degree) {
    if (cropperInstance) {
        cropperInstance.rotate(degree);
    }
}

function resetCropper() {
    if (cropperInstance) {
        cropperInstance.reset();
        var slider = document.getElementById('avatar-zoom-slider');
        if (slider) slider.value = 1;
    }
}

function confirmAvatarCrop() {
    if (!cropperInstance) return;

    var canvas = cropperInstance.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
    });
    if (!canvas) return;

    var dataUrl = canvas.toDataURL('image/jpeg', 0.92);

    // 1. Gán chuỗi base64 vào hidden input
    var hiddenCrop = document.getElementById('avatar_cropped');
    if (hiddenCrop) hiddenCrop.value = dataUrl;

    // 2. Cập nhật ảnh đại diện trên form ngay lập tức
    var previewImg = document.getElementById('avatar-form-preview');
    if (previewImg) {
        previewImg.src = dataUrl;
        previewImg.style.display = 'block';
    }
    var placeholder = document.getElementById('avatar-form-placeholder');
    if (placeholder) {
        placeholder.classList.add('d-none');
        placeholder.classList.remove('d-flex');
    }

    // 3. Hiển thị badge thông báo trạng thái
    var statusWrap = document.getElementById('avatar-crop-status');
    if (statusWrap) {
        statusWrap.innerHTML = '<span class="badge text-bg-success py-1.5 px-3 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">' +
            '<i class="bi bi-check-circle-fill"></i> Đã căn chỉnh ảnh mới — hãy bấm "Lưu hồ sơ" bên dưới' +
            '</span>';
        statusWrap.style.display = 'block';
    }

    // 4. Hiện nút căn chỉnh lại nếu muốn đổi ý
    var recropBtn = document.getElementById('btn-recrop-avatar');
    if (recropBtn) {
        recropBtn.style.display = 'inline-flex';
    }

    // 5. Cập nhật DataTransfer vào input[type=file] để tương thích multipart/form-data
    try {
        canvas.toBlob(function(blob) {
            if (blob && window.DataTransfer) {
                var file = new File([blob], 'avatar_cropped.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer();
                dt.items.add(file);
                var fileInput = document.getElementById('avatar');
                if (fileInput) fileInput.files = dt.files;
            }
        }, 'image/jpeg', 0.92);
    } catch (e) {
        console.warn('DataTransfer fallback to base64', e);
    }

    // Đóng modal
    closeAvatarCropModal();
}

function switchToPostsTab() {
    var postsTabBtn = document.getElementById('tab-posts-tab');
    if (postsTabBtn && window.bootstrap && window.bootstrap.Tab) {
        var tabInstance = window.bootstrap.Tab.getOrCreateInstance(postsTabBtn);
        tabInstance.show();
        postsTabBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function switchToActivityTab() {
    var actTabBtn = document.getElementById('tab-activity-tab');
    if (actTabBtn && window.bootstrap && window.bootstrap.Tab) {
        var tabInstance = window.bootstrap.Tab.getOrCreateInstance(actTabBtn);
        tabInstance.show();
        renderActivitiesWithPagination();
        actTabBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

var activityPageSize = 10;
var activityCurrentLimit = 10;
var currentActivityFilter = 'all';

function renderActivitiesWithPagination() {
    var rows = document.querySelectorAll('#activityTable tbody tr.activity-row');
    if (!rows || !rows.length) return;

    var matchingRows = [];
    rows.forEach(function(row) {
        if (currentActivityFilter === 'all' || row.getAttribute('data-group') === currentActivityFilter) {
            matchingRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    var totalMatching = matchingRows.length;
    var counter = document.getElementById('activityTotalCount');
    if (counter) {
        counter.textContent = totalMatching;
    }

    for (var i = 0; i < matchingRows.length; i++) {
        if (i < activityCurrentLimit) {
            matchingRows[i].style.display = '';
        } else {
            matchingRows[i].style.display = 'none';
        }
    }

    var loadMoreContainer = document.getElementById('activityLoadMoreContainer');
    var loadMoreBtn = document.getElementById('btnLoadMoreActivities');
    var remainingSpan = document.getElementById('actRemainingCount');
    var allShownMsg = document.getElementById('actAllShownMsg');

    if (loadMoreContainer) {
        if (totalMatching === 0) {
            loadMoreContainer.style.display = 'none';
        } else if (activityCurrentLimit < totalMatching) {
            loadMoreContainer.style.display = 'block';
            if (loadMoreBtn) loadMoreBtn.style.display = 'inline-flex';
            if (remainingSpan) {
                var nextBatch = Math.min(activityPageSize, totalMatching - activityCurrentLimit);
                remainingSpan.textContent = '+' + nextBatch;
            }
            if (allShownMsg) allShownMsg.style.display = 'none';
        } else {
            loadMoreContainer.style.display = 'block';
            if (loadMoreBtn) loadMoreBtn.style.display = 'none';
            if (allShownMsg) {
                allShownMsg.style.display = 'inline-flex';
                var shownCountEl = document.getElementById('actShownAllCount');
                if (shownCountEl) shownCountEl.textContent = totalMatching;
            }
        }
    }
}

function loadMoreActivities() {
    activityCurrentLimit += activityPageSize;
    renderActivitiesWithPagination();
}

function filterActivities(group, btn) {
    var buttons = document.querySelectorAll('#activityFilterGroup button');
    buttons.forEach(function(b) {
        b.style.background = 'rgba(0,0,0,0.05)';
        b.style.color = '#6c757d';
        b.classList.remove('active-act-filter');
    });
    btn.style.background = '#233d2c';
    btn.style.color = '#fff';
    btn.classList.add('active-act-filter');

    currentActivityFilter = group;
    activityCurrentLimit = activityPageSize;
    renderActivitiesWithPagination();
}

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo phân trang lũy tiến cho Lịch sử hoạt động
    renderActivitiesWithPagination();

    var actTabBtn = document.getElementById('tab-activity-tab');
    if (actTabBtn) {
        actTabBtn.addEventListener('shown.bs.tab', function() {
            renderActivitiesWithPagination();
        });
    }

    var modalEl = document.getElementById('avatarCropModal');
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function() {
            initCropperInstance();
        });
    }

    // Tự động chuyển tab theo URL param ?tab=... hoặc hash #tab-...
    var hash = window.location.hash;
    var urlParams = new URLSearchParams(window.location.search);
    var targetTab = urlParams.get('tab') || (hash ? hash.replace('#tab-', '').replace('#', '') : '');
    if (targetTab === 'activity') {
        switchToActivityTab();
    } else if (targetTab === 'posts') {
        switchToPostsTab();
    } else if (targetTab === 'bookmarks') {
        var bmTabBtn = document.getElementById('tab-bookmarks-tab');
        if (bmTabBtn && window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(bmTabBtn).show();
        }
    } else if (targetTab === 'following') {
        var fwTabBtn = document.getElementById('tab-following-tab');
        if (fwTabBtn && window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(fwTabBtn).show();
        }
    }
});
</script>
