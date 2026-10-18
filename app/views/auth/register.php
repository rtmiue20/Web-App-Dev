<?php
$title = 'Đăng ký';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="hero-scene" style="justify-content: center; align-items: center; padding: 6.5rem 1rem 3rem 1rem;">
    <!-- Dimmed Background Elements -->
    <div class="scene-floor" aria-hidden="true"></div>
    
    <div class="moss-container" style="opacity: 0.25; filter: blur(4px);" aria-hidden="true">
        <div class="parallax" style="--depth: 16px;">
            <div class="moss-breathe">
                <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/moss-form.png" alt="">
            </div>
        </div>
    </div>

    <!-- Register Card -->
    <div class="nature-card-wrap parallax frame-in" style="--depth: 8px; max-width: 500px;">
        <div class="nature-card">
            <p class="nature-label">Gia nhập cộng đồng</p>
            <h1 class="h2 fw-normal mb-2" style="color: var(--color-ink);">Tạo tài khoản</h1>
            <p class="small mb-4" style="color: #6B6E64;">
                Cùng chia sẻ kinh nghiệm và khám phá những cung đường mới.
            </p>

            <?php if (!empty($errors['form'])): ?>
                <div class="nature-alert nature-alert-error mb-3" style="padding: 0.75rem 1rem; font-size: 0.9rem;">
                    <i class="bi bi-exclamation-circle-fill me-1"></i><?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/register">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <!-- Username -->
                <div class="mb-3">
                    <label for="username" class="nature-label">Tên người dùng</label>
                    <input class="nature-input" 
                           id="username" 
                           name="username" 
                           type="text" 
                           required 
                           minlength="3" 
                           maxlength="50"
                           autocomplete="username" 
                           placeholder="Ví dụ: phuotthubienhoa"
                           value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['username'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="nature-label">Địa chỉ email</label>
                    <input class="nature-input" 
                           id="email" 
                           name="email" 
                           type="email" 
                           required 
                           maxlength="120"
                           autocomplete="email" 
                           placeholder="ban@daingoai.vn"
                           value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="nature-label">Mật khẩu (tối thiểu 8 ký tự)</label>
                    <input class="nature-input" 
                           id="password" 
                           name="password" 
                           type="password" 
                           required 
                           minlength="8" 
                           autocomplete="new-password"
                           placeholder="••••••••">
                    <?php if (!empty($errors['password'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="nature-label">Nhập lại mật khẩu</label>
                    <input class="nature-input" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           type="password" 
                           required 
                           minlength="8" 
                           autocomplete="new-password"
                           placeholder="••••••••">
                    <?php if (!empty($errors['password_confirmation'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['password_confirmation'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="nature-btn-primary mb-3">
                    Đăng ký tài khoản
                </button>
            </form>

            <div class="text-center mt-3 pt-2" style="border-top: 1px solid #e0e3da;">
                <p class="small mb-0" style="color: #6B6E64;">
                    Đã có tài khoản? 
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login" 
                       style="color: var(--color-ink); font-weight: 500; text-decoration: underline;">
                        Đăng nhập
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>