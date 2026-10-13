<?php $title = 'Quản trị'; ?>
<section class="card shadow-sm mx-auto" style="max-width: 720px">
    <div class="card-body p-4">
        <h1 class="h3">Khu vực quản trị</h1>
        <p class="mb-0">
            Xin chào quản trị viên
            <strong><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></strong>.
        </p>
    </div>
</section>