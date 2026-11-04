/**
 * Main JavaScript for Dã Ngoại Forum
 * Handles mouse parallax, password visibility, and interactions
 */

// Global function to dismiss a toast notification
window.dismissToast = function(toast) {
  if (!toast) return;
  toast.classList.add('toast-hiding');
  setTimeout(function() {
    toast.remove();
    var container = document.getElementById('flash-toast-container');
    if (container && !container.children.length) {
      container.remove();
    }
  }, 350);
};

// Global function to show a toast programmatically (Apple Liquid Glass)
window.showToast = function(message, type) {
  type = type || 'success';
  var container = document.getElementById('flash-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'flash-toast-container';
    document.body.appendChild(container);
  }
  var isSuccess = type === 'success';
  var toast = document.createElement('div');
  toast.className = 'app-toast ' + (isSuccess ? 'app-toast-success' : 'app-toast-error') + ' shadow-lg';
  toast.setAttribute('role', 'alert');
  toast.innerHTML =
    '<div class="toast-body-row d-flex align-items-center gap-2">' +
      '<div class="toast-icon-badge ' + (isSuccess ? 'toast-icon-success' : 'toast-icon-error') + ' flex-shrink-0">' +
        '<i class="bi ' + (isSuccess ? 'bi-check-lg' : 'bi-exclamation-lg') + '"></i>' +
      '</div>' +
      '<div class="flex-grow-1 min-w-0">' +
        '<div class="toast-title">' + (isSuccess ? 'Thành công!' : 'Thông báo') + '</div>' +
        '<div class="toast-desc">' + message + '</div>' +
      '</div>' +
      '<button type="button" class="toast-glass-close flex-shrink-0" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>' +
    '</div>' +
    '<div class="toast-progress-track"><div class="toast-progress-fill ' + (isSuccess ? 'toast-progress-success' : 'toast-progress-error') + '"></div></div>';

  var closeBtn = toast.querySelector('.toast-glass-close');
  if (closeBtn) {
    closeBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      window.dismissToast(toast);
    });
  }
  container.appendChild(toast);
  setTimeout(function() {
    window.dismissToast(toast);
  }, 5000);
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
