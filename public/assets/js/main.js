/**
 * Main JavaScript for Dã Ngoại Forum
 * Handles mouse parallax, password visibility, and interactions
 */

// ============================================================
// Toast Notification System – 4 loại: success, error, warning, info
// ============================================================

// Cấu hình từng loại toast
var _toastConfig = {
  success: { title: 'Thành công!',  icon: 'bi-check-lg'       },
  error:   { title: 'Lỗi!',         icon: 'bi-x-lg'           },
  warning: { title: 'Cảnh báo!',    icon: 'bi-exclamation-lg' },
  info:    { title: 'Thông tin',     icon: 'bi-question-lg'    },
};

// Dismiss một toast (slide out)
window.dismissToast = function(toast) {
  if (!toast || toast._dismissing) return;
  toast._dismissing = true;
  toast.classList.add('toast-hiding');
  setTimeout(function() {
    if (toast.parentNode) toast.remove();
    var container = document.getElementById('flash-toast-container');
    if (container && !container.children.length) container.remove();
  }, 320);
};

// Hiển thị toast động từ JS (dùng trong ajax.js, inline scripts,...)
// Ví dụ: window.showToast('Đã lưu thành công', 'success');
//         window.showToast('Không có quyền', 'error');
//         window.showToast('Dung lượng sắp đầy', 'warning');
//         window.showToast('Hãy cập nhật hồ sơ', 'info');
window.showToast = function(message, type, durationMs) {
  type       = (['success','error','warning','info'].indexOf(type) >= 0 ? type : 'info');
  durationMs = durationMs || 5500;

  var container = document.getElementById('flash-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'flash-toast-container';
    document.body.appendChild(container);
  }

  var cfg   = _toastConfig[type];
  var toast = document.createElement('div');
  toast.className  = 'app-toast app-toast-' + type;
  toast.setAttribute('role', 'alert');

  var closeId = 'tc-' + Date.now();
      toast.innerHTML =
      '<div class="toast-bubble-badge">' +
        '<i class="bi ' + cfg.icon + '"></i>' +
      '</div>' +
      '<div class="toast-decor-blobs" aria-hidden="true">' +
        '<span class="blob blob-1"></span>' +
        '<span class="blob blob-2"></span>' +
        '<span class="blob blob-3"></span>' +
        '<span class="blob blob-4"></span>' +
      '</div>' +
      '<div class="toast-body-content">' +
        '<div class="toast-title">' + cfg.title + '</div>' +
        '<div class="toast-desc"></div>' +
      '</div>' +
      '<button type="button" class="toast-close-btn" id="' + closeId + '" aria-label="Đóng">' +
        '<i class="bi bi-x-lg"></i>' +
      '</button>' +
      '<div class="toast-progress-bar" style="animation-duration:' + (durationMs/1000) + 's"></div>';

  // toast.querySelector('.toast-desc').textContent = String(message || '');

  container.appendChild(toast);

  // Close button
  var closeBtn = document.getElementById(closeId);
  if (closeBtn) {
    closeBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      window.dismissToast(toast);
    });
  }

  // Auto-dismiss
  setTimeout(function() { window.dismissToast(toast); }, durationMs + 200);
};
// Global function to toggle password visibility
window.togglePasswordVisibility = function(targetId, btn) {
  const input = typeof targetId === 'string' ? document.getElementById(targetId) : targetId;
  if (!input) return;

  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';

  if (btn) {
    btn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
    const eyeOpen = btn.querySelector('.icon-eye-open');
    const eyeClosed = btn.querySelector('.icon-eye-closed');
    if (eyeOpen && eyeClosed) {
      if (isPassword) {
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'inline-block';
      } else {
        eyeOpen.style.display = 'inline-block';
        eyeClosed.style.display = 'none';
      }
    }
  }
};

// Global function to initialize marquee on long tool titles
window.initToolMarquees = function() {
  var marquees = document.querySelectorAll('.tool-title-marquee');
  if (!marquees.length) return;

  marquees.forEach(function(marquee) {
    var inner = marquee.querySelector('.tool-title-inner');
    if (!inner) return;

    marquee.classList.remove('has-overflow');
    var overflow = inner.scrollWidth - marquee.clientWidth;
    if (overflow > 2) {
      marquee.classList.add('has-overflow');
      var distance = overflow + 10;
      var duration = Math.min(14, Math.max(6, Math.round(distance / 22) + 3));
      inner.style.setProperty('--marquee-distance', '-' + distance + 'px');
      inner.style.setProperty('--marquee-duration', duration + 's');
    } else {
      marquee.classList.remove('has-overflow');
      inner.style.removeProperty('--marquee-distance');
      inner.style.removeProperty('--marquee-duration');
    }
  });
};

document.addEventListener('DOMContentLoaded', function() {
  // 1. Mouse Parallax Effect
  let frameId = null;
  window.addEventListener('pointermove', function(event) {
    if (event.pointerType !== 'mouse') return;
    if (frameId) cancelAnimationFrame(frameId);

    frameId = requestAnimationFrame(function() {
      const x = ((event.clientX / window.innerWidth) * 2 - 1).toFixed(3);
      const y = ((event.clientY / window.innerHeight) * 2 - 1).toFixed(3);
      document.documentElement.style.setProperty('--mx', x);
      document.documentElement.style.setProperty('--my', y);
    });
  });

  window.addEventListener('pointerleave', function() {
    document.documentElement.style.setProperty('--mx', '0');
    document.documentElement.style.setProperty('--my', '0');
  });

  // 2. Toggle Password Visibility (Event Delegation for reliability)
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-toggle-password');
    if (!btn || btn.hasAttribute('onclick')) return;

    const targetId = btn.getAttribute('data-target');
    window.togglePasswordVisibility(targetId, btn);
  });

  // 3. Smooth active state on dock
  const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
  document.querySelectorAll('.dock-pill').forEach(function(pill) {
    const href = pill.getAttribute('href');
    if (href && (href === currentPath || (href !== '/' && currentPath.endsWith(href)))) {
      pill.classList.add('active');
    }
  });

  // 4. Initialize Tool Marquees
  window.initToolMarquees();
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(function() {
      window.initToolMarquees();
    });
  }

  // 5. Auto dismiss flash toasts after 6 seconds
  document.querySelectorAll('.app-toast').forEach(function(toast) {
    setTimeout(function() {
      window.dismissToast(toast);
    }, 6000);
  });
});

window.addEventListener('load', function() {
  window.initToolMarquees();
});

window.addEventListener('resize', function() {
  clearTimeout(window._marqueeResizeTimer);
  window._marqueeResizeTimer = setTimeout(function() {
    window.initToolMarquees();
  }, 150);
});
