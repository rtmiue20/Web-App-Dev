/**
 * AJAX & Web Service Helper for Forum Dã Ngoại (Vương - FR06, FR07, FR08)
 * - FR08: Thích / Bỏ thích bài viết qua AJAX (JSON + CSRF)
 * - FR07: Gửi bình luận & Xóa bình luận của mình qua AJAX (JSON + CSRF)
 * - FR06: Tích hợp Web Service Thời tiết & Dự báo 4 ngày (Open-Meteo JSON)
 * - Tích hợp Web Service Địa chỉ hành chính Việt Nam (Tỉnh/Thành - Quận/Huyện JSON)
 */

const ForumAjax = {
  getCsrfToken: function () {
    const csrfInput = document.querySelector('input[name="_csrf"]');
    return csrfInput ? csrfInput.value : '';
  },

  // 1. FR08: Toggle like on a post via AJAX
  toggleLike: async function (postId, btnElement) {
    if (!postId || !btnElement) return;

    const csrfToken = this.getCsrfToken();
    const baseUrl = window.BASE_URL || '';

    btnElement.disabled = true;
    try {
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
        const isLiked = Boolean(data.data.liked);
        btnElement.classList.toggle('liked', isLiked);
        btnElement.style.background = isLiked ? 'rgba(220, 53, 69, 0.12)' : '';
        btnElement.style.borderColor = isLiked ? 'rgba(220, 53, 69, 0.45)' : '';

        const icon = btnElement.querySelector('i');
        if (icon) {
          icon.className = isLiked
            ? 'bi bi-heart-fill text-danger me-1'
            : 'bi bi-heart text-danger me-1';
        }
      } else {
        alert(data.message || 'Vui lòng đăng nhập để thích bài viết.');
      }
    } catch (err) {
      console.error('Lỗi khi gửi like:', err);
    } finally {
      btnElement.disabled = false;
    }
  },

  // 2. FR07: Submit new comment via AJAX
  postComment: async function (postId, content, listContainer) {
    if (!postId || !content.trim()) return false;

    const csrfToken = this.getCsrfToken();
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
          content: content.trim(),
          _csrf: csrfToken,
        }),
      });

      const data = await response.json();
      if (data && data.success && data.data) {
        const cm = data.data;
        if (listContainer) {
          const item = document.createElement('div');
          item.className = 'comment-card rise-in';
          item.id = `comment-item-${cm.id}`;
          item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="comment-author">
                <i class="bi bi-person-fill me-1"></i>${cm.username || 'Bạn'}
              </span>
              <div class="d-flex align-items-center gap-2">
                <span class="comment-time">${cm.created_at || 'Vừa xong'}</span>
                <button type="button"
                        class="btn btn-sm btn-link text-danger p-0 text-decoration-none small"
                        title="Xóa bình luận của bạn"
                        onclick="ForumAjax.deleteComment(${cm.id}, ${postId}, this)">
                  <i class="bi bi-trash3"></i> Xóa
                </button>
              </div>
            </div>
            <div class="comment-text">${cm.content.replace(/\n/g, '<br>')}</div>
          `;
          listContainer.prepend(item);
        }

        const countBadge = document.getElementById('comments-count-badge');
        if (countBadge && typeof cm.total_comments !== 'undefined') {
          countBadge.textContent = cm.total_comments;
        }
        return true;
      } else {
        alert((data && data.message) || 'Không thể gửi bình luận.');
        return false;
      }
    } catch (err) {
      console.error('Lỗi gửi bình luận:', err);
      return false;
    }
  },

  // 3. FR07: Delete own comment via AJAX
  deleteComment: async function (commentId, postId, btnElement) {
    if (!commentId) return;
    if (!confirm('Bạn có chắc muốn xóa bình luận này không?')) return;

    const csrfToken = this.getCsrfToken();
    const baseUrl = window.BASE_URL || '';

    if (btnElement) btnElement.disabled = true;
    try {
      const response = await fetch(`${baseUrl}/api/comments.php?action=delete`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          comment_id: commentId,
          post_id: postId,
          _csrf: csrfToken,
        }),
      });

      const data = await response.json();
      if (data && data.success) {
        const card =
          document.getElementById(`comment-item-${commentId}`) ||
          (btnElement ? btnElement.closest('.comment-card') : null);
        if (card) {
          card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
          card.style.opacity = '0';
          card.style.transform = 'translateY(-6px)';
          setTimeout(() => card.remove(), 250);
        }

        const countBadge = document.getElementById('comments-count-badge');
        if (countBadge && data.data && typeof data.data.total_comments !== 'undefined') {
          countBadge.textContent = data.data.total_comments;
        }
      } else {
        alert((data && data.message) || 'Không thể xóa bình luận.');
        if (btnElement) btnElement.disabled = false;
      }
    } catch (err) {
      console.error('Lỗi xóa bình luận:', err);
      if (btnElement) btnElement.disabled = false;
    }
  },

  // 4. FR06: Fetch weather & 4-day forecast from Open-Meteo Web Service
  fetchWeather: async function (lat, lng, badgeElement, panelElement) {
    try {
      const baseUrl = window.BASE_URL || '';
      const response = await fetch(
        `${baseUrl}/api/weather.php?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`
      );
      const data = await response.json();

      if (data && data.success && data.data) {
        const w = data.data;

        if (badgeElement) {
          badgeElement.innerHTML = `
            <i class="bi ${w.icon || 'bi-cloud-sun-fill text-warning'} me-1"></i>
            <span>${w.temp}°C · ${w.condition}</span>
          `;
        }

        const targetPanel = panelElement || document.getElementById('weather-forecast-panel');
        if (targetPanel) {
          let dailyHtml = '';
          if (Array.isArray(w.daily) && w.daily.length > 0) {
            dailyHtml = `
              <div class="row row-cols-2 row-cols-sm-4 g-2 mt-2 pt-2 border-top" style="border-color: rgba(0,0,0,0.08) !important;">
                ${w.daily
                  .map(
                    (d) => `
                  <div class="col">
                    <div class="p-2 rounded-3 text-center h-100" style="background: rgba(255,255,255,0.65); border: 1px solid rgba(0,0,0,0.06);">
                      <div class="fw-semibold text-dark" style="font-size: 12px;">${d.day_label}</div>
                      <div class="text-muted" style="font-size: 10.5px;">${d.date}</div>
                      <div class="my-1 fs-5"><i class="bi ${d.icon || 'bi-cloud-sun'}"></i></div>
                      <div class="fw-bold text-dark" style="font-size: 12.5px;">
                        ${d.temp_max}° <span class="text-muted fw-normal">/ ${d.temp_min}°C</span>
                      </div>
                      <div class="text-primary" style="font-size: 10.5px;" title="Xác suất mưa">
                        <i class="bi bi-droplet-half"></i> Mưa ${d.rain_prob}%
                      </div>
                    </div>
                  </div>
                `
                  )
                  .join('')}
              </div>
            `;
          }

          targetPanel.innerHTML = `
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
              <div class="d-flex align-items-center gap-3">
                <div class="display-6 fw-bold mb-0 d-flex align-items-center gap-2" style="color: var(--color-ink);">
                  <i class="bi ${w.icon || 'bi-cloud-sun-fill text-warning'}"></i>
                  <span>${w.temp}°C</span>
                </div>
                <div>
                  <div class="fw-semibold text-dark">${w.condition}</div>
                  <div class="text-muted" style="font-size: 12px;">
                    Cảm giác như <strong>${w.feels_like ?? w.temp}°C</strong> · Độ ẩm <strong>${w.humidity ?? 75}%</strong> · Gió <strong>${w.windspeed} km/h</strong>
                  </div>
                </div>
              </div>
              <span class="badge rounded-pill text-bg-light border text-secondary" style="font-size: 11px;">
                <i class="bi bi-cloud-check me-1 text-success"></i>${w.source || 'Open-Meteo API'}
              </span>
            </div>
            ${
              w.advice
                ? `<div class="p-2 px-3 rounded-3 small mb-1 d-flex align-items-center gap-2" style="background: rgba(155, 189, 85, 0.14); color: #2b3a1a;">
                     <i class="bi bi-compass-fill text-success"></i>
                     <span><strong>Gợi ý dã ngoại:</strong> ${w.advice}</span>
                   </div>`
                : ''
            }
            ${dailyHtml}
          `;
        }
      }
    } catch (e) {
      if (badgeElement) badgeElement.style.display = 'none';
    }
  },

  // 5. Vietnam Administrative Units API (Tỉnh/Thành phố -> Quận/Huyện + Tọa độ)
  initProvinceSelector: async function (config) {
    const provSelect = document.getElementById(config.provinceSelectId);
    const distSelect = document.getElementById(config.districtSelectId);
    const locInput = document.getElementById(config.locationInputId);
    const latInput = document.getElementById(config.latInputId);
    const lngInput = document.getElementById(config.lngInputId);

    if (!provSelect || !distSelect) return;

    const baseUrl = window.BASE_URL || '';
    let provincesData = [];

    try {
      const res = await fetch(`${baseUrl}/api/provinces.php`);
      const json = await res.json();
      if (json && json.success && Array.isArray(json.data)) {
        provincesData = json.data;
      }
    } catch (err) {
      console.error('Lỗi tải danh sách tỉnh thành:', err);
      return;
    }

    provSelect.innerHTML = '<option value="">-- Chọn Tỉnh / Thành phố --</option>';
    provincesData.forEach((p) => {
      const opt = document.createElement('option');
      opt.value = p.code;
      opt.textContent = p.name;
      provSelect.appendChild(opt);
    });

    provSelect.addEventListener('change', function () {
      const code = this.value;
      distSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện hoặc Điểm dã ngoại --</option>';

      if (!code) {
        distSelect.disabled = true;
        return;
      }

      const prov = provincesData.find((p) => String(p.code) === String(code));
      if (!prov) return;

      distSelect.disabled = false;
      if (Array.isArray(prov.districts)) {
        prov.districts.forEach((d, idx) => {
          const opt = document.createElement('option');
          opt.value = String(idx);
          opt.textContent = d.name;
          distSelect.appendChild(opt);
        });
      }

      // Cập nhật tạm theo tỉnh nếu chưa chọn huyện
      if (locInput && !locInput.value.trim()) {
        locInput.value = prov.name;
      }
      if (latInput && prov.lat) latInput.value = Number(prov.lat).toFixed(4);
      if (lngInput && prov.lng) lngInput.value = Number(prov.lng).toFixed(4);
      if (typeof config.onCoordinateChange === 'function' && prov.lat && prov.lng) {
        config.onCoordinateChange(Number(prov.lat), Number(prov.lng), prov.name);
      }
    });

    distSelect.addEventListener('change', function () {
      const code = provSelect.value;
      const distIdx = this.value;
      const prov = provincesData.find((p) => String(p.code) === String(code));
      if (!prov) return;

      if (distIdx === '' || !prov.districts || !prov.districts[distIdx]) {
        return;
      }

      const dist = prov.districts[distIdx];
      const fullName = `${dist.name}, ${prov.name}`;
      if (locInput) locInput.value = fullName;
      if (latInput && dist.lat) latInput.value = Number(dist.lat).toFixed(4);
      if (lngInput && dist.lng) lngInput.value = Number(dist.lng).toFixed(4);

      if (typeof config.onCoordinateChange === 'function' && dist.lat && dist.lng) {
        config.onCoordinateChange(Number(dist.lat), Number(dist.lng), fullName);
      }
    });
  },
};

window.ForumAjax = ForumAjax;
