<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$user = class_exists('Auth') ? Auth::user() : Session::get('user');
$userId = is_array($user) ? (int) $user['id'] : null;
$isAdmin = class_exists('Auth') ? Auth::isModerator($user) : (is_array($user) && (((int) ($user['role_id'] ?? 0) === 2) || (($user['role_name'] ?? '') === 'admin')));
$isMember = class_exists('Auth') ? Auth::isMember($user) : is_array($user);
$isGuestAccount = is_array($user) && !$isMember;
$escape = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<main class="app-container" style="max-width: 1100px;">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 page-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background: rgba(155, 189, 85, 0.18); color: var(--color-moss); border: 1px solid rgba(155, 189, 85, 0.35);">
                    <i class="bi bi-card-checklist me-1"></i>Hành trang dã ngoại
                </span>
            </div>
            <h1 class="page-title mb-1">Checklist đồ dùng</h1>
            <p class="page-subtitle mb-0">Lên danh sách và kiểm tra trang bị dã ngoại trước khi lên đường, tránh quên vật dụng quan trọng.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="nature-btn-secondary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/trips" style="height: 42px; font-size: 13.5px;">
                <i class="bi bi-people-fill"></i> Nhóm đi chung
            </a>
            <a class="nature-btn-secondary px-3 py-2 d-inline-flex align-items-center gap-1" href="<?= $escape($baseUrl) ?>/places" style="height: 42px; font-size: 13.5px;">
                <i class="bi bi-map-fill"></i> Bản đồ
            </a>
        </div>
    </div>

    <!-- Category / Trip Type Filter Pills -->
    <?php if (!empty($tripTypes)): ?>
        <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
            <span class="text-white-50 small me-1"><i class="bi bi-funnel me-1"></i>Chủ đề:</span>
            <a href="<?= $escape($baseUrl) ?>/checklists"
               class="badge rounded-pill text-decoration-none px-3 py-2 <?= empty($currentType) || $currentType === 'all' ? 'text-bg-success' : 'text-bg-dark border border-white-20' ?>">
                Tất cả
            </a>
            <?php foreach ($tripTypes as $type): ?>
                <a href="<?= $escape($baseUrl) ?>/checklists?type=<?= urlencode($type) ?>"
                   class="badge rounded-pill text-decoration-none px-3 py-2 <?= $currentType === $type ? 'text-bg-success' : 'text-bg-dark border border-white-20' ?>">
                    <?= $escape($type) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Create Checklist Card (Member / Moderator / Admin only) -->
    <?php if ($isMember): ?>
        <section class="nature-card p-4 mb-4" style="border-radius: 28px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: rgba(155, 189, 85, 0.2); color: var(--color-moss);">
                        <i class="bi bi-plus-circle-fill fs-5"></i>
                    </span>
                    <h2 class="h5 mb-0 fw-semibold" style="color: var(--color-ink);">Tạo checklist mới</h2>
                </div>
                <span class="text-muted small">Cá nhân hoặc chia sẻ</span>
            </div>

            <form method="post" action="<?= $escape($baseUrl) ?>/checklists">
                <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="nature-label" for="checklist-title">Tên checklist <span class="text-danger">*</span></label>
                        <input class="nature-input" id="checklist-title" name="title" maxlength="120" required
                               value="<?= $escape($old['title'] ?? '') ?>" placeholder="Ví dụ: Trekking Tà Năng 2N1Đ, Cắm trại cuối tuần...">
                        <?php if (!empty($errors['title'])): ?><div class="text-danger small mt-1"><?= $escape($errors['title']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="nature-label" for="trip-type">Loại chuyến đi <span class="text-danger">*</span></label>
                        <input class="nature-input" id="trip-type" name="trip_type" maxlength="60" required
                               value="<?= $escape($old['trip_type'] ?? '') ?>" placeholder="Cắm trại, Trekking, Leo núi, Săn mây...">
                        <?php if (!empty($errors['trip_type'])): ?><div class="text-danger small mt-1"><?= $escape($errors['trip_type']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="nature-label" for="checklist-items">Danh sách đồ dùng <span class="text-muted fw-normal">(Mỗi món một dòng, tối đa 50 món)</span> <span class="text-danger">*</span></label>
                        <textarea class="nature-textarea" id="checklist-items" name="items" rows="4" required
                                  placeholder="Balo dã ngoại 40L&#10;Lều 2 lớp chịu gió&#10;Túi ngủ 15°C&#10;Bình nước 2L&#10;Túi sơ cứu y tế"><?= $escape($old['items'] ?? '') ?></textarea>
                        <?php if (!empty($errors['items'])): ?><div class="text-danger small mt-1"><?= $escape($errors['items']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3 pt-2">
                        <label class="form-check-label d-flex align-items-center gap-2 cursor-pointer" style="color: var(--color-ink);">
                            <input class="form-check-input mt-0" type="checkbox" name="is_public" value="1"
                                   <?= !array_key_exists('is_public', $old) || isset($old['is_public']) ? 'checked' : '' ?>>
                            <span>Chia sẻ checklist công khai cho mọi thành viên cùng xem</span>
                        </label>
                        <button class="nature-btn-primary px-4 py-2" type="submit" style="width: auto; height: 44px;">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Lưu checklist
                        </button>
                    </div>
                </div>
            </form>
        </section>
    <?php elseif ($isGuestAccount): ?>
        <div class="p-3 mb-4 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(155, 189, 85, 0.12); border: 1px solid rgba(155, 189, 85, 0.25);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-eye-fill text-success fs-5"></i>
                <span class="text-white">Tài khoản của bạn đang ở vai trò <strong>Khách (Chỉ xem)</strong>. Bạn có thể xem và đánh dấu thử các mẫu checklist công khai, nhưng không thể tạo checklist mới.</span>
            </div>
        </div>
    <?php else: ?>
        <div class="p-3 mb-4 rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(155, 189, 85, 0.12); border: 1px solid rgba(155, 189, 85, 0.25);">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill text-success fs-5"></i>
                <span class="text-white">Bạn đang xem danh sách dã ngoại công khai.</span>
            </div>
            <a href="<?= $escape($baseUrl) ?>/login" class="nature-btn-primary py-1 px-3" style="width: auto; height: 36px; font-size: 13px;">
                Đăng nhập để tạo checklist riêng
            </a>
        </div>
    <?php endif; ?>

    <!-- Checklists Grid -->
    <div class="row g-4">
        <?php if (empty($checklists)): ?>
            <div class="col-12">
                <div class="nature-card text-center py-5" style="border-radius: 28px;">
                    <i class="bi bi-card-checklist fs-1 text-muted d-block mb-3"></i>
                    <h3 class="h5 fw-normal mb-1" style="color: var(--color-ink);">Chưa có checklist nào</h3>
                    <p class="text-muted small">Hãy tạo checklist đầu tiên để chuẩn bị cho chuyến dã ngoại sắp tới.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($checklists as $idx => $checklist): ?>
            <?php
            $isOwner = $userId !== null && (int) $checklist['user_id'] === $userId;
            $canDelete = $isMember && ($isOwner || $isAdmin);
            $items = $checklist['items'] ?? [];
            $totalItems = count($items);
            ?>
            <div class="col-12 col-md-6">
                <article class="nature-card h-100 d-flex flex-column p-4" style="border-radius: 28px;" id="checklist-card-<?= (int)$checklist['id'] ?>">
                    <!-- Card Top Header -->
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge text-bg-success" style="font-weight: 500;">
                                <i class="bi bi-tag-fill me-1"></i><?= $escape($checklist['trip_type']) ?>
                            </span>
                            <span class="badge <?= (int)$checklist['is_public'] === 1 ? 'text-bg-light border' : 'text-bg-secondary' ?>" style="font-size: 11px;">
                                <?= (int)$checklist['is_public'] === 1 ? '<i class="bi bi-globe me-1"></i>Công khai' : '<i class="bi bi-lock me-1"></i>Cá nhân' ?>
                            </span>
                        </div>

                        <?php if ($canDelete): ?>
                            <form method="post" action="<?= $escape($baseUrl) ?>/checklists/<?= (int) $checklist['id'] ?>/delete"
                                  onsubmit="return window.confirmFormSubmit(event, this, 'Bạn có chắc chắn muốn xóa checklist “<?= $escape(addslashes($checklist['title'])) ?>” khỏi danh sách trang bị không? Hành động này sẽ gỡ checklist khỏi danh mục chung.', 'Xác nhận xóa', 'Xóa checklist khỏi danh sách?');" class="m-0">
                                <input type="hidden" name="_csrf" value="<?= $escape(Csrf::token()) ?>">
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Xóa checklist" aria-label="Xóa checklist">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Checklist Title -->
                    <h2 class="h5 fw-semibold mt-1 mb-1" style="color: var(--color-ink);">
                        <?= $escape($checklist['title']) ?>
                    </h2>

                    <!-- Author Info & Progress -->
                    <div class="d-flex align-items-center justify-content-between text-muted small mb-3 pb-2 border-bottom">
                        <span><i class="bi bi-person me-1"></i><?= $escape($checklist['username']) ?> · <?= date('d/m/Y', strtotime($checklist['created_at'])) ?></span>
                        <span class="fw-semibold text-success progress-text-<?= (int)$checklist['id'] ?>" style="font-size: 11.5px;">
                            0/<?= $totalItems ?> món
                        </span>
                    </div>

                    <!-- Interactive Checklist Items -->
                    <div class="checklist-items-box flex-grow-1 mb-3">
                        <?php foreach ($items as $itemIdx => $it): ?>
                            <?php $checkId = 'chk-' . (int)$checklist['id'] . '-' . (int)($it['id'] ?? $itemIdx); ?>
                            <div class="form-check py-1 d-flex align-items-center gap-2 border-bottom border-light-subtle">
                                <input class="form-check-input mt-0 checklist-item-cb" type="checkbox" id="<?= $checkId ?>"
                                       data-card-id="<?= (int)$checklist['id'] ?>" style="cursor: pointer; width: 1.15em; height: 1.15em;">
                                <label class="form-check-label mb-0 cursor-pointer flex-grow-1 checklist-label" for="<?= $checkId ?>"
                                       style="color: var(--color-ink); font-size: 14px; transition: all 150ms ease;">
                                    <?= $escape($it['item']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Action Bar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill" style="font-size: 11.5px;"
                                    onclick="toggleAllChecks(<?= (int)$checklist['id'] ?>)">
                                <i class="bi bi-check2-all me-1"></i>Chọn tất cả
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill py-1 px-2.5 fw-semibold"
                                    style="font-size: 11.5px; background: rgba(35, 61, 44, 0.08); color: #233d2c; border: 1px solid rgba(35, 61, 44, 0.22);"
                                    onclick="ForumAjax.openQrShareModal('/checklists#checklist-card-<?= (int)$checklist['id'] ?>', '<?= $escape(addslashes($checklist['title'])) ?>', 'checklist')"
                                    title="Mã QR chia sẻ checklist qua điện thoại">
                                <i class="bi bi-qr-code-scan text-success me-1"></i>Mã QR chia sẻ
                            </button>
                        </div>
                        <a href="<?= $escape($baseUrl) ?>/trips" class="text-success small text-decoration-none fw-semibold">
                            Rủ bạn đi chung <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Phân trang Checklist (2 ô / trang) -->
    <?php if (isset($totalPages) && $totalPages > 1): ?>
        <?php
        $typeParam = (!empty($currentType) && $currentType !== 'all') ? 'type=' . urlencode($currentType) . '&' : '';
        $currentPage = isset($currentPage) ? (int)$currentPage : 1;
        ?>
        <nav aria-label="Điều hướng phân trang checklist" class="mt-5 mb-4">
            <ul class="pagination forum-pagination justify-content-center flex-wrap">
                <!-- Nút trang trước -->
                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill" 
                       href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/checklists?<?= $typeParam ?>page=<?= max(1, $currentPage - 1) ?>" 
                       tabindex="<?= ($currentPage <= 1) ? '-1' : '0' ?>"
                       aria-disabled="<?= ($currentPage <= 1) ? 'true' : 'false' ?>">
                        <i class="bi bi-chevron-left me-1"></i> Trước
                    </a>
                </li>

                <!-- Danh sách số trang -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $currentPage) ? 'active' : '' ?>">
                        <a class="page-link rounded-pill" 
                           href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/checklists?<?= $typeParam ?>page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Nút trang sau -->
                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill" 
                       href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/checklists?<?= $typeParam ?>page=<?= min($totalPages, $currentPage + 1) ?>" 
                       tabindex="<?= ($currentPage >= $totalPages) ? '-1' : '0' ?>"
                       aria-disabled="<?= ($currentPage >= $totalPages) ? 'true' : 'false' ?>">
                        Sau <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </li>
            </ul>

            <div class="text-center text-white-50 small mt-2">
                Trang <?= $currentPage ?> / <?= $totalPages ?> (Hiển thị 2 checklist / trang • Tổng số <?= $totalChecklists ?? count($checklists) ?> checklist)
            </div>
        </nav>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lưu và đồng bộ trạng thái tick của checklist vào localStorage
    document.querySelectorAll('.checklist-item-cb').forEach(function(cb) {
        var cardId = cb.getAttribute('data-card-id');
        var storageKey = 'fs_chk_' + cb.id;
        
        if (localStorage.getItem(storageKey) === '1') {
            cb.checked = true;
            var label = cb.nextElementSibling;
            if (label) {
                label.style.textDecoration = 'line-through';
                label.style.opacity = '0.55';
            }
        }
        
        cb.addEventListener('change', function() {
            var label = cb.nextElementSibling;
            if (cb.checked) {
                localStorage.setItem(storageKey, '1');
                if (label) {
                    label.style.textDecoration = 'line-through';
                    label.style.opacity = '0.55';
                }
            } else {
                localStorage.removeItem(storageKey);
                if (label) {
                    label.style.textDecoration = 'none';
                    label.style.opacity = '1';
                }
            }
            updateChecklistProgress(cardId);
        });
    });

    // Cập nhật tiến độ ban đầu cho tất cả thẻ checklist
    var uniqueCards = new Set();
    document.querySelectorAll('.checklist-item-cb').forEach(function(cb) {
        uniqueCards.add(cb.getAttribute('data-card-id'));
    });
    uniqueCards.forEach(function(cardId) {
        updateChecklistProgress(cardId);
    });
});

function updateChecklistProgress(cardId) {
    var cbs = document.querySelectorAll('.checklist-item-cb[data-card-id="' + cardId + '"]');
    var total = cbs.length;
    var checked = 0;
    cbs.forEach(function(c) {
        if (c.checked) checked++;
    });
    var textEl = document.querySelector('.progress-text-' + cardId);
    if (textEl) {
        textEl.textContent = checked + '/' + total + ' món';
        if (checked === total && total > 0) {
            textEl.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Đã chuẩn bị đủ ' + total + ' món!';
        }
    }
}

function toggleAllChecks(cardId) {
    var cbs = document.querySelectorAll('.checklist-item-cb[data-card-id="' + cardId + '"]');
    var allChecked = true;
    cbs.forEach(function(c) {
        if (!c.checked) allChecked = false;
    });

    var targetState = !allChecked;
    cbs.forEach(function(cb) {
        cb.checked = targetState;
        var storageKey = 'fs_chk_' + cb.id;
        var label = cb.nextElementSibling;
        if (targetState) {
            localStorage.setItem(storageKey, '1');
            if (label) {
                label.style.textDecoration = 'line-through';
                label.style.opacity = '0.55';
            }
        } else {
            localStorage.removeItem(storageKey);
            if (label) {
                label.style.textDecoration = 'none';
                label.style.opacity = '1';
            }
        }
    });
    updateChecklistProgress(cardId);
}
</script>
