<?php
/**
 * Partial: Chatbox Trợ lý Dã ngoại Forum AI
 * Tích hợp Google Gemini API & Kho kiến thức dã ngoại Outdoor Forum
 * Thiết kế chuẩn phong cách Nature Paper & Liquid Glass
 * Nút Forum AI di chuyển linh động, cửa sổ Chatbox hiển thị chuẩn đẹp góc dưới phải
 */
$chatbotBaseUrl = $baseUrl ?? (defined('BASE_URL') ? BASE_URL : '');
$chatbotLogoUrl = htmlspecialchars($chatbotBaseUrl, ENT_QUOTES, 'UTF-8') . '/assets/img/celtic-logo.png';
?>

<!-- Nút kích hoạt Chatbox di chuyển linh động (Draggable Forum AI Launcher) -->
<div id="outdoorChatbotLauncher" 
     class="outdoor-chatbot-launcher" 
     title="Kéo thả để di chuyển • Nhấp để mở Forum AI"
     style="opacity: 0;">
    <div class="outdoor-chatbot-btn shadow-lg" id="outdoorChatbotToggleBtn" role="button" tabindex="0" aria-label="Mở Trợ lý Forum AI">
        <!-- Tay cầm kéo di chuyển (Drag Grip Handle) -->
        <span class="chatbot-drag-handle" title="Kéo để di chuyển vị trí">
            <i class="bi bi-grip-vertical"></i>
        </span>

        <!-- Logo Celtic của ứng dụng kèm chấm trạng thái Online -->
        <div class="chatbot-brand-wrap">
            <img src="<?= $chatbotLogoUrl ?>" alt="Forum AI Logo" class="chatbot-brand-img">
            <span class="chatbot-online-dot" title="Forum AI đang sẵn sàng"></span>
        </div>

        <!-- Nhãn tên Forum AI -->
        <span class="chatbot-brand-label">Forum AI</span>

        <!-- Biểu tượng AI lấp lánh -->
        <i class="bi bi-stars chatbot-sparkle-icon"></i>
    </div>
</div>

<!-- Khung cửa sổ trò chuyện Chatbox (Forum AI Window - Giữ chuẩn vị trí góc dưới phải thanh lịch) -->
<div id="outdoorChatbotWindow" class="outdoor-chatbot-window shadow-lg" style="display: none;" aria-hidden="true">
    <!-- Header Chatbox -->
    <div class="outdoor-chatbot-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="chatbot-avatar-box">
                <img src="<?= $chatbotLogoUrl ?>" alt="Forum AI Logo" class="chatbot-header-logo">
                <span class="chatbot-status-dot" title="Trực tuyến"></span>
            </div>
            <div>
                <h6 class="mb-0 fw-bold chatbot-title d-flex align-items-center gap-1">
                    <span>Forum AI</span>
                    <span class="chatbot-mini-tag">Gemini</span>
                </h6>
                <span class="chatbot-subtitle"><i class="bi bi-patch-check-fill text-success me-1"></i>Trợ lý Dã ngoại thông minh</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="chatbot-header-btn" id="outdoorChatbotClearBtn" title="Làm mới cuộc trò chuyện" aria-label="Xóa đoạn chat">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button type="button" class="chatbot-header-btn" id="outdoorChatbotCloseBtn" title="Đóng cửa sổ" aria-label="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Vùng hiển thị tin nhắn -->
    <div class="outdoor-chatbot-body" id="outdoorChatbotMessages">
        <!-- Tin nhắn chào mừng mặc định từ Forum AI -->
        <div class="chat-msg chat-msg-bot">
            <div class="chat-msg-avatar">
                <img src="<?= $chatbotLogoUrl ?>" alt="Forum AI" class="chat-bot-avatar-img">
            </div>
            <div class="chat-msg-bubble">
                <p class="mb-1">Xin chào! 👋 Mình là <strong>Forum AI</strong> — trợ lý dã ngoại &amp; cắm trại thông minh.</p>
                <p class="mb-2">Bạn cần mình gợi ý lịch trình, danh sách đồ dùng trekking hay cẩm nang sinh tồn cho chuyến đi nào?</p>
                
                <!-- Gợi ý câu hỏi nhanh -->
                <div class="chatbot-quick-prompts mt-2">
                    <span class="quick-prompt-label">Gợi ý câu hỏi nhanh:</span>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <button type="button" class="quick-prompt-btn" data-prompt="Đi cắm trại qua đêm lần đầu cần chuẩn bị những gì?">
                            🎒 Chuẩn bị đồ cắm trại
                        </button>
                        <button type="button" class="quick-prompt-btn" data-prompt="Gợi ý lịch trình dã ngoại 2 ngày 1 đêm chuẩn?">
                            🏕️ Lịch trình 2 ngày 1 đêm
                        </button>
                        <button type="button" class="quick-prompt-btn" data-prompt="Kinh nghiệm cắm trại săn mây Đà Lạt cần lưu ý gì?">
                            🌲 Săn mây Đà Lạt
                        </button>
                        <button type="button" class="quick-prompt-btn" data-prompt="Cách xử lý an toàn khi gặp mưa to bất ngờ lúc cắm trại?">
                            🌧️ Xử lý khi gặp mưa rừng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Khung đang gõ (Typing Indicator) -->
    <div id="outdoorChatbotTyping" class="outdoor-chatbot-typing px-3 py-1" style="display: none;">
        <div class="typing-bubble">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="small ms-2 text-muted fw-semibold">Forum AI đang suy nghĩ...</span>
        </div>
    </div>

    <!-- Footer nhập tin nhắn (Ô input dạng viên thuốc thanh lịch, tự động cuộn chữ theo con trỏ) -->
    <div class="outdoor-chatbot-footer">
        <form id="outdoorChatbotForm" class="d-flex align-items-center gap-2">
            <input type="text" 
                   id="outdoorChatbotInput" 
                   class="form-control chatbot-input" 
                   placeholder="Nhập câu hỏi dã ngoại cho Forum AI..." 
                   autocomplete="off" 
                   maxlength="1000">
            <button type="submit" class="btn chatbot-send-btn" id="outdoorChatbotSendBtn" aria-label="Gửi tin nhắn">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
        <div class="chatbot-footer-caption text-center mt-1">
            <small>Tích hợp Google Gemini &amp; Kho tri thức Outdoor Forum</small>
        </div>
    </div>
</div>

<!-- Script xử lý Chatbox & Draggable (TUYỆT ĐỐI KHÔNG DÙNG ARROW FUNCTION =>) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var launcher = document.getElementById('outdoorChatbotLauncher');
    var toggleBtn = document.getElementById('outdoorChatbotToggleBtn');
    var windowEl = document.getElementById('outdoorChatbotWindow');
    var closeBtn = document.getElementById('outdoorChatbotCloseBtn');
    var clearBtn = document.getElementById('outdoorChatbotClearBtn');
    var formEl = document.getElementById('outdoorChatbotForm');
    var inputEl = document.getElementById('outdoorChatbotInput');
    var messagesEl = document.getElementById('outdoorChatbotMessages');
    var typingEl = document.getElementById('outdoorChatbotTyping');
    var sendBtn = document.getElementById('outdoorChatbotSendBtn');

    if (!launcher || !toggleBtn || !windowEl || !formEl || !inputEl || !messagesEl) {
        return;
    }

    var botLogoUrl = <?= json_encode($chatbotLogoUrl, JSON_UNESCAPED_SLASHES) ?>;
    var chatHistory = [];
    var isOpen = false;
    var storageKey = 'outdoor_forum_ai_btn_pos';

    // ─────────────────────────────────────────────────────────────
    // 1. TÍNH NĂNG DI CHUYỂN LINH ĐỘNG (DRAG & DROP) CHO NÚT LAUNCHER
    // ─────────────────────────────────────────────────────────────
    var isPointerDown = false;
    var hasMovedDistance = false;
    var startPointerX = 0;
    var startPointerY = 0;
    var startElementLeft = 0;
    var startElementTop = 0;
    var lastDragTimestamp = 0;
    var lastToggleTimestamp = 0;
    var capturedPointerId = null;

    // Giới hạn tọa độ trong màn hình (Clamping)
    function clampLauncherPosition(left, top) {
        var pad = 12;
        var launcherWidth = launcher.offsetWidth || 150;
        var launcherHeight = launcher.offsetHeight || 48;
        var maxLeft = window.innerWidth - launcherWidth - pad;
        var maxTop = window.innerHeight - launcherHeight - pad;

        var safeLeft = Math.max(pad, Math.min(left, maxLeft));
        var safeTop = Math.max(pad, Math.min(top, maxTop));

        return {
            left: safeLeft,
            top: safeTop
        };
    }

    // Khôi phục vị trí đã lưu trong LocalStorage hoặc khởi tạo mặc định góc dưới phải
    function restoreLauncherPosition() {
        var pad = 16;
        var launcherWidth = launcher.offsetWidth || 150;
        var launcherHeight = launcher.offsetHeight || 48;

        var savedPos = null;
        try {
            var raw = localStorage.getItem(storageKey);
            if (raw) {
                savedPos = JSON.parse(raw);
            }
        } catch (err) {
            savedPos = null;
        }

        var targetLeft, targetTop;
        if (savedPos && typeof savedPos.left === 'number' && typeof savedPos.top === 'number') {
            targetLeft = savedPos.left;
            targetTop = savedPos.top;
        } else {
            // Vị trí mặc định: góc dưới bên phải
            targetLeft = window.innerWidth - launcherWidth - pad;
            targetTop = window.innerHeight - launcherHeight - pad;
        }

        var clamped = clampLauncherPosition(targetLeft, targetTop);
        launcher.style.left = clamped.left + 'px';
        launcher.style.top = clamped.top + 'px';
        launcher.style.right = 'auto';
        launcher.style.bottom = 'auto';
        launcher.style.opacity = '1';
    }

    // Bắt đầu nhấn chuột / chạm tay (Pointer Down)
    function onPointerDown(e) {
        if (e.button !== undefined && e.button !== 0) {
            return;
        }

        isPointerDown = true;
        hasMovedDistance = false;
        capturedPointerId = e.pointerId;

        startPointerX = e.clientX;
        startPointerY = e.clientY;

        var rect = launcher.getBoundingClientRect();
        startElementLeft = rect.left;
        startElementTop = rect.top;

        window.addEventListener('pointermove', onPointerMove, { passive: false });
        window.addEventListener('pointerup', onPointerUp);
        window.addEventListener('pointercancel', onPointerUp);
    }

    // Trong lúc di chuyển chuột / ngón tay (Pointer Move)
    function onPointerMove(e) {
        if (!isPointerDown) {
            return;
        }

        var dx = e.clientX - startPointerX;
        var dy = e.clientY - startPointerY;

        // Chỉ khi di chuyển vượt quá 8px mới tính là hành vi KÉO (Drag), tránh nhận nhầm khi click
        if (!hasMovedDistance && Math.hypot(dx, dy) > 8) {
            hasMovedDistance = true;
            launcher.classList.add('is-dragging');
            if (launcher.setPointerCapture && capturedPointerId !== null) {
                try {
                    launcher.setPointerCapture(capturedPointerId);
                } catch (err) {}
            }
        }

        if (hasMovedDistance) {
            if (e.cancelable) {
                e.preventDefault();
            }

            var nextLeft = startElementLeft + dx;
            var nextTop = startElementTop + dy;
            var clamped = clampLauncherPosition(nextLeft, nextTop);

            launcher.style.left = clamped.left + 'px';
            launcher.style.top = clamped.top + 'px';
            launcher.style.right = 'auto';
            launcher.style.bottom = 'auto';
        }
    }

    // Thả chuột / nhấc ngón tay (Pointer Up)
    function onPointerUp(e) {
        if (!isPointerDown) {
            return;
        }

        isPointerDown = false;
        launcher.classList.remove('is-dragging');

        if (capturedPointerId !== null && launcher.releasePointerCapture) {
            try {
                launcher.releasePointerCapture(capturedPointerId);
            } catch (err) {}
        }
        capturedPointerId = null;

        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', onPointerUp);
        window.removeEventListener('pointercancel', onPointerUp);

        if (hasMovedDistance) {
            // Là hành vi KÉO THẢ -> Lưu tọa độ mới vào localStorage
            lastDragTimestamp = Date.now();
            try {
                var finalRect = launcher.getBoundingClientRect();
                localStorage.setItem(storageKey, JSON.stringify({
                    left: finalRect.left,
                    top: finalRect.top
                }));
            } catch (err) {}
        } else {
            // Không di chuyển -> Là hành vi NHẤP CHUỘT / CHẠM TAY (CLICK / TAP) -> Mở/đóng Chatbox ngay lập tức!
            lastToggleTimestamp = Date.now();
            toggleChat();
        }
    }

    // Đăng ký sự kiện Drag trên launcher
    launcher.addEventListener('pointerdown', onPointerDown);

    // Bắt thêm sự kiện click đề phòng cho các trình duyệt hoặc bàn phím
    launcher.addEventListener('click', function (e) {
        if (Date.now() - lastToggleTimestamp < 350 || Date.now() - lastDragTimestamp < 350) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }
        lastToggleTimestamp = Date.now();
        toggleChat();
    });

    // Hỗ trợ phím Enter hoặc Space khi focus bằng bàn phím trên nút launcher
    toggleBtn.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            lastToggleTimestamp = Date.now();
            toggleChat();
        }
    });

    // Xử lý khi resize cửa sổ trình duyệt
    window.addEventListener('resize', function () {
        var rect = launcher.getBoundingClientRect();
        var clamped = clampLauncherPosition(rect.left, rect.top);
        launcher.style.left = clamped.left + 'px';
        launcher.style.top = clamped.top + 'px';
    });

    // Khôi phục vị trí launcher khi nạp trang
    restoreLauncherPosition();
    window.addEventListener('load', function () {
        restoreLauncherPosition();
    });

    // ─────────────────────────────────────────────────────────────
    // 2. TÍNH NĂNG MỞ / ĐÓNG CỬA SỔ CHATBOX
    // ─────────────────────────────────────────────────────────────
    function toggleChat(forceState) {
        if (typeof forceState === 'boolean') {
            isOpen = forceState;
        } else {
            isOpen = !isOpen;
        }

        if (isOpen) {
            windowEl.style.display = 'flex';
            windowEl.setAttribute('aria-hidden', 'false');
            toggleBtn.classList.add('active');
            setTimeout(function () {
                inputEl.focus();
                scrollToBottom();
            }, 100);
        } else {
            windowEl.style.display = 'none';
            windowEl.setAttribute('aria-hidden', 'true');
            toggleBtn.classList.remove('active');
        }
    }

    // Nút đóng cửa sổ chat
    closeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleChat(false);
    });

    // ─────────────────────────────────────────────────────────────
    // 3. XỬ LÝ NHẬP LIỆU: TỰ ĐỘNG CUỘN CHỮ CHẠY THEO CON TRỎ KHI GÕ PROMPT DÀI
    // ─────────────────────────────────────────────────────────────
    function followInputCursor() {
        // Tự động cuộn theo con trỏ nhập liệu khi câu prompt dài
        inputEl.scrollLeft = inputEl.scrollWidth;
    }

    inputEl.addEventListener('input', followInputCursor);
    inputEl.addEventListener('keyup', followInputCursor);

    // ─────────────────────────────────────────────────────────────
    // 4. XỬ LÝ NỘI DUNG VÀ GỬI NHẬN TIN NHẮN VỚI GEMINI
    // ─────────────────────────────────────────────────────────────
    function formatMessageText(text) {
        if (!text) return '';
        var safe = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Định dạng Markdown cơ bản
        safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        safe = safe.replace(/\*(.*?)\*/g, '<em>$1</em>');
        safe = safe.replace(/^• (.*?)$/gm, '<li class="ms-3">$1</li>');
        safe = safe.replace(/^- (.*?)$/gm, '<li class="ms-3">$1</li>');
        safe = safe.replace(/\n/g, '<br>');
        return safe;
    }

    function scrollToBottom(isSmooth) {
        if (isSmooth && typeof messagesEl.scrollTo === 'function') {
            messagesEl.scrollTo({ top: messagesEl.scrollHeight, behavior: 'smooth' });
        } else {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
        setTimeout(function () {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }, 50);
    }

    function appendMessage(sender, text) {
        var msgWrap = document.createElement('div');
        msgWrap.className = 'chat-msg chat-msg-' + sender;

        if (sender === 'user') {
            msgWrap.innerHTML = '<div class="chat-msg-bubble user-bubble">' + formatMessageText(text) + '</div>' +
                                '<div class="chat-msg-avatar user-avatar"><i class="bi bi-person-fill"></i></div>';
        } else {
            msgWrap.innerHTML = '<div class="chat-msg-avatar"><img src="' + botLogoUrl + '" alt="Forum AI" class="chat-bot-avatar-img"></div>' +
                                '<div class="chat-msg-bubble bot-bubble">' + formatMessageText(text) + '</div>';
        }

        messagesEl.appendChild(msgWrap);
        scrollToBottom(true);
    }

    function sendMessage(text) {
        var msg = text || inputEl.value.trim();
        if (!msg) {
            return;
        }

        appendMessage('user', msg);
        inputEl.value = '';
        inputEl.focus();

        chatHistory.push({ sender: 'user', text: msg });

        // Hiển thị trạng thái AI đang suy nghĩ
        if (typingEl) {
            typingEl.style.display = 'block';
            scrollToBottom(true);
        }
        if (sendBtn) {
            sendBtn.disabled = true;
        }

        // Đặt thời gian suy nghĩ tối thiểu (khoảng 2.0s) để AI phân tích câu hỏi trước khi trả lời
        var sendStartTime = Date.now();
        var minThinkingMs = 2000;

        var baseUrl = window.BASE_URL || '';
        var postData = JSON.stringify({
            message: msg,
            history: chatHistory
        });

        var xhr = new XMLHttpRequest();
        xhr.open('POST', baseUrl + '/api/chat', true);
        xhr.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function () {
            var elapsed = Date.now() - sendStartTime;
            var remainingDelay = Math.max(0, minThinkingMs - elapsed);

            setTimeout(function () {
                if (typingEl) {
                    typingEl.style.display = 'none';
                }
                if (sendBtn) {
                    sendBtn.disabled = false;
                }

                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        var botReply = res.reply || 'Cảm ơn câu hỏi của bạn. Forum AI chúc bạn có chuyến dã ngoại tuyệt vời!';
                        appendMessage('bot', botReply);
                        chatHistory.push({ sender: 'bot', text: botReply });
                    } catch (e) {
                        appendMessage('bot', 'Hệ thống đang xử lý, bạn thử lại sau giây lát nhé!');
                    }
                } else {
                    appendMessage('bot', 'Không thể kết nối với máy chủ Forum AI. Vui lòng thử lại sau!');
                }
            }, remainingDelay);
        };

        xhr.onerror = function () {
            var elapsed = Date.now() - sendStartTime;
            var remainingDelay = Math.max(0, minThinkingMs - elapsed);

            setTimeout(function () {
                if (typingEl) {
                    typingEl.style.display = 'none';
                }
                if (sendBtn) {
                    sendBtn.disabled = false;
                }
                appendMessage('bot', 'Đã xảy ra lỗi kết nối mạng. Bạn vui lòng kiểm tra lại đường truyền nhé!');
            }, remainingDelay);
        };

        xhr.send(postData);
    }

    formEl.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage();
    });

    // Bắt sự kiện bấm gợi ý câu hỏi nhanh
    messagesEl.addEventListener('click', function (e) {
        var target = e.target.closest('.quick-prompt-btn');
        if (target) {
            var promptText = target.getAttribute('data-prompt');
            if (promptText) {
                sendMessage(promptText);
            }
        }
    });

    // Nút làm mới đoạn chat
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (confirm('Bạn có muốn xóa cuộc trò chuyện và bắt đầu lại với Forum AI không?')) {
                chatHistory = [];
                messagesEl.innerHTML = '';
                appendMessage('bot', 'Đã làm mới cuộc trò chuyện! Mình là Forum AI, bạn đang chuẩn bị đi dã ngoại hay trekking ở đâu?');
            }
        });
    }
});
</script>
