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

  getBaseUrl: function () {
    const raw = String(window.BASE_URL || '').replace(/\/+$/, '');
    if (raw === '/posts' || raw === '/admin' || raw === '/categories' || raw === '/products') {
      return '';
    }
    return raw;
  },

  // 1. FR08: Hệ thống Vote (Upvote +1 / Downvote -1) bài viết qua AJAX
  votePost: async function (postId, voteType, btnElement) {
    if (!postId) return;

    const csrfToken = this.getCsrfToken();
    const baseUrl = this.getBaseUrl();
    const cleanVote = Number(voteType) < 0 ? -1 : 1;

    if (btnElement) {
      btnElement.disabled = true;
    }

    try {
      const response = await fetch(`${baseUrl}/api/likes.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          post_id: postId,
          vote: cleanVote,
          _csrf: csrfToken,
        }),
      });

      const data = await response.json();
      if (data && data.success && data.data) {
        const score = Number(data.data.score ?? data.data.likes ?? 0);
        const upvotes = Number(data.data.upvotes ?? Math.max(0, score));
        const downvotes = Number(data.data.downvotes ?? 0);
        const userVote = Number(data.data.user_vote ?? 0);

        // Cập nhật tất cả bộ đếm điểm vote của bài viết trên trang
        document.querySelectorAll(`.vote-score[data-post-id="${postId}"], .like-count[data-post-id="${postId}"]`).forEach(function (el) {
          el.textContent = score;
        });

        // Cập nhật chi tiết (+up / -down) nếu có
        document.querySelectorAll(`.vote-breakdown[data-post-id="${postId}"]`).forEach(function (el) {
          el.textContent = `(+${upvotes} / -${downvotes})`;
        });

        // Cập nhật trạng thái nút Upvote (+1)
        document.querySelectorAll(`.vote-up-btn[data-post-id="${postId}"]`).forEach(function (upBtn) {
          const isUp = userVote === 1;
          upBtn.classList.toggle('active', isUp);
          upBtn.style.background = isUp ? '#2f5e2e' : '';
          upBtn.style.color = isUp ? '#ffffff' : '#2f5e2e';
        });

        // Cập nhật trạng thái nút Downvote (-1)
        document.querySelectorAll(`.vote-down-btn[data-post-id="${postId}"]`).forEach(function (downBtn) {
          const isDown = userVote === -1;
          downBtn.classList.toggle('active', isDown);
          downBtn.style.background = isDown ? '#b02a37' : '';
          downBtn.style.color = isDown ? '#ffffff' : '#8c3b2b';
        });
      } else {
        alert((data && data.message) ? data.message : 'Vui lòng đăng nhập tài khoản Thành viên để bình chọn bài viết.');
      }
    } catch (err) {
      console.error('Lỗi khi gửi vote:', err);
    } finally {
      if (btnElement) {
        btnElement.disabled = false;
      }
    }
  },

  // Tương thích ngược với các nút gọi toggleLike cũ
  toggleLike: async function (postId, btnElement) {
    return this.votePost(postId, 1, btnElement);
  },

  // 2. FR07: Submit new comment or multi-level nested reply via AJAX
  postComment: async function (postId, content, listContainer, parentId = null) {
    if (!postId || !content.trim()) return false;

    const csrfToken = this.getCsrfToken();
    const baseUrl = this.getBaseUrl();

    try {
      const response = await fetch(`${baseUrl}/api/comments.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          post_id: postId,
          parent_id: parentId,
          content: content.trim(),
          _csrf: csrfToken,
        }),
      });

      const data = await response.json();
      if (data && data.success && data.data) {
        const cm = data.data;
        const threadEl = this.buildCommentThreadElement(cm, postId);

        if (parentId && parentId > 0) {
          let childrenContainer = document.getElementById(`comment-children-${parentId}`);
          if (!childrenContainer) {
            const parentThread = document.getElementById(`comment-thread-${parentId}`);
            if (parentThread) {
              childrenContainer = document.createElement('div');
              childrenContainer.className = 'comment-children-list';
              childrenContainer.id = `comment-children-${parentId}`;
              parentThread.appendChild(childrenContainer);
            }
          }
          if (childrenContainer) {
            childrenContainer.appendChild(threadEl);
          } else if (listContainer) {
            listContainer.prepend(threadEl);
          }
        } else if (listContainer) {
          listContainer.prepend(threadEl);
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

  // Helper: Tạo phần tử DOM cho bình luận hoặc câu trả lời lồng nhau
  buildCommentThreadElement: function (cm, postId) {
    const isReply = Boolean(cm.parent_id && Number(cm.parent_id) > 0);
    const threadWrap = document.createElement('div');
    threadWrap.className = `comment-thread-node ${isReply ? 'comment-reply-node' : ''} rise-in`;
    threadWrap.id = `comment-thread-${cm.id}`;

    let roleBadgeHtml = '';
    if (cm.role_name === 'admin') {
      roleBadgeHtml = '<span class="badge rounded-pill text-bg-danger ms-1" style="font-size:10px;">ADMIN</span>';
    } else if (cm.role_name === 'moderator') {
      roleBadgeHtml = '<span class="badge rounded-pill text-bg-primary ms-1" style="font-size:10px;">MOD</span>';
    }

    const replyToHtml = cm.parent_username
      ? `<span class="badge rounded-pill ms-2 fw-normal" style="background: rgba(155, 189, 85, 0.16); color: #c4e482; border: 1px solid rgba(155, 189, 85, 0.35); font-size: 11px;">
           <i class="bi bi-reply-fill me-1"></i>@${cm.parent_username}
         </span>`
      : '';

    const safeUsernameAttr = String(cm.username || 'Thành viên').replace(/'/g, "\\'");

    threadWrap.innerHTML = `
      <div class="comment-card" id="comment-item-${cm.id}">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
          <div class="d-flex align-items-center flex-wrap">
            <span class="comment-author">
              <i class="bi bi-person-fill me-1"></i>${cm.username || 'Bạn'}
            </span>
            ${roleBadgeHtml}
            ${replyToHtml}
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="comment-time" style="color: rgba(255, 255, 255, 0.55);">${cm.created_at || 'Vừa xong'}</span>
            <button type="button"
                    class="btn btn-sm btn-link p-0 text-decoration-none small fw-medium"
                    style="color: #c4e482;"
                    onclick="ForumAjax.toggleReplyBox(${cm.id}, ${postId}, '${safeUsernameAttr}')">
              <i class="bi bi-reply-fill"></i> Trả lời
            </button>
            <button type="button"
                    class="btn btn-sm btn-link text-danger p-0 text-decoration-none small"
                    title="Xóa bình luận"
                    onclick="ForumAjax.deleteComment(${cm.id}, ${postId}, this)">
              <i class="bi bi-trash3"></i>
            </button>
          </div>
        </div>
        <div class="comment-text">${String(cm.content || '').replace(/\n/g, '<br>')}</div>
        <div id="reply-box-container-${cm.id}" class="mt-2" style="display: none;"></div>
      </div>
      <div class="comment-children-list" id="comment-children-${cm.id}"></div>
    `;
    return threadWrap;
  },

  // Bật / tắt khung nhập trả lời lồng nhau ngay dưới bình luận bất kỳ
  toggleReplyBox: function (commentId, postId, targetUsername) {
    const container = document.getElementById(`reply-box-container-${commentId}`);
    if (!container) return;

    if (container.style.display === 'block') {
      container.style.display = 'none';
      container.innerHTML = '';
      return;
    }

    // Đóng các ô reply đang mở khác cho gọn giao diện
    document.querySelectorAll('[id^="reply-box-container-"]').forEach(function(el) {
      if (el.id !== `reply-box-container-${commentId}`) {
        el.style.display = 'none';
        el.innerHTML = '';
      }
    });

    container.style.display = 'block';
    container.innerHTML = `
      <form class="p-3 rounded-4 mt-3" style="background: var(--color-paper, #f2f3ef); color: var(--color-ink, #23261f); border: 1px solid rgba(155, 189, 85, 0.45); box-shadow: 0 10px 24px rgba(0,0,0,0.25);" onsubmit="event.preventDefault(); ForumAjax.submitReply(${postId}, ${commentId});">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="small fw-medium" style="color: #23261f;">
            <i class="bi bi-corner-down-right text-success me-1"></i>Đang trả lời <strong class="text-success">@${targetUsername}</strong>
          </span>
          <button type="button" class="btn-close" style="font-size: 10px;" onclick="ForumAjax.toggleReplyBox(${commentId}, ${postId}, '')"></button>
        </div>
        <textarea id="reply-input-${commentId}" class="nature-textarea mb-2" rows="2" required placeholder="Nhập nội dung phản hồi @${targetUsername}..."></textarea>
        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="ForumAjax.toggleReplyBox(${commentId}, ${postId}, '')">Hủy</button>
          <button type="submit" id="reply-submit-btn-${commentId}" class="nature-btn-primary px-3 py-1" style="width: auto; height: 34px; font-size: 12.5px;">
            <i class="bi bi-send me-1"></i> Gửi trả lời
          </button>
        </div>
      </form>
    `;

    const textarea = document.getElementById(`reply-input-${commentId}`);
    if (textarea) textarea.focus();
  },

  // Gửi câu trả lời con (multi-level reply) qua AJAX
  submitReply: async function (postId, parentId) {
    const input = document.getElementById(`reply-input-${parentId}`);
    const submitBtn = document.getElementById(`reply-submit-btn-${parentId}`);
    if (!input || !input.value.trim()) return;

    if (submitBtn) submitBtn.disabled = true;
    const listContainer = document.getElementById('comments-list');
    const ok = await this.postComment(postId, input.value, listContainer, parentId);
    if (submitBtn) submitBtn.disabled = false;

    if (ok) {
      const container = document.getElementById(`reply-box-container-${parentId}`);
      if (container) {
        container.style.display = 'none';
        container.innerHTML = '';
      }
      const noMsg = document.getElementById('no-comments-msg');
      if (noMsg) noMsg.remove();
    }
  },

  // 3. FR07: Delete own comment (and its nested replies) via custom Modal + AJAX
  _pendingDelete: null,

  deleteComment: function (commentId, postId, btnElement) {
    if (!commentId) return;

    const modalEl = document.getElementById('deleteCommentModal');
    const confirmBtn = document.getElementById('btnConfirmDeleteComment');

    if (modalEl && confirmBtn && typeof bootstrap !== 'undefined') {
      this._pendingDelete = { commentId, postId, btnElement };
      const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
      const self = this;

      confirmBtn.onclick = async function() {
        if (!self._pendingDelete) return;
        const cId = self._pendingDelete.commentId;
        const pId = self._pendingDelete.postId;
        const btn = self._pendingDelete.btnElement;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xóa...';
        await self.executeDeleteComment(cId, pId, btn);
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Xóa bình luận';
        self._pendingDelete = null;
        modalInstance.hide();
      };

      modalInstance.show();
      return;
    }

    if (confirm('Bạn có chắc muốn xóa bình luận này (cùng các phản hồi bên dưới) không?')) {
      this.executeDeleteComment(commentId, postId, btnElement);
    }
  },

  executeDeleteComment: async function (commentId, postId, btnElement) {
    const csrfToken = this.getCsrfToken();
    const baseUrl = this.getBaseUrl();

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
        const targetNode =
          document.getElementById(`comment-thread-${commentId}`) ||
          document.getElementById(`comment-item-${commentId}`) ||
          (btnElement ? btnElement.closest('.comment-thread-node') : null) ||
          (btnElement ? btnElement.closest('.comment-card') : null);
        if (targetNode) {
          targetNode.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
          targetNode.style.opacity = '0';
          targetNode.style.transform = 'translateY(-6px)';
          setTimeout(function() {
            targetNode.remove();
          }, 250);
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
      const baseUrl = this.getBaseUrl();
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
                    function(d) {
                      return `
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
                `;
                    }
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
    provincesData.forEach(function(p) {
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

      const prov = provincesData.find(function(p) { return String(p.code) === String(code); });
      if (!prov) return;

      distSelect.disabled = false;
      if (Array.isArray(prov.districts)) {
        prov.districts.forEach(function(d, idx) {
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
      const prov = provincesData.find(function(p) { return String(p.code) === String(code); });
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
