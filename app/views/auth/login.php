<?php
$title = 'Đăng nhập';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="hero-scene" style="justify-content: center; align-items: center; padding: 6rem 1rem 3rem 1rem; position: relative;">
    <!-- Dimmed Background Elements -->
    <div class="scene-floor" aria-hidden="true"></div>
    
    <!-- Dimmed Moss Centerpiece -->
    <div class="moss-container" style="opacity: 0.35; filter: blur(3px);" aria-hidden="true">
        <div class="parallax" style="--depth: 20px;">
            <div class="moss-breathe">
                <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/moss-form.png" alt="" loading="lazy">
            </div>
        </div>
    </div>

    <!-- Login Card -->
    <div class="nature-card-wrap parallax frame-in" id="login-card-wrap" style="--depth: 8px; width: 460px; max-width: 100%;">
        <div class="nature-card" id="login-card">
            <p class="nature-label" style="letter-spacing: 0.18em; font-size: 11px; font-weight: 600; text-transform: uppercase;">Chào mừng trở lại</p>
            <h1 class="h2 fw-medium mb-2" style="color: var(--color-ink); font-size: 34px;">Đăng nhập</h1>
            <p class="small mb-4" style="color: #7A7D72; font-size: 14px; line-height: 1.6;">
                Vào cộng đồng dã ngoại để chia sẻ và đọc kinh nghiệm.
            </p>

            <div id="login-alert-box" class="nature-alert nature-alert-error mb-3" style="display: <?= !empty($errors['form']) ? 'block' : 'none' ?>; padding: 0.75rem 1rem; font-size: 0.9rem;">
                <i class="bi bi-exclamation-circle-fill me-1"></i>
                <span id="login-alert-text"><?= htmlspecialchars($errors['form'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <form id="login-form" method="post" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/login">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

                <!-- Email / Account Field -->
                <div class="mb-3">
                    <label for="email" class="nature-label" style="letter-spacing: 0.14em; font-size: 10px; font-weight: 600;">Tài khoản hoặc email</label>
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
                    <label for="password" class="nature-label" style="letter-spacing: 0.14em; font-size: 10px; font-weight: 600;">Mật khẩu</label>
                    <div style="position: relative;">
                        <input class="nature-input" 
                               style="padding-right: 3rem;" 
                               id="password" 
                               name="password" 
                               type="password" 
                               required 
                               autocomplete="current-password" 
                               placeholder="••••••••"
                               value="">
                        <button type="button" 
                                id="btn-toggle-pw"
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
                <button type="submit" id="btn-submit-login" class="nature-btn-primary mb-3" style="width: 100%; height: 52px; border-radius: 999px; font-weight: 500; font-size: 15px;">
                    Đăng nhập
                </button>
            </form>

            <div class="text-center mt-3 pt-3" style="border-top: 1px solid #e0e3da;">
                <p class="small mb-0" style="color: #7A7D72; font-size: 13.5px;">
                    Chưa có tài khoản? 
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/register" 
                       style="color: var(--color-ink); font-weight: 600; text-decoration: underline; text-underline-offset: 3px;">
                        Đăng ký ngay
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- INTRO OVERLAY (Hiệu ứng logo reveal chạy trong lúc xử lý đăng nhập)     -->
<!-- ========================================================================= -->
<div class="intro" id="intro" aria-hidden="true">
    <div class="lock">
        <div class="m">
            <div class="disc"></div>
            <svg class="ring" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="49" pathLength="100"/>
            </svg>
            <img class="mk" src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-logo.png" alt="Outdoor Forum Logo">
        </div>
        <div class="w">
            <img src="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/img/celtic-wordmark.png" alt="Outdoor Forum">
        </div>
    </div>
    <div class="tag">Cộng đồng dã ngoại</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const loginCard = document.getElementById('login-card-wrap');
    const intro = document.getElementById('intro');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('btn-submit-login');
    const alertBox = document.getElementById('login-alert-box');
    const alertText = document.getElementById('login-alert-text');

    // Password Show/Hide Toggle
    const toggleBtn = document.getElementById('btn-toggle-pw');
    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            const isPw = passwordInput.type === 'password';
            passwordInput.type = isPw ? 'text' : 'password';
            const iconOpen = toggleBtn.querySelector('.icon-eye-open');
            const iconClosed = toggleBtn.querySelector('.icon-eye-closed');
            if (iconOpen && iconClosed) {
                iconOpen.style.display = isPw ? 'none' : 'block';
                iconClosed.style.display = isPw ? 'block' : 'none';
            }
        });
    }

    if (!loginForm) return;

    let busy = false;
    // Thời gian hiệu ứng mượt mà (~2.2s tổng cộng)
    const ANIM_MS = 2000;
    const HOLD_MS = 300;
    const totalMs = ANIM_MS;

    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (busy) return;

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        if (!email || !password) {
            if (alertBox) {
                alertText.textContent = 'Vui lòng nhập đầy đủ tài khoản và mật khẩu.';
                alertBox.style.display = 'block';
            }
            return;
        }

        // 1. Thu thập dữ liệu form trước khi disable input (tránh việc FormData bỏ qua các trường disabled)
        const formData = new FormData(loginForm);
        formData.set('email', email);
        formData.set('password', password);
        formData.set('ajax', '1');

        busy = true;
        emailInput.disabled = true;
        passwordInput.disabled = true;
        submitBtn.disabled = true;

        if (alertBox) alertBox.style.display = 'none';

        // 2. Thẻ đăng nhập thu nhẹ và mờ dần
        loginCard.classList.add('out');

        // 3. Gửi request đăng nhập AJAX song song với animation

        let apiPromise = fetch(loginForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(async res => {
            const data = await res.json().catch(() => null);
            return { ok: res.ok, status: res.status, data: data };
        }).catch(err => {
            return { ok: false, error: err };
        });

        // 3. Đợi 200ms cho card fade out rồi kích hoạt intro reveal
        await new Promise(r => setTimeout(r, 200));

        intro.style.setProperty('--d', totalMs + 'ms');
        intro.classList.remove('play');
        void intro.offsetWidth; // Force reflow
        intro.classList.add('show', 'play');

        // 4. Chờ animation hoàn thiện và nhận kết quả API song song
        const [result] = await Promise.all([
            apiPromise,
            new Promise(r => setTimeout(r, totalMs + HOLD_MS))
        ]);

        if (result && result.ok && result.data && result.data.success) {
            // Thành công: chuyển trang sang Trang chủ
            const redirectUrl = (result.data.data && result.data.data.redirect) 
                             || result.data.redirect 
                             || (window.BASE_URL ? window.BASE_URL + '/' : '/');
            window.location.href = redirectUrl;
        } else {
            // Thất bại: đóng intro, khôi phục form và hiển thị thông báo lỗi
            intro.classList.remove('show');
            await new Promise(r => setTimeout(r, 250));
            intro.classList.remove('play');

            loginCard.classList.remove('out');
            emailInput.disabled = false;
            passwordInput.disabled = false;
            submitBtn.disabled = false;
            busy = false;

            const errorMsg = (result && result.data && (result.data.message || (result.data.data && result.data.data.message)))
                || 'Tài khoản hoặc mật khẩu không chính xác.';
            if (alertBox) {
                alertText.textContent = errorMsg;
                alertBox.style.display = 'block';
            }
        }
    });
});
</script>
