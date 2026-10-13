<?php $title = 'Hồ sơ'; ?>
<section class="card shadow-sm mx-auto" style="max-width: 640px">
    <div class="card-body p-4">
        <h1 class="h3">Xin chào, <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></h1>
        <dl class="row mt-4 mb-0">
            <dt class="col-sm-4">Email</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-sm-4">Vai trò</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($user['role_name'], ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-sm-4">Ngày tham gia</dt>
            <dd class="col-sm-8"><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></dd>
        </dl>
    </div>
</section>