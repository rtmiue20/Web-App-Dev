<?php
$title = 'Đăng nhập';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="hero-scene" style="justify-content: center; align-items: center; padding: 6rem 1rem 3rem 1rem;">
    <!-- Dimmed Background Elements -->
    <div class="scene-floor" aria-hidden="true"></div>
    
    <!-- Dimmed Moss Centerpiece -->
    <div class="moss-container" style="opacity: 0.35; filter: blur(3px);" aria-hidden="true">
        <div class="parallax" style="--depth: 20px;">
            <div class="moss-breathe">
                <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/moss-form.png" alt="">
            </div>
        </div>
    </div>

    <!-- Login Card -->
    <div class="nature-card-wrap parallax frame-in" style="--depth: 8px;">
        <div class="nature-card">
            <p class="nature-label">Chào mừng trở lại</p>
            <h1 class="h2 fw-normal mb-2" style="color: var(--color-ink);">Đăng nhập</h1>
            <p class="small mb-4" style="color: #6B6E64;">
                Vào cộng đồng dã ngoại để chia sẻ và đọc kinh nghiệm.
            </p>

            <?php if (!empty($errors['form'])): ?>
                <div class="nature-alert nature-alert-error mb-3" style="padding: 0.75rem 1rem; font-size: 0.9rem;">
                    <i class="bi bi-exclamation-circle-fill me-1"></i><?= htmlspecialchars($errors['form'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <!-- Email / Account Field -->
                <div class="mb-3">
                    <label for="email" class="nature-label">Tài khoản hoặc email</label>
                    <input class="nature-input" 
                           id="email" 
                           name="email" 
                           type="text" 
                           required 
                           autocomplete="username" 
                           placeholder="ban@daingoai.vn" 
                           value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password Field with Show/Hide Toggle -->
                <div class="mb-4">
                    <label for="password" class="nature-label">Mật khẩu</label>
                    <div style="position: relative;">
                        <input class="nature-input" 
                               style="padding-right: 3rem;" 
                               id="password" 
                               name="password" 
                               type="password" 
                               required 
                               autocomplete="current-password" 
                               placeholder="••••••••">
                        <button type="button" 
                                class="btn-toggle-password" 
                                data-target="password"
                                aria-label="Hiện hoặc ẩn mật khẩu"
                                style="position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #8A8D83; cursor: pointer; padding: 0.4rem; display: flex; align-items: center; justify-content: center;">
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
                    <?php if (!empty($errors['password'])): ?>
                        <div class="text-danger small mt-1"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="nature-btn-primary mb-3">
                    Đăng nhập
                </button>
            </form>

            <div class="text-center mt-3 pt-2" style="border-top: 1px solid #e0e3da;">
                <p class="small mb-0" style="color: #6B6E64;">
                    Chưa có tài khoản? 
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/register" 
                       style="color: var(--color-ink); font-weight: 500; text-decoration: underline;">
                        Đăng ký ngay
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>