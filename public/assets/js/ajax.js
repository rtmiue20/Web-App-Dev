/**
 * AJAX Helper for Dã Ngoại Forum
 * Handles Likes, Comments, and External Weather API
 */

const ForumAjax = {
  // 1. Toggle like on a post
  toggleLike: async function (postId, btnElement) {
    if (!postId || !btnElement) return;

    const csrfInput = document.querySelector('input[name="_csrf"]');
    const csrfToken = csrfInput ? csrfInput.value : '';

    try {
      const baseUrl = window.BASE_URL || '';
      const response = await fetch(`${baseUrl}/posts/${postId}/like`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ _csrf: csrfToken }),
      });

      const data = await response.json();
      if (data && data.success) {
        const countSpan = btnElement.querySelector('.like-count');
        if (countSpan) {
          countSpan.textContent = data.data.likes;
        }
        btnElement.classList.toggle('liked', data.data.liked);
      } else {
        alert(data.message || 'Không thể thực hiện thích bài viết.');
      }
    } catch (err) {
      console.error('Lỗi khi gửi like:', err);
    }
  },

  // 2. Submit comment
  postComment: async function (postId, content, listContainer) {
    if (!postId || !content.trim()) return false;

    const csrfInput = document.querySelector('input[name="_csrf"]');
    const csrfToken = csrfInput ? csrfInput.value : '';
    const baseUrl = window.BASE_URL || '';

    try {
      const response = await fetch(`${baseUrl}/api/comments.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          post_id: postId,
          content: content,
          _csrf: csrfToken,
        }),
      });

      const data = await response.json();
      if (data && data.success) {
        if (listContainer) {
          const item = document.createElement('div');
          item.className = 'comment-card rise-in';
          item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="comment-author">${data.data.username || 'Bạn'}</span>
              <span class="comment-time">Vừa xong</span>
            </div>
            <div class="comment-text">${data.data.content}</div>
          `;
          listContainer.prepend(item);
        }
        return true;
      } else {
        alert(data.message || 'Không thể gửi bình luận.');
        return false;
      }
    } catch (err) {
      console.error('Lỗi gửi bình luận:', err);
      return false;
    }
  },

  // 3. Fetch weather from Open-Meteo
  fetchWeather: async function (lat, lng, targetElement) {
    if (!targetElement) return;

    try {
      const baseUrl = window.BASE_URL || '';
      const response = await fetch(`${baseUrl}/api/weather.php?lat=${lat}&lng=${lng}`);
      const data = await response.json();

      if (data && data.success && data.data) {
        targetElement.innerHTML = `
          <i class="bi bi-cloud-sun me-1"></i>
          <span>${data.data.temp}°C - ${data.data.condition}</span>
        `;
      }
    } catch (e) {
      targetElement.style.display = 'none';
    }
  },
};

window.ForumAjax = ForumAjax;
