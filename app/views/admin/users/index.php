<?php
$title = 'Quản lý Thành viên';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

$totalCount = (int)($totalCount ?? 0);
$memberCount = (int)($memberCount ?? 0);
$adminCount = (int)($adminCount ?? 0);
$searchQuery = (string)($searchQuery ?? '');
$selectedRole = (string)($selectedRole ?? 'all');
$users = $users ?? [];
$resetResult = $resetResult ?? null;
?>
<div class="app-container" style="max-width: 1140px;">
    <!-- Breadcrumb & Header -->
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="text-white-50 text-decoration-none">Quản trị</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Thành viên</li>
            </ol>
        </nav>
    </div>

    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title">Quản lý Thành viên</h1>
            <p class="page-subtitle">Xem thông tin danh sách tài khoản đã đăng ký và hỗ trợ cấp lại mật khẩu khi thành viên quên.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="nature-btn-secondary px-3 py-2 text-white border-white-20 d-inline-flex align-items-center" style="height: 44px; font-size: 14px;">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Alert thông báo vừa cấp lại mật khẩu (kèm nút sao chép) -->
    <?php if (!empty($resetResult)): ?>
        <div class="nature-card mb-4 border border-warning" style="border-radius: 24px; padding: 1.5rem; background: rgba(255, 193, 7, 0.08);">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 46px; height: 46px; border-radius: 50%; background: rgba(255, 193, 7, 0.2); color: #b78103;">
                        <i class="bi bi-key-fill fs-4"></i>
                    </span>
                    <div>
                        <div class="fw-semibold text-dark mb-1">Đã cấp lại mật khẩu cho tài khoản <code><?= htmlspecialchars($resetResult['username'], ENT_QUOTES, 'UTF-8') ?></code>!</div>
                        <div class="small text-muted">
                            Mật khẩu mới: <strong class="badge bg-warning text-dark fs-6 px-3 py-1 font-monospace" id="newPasswordText"><?= htmlspecialchars($resetResult['password'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-dark rounded-pill px-3 py-2 btn-sm" id="btnCopyPassword" data-password="<?= htmlspecialchars($resetResult['password'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-clipboard me-1"></i> Sao chép mật khẩu
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Summary Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="nature-card py-3 px-4" style="border-radius: 22px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Tổng tài khoản</span>
                        <h3 class="fw-normal mb-0 mt-1" style="color: var(--color-ink);"><?= (int)$totalCount ?></h3>
                    </div>
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%; background: rgba(155, 189, 85, 0.15); color: var(--color-moss);">
                        <i class="bi bi-people-fill fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="nature-card py-3 px-4" style="border-radius: 22px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Thành viên (Member)</span>
                        <h3 class="fw-normal mb-0 mt-1 text-primary"><?= (int)$memberCount ?></h3>
                    </div>
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%; background: rgba(13, 110, 253, 0.12); color: #0d6efd;">
                        <i class="bi bi-person-check-fill fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="nature-card py-3 px-4" style="border-radius: 22px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Quản trị viên (Admin)</span>
                        <h3 class="fw-normal mb-0 mt-1 text-danger"><?= (int)$adminCount ?></h3>
                    </div>
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%; background: rgba(220, 53, 69, 0.12); color: #dc3545;">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="nature-card mb-4" style="border-radius: 24px; padding: 1.25rem 1.75rem;">
        <form method="get" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="row align-items-center g-3">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="userSearchInput" 
                           name="q" 
                           value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" 
                           class="form-control border-start-0 rounded-end-pill py-2 shadow-none" 
                           placeholder="Tìm kiếm theo tên đăng nhập hoặc email...">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <select name="role" id="userRoleFilter" class="form-select rounded-pill py-2 shadow-none">
                    <option value="all" <?= $selectedRole === 'all' ? 'selected' : '' ?>>Tất cả vai trò</option>
                    <option value="member" <?= $selectedRole === 'member' ? 'selected' : '' ?>>Thành viên (member)</option>
                    <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Quản trị viên (admin)</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2 text-md-end d-flex gap-2 justify-content-md-end">
                <button type="submit" class="nature-btn-primary px-3 py-2" style="width: auto; height: 40px; font-size: 13px;">
                    <i class="bi bi-funnel me-1"></i> Lọc
                </button>
                <?php if ($searchQuery !== '' || $selectedRole !== 'all'): ?>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm d-inline-flex align-items-center" title="Bỏ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="admin-table">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-person-x fs-1 text-muted d-block mb-3"></i>
                <h3 class="h5 fw-normal text-dark mb-1">Không tìm thấy thành viên phù hợp</h3>
                <p class="text-muted small mb-3">Hãy thử thay đổi từ khóa tìm kiếm hoặc bỏ bộ lọc vai trò.</p>
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/users" class="btn btn-light rounded-pill px-4">
                    Xem tất cả thành viên
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="usersTable">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 70px;">ID</th>
                            <th scope="col" style="min-width: 220px;">Thành viên</th>
                            <th scope="col" style="min-width: 200px;">Email</th>
                            <th scope="col" class="text-center" style="width: 140px;">Vai trò</th>
                            <th scope="col" class="text-center" style="width: 120px;">Số bài viết</th>
                            <th scope="col" style="width: 160px;">Ngày đăng ký</th>
                            <th scope="col" class="text-end" style="width: 170px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): 
                            $isAdmin = ($u['role_name'] ?? '') === 'admin';
                            $postCount = (int)($u['total_posts'] ?? 0);
                            $avatar = !empty($u['avatar_path']) ? $u['avatar_path'] : $baseUrl . '/placeholder-user.jpg';
                            $bio = !empty($u['bio']) ? $u['bio'] : 'Chưa cập nhật tiểu sử cá nhân';
                            $joinedDate = !empty($u['created_at']) ? date('d/m/Y H:i', strtotime($u['created_at'])) : '—';
                        ?>
                            <tr class="user-row">
                                <td class="fw-medium text-muted">#<?= (int)$u['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle overflow-hidden flex-shrink-0 d-flex align-items-center justify-content-center" 
                                             style="width: 38px; height: 38px; background: #e5e8e0; border: 1px solid rgba(0,0,0,0.08);">
                                            <?php if (!empty($u['avatar_path'])): ?>
                                                <img src="<?= htmlspecialchars($baseUrl . '/' . ltrim($u['avatar_path'], '/'), ENT_QUOTES, 'UTF-8') ?>" 
                                                     alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;"
                                                     onerror="this.src='<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/placeholder-user.jpg'">
                                            <?php else: ?>
                                                <i class="bi bi-person-fill text-muted fs-5"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark user-name">
                                                <?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="text-muted small d-md-none">
                                                <?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="user-email text-secondary small">
                                        <?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($isAdmin): ?>
                                        <span class="badge rounded-pill text-bg-danger px-3 py-1" style="font-size: 11px; font-weight: 500;">
                                            <i class="bi bi-shield-lock-fill me-1"></i>ADMIN
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill text-bg-success px-3 py-1" style="font-size: 11px; font-weight: 500;">
                                            <i class="bi bi-person-check me-1"></i>MEMBER
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill <?= $postCount > 0 ? 'text-bg-light border text-dark' : 'text-bg-light text-muted border' ?> px-2 py-1">
                                        <?= $postCount ?> bài
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?= htmlspecialchars($joinedDate, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <!-- Nút Xem chi tiết Modal -->
                                    <button type="button" 
                                            class="admin-action-btn btn-view-user me-1" 
                                            data-id="<?= (int)$u['id'] ?>"
                                            data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-role="<?= htmlspecialchars($u['role_name'] ?? 'member', ENT_QUOTES, 'UTF-8') ?>"
                                            data-posts="<?= $postCount ?>"
                                            data-joined="<?= htmlspecialchars($joinedDate, ENT_QUOTES, 'UTF-8') ?>"
                                            data-bio="<?= htmlspecialchars($bio, ENT_QUOTES, 'UTF-8') ?>"
                                            data-avatar="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>"
                                            title="Xem thông tin chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Nút Cấp lại Mật khẩu -->
                                    <button type="button" 
                                            class="admin-action-btn btn-reset-password"
                                            data-id="<?= (int)$u['id'] ?>"
                                            data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>"
                                            title="Cấp lại mật khẩu cho thành viên">
                                        <i class="bi bi-key"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ================= MODAL XEM CHI TIẾT THÀNH VIÊN ================= -->
<div class="modal fade" id="userDetailModal" tabindex="-1" aria-labelledby="userDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(155, 189, 85, 0.15); color: var(--color-moss);">
                        <i class="bi bi-person-lines-fill fs-5"></i>
                    </span>
                    <h5 class="modal-title fw-normal mb-0" id="userDetailModalLabel" style="color: var(--color-ink);">Thông tin thành viên</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            
            <div class="modal-body px-4 py-3">
                <div class="text-center py-2 mb-3">
                    <div class="rounded-circle overflow-hidden mx-auto mb-2 border shadow-sm" style="width: 76px; height: 76px; background: #e5e8e0;">
                        <img id="detailUserAvatar" src="" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <h5 id="detailUsername" class="mb-0 fw-semibold text-dark"></h5>
                    <span id="detailUserRoleBadge" class="badge rounded-pill mt-1"></span>
                </div>

                <div class="nature-card p-3 mb-3" style="background: rgba(0,0,0,0.02); border-radius: 18px;">
                    <div class="row g-2 small">
                        <div class="col-4 text-muted">Mã ID:</div>
                        <div class="col-8 fw-medium text-dark" id="detailUserId"></div>

                        <div class="col-4 text-muted">Email:</div>
                        <div class="col-8 fw-medium text-dark" id="detailUserEmail"></div>

                        <div class="col-4 text-muted">Số bài viết:</div>
                        <div class="col-8 fw-medium text-dark" id="detailUserPosts"></div>

                        <div class="col-4 text-muted">Ngày tham gia:</div>
                        <div class="col-8 fw-medium text-dark" id="detailUserJoined"></div>
                    </div>
                </div>

                <div>
                    <label class="nature-label mb-1">Tiểu sử cá nhân:</label>
                    <div class="p-3 rounded-4 bg-light small text-secondary border" id="detailUserBio" style="min-height: 60px;"></div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-warning rounded-pill px-3 btn-sm" id="btnSwitchToResetPassword">
                    <i class="bi bi-key-fill me-1"></i> Cấp lại mật khẩu
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL CẤP LẠI MẬT KHẨU (RESET PASSWORD) ================= -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 50%; background: rgba(255, 193, 7, 0.15); color: #b78103;">
                        <i class="bi bi-key-fill fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-normal mb-0" id="resetPasswordModalLabel" style="color: var(--color-ink);">Cấp lại mật khẩu</h5>
                        <div class="small text-muted" id="resetModalSubtext">Cho thành viên: <strong id="resetTargetUsername" class="text-dark"></strong></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            
            <form id="resetPasswordForm" method="post" action="">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="modal-body px-4 py-3">
                    <p class="text-secondary small mb-3">
                        Khi thành viên quên mật khẩu, Quản trị viên có thể đặt mật khẩu mới hoặc hệ thống sẽ tạo mật khẩu ngẫu nhiên an toàn. Sau khi hoàn tất, hãy gửi mật khẩu mới cho thành viên đăng nhập.
                    </p>

                    <div class="mb-3">
                        <label for="new_password" class="nature-label mb-1">Mật khẩu mới (tối thiểu 6 ký tự):</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="nature-input border-end-0 rounded-end-0" 
                                   id="new_password" 
                                   name="new_password" 
                                   placeholder="Để trống để hệ thống tự tạo mật khẩu mạnh..." 
                                   minlength="6">
                            <button type="button" class="btn btn-outline-secondary border-start-0 rounded-end-pill px-3" id="btnGenPassword" title="Tự sinh mật khẩu">
                                <i class="bi bi-arrow-repeat me-1"></i> Sinh ngẫu nhiên
                            </button>
                        </div>
                        <div class="form-text small text-muted">Ví dụ tự sinh: <code>Forum@782341</code>. Bạn có thể nhấn <strong>Sinh ngẫu nhiên</strong> để điền nhanh.</div>
                    </div>

                    <div class="alert alert-warning border-0 rounded-4 small mb-0">
                        <i class="bi bi-shield-exclamation me-1"></i>
                        <strong>Lưu ý:</strong> Mật khẩu cũ của thành viên sẽ lập tức bị vô hiệu hóa sau khi cấp lại.
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="nature-btn-primary px-4 py-2" style="width: auto; height: 42px;">
                        <i class="bi bi-check2-circle me-1"></i> Xác nhận cấp lại
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;
    
    // 1. Modal xem chi tiết thành viên
    const detailModalEl = document.getElementById('userDetailModal');
    const detailModal = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;
    let currentSelectedUserId = null;
    let currentSelectedUsername = '';

    document.querySelectorAll('.btn-view-user').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentSelectedUserId = this.getAttribute('data-id');
            currentSelectedUsername = this.getAttribute('data-username');
            const email = this.getAttribute('data-email');
            const role = this.getAttribute('data-role');
            const posts = this.getAttribute('data-posts');
            const joined = this.getAttribute('data-joined');
            const bio = this.getAttribute('data-bio');
            const avatar = this.getAttribute('data-avatar');

            document.getElementById('detailUserId').textContent = '#' + currentSelectedUserId;
            document.getElementById('detailUsername').textContent = currentSelectedUsername;
            document.getElementById('detailUserEmail').textContent = email;
            document.getElementById('detailUserPosts').textContent = posts + ' bài';
            document.getElementById('detailUserJoined').textContent = joined;
            document.getElementById('detailUserBio').textContent = bio;
            document.getElementById('detailUserAvatar').src = avatar;

            const badge = document.getElementById('detailUserRoleBadge');
            if (role === 'admin') {
                badge.className = 'badge rounded-pill text-bg-danger mt-1';
                badge.textContent = 'Quản trị viên (Admin)';
            } else {
                badge.className = 'badge rounded-pill text-bg-success mt-1';
                badge.textContent = 'Thành viên (Member)';
            }

            if (detailModal) {
                detailModal.show();
            }
        });
    });

    // 2. Modal Cấp lại mật khẩu
    const resetModalEl = document.getElementById('resetPasswordModal');
    const resetModal = resetModalEl ? new bootstrap.Modal(resetModalEl) : null;
    const resetForm = document.getElementById('resetPasswordForm');
    const resetTargetSpan = document.getElementById('resetTargetUsername');
    const newPasswordInput = document.getElementById('new_password');
    const btnGenPassword = document.getElementById('btnGenPassword');

    function openResetPasswordModal(userId, username) {
        if (!resetForm) return;
        resetForm.action = baseUrl + '/admin/users/' + userId + '/reset-password';
        resetTargetSpan.textContent = username;
        newPasswordInput.value = '';
        if (resetModal) {
            resetModal.show();
        }
    }

    document.querySelectorAll('.btn-reset-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            openResetPasswordModal(userId, username);
        });
    });

    // Nút chuyển từ modal xem chi tiết sang cấp lại mật khẩu
    const btnSwitch = document.getElementById('btnSwitchToResetPassword');
    if (btnSwitch) {
        btnSwitch.addEventListener('click', function() {
            if (detailModal) {
                detailModal.hide();
            }
            if (currentSelectedUserId) {
                setTimeout(function() {
                    openResetPasswordModal(currentSelectedUserId, currentSelectedUsername);
                }, 300);
            }
        });
    }

    // 3. Tự sinh mật khẩu ngẫu nhiên khi bấm nút
    if (btnGenPassword && newPasswordInput) {
        btnGenPassword.addEventListener('click', function() {
            const randomDigits = Math.floor(100000 + Math.random() * 900000);
            newPasswordInput.value = 'Forum@' + randomDigits;
            newPasswordInput.focus();
            newPasswordInput.select();
        });
    }

    // 4. Sao chép mật khẩu vừa cấp lại vào clipboard
    const btnCopy = document.getElementById('btnCopyPassword');
    const resetPasswordValue = <?= json_encode($resetResult['password'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
    if (btnCopy && resetPasswordValue) {
        btnCopy.dataset.password = resetPasswordValue;
    }
    if (btnCopy) {
        btnCopy.addEventListener('click', function() {
            const pass = this.dataset['password'];
            if (!pass) return;
            navigator.clipboard.writeText(pass).then(function() {
                btnCopy.innerHTML = '<i class="bi bi-check-lg me-1"></i> Đã sao chép!';
                btnCopy.classList.remove('btn-dark');
                btnCopy.classList.add('btn-success');
                setTimeout(function() {
                    btnCopy.innerHTML = '<i class="bi bi-clipboard me-1"></i> Sao chép mật khẩu';
                    btnCopy.classList.remove('btn-success');
                    btnCopy.classList.add('btn-dark');
                }, 2500);
            }).catch(function() {
                alert('Mật khẩu mới: ' + pass);
            });
        });
    }
});
</script>
