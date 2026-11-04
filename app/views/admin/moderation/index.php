<?php
$title = 'Hàng chờ kiểm duyệt & Báo cáo vi phạm';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="app-container" style="max-width: 1100px;">
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="nature-badge mb-2" style="background: rgba(231, 76, 60, 0.15); color: #ff6b81; border-color: rgba(231, 76, 60, 0.3);">
                <i class="bi bi-shield-exclamation me-1"></i>Khu vực Moderator &amp; Admin
            </span>
            <h1 class="page-title">Hàng chờ kiểm duyệt &amp; Báo cáo vi phạm</h1>
            <p class="page-subtitle">Theo dõi, xét duyệt bài viết mới và xử lý các báo cáo vi phạm từ cộng đồng.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="nature-btn-secondary px-3 py-2 text-white border-white-20" style="height: 44px;">
                <i class="bi bi-arrow-left me-1"></i> Về Bảng điều khiển
            </a>
        </div>
    </div>

    <!-- Stats & Tabs -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/moderation?tab=reports" class="text-decoration-none">
                <div class="nature-card p-3 h-100 <?= ($currentTab === 'reports') ? 'border-primary' : '' ?>" style="border-radius: 20px; transition: transform 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background: rgba(231, 76, 60, 0.15); color: #e74c3c;">
                                <i class="bi bi-flag-fill fs-5"></i>
                            </span>
                            <div>
                                <h2 class="h6 mb-0 text-dark fw-bold">Báo cáo vi phạm</h2>
                                <span class="text-muted small">Cần người điều hành xem xét</span>
                            </div>
                        </div>
                        <span class="badge rounded-pill text-bg-danger fs-6 px-3 py-2"><?= (int)$pendingReportsCount ?></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/moderation?tab=posts" class="text-decoration-none">
                <div class="nature-card p-3 h-100 <?= ($currentTab === 'posts') ? 'border-primary' : '' ?>" style="border-radius: 20px; transition: transform 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background: rgba(241, 196, 15, 0.18); color: #f39c12;">
                                <i class="bi bi-hourglass-split fs-5"></i>
                            </span>
                            <div>
                                <h2 class="h6 mb-0 text-dark fw-bold">Bài viết chờ duyệt</h2>
                                <span class="text-muted small">Bài viết mới từ thành viên</span>
                            </div>
                        </div>
                        <span class="badge rounded-pill text-bg-warning fs-6 px-3 py-2 text-dark"><?= (int)$pendingPostsCount ?></span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <?php if ($currentTab === 'posts'): ?>
        <!-- Danh sách bài viết chờ duyệt -->
        <div class="nature-card p-4" style="border-radius: 28px;">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="h5 mb-0 fw-bold text-dark"><i class="bi bi-hourglass-split text-warning me-2"></i>Hàng chờ duyệt bài viết (<?= count($pendingPosts) ?>)</h2>
                <span class="text-muted small">Chỉ thành viên có quyền Moderator/Admin mới thao tác được</span>
            </div>

            <?php if (empty($pendingPosts)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                    <p class="mb-0">Tuyệt vời! Hiện không có bài viết nào đang chờ duyệt.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bài viết</th>
                                <th>Tác giả</th>
                                <th>Chuyên mục</th>
                                <th>Thời gian gửi</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingPosts as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small text-truncate" style="max-width: 320px;"><?= htmlspecialchars(mb_substr($p['content'], 0, 100), ENT_QUOTES, 'UTF-8') ?>...</div>
                                    </td>
                                    <td><span class="badge text-bg-light border"><?= htmlspecialchars($p['username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><span class="badge text-bg-secondary"><?= htmlspecialchars($p['category_name'] ?? 'Dã ngoại', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="small text-muted"><?= htmlspecialchars($p['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= (int)$p['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Xem bài">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts/<?= (int)$p['id'] ?>/approve" class="d-inline">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-sm btn-success" title="Phê duyệt bài">
                                                    <i class="bi bi-check-lg me-1"></i>Duyệt
                                                </button>
                                            </form>
                                            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/posts/<?= (int)$p['id'] ?>/hide" class="d-inline">
                                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Từ chối / Ẩn bài" onclick="return confirm('Bạn có chắc chắn muốn ẩn bài viết này?');">
                                                    <i class="bi bi-x-lg me-1"></i>Từ chối
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
    <?php else: ?>
        <!-- Danh sách báo cáo vi phạm -->
        <div class="nature-card p-4" style="border-radius: 28px;">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="h5 mb-0 fw-bold text-dark"><i class="bi bi-flag-fill text-danger me-2"></i>Danh sách báo cáo vi phạm</h2>
                <div class="btn-group btn-group-sm">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/moderation?tab=reports&status=pending" class="btn <?= ($currentStatus === 'pending') ? 'btn-danger' : 'btn-outline-secondary' ?>">Đang chờ (<?= $pendingReportsCount ?>)</a>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/moderation?tab=reports&status=all" class="btn <?= ($currentStatus === 'all') ? 'btn-dark' : 'btn-outline-secondary' ?>">Tất cả</a>
                </div>
            </div>

            <?php if (empty($reports)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
                    <p class="mb-0">Không có báo cáo vi phạm nào trong danh sách.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Đối tượng</th>
                                <th>Lý do vi phạm</th>
                                <th>Người báo cáo</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Xử lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): 
                                $rId = (int)$r['id'];
                                $tType = $r['target_type'];
                                $tId = (int)$r['target_id'];
                                $info = $r['target_info'] ?? [];
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-bg-<?= ($tType === 'post') ? 'primary' : (($tType === 'comment') ? 'info' : 'warning') ?> text-uppercase" style="font-size: 10px;">
                                                <?= htmlspecialchars($tType, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <?php if ($tType === 'post'): ?>
                                                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/posts/<?= $tId ?>" target="_blank" class="fw-semibold text-decoration-none text-dark">
                                                    <?= htmlspecialchars($info['title'] ?? ('Bài viết #' . $tId), ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            <?php elseif ($tType === 'comment'): ?>
                                                <span class="small text-muted text-truncate" style="max-width: 250px;">
                                                    "<?= htmlspecialchars($info['content'] ?? ('Bình luận #' . $tId), ENT_QUOTES, 'UTF-8') ?>"
                                                </span>
                                            <?php else: ?>
                                                <span class="fw-semibold"><?= htmlspecialchars($info['username'] ?? ('User #' . $tId), ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-danger small"><?= htmlspecialchars($r['reason'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php if (!empty($r['details'])): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($r['details'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="small text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($r['reporter_username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></span>
                                        <div class="small text-muted" style="font-size: 11px;"><?= htmlspecialchars($r['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] === 'pending'): ?>
                                            <span class="badge text-bg-warning text-dark"><i class="bi bi-clock me-1"></i>Chờ xử lý</span>
                                        <?php elseif ($r['status'] === 'resolved'): ?>
                                            <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Đã xử lý</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary"><i class="bi bi-dash-circle me-1"></i>Bác bỏ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($r['status'] === 'pending'): ?>
                                            <div class="d-flex justify-content-end gap-1">
                                                <!-- Nút Ẩn nội dung vi phạm -->
                                                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/moderation/<?= $rId ?>/resolve">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="action" value="hide_target">
                                                    <input type="hidden" name="target_type" value="<?= htmlspecialchars($tType, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="target_id" value="<?= $tId ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger px-2" title="Ẩn nội dung và đánh dấu vi phạm">
                                                        <i class="bi bi-eye-slash-fill me-1"></i>Ẩn vi phạm
                                                    </button>
                                                </form>

                                                <!-- Nút Bác bỏ báo cáo -->
                                                <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin/moderation/<?= $rId ?>/resolve">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="action" value="dismiss">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary px-2" title="Bác bỏ báo cáo (không vi phạm)">
                                                        <i class="bi bi-x-circle me-1"></i>Bác bỏ
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="small text-muted">Bởi: <?= htmlspecialchars($r['resolved_by_username'] ?? 'Mod', ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
