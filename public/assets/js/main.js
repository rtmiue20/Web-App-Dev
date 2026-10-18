/**
 * Main JavaScript for Dã Ngoại Forum
 * Handles mouse parallax, password visibility, and interactions
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Mouse Parallax Effect
  let frameId = null;
  window.addEventListener('pointermove', (event) => {
    if (event.pointerType !== 'mouse') return;
    if (frameId) cancelAnimationFrame(frameId);

    frameId = requestAnimationFrame(() => {
      const x = ((event.clientX / window.innerWidth) * 2 - 1).toFixed(3);
      const y = ((event.clientY / window.innerHeight) * 2 - 1).toFixed(3);
      document.documentElement.style.setProperty('--mx', x);
      document.documentElement.style.setProperty('--my', y);
    });
  });

  window.addEventListener('pointerleave', () => {
    document.documentElement.style.setProperty('--mx', '0');
    document.documentElement.style.setProperty('--my', '0');
  });

  // 2. Toggle Password Visibility
  document.querySelectorAll('.btn-toggle-password').forEach((btn) => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (!input) return;

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
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
    });
  });

  // 3. Smooth active state on dock
  const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
  document.querySelectorAll('.dock-pill').forEach((pill) => {
    const href = pill.getAttribute('href');
    if (href && (href === currentPath || (href !== '/' && currentPath.endsWith(href)))) {
      pill.classList.add('active');
    }
  });
});
