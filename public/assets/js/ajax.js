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
    const apiBaseUrl = window.API_BASE_URL || `${window.BASE_URL || ''}/api`;

    try {
      const response = await fetch(`${apiBaseUrl}/comments.php`, {
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
  fetchWeather: async function (lat, lng, targetElement, warningElement = null, date = null) {
    if (!targetElement) return;

    try {
      const apiBaseUrl = window.API_BASE_URL || `${window.BASE_URL || ''}/api`;
      const params = new URLSearchParams({ lat, lng });
      if (date) params.set('date', date);
      const response = await fetch(`${apiBaseUrl}/weather.php?${params.toString()}`);
      const data = await response.json();

      if (!response.ok || !data || !data.success || !data.data) {
        throw new Error(data && data.message ? data.message : 'Không thể tải dữ liệu thời tiết.');
      }

      const forecastDate = data.data.date ? ` ngày ${data.data.date}` : '';
      targetElement.textContent = `${data.data.temp}°C - ${data.data.condition}${forecastDate}`;
      if (warningElement) {
        const alerts = Array.isArray(data.data.alerts) ? data.data.alerts : [];
        warningElement.className = alerts.length ? 'alert alert-warning mt-2' : 'alert alert-success mt-2';
        warningElement.textContent = alerts.length
          ? `Cảnh báo: ${alerts.join(' ')}`
          : 'Chưa ghi nhận cảnh báo thời tiết nguy hiểm trong dữ liệu hiện có; điều này không đảm bảo đường đi an toàn.';
      }
    } catch (error) {
      targetElement.textContent = 'Không tải được dữ liệu thời tiết.';
      if (warningElement) {
        warningElement.className = 'alert alert-warning mt-2';
        warningElement.textContent = 'Không thể xác minh cảnh báo thời tiết. Hãy kiểm tra dự báo chính thức trước khi đi.';
      }
      console.error('Lỗi tải thời tiết:', error);
    }
  },
};

window.ForumAjax = ForumAjax;
