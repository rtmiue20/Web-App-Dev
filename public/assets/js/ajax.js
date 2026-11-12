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
    if (csrfInput && csrfInput.value) {
      return csrfInput.value;
    }
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    return csrfMeta ? (csrfMeta.getAttribute('content') || '') : '';
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
          'X-CSRF-TOKEN': csrfToken,
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

        this.fetchNotifications();
      } else {
        if (typeof window.showToast === 'function') { window.showToast((data && data.message) ? data.message : 'Vui lòng đăng nhập tài khoản Thành viên để bình chọn bài viết.', 'error'); } else { alert((data && data.message) ? data.message : 'Vui lòng đăng nhập tài khoản Thành viên để bình chọn bài viết.'); }
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

  // 1b. Thích / Bỏ thích bình luận qua AJAX không reload trang (FR07, FR08)
  likeComment: async function (commentId, btnElement) {
    if (!commentId) return;
    const csrfToken = this.getCsrfToken();
    const baseUrl = this.getBaseUrl();
    if (btnElement) btnElement.disabled = true;

    try {
      const response = await fetch(`${baseUrl}/api/comments.php?action=like`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          comment_id: commentId,
          _csrf: csrfToken,
        }),
      });

      const data = await response.json();
      if (data && data.success && data.data) {
        const liked = Boolean(data.data.liked);
        const count = Number(data.data.likes_count || 0);

        document.querySelectorAll(`.comment-like-btn[data-comment-id="${commentId}"]`).forEach(function (btn) {
          const icon = btn.querySelector('i');
          const countSpan = btn.querySelector('.comment-likes-count');
          if (liked) {
            btn.classList.add('liked');
            btn.style.setProperty('color', '#ff6b81', 'important');
            if (icon) {
              icon.className = 'bi bi-heart-fill';
            }
          } else {
            btn.classList.remove('liked');
            btn.style.setProperty('color', 'rgba(255, 255, 255, 0.65)', 'important');
            if (icon) {
              icon.className = 'bi bi-heart';
            }
          }
          if (countSpan) {
            countSpan.textContent = count > 0 ? count : '';
          }
        });

        this.fetchNotifications();
      } else {
        if (typeof window.showToast === 'function') { window.showToast((data && data.message) ? data.message : 'Vui lòng đăng nhập để thích bình luận.', 'error'); } else { alert((data && data.message) ? data.message : 'Vui lòng đăng nhập để thích bình luận.'); }
      }
    } catch (err) {
      console.error('Lỗi khi thích bình luận:', err);
    } finally {
      if (btnElement) btnElement.disabled = false;
    }
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
        this.fetchNotifications();
        return true;
      } else {
        if (typeof window.showToast === 'function') { window.showToast((data && data.message) || 'Không thể gửi bình luận.', 'error'); } else { alert((data && data.message) || 'Không thể gửi bình luận.'); }
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
                    class="btn btn-sm btn-link p-0 text-decoration-none small d-inline-flex align-items-center gap-1 comment-like-btn ${cm.user_liked ? 'liked' : ''}"
                    data-comment-id="${cm.id}"
                    style="${cm.user_liked ? 'color: #ff6b81 !important;' : 'color: rgba(255,255,255,0.65) !important;'}"
                    onclick="ForumAjax.likeComment(${cm.id}, this)"
                    title="Thích bình luận">
              <i class="bi ${cm.user_liked ? 'bi-heart-fill' : 'bi-heart'}"></i>
              <span class="comment-likes-count">${cm.likes_count ? cm.likes_count : ''}</span>
            </button>
            <button type="button"
                    class="btn btn-sm btn-link p-0 text-decoration-none small fw-medium"
                    style="color: #c4e482;"
                    onclick="ForumAjax.toggleReplyBox(${cm.id}, ${postId}, '${safeUsernameAttr}')">
              <i class="bi bi-reply-fill"></i> Trả lời
            </button>
            ${cm.can_delete ? `
            <button type="button"
                    class="btn btn-sm btn-link text-danger p-0 text-decoration-none small"
                    title="Xóa bình luận"
                    onclick="ForumAjax.deleteComment(${cm.id}, ${postId}, this)">
              <i class="bi bi-trash3"></i>
            </button>` : ''}
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
    document.querySelectorAll('[id^="reply-box-container-"]').forEach(function (el) {
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

      confirmBtn.onclick = async function () {
        if (!self._pendingDelete) return;
        const cId = self._pendingDelete.commentId;
        const pId = self._pendingDelete.postId;
        const btn = self._pendingDelete.btnElement;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xóa...';
        await self.executeDeleteComment(cId, pId, btn);
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Xác nhận xóa';
        self._pendingDelete = null;
        modalInstance.hide();
      };

      modalInstance.show();
      return;
    }

    const self = this;
    if (typeof window.showConfirmToast === 'function') {
      window.showConfirmToast(
        'Bạn có chắc muốn xóa bình luận này (cùng các phản hồi bên dưới) khỏi bài viết không? Hành động này không thể hoàn tác.',
        function () {
          self.executeDeleteComment(commentId, postId, btnElement);
        },
        {
          badgeText: 'Xác nhận xóa',
          title: 'Xóa bình luận khỏi bài viết?',
          confirmLabel: 'Xác nhận xóa',
          cancelLabel: 'Hủy',
          icon: 'bi-trash-fill',
        }
      );
    } else {
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
          setTimeout(function () {
            targetNode.remove();
          }, 250);
        }

        const countBadge = document.getElementById('comments-count-badge');
        if (countBadge && data.data && typeof data.data.total_comments !== 'undefined') {
          countBadge.textContent = data.data.total_comments;
        }
        if (typeof window.showToast === 'function') {
          window.showToast(data.message || 'Đã xóa bình luận thành công.', 'success');
        }
        this.fetchNotifications();
      } else {
        if (typeof window.showToast === 'function') { window.showToast((data && data.message) || 'Không thể xóa bình luận.', 'error'); } else { alert((data && data.message) || 'Không thể xóa bình luận.'); }
        if (btnElement) btnElement.disabled = false;
      }
    } catch (err) {
      console.error('Lỗi xóa bình luận:', err);
      if (btnElement) btnElement.disabled = false;
    }
  },

  // 4. FR06: Fetch weather, UV Index, Air Quality (AQI/PM2.5) & 4-day forecast from Open-Meteo Web Service
  fetchWeather: async function (lat, lng, badgeElement, panelElement = null, warningElement = null, date = null) {
    try {
      const baseUrl = this.getBaseUrl();
      const params = new URLSearchParams({ lat: lat, lng: lng });
      if (date) params.set('date', date);
      const response = await fetch(`${baseUrl}/api/weather.php?${params.toString()}`);
      const data = await response.json();

      if (response.ok && data && data.success && data.data) {
        const w = data.data;
        const uv = w.uv || {
          index: 5.0,
          max_daily: 6.5,
          level: 'Trung bình',
          percent: 45,
          color: '#facc15',
          badge_bg: 'rgba(250, 204, 21, 0.16)',
          badge_border: 'rgba(250, 204, 21, 0.4)',
          icon: 'bi-brightness-high-fill',
          advice: 'Nên đội mũ rộng vành và thoa kem chống nắng SPF 30+ khi di chuyển ngoài trời.'
        };
        const aq = w.air_quality || {
          aqi: 20,
          pm2_5: 5.2,
          pm10: 11.0,
          level: 'Rất trong lành',
          percent: 12,
          color: '#4ade80',
          badge_bg: 'rgba(74, 222, 128, 0.16)',
          badge_border: 'rgba(74, 222, 128, 0.4)',
          icon: 'bi-wind',
          advice: 'Không khí vùng núi rất trong lành, lý tưởng để trekking và cắm trại.'
        };

        if (badgeElement) {
          badgeElement.textContent = ` · ${w.temp}°C · ${w.condition}${w.date ? ` · ${w.date}` : ''}`;
        }

        if (warningElement) {
          const alerts = Array.isArray(w.alerts) ? w.alerts : [];
          warningElement.className = alerts.length ? 'alert alert-warning mt-2 mb-0' : 'alert alert-success mt-2 mb-0';
          warningElement.textContent = alerts.length
            ? `Cảnh báo an toàn: ${alerts.join(' ')}`
            : 'Các chỉ số thời tiết, bức xạ tia UV và chất lượng không khí hiện tại đều nằm trong ngưỡng an toàn cho chuyến dã ngoại.';
        }

        const targetPanel = panelElement || document.getElementById('weather-forecast-panel');
        if (targetPanel) {
          let dailyHtml = '';
          if (Array.isArray(w.daily) && w.daily.length > 0) {
            let dailyCards = '';
            for (let i = 0; i < w.daily.length; i++) {
              const d = w.daily[i];
              const dayUvVal = typeof d.uv_max !== 'undefined' ? d.uv_max : '5.0';
              dailyCards += `
                <div class="col">
                  <div class="p-3.5 text-center h-100 d-flex flex-column justify-content-between shadow-sm" style="border-radius: 20px !important; background: linear-gradient(160deg, rgba(255,255,255,0.075) 0%, rgba(255,255,255,0.02) 100%); border: 1px solid rgba(255,255,255,0.12); min-height: 165px; transition: transform 0.2s ease, border-color 0.2s ease;">
                    <div>
                      <div class="fw-bold text-white mb-0" style="font-size: 13px; letter-spacing: 0.2px;">${d.day_label}</div>
                      <div class="text-white-50 mb-1" style="font-size: 11px;">${d.date}</div>
                      <div class="my-2 fs-3" style="filter: drop-shadow(0 3px 6px rgba(0,0,0,0.4));"><i class="bi ${d.icon || 'bi-cloud-sun-fill text-warning'}"></i></div>
                      <div class="fw-bold text-white mt-1" style="font-size: 13px;">
                        ${d.temp_max}° <span class="text-white-50 fw-normal" style="font-size: 11.5px;">/ ${d.temp_min}°C</span>
                      </div>
                    </div>
                    <div class="mt-2.5 pt-2 border-top border-white border-opacity-10 d-flex align-items-center justify-content-center gap-1.5 flex-wrap">
                      <span class="badge rounded-pill px-2 py-1" style="background: rgba(56, 189, 248, 0.16); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.32); font-size: 10px; font-weight: 500;" title="Xác suất mưa">
                        <i class="bi bi-droplet-fill me-0.5"></i>${d.rain_prob}%
                      </span>
                      <span class="badge rounded-pill px-2 py-1" style="background: rgba(250, 204, 21, 0.16); color: #facc15; border: 1px solid rgba(250, 204, 21, 0.35); font-size: 10px; font-weight: 500;" title="Chỉ số tia UV đỉnh ngày">
                        UV ${dayUvVal}
                      </span>
                    </div>
                  </div>
                </div>
              `;
            }
            dailyHtml = `
              <div class="d-flex align-items-center justify-content-between mt-3 mb-2.5 pt-3 border-top border-white border-opacity-10 flex-wrap gap-2 px-1">
                <span class="small fw-semibold text-white d-inline-flex align-items-center gap-2" style="font-size: 13px;">
                  <i class="bi bi-calendar3 text-success"></i>Dự báo khí tượng &amp; chỉ số UV 4 ngày tới
                </span>
                <span class="text-white-50 small" style="font-size: 11.5px;">Cập nhật theo chu kỳ vệ tinh Open-Meteo</span>
              </div>
              <div class="row row-cols-2 row-cols-md-4 g-3 mb-1">
                ${dailyCards}
              </div>
            `;
          }

          const summaryHtml = `
            <div class="row g-3 mb-3">
              <div class="col-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3.5 px-4 rounded-4" style="background: rgba(255, 255, 255, 0.045); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px !important;">
                  <div class="d-flex align-items-center gap-3">
                    <div class="display-5 fw-bold mb-0 d-flex align-items-center gap-2 text-white" style="text-shadow: 0 4px 12px rgba(0,0,0,0.35);">
                      <i class="bi ${w.icon || 'bi-cloud-sun-fill text-warning'}"></i>
                      <span>${w.temp}°C</span>
                    </div>
                    <div>
                      <div class="fw-bold text-white fs-5">${w.condition}</div>
                      <div class="d-flex align-items-center gap-2 flex-wrap text-white-50 mt-1" style="font-size: 12px;">
                        <span class="badge rounded-pill bg-white bg-opacity-10 text-white fw-normal px-2.5 py-1">Cảm giác: <strong class="text-white">${w.feels_like ?? w.temp}°C</strong></span>
                        <span class="badge rounded-pill bg-white bg-opacity-10 text-white fw-normal px-2.5 py-1"><i class="bi bi-droplet text-info me-1"></i>Độ ẩm: <strong class="text-white">${w.humidity ?? 75}%</strong></span>
                        <span class="badge rounded-pill bg-white bg-opacity-10 text-white fw-normal px-2.5 py-1"><i class="bi bi-wind text-success me-1"></i>Gió: <strong class="text-white">${w.windspeed} km/h</strong></span>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-2 ms-auto ms-sm-0">
                    <span class="badge rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5" style="background: rgba(155, 189, 85, 0.16); border: 1px solid rgba(155, 189, 85, 0.35); color: #d4f291; font-size: 11.5px; font-weight: 500;">
                      <span style="width: 7px; height: 7px; border-radius: 50%; background: #4ade80; display: inline-block; box-shadow: 0 0 8px #4ade80;"></span>
                      Trạm khí tượng thực địa GPS
                    </span>
                  </div>
                </div>
              </div>
            </div>
          `;

          const envCardsHtml = `
            <div class="row g-3 mb-3">
              <!-- Card 1: Chỉ số Tia cực tím (UV Index) -->
              <div class="col-12 col-md-6">
                <div class="p-3.5 p-md-4 h-100 d-flex flex-column justify-content-between shadow-sm" style="border-radius: 20px !important; background: linear-gradient(145deg, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0.025) 100%); border: 1px solid rgba(255, 255, 255, 0.14); backdrop-filter: blur(12px);">
                  <div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2.5">
                      <span class="fw-semibold text-white d-inline-flex align-items-center gap-2" style="font-size: 13px;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);">
                          <i class="bi ${uv.icon || 'bi-sun-fill'}" style="color: ${uv.color || '#facc15'}; font-size: 14px;"></i>
                        </span>
                        Chỉ số Tia cực tím (UV)
                      </span>
                      <span class="badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5" style="background: ${uv.badge_bg || 'rgba(250,204,21,0.16)'}; color: ${uv.color || '#facc15'}; border: 1px solid ${uv.badge_border || 'rgba(250,204,21,0.4)'}; font-size: 11px; font-weight: 600;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: ${uv.color || '#facc15'}; display: inline-block;"></span>
                        ${uv.level}
                      </span>
                    </div>

                    <div class="d-flex align-items-baseline justify-content-between mb-2 flex-wrap gap-1">
                      <div class="display-6 fw-bold text-white lh-1">
                        UV ${uv.index}
                        <span class="small fw-normal text-white-50 ms-1" style="font-size: 12px;">/ 11+ UV</span>
                      </div>
                      <div class="text-white-50" style="font-size: 11.5px;">
                        Đỉnh trưa: <strong class="text-white">UV ${uv.max_daily ?? uv.index}</strong>
                      </div>
                    </div>

                    <div class="progress mb-2.5" style="height: 7px; background: rgba(255,255,255,0.12); border-radius: 999px; overflow: hidden;">
                      <div class="progress-bar" role="progressbar" style="width: ${uv.percent || 35}%; background: linear-gradient(90deg, #4ade80 0%, #facc15 45%, #fb923c 75%, #ef4444 100%); border-radius: 999px;" aria-valuenow="${uv.percent || 35}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>

                  <div class="p-2.5 px-3 rounded-3 text-white-50 d-flex align-items-start gap-2.5 mt-auto" style="background: rgba(0, 0, 0, 0.24); border: 1px solid rgba(255, 255, 255, 0.06); font-size: 12px; line-height: 1.5;">
                    <i class="bi bi-shield-shaded flex-shrink-0 mt-0.5 fs-6" style="color: ${uv.color || '#facc15'};"></i>
                    <span>${uv.advice}</span>
                  </div>
                </div>
              </div>

              <!-- Card 2: Chất lượng Không khí (AQI & Bụi mịn PM2.5) -->
              <div class="col-12 col-md-6">
                <div class="p-3.5 p-md-4 h-100 d-flex flex-column justify-content-between shadow-sm" style="border-radius: 20px !important; background: linear-gradient(145deg, rgba(255, 255, 255, 0.07) 0%, rgba(255, 255, 255, 0.025) 100%); border: 1px solid rgba(255, 255, 255, 0.14); backdrop-filter: blur(12px);">
                  <div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2.5">
                      <span class="fw-semibold text-white d-inline-flex align-items-center gap-2" style="font-size: 13px;">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);">
                          <i class="bi ${aq.icon || 'bi-wind'}" style="color: ${aq.color || '#4ade80'}; font-size: 14px;"></i>
                        </span>
                        Chất lượng Không khí (AQI)
                      </span>
                      <span class="badge rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5" style="background: ${aq.badge_bg || 'rgba(74,222,128,0.16)'}; color: ${aq.color || '#4ade80'}; border: 1px solid ${aq.badge_border || 'rgba(74,222,128,0.4)'}; font-size: 11px; font-weight: 600;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: ${aq.color || '#4ade80'}; display: inline-block;"></span>
                        ${aq.level}
                      </span>
                    </div>

                    <div class="d-flex align-items-baseline justify-content-between mb-2 flex-wrap gap-1">
                      <div class="display-6 fw-bold text-white lh-1">
                        AQI ${aq.aqi}
                        <span class="small fw-normal text-white-50 ms-1" style="font-size: 12px;">(US AQI)</span>
                      </div>
                      <div class="text-white-50" style="font-size: 11.5px;">
                        PM2.5: <strong class="text-white">${aq.pm2_5} µg/m³</strong> · PM10: <strong class="text-white">${aq.pm10 ?? 10} µg/m³</strong>
                      </div>
                    </div>

                    <div class="progress mb-2.5" style="height: 7px; background: rgba(255,255,255,0.12); border-radius: 999px; overflow: hidden;">
                      <div class="progress-bar" role="progressbar" style="width: ${aq.percent || 15}%; background: ${aq.color || '#4ade80'}; border-radius: 999px;" aria-valuenow="${aq.percent || 15}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>

                  <div class="p-2.5 px-3 rounded-3 text-white-50 d-flex align-items-start gap-2.5 mt-auto" style="background: rgba(0, 0, 0, 0.24); border: 1px solid rgba(255, 255, 255, 0.06); font-size: 12px; line-height: 1.5;">
                    <i class="bi bi-lungs flex-shrink-0 mt-0.5 fs-6" style="color: ${aq.color || '#4ade80'};"></i>
                    <span>${aq.advice}</span>
                  </div>
                </div>
              </div>
            </div>
          `;

          const adviceHtml = w.advice
            ? `
              <div class="row g-3 mb-3">
                <div class="col-12">
                  <div class="p-3 px-3.5 rounded-pill small d-flex align-items-center gap-2.5 shadow-sm" style="background: linear-gradient(90deg, rgba(155, 189, 85, 0.22) 0%, rgba(155, 189, 85, 0.10) 100%); color: #d6f296; border: 1px solid rgba(155, 189, 85, 0.4); backdrop-filter: blur(8px);">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 24px; height: 24px; background: #9bbd55; color: #182213;">
                      <i class="bi bi-compass-fill" style="font-size: 12px;"></i>
                    </span>
                    <span class="fw-semibold">Gợi ý dã ngoại:</span>
                    <span class="text-white text-opacity-90">${w.advice}</span>
                  </div>
                </div>
              </div>
            `
            : '';

          targetPanel.innerHTML = `
            ${summaryHtml}
            ${envCardsHtml}
            ${adviceHtml}
            ${dailyHtml}
          `;
        }
      } else {
        throw new Error(data && data.message ? data.message : 'Không thể tải dữ liệu thời tiết.');
      }
    } catch (e) {
      if (badgeElement) badgeElement.textContent = 'Không tải được thời tiết.';
      if (warningElement) {
        warningElement.className = 'alert alert-warning mt-2';
        warningElement.textContent = 'Không thể xác minh cảnh báo thời tiết. Hãy kiểm tra dự báo chính thức trước khi đi.';
      }
      console.error('Lỗi tải thời tiết:', e);
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
    provincesData.forEach(function (p) {
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

      const prov = provincesData.find(function (p) { return String(p.code) === String(code); });
      if (!prov) return;

      distSelect.disabled = false;
      if (Array.isArray(prov.districts)) {
        prov.districts.forEach(function (d, idx) {
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
      const prov = provincesData.find(function (p) { return String(p.code) === String(code); });
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

  // =========================================================================
  // 6. FR07: Bình luận Realtime (Polling định kỳ không reload trang)
  // =========================================================================
  _commentPollingTimer: null,
  startCommentPolling: function (postId, intervalMs) {
    if (!postId) return;
    const self = this;
    const interval = intervalMs || 5000;

    if (this._commentPollingTimer) {
      clearInterval(this._commentPollingTimer);
    }

    this._commentPollingTimer = setInterval(function () {
      if (document.hidden) return; // Tiết kiệm mạng khi chuyển tab

      // Không polling cập nhật nếu người dùng đang gõ trong ô nhập bình luận
      const activeEl = document.activeElement;
      if (activeEl && (activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'INPUT')) {
        return;
      }

      self.fetchCommentsRealtime(postId);
    }, interval);
  },

  fetchCommentsRealtime: async function (postId) {
    const baseUrl = this.getBaseUrl();
    try {
      const response = await fetch(`${baseUrl}/api/comments.php?post_id=${postId}`);
      const json = await response.json();
      if (!json || !json.success || !json.data || !Array.isArray(json.data.comments)) {
        return;
      }

      const comments = json.data.comments;
      const listContainer = document.getElementById('comments-list');
      if (!listContainer) return;

      const self = this;
      comments.forEach(function (cm) {
        const existingNode = document.getElementById(`comment-thread-${cm.id}`) || document.getElementById(`comment-item-${cm.id}`);
        if (!existingNode) {
          // Bình luận mới xuất hiện từ thành viên khác: thêm động vào cây giao diện
          const threadEl = self.buildCommentThreadElement(cm, postId);
          const parentId = cm.parent_id;
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
            } else {
              listContainer.appendChild(threadEl);
            }
          } else {
            // Bình luận gốc thêm vào cuối danh sách hiện tại
            listContainer.appendChild(threadEl);
          }

          const noMsg = document.getElementById('no-comments-msg');
          if (noMsg) noMsg.remove();
        } else {
          // Cập nhật số lượt thích thời gian thực
          const countSpan = existingNode.querySelector('.comment-likes-count');
          if (countSpan && typeof cm.likes_count !== 'undefined') {
            countSpan.textContent = cm.likes_count > 0 ? cm.likes_count : '';
          }
        }
      });

      const countBadge = document.getElementById('comments-count-badge');
      if (countBadge && typeof json.data.total !== 'undefined') {
        countBadge.textContent = json.data.total;
      }
    } catch (err) {
      // Bỏ qua lỗi nền khi đang polling
    }
  },

  // =========================================================================
  // 7. FR04: Tìm kiếm gợi ý (Autocomplete khi gõ bằng AJAX)
  // =========================================================================
  initSearchAutocomplete: function (inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    if (!input || !dropdown) return;

    let debounceTimer = null;
    let selectedIndex = -1;
    const baseUrl = this.getBaseUrl();
    const self = this;

    input.addEventListener('input', function () {
      const q = input.value.trim();
      clearTimeout(debounceTimer);
      selectedIndex = -1;

      if (q.length < 2) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        return;
      }

      const spinner = document.getElementById('searchSpinner');
      if (spinner) spinner.style.display = 'inline-block';

      debounceTimer = setTimeout(async function () {
        try {
          const res = await fetch(`${baseUrl}/api/posts.php?q=${encodeURIComponent(q)}&suggest=1`);
          const json = await res.json();
          if (spinner) spinner.style.display = 'none';

          if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
            self.renderSearchSuggestions(dropdown, json.data, q, input);
          } else {
            dropdown.innerHTML = '<div class="p-3 text-center text-white-50 small"><i class="bi bi-search me-1"></i>Không tìm thấy gợi ý phù hợp cho "' + self.escapeHtml(q) + '"</div>';
            dropdown.style.display = 'block';
          }
        } catch (err) {
          if (spinner) spinner.style.display = 'none';
          dropdown.style.display = 'none';
        }
      }, 300);
    });

    // Bàn phím điều hướng (ArrowUp, ArrowDown, Enter, Escape)
    input.addEventListener('keydown', function (e) {
      const items = dropdown.querySelectorAll('.search-suggest-item');
      if (!items.length || dropdown.style.display === 'none') return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = (selectedIndex + 1) % items.length;
        self.updateSelectedSuggestion(items, selectedIndex);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
        self.updateSelectedSuggestion(items, selectedIndex);
      } else if (e.key === 'Enter') {
        if (selectedIndex >= 0 && items[selectedIndex]) {
          e.preventDefault();
          items[selectedIndex].click();
        }
      } else if (e.key === 'Escape') {
        dropdown.style.display = 'none';
      }
    });

    // Đóng dropdown khi bấm ra ngoài
    document.addEventListener('click', function (e) {
      if (!input.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });
  },

  updateSelectedSuggestion: function (items, index) {
    items.forEach(function (el, i) {
      el.classList.toggle('active', i === index);
      if (i === index) {
        el.scrollIntoView({ block: 'nearest' });
      }
    });
  },

  renderSearchSuggestions: function (dropdown, items, query, input) {
    const baseUrl = this.getBaseUrl();
    const self = this;
    let html = '';

    items.forEach(function (item) {
      const title = self.escapeHtml(item.title || '');
      const location = item.location ? self.escapeHtml(item.location) : '';
      const url = item.url ? item.url : `${baseUrl}/posts/${item.id}`;

      html += `
        <a href="${url}" class="search-suggest-item">
          <div class="search-suggest-icon">
            <i class="bi bi-compass-fill"></i>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="search-suggest-title text-truncate">${title}</div>
            ${location ? `<div class="search-suggest-location text-truncate"><i class="bi bi-geo-alt me-1"></i>${location}</div>` : ''}
          </div>
          <i class="bi bi-arrow-up-right small opacity-50"></i>
        </a>
      `;
    });

    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
  },

  escapeHtml: function (str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  },

  // =========================================================================
  // 8. FR03: Phân trang / "Tải thêm" (Infinite Scroll bằng AJAX)
  // =========================================================================
  initInfiniteScroll: function (config) {
    const btn = document.getElementById(config.btnId);
    const row = document.getElementById(config.rowId);
    const toggle = document.getElementById('toggleInfiniteScroll');
    const noMoreMsg = document.getElementById('no-more-posts-msg');
    if (!btn || !row) return;

    let currentPage = Number(btn.getAttribute('data-current-page') || 1);
    let totalPages = Number(btn.getAttribute('data-total-pages') || 1);
    const categoryId = btn.getAttribute('data-category-id') || '';
    const sort = btn.getAttribute('data-sort') || 'hot';
    let isLoading = false;
    const baseUrl = this.getBaseUrl();
    const self = this;

    async function loadNextPage() {
      if (isLoading || currentPage >= totalPages) return;
      isLoading = true;

      const originalBtnHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Đang tải thêm bài viết...';

      const nextPage = currentPage + 1;
      let url = `${baseUrl}/api/posts?page=${nextPage}&limit=6&sort=${encodeURIComponent(sort)}`;
      if (categoryId !== '') {
        url += `&category=${encodeURIComponent(categoryId)}`;
      }

      try {
        const response = await fetch(url);
        const json = await response.json();

        if (json && json.success && json.data && Array.isArray(json.data.posts)) {
          currentPage = nextPage;
          btn.setAttribute('data-current-page', String(currentPage));

          json.data.posts.forEach(function (post) {
            const cardCol = document.createElement('div');
            cardCol.className = 'col-12 col-md-6 col-lg-4 rise-in';
            cardCol.innerHTML = self.buildPostCardHtml(post);
            row.appendChild(cardCol);
          });

          if (currentPage >= totalPages || !json.data.has_more) {
            btn.style.display = 'none';
            if (toggle) {
              const toggleWrapper = toggle.closest('div');
              if (toggleWrapper) toggleWrapper.style.display = 'none';
            }
            if (noMoreMsg) noMoreMsg.style.display = 'block';
          }
        }
      } catch (err) {
        console.error('Lỗi tải thêm bài viết:', err);
      } finally {
        isLoading = false;
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;
      }
    }

    btn.addEventListener('click', function () {
      loadNextPage();
    });

    // Tự động tải khi cuộn (Infinite scroll) nếu bật toggle
    window.addEventListener('scroll', function () {
      if (!toggle || !toggle.checked) return;
      if (isLoading || currentPage >= totalPages) return;

      const scrollPosition = window.innerHeight + window.scrollY;
      const threshold = document.documentElement.offsetHeight - 450;
      if (scrollPosition >= threshold) {
        loadNextPage();
      }
    });
  },

  buildPostCardHtml: function (post) {
    const baseUrl = this.getBaseUrl();
    const postId = Number(post.id);
    const title = this.escapeHtml(post.title || '');
    const catName = this.escapeHtml(post.category_name || 'Dã ngoại');
    const location = post.location ? this.escapeHtml(post.location) : '';
    const voteScore = Number(post.vote_score ?? post.likes ?? 0);
    const commentsCount = Number(post.comments_count || 0);

    const defaultImages = [
      `${baseUrl}/assets/img/tent-forest.png`,
      `${baseUrl}/assets/img/ta-xua-trek.png`
    ];
    let img = post.image_url;
    if (!img) {
      img = defaultImages[postId % defaultImages.length];
    } else if (!img.startsWith('http')) {
      img = `${baseUrl}/${img.replace(/^\/+/, '')}`;
    }

    return `
      <article class="paper-card h-100 d-flex flex-column">
        <div class="card-header-meta">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="card-meta-tag">${catName}</span>
            ${location ? `<span class="badge text-bg-light border small text-muted text-truncate" style="max-width: 170px;"><i class="bi bi-geo-alt me-1"></i>${location}</span>` : ''}
          </div>
          <h2 class="card-title text-truncate">
            <a href="${baseUrl}/posts/${postId}" style="color: inherit; text-decoration: none;">${title}</a>
          </h2>
        </div>
        <div class="card-image-box">
          <img src="${img}" alt="${title}" loading="lazy">
          <a href="${baseUrl}/posts/${postId}" class="card-action-btn" aria-label="Xem chi tiết: ${title}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M7 7h10v10"/><path d="M7 17 17 7"/>
            </svg>
          </a>
        </div>
        <div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2 text-muted small mt-auto flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <div class="d-inline-flex align-items-center rounded-pill border px-1 py-0" style="background: rgba(34, 40, 31, 0.05); border-color: rgba(34, 40, 31, 0.14) !important;">
              <button type="button" class="btn btn-sm border-0 px-2 py-0 vote-up-btn" data-post-id="${postId}" onclick="ForumAjax.votePost(${postId}, 1, this)" title="Upvote (+1)" style="color: #2f5e2e; font-size: 0.85rem;">
                <i class="bi bi-caret-up-fill"></i>
              </button>
              <span class="fw-bold px-1 vote-score" data-post-id="${postId}" style="font-size: 0.82rem; color: #22281f; min-width: 20px; text-align: center;">${voteScore}</span>
              <button type="button" class="btn btn-sm border-0 px-2 py-0 vote-down-btn" data-post-id="${postId}" onclick="ForumAjax.votePost(${postId}, -1, this)" title="Downvote (-1)" style="color: #8c3b2b; font-size: 0.85rem;">
                <i class="bi bi-caret-down-fill"></i>
              </button>
            </div>
            <a href="${baseUrl}/posts/${postId}#comments-list" class="text-decoration-none text-muted d-inline-flex align-items-center px-2 py-1 rounded-pill" style="background: rgba(34, 40, 31, 0.05); font-size: 0.8rem;" title="Số bình luận">
              <i class="bi bi-chat-dots me-1"></i> ${commentsCount}
            </a>
          </div>
          <span style="font-size: 0.78rem;">${this.escapeHtml(post.username || '')}</span>
        </div>
      </article>
    `;
  },

  // =========================================================================
  // 9. FR01: Kiểm tra trùng username / email ngay khi đăng ký bằng AJAX
  // =========================================================================
  initRegisterLiveValidation: function () {
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const submitBtn = document.getElementById('registerSubmitBtn') || document.querySelector('button[type="submit"]');
    const baseUrl = this.getBaseUrl();
    const self = this;

    let usernameTimer = null;
    let emailTimer = null;
    let isUsernameAvailable = true;
    let isEmailAvailable = true;

    function updateSubmitBtn() {
      if (submitBtn) {
        submitBtn.disabled = (!isUsernameAvailable || !isEmailAvailable);
        submitBtn.style.opacity = (!isUsernameAvailable || !isEmailAvailable) ? '0.6' : '1';
      }
    }

    if (usernameInput) {
      const feedback = document.getElementById('username-feedback');

      usernameInput.addEventListener('input', function () {
        const val = usernameInput.value.trim();
        clearTimeout(usernameTimer);

        if (val.length < 3) {
          usernameInput.classList.remove('is-valid-ajax', 'is-invalid-ajax');
          if (feedback) feedback.style.display = 'none';
          isUsernameAvailable = true;
          updateSubmitBtn();
          return;
        }

        if (feedback) {
          feedback.innerHTML = '<span class="text-secondary"><span class="spinner-border spinner-border-sm me-1" role="status"></span> Đang kiểm tra tên tài khoản...</span>';
          feedback.style.display = 'block';
        }

        usernameTimer = setTimeout(async function () {
          try {
            const res = await fetch(`${baseUrl}/api/check-user.php?username=${encodeURIComponent(val)}`);
            const json = await res.json();
            if (json && json.success) {
              if (json.available) {
                usernameInput.classList.add('is-valid-ajax');
                usernameInput.classList.remove('is-invalid-ajax');
                if (feedback) {
                  feedback.innerHTML = '<span class="text-success fw-medium"><i class="bi bi-check-circle-fill me-1"></i>' + self.escapeHtml(json.message) + '</span>';
                }
                isUsernameAvailable = true;
              } else {
                usernameInput.classList.add('is-invalid-ajax');
                usernameInput.classList.remove('is-valid-ajax');
                if (feedback) {
                  feedback.innerHTML = '<span class="text-danger fw-medium"><i class="bi bi-x-circle-fill me-1"></i>' + self.escapeHtml(json.message) + '</span>';
                }
                isUsernameAvailable = false;
              }
            }
          } catch (err) {
            // bỏ qua lỗi tạm thời
          }
          updateSubmitBtn();
        }, 350);
      });
    }

    if (emailInput) {
      const feedback = document.getElementById('email-feedback');

      emailInput.addEventListener('input', function () {
        const val = emailInput.value.trim();
        clearTimeout(emailTimer);

        if (val.indexOf('@') === -1 || val.indexOf('.') === -1) {
          emailInput.classList.remove('is-valid-ajax', 'is-invalid-ajax');
          if (feedback) feedback.style.display = 'none';
          isEmailAvailable = true;
          updateSubmitBtn();
          return;
        }

        if (feedback) {
          feedback.innerHTML = '<span class="text-secondary"><span class="spinner-border spinner-border-sm me-1" role="status"></span> Đang kiểm tra email...</span>';
          feedback.style.display = 'block';
        }

        emailTimer = setTimeout(async function () {
          try {
            const res = await fetch(`${baseUrl}/api/check-user.php?email=${encodeURIComponent(val)}`);
            const json = await res.json();
            if (json && json.success) {
              if (json.available) {
                emailInput.classList.add('is-valid-ajax');
                emailInput.classList.remove('is-invalid-ajax');
                if (feedback) {
                  feedback.innerHTML = '<span class="text-success fw-medium"><i class="bi bi-check-circle-fill me-1"></i>' + self.escapeHtml(json.message) + '</span>';
                }
                isEmailAvailable = true;
              } else {
                emailInput.classList.add('is-invalid-ajax');
                emailInput.classList.remove('is-valid-ajax');
                if (feedback) {
                  feedback.innerHTML = '<span class="text-danger fw-medium"><i class="bi bi-x-circle-fill me-1"></i>' + self.escapeHtml(json.message) + '</span>';
                }
                isEmailAvailable = false;
              }
            }
          } catch (err) {
            // bỏ qua lỗi tạm thời
          }
          updateSubmitBtn();
        }, 350);
      });
    }
  },

  // =========================================================================
  // 10. Tính năng nâng cao: Lưu bài viết (Bookmark) qua AJAX
  // =========================================================================
  toggleBookmark: async function (postId, btn) {
    const baseUrl = this.getBaseUrl();
    const csrfToken = this.getCsrfToken();
    const self = this;

    try {
      const res = await fetch(`${baseUrl}/api/bookmarks.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          post_id: postId,
          _csrf: csrfToken,
        }),
      });

      const json = await res.json();
      if (!res.ok || !json.success) {
        if (typeof window.showToast === 'function') { window.showToast((json && json.message) || 'Không thể thực hiện lưu bài viết.', 'error'); } else { alert((json && json.message) || 'Không thể thực hiện lưu bài viết.'); }
        return false;
      }

      if (btn) {
        const bookmarked = Boolean(json.data && json.data.bookmarked);
        btn.classList.toggle('bookmarked', bookmarked);
        btn.classList.toggle('text-danger', bookmarked);
        btn.classList.toggle('text-dark', !bookmarked);

        const icon = btn.querySelector('i');
        if (icon) {
          icon.className = bookmarked ? 'bi bi-bookmark-heart-fill' : 'bi bi-bookmark-heart';
        }
        const text = btn.querySelector('.bookmark-text');
        if (text) {
          text.textContent = bookmarked ? 'Đã lưu' : 'Lưu bài';
        }
        btn.title = bookmarked ? 'Bỏ lưu bài viết' : 'Lưu bài viết vào bộ sưu tập';
      }
      if (typeof window.showToast === 'function') {
        window.showToast(json.message || (json.data && json.data.bookmarked ? 'Đã lưu bài viết vào bộ sưu tập.' : 'Đã bỏ lưu bài viết.'), 'success');
      }
      this.fetchNotifications();
      return true;
    } catch (err) {
      console.error('Lỗi lưu bài viết:', err);
      return false;
    }
  },

  // =========================================================================
  // 11. Tính năng nâng cao: Theo dõi / Bỏ theo dõi người dùng (Follow User)
  // =========================================================================
  toggleFollowUser: async function (userId, btn) {
    const baseUrl = this.getBaseUrl();
    const csrfToken = this.getCsrfToken();

    try {
      const res = await fetch(`${baseUrl}/api/follows.php?type=user`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          user_id: userId,
          _csrf: csrfToken,
        }),
      });

      const json = await res.json();
      if (!res.ok || !json.success) {
        if (typeof window.showToast === 'function') { window.showToast((json && json.message) || 'Không thể thực hiện theo dõi.', 'error'); } else { alert((json && json.message) || 'Không thể thực hiện theo dõi.'); }
        return false;
      }

      const isFollowing = Boolean(json.data && json.data.following);
      const followersCount = json.data ? json.data.followers_count : 0;

      if (btn) {
        btn.classList.toggle('following', isFollowing);
        const icon = btn.querySelector('i');
        if (icon) {
          icon.className = isFollowing ? 'bi bi-check-lg me-1' : 'bi bi-person-plus-fill me-1';
        }
        const text = btn.querySelector('.follow-btn-text');
        if (text) {
          text.textContent = isFollowing ? 'Đang theo dõi' : 'Theo dõi tác giả';
        }
        if (btn.id === 'btnFollowUser') {
          btn.style.background = isFollowing ? '#6c757d' : 'var(--color-moss, #9bbd55)';
        }
      }

      if (typeof window.showToast === 'function') {
        window.showToast(json.message || (isFollowing ? 'Đã theo dõi tác giả.' : 'Đã hủy theo dõi tác giả.'), 'success');
      }

      // Cập nhật số lượng người theo dõi nếu có counter trên trang
      const counter1 = document.getElementById('userFollowersCount');
      if (counter1) {
        counter1.textContent = followersCount;
      }
      const counter2 = document.getElementById('authorFollowersCount');
      if (counter2) {
        counter2.innerHTML = '<i class="bi bi-people me-1"></i>' + followersCount + ' người theo dõi';
      }

      this.fetchNotifications();
      return true;
    } catch (err) {
      console.error('Lỗi theo dõi người dùng:', err);
      return false;
    }
  },

  // =========================================================================
  // 12. Tính năng nâng cao: Theo dõi chuyên mục (Follow Category)
  // =========================================================================
  toggleFollowCategory: async function (categoryId, btn) {
    const baseUrl = this.getBaseUrl();
    const csrfToken = this.getCsrfToken();

    try {
      const res = await fetch(`${baseUrl}/api/follows.php?type=category`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          category_id: categoryId,
          _csrf: csrfToken,
        }),
      });

      const json = await res.json();
      if (!res.ok || !json.success) {
        if (typeof window.showToast === 'function') { window.showToast((json && json.message) || 'Không thể thực hiện thao tác.', 'error'); } else { alert((json && json.message) || 'Không thể thực hiện thao tác.'); }
        return false;
      }
      this.fetchNotifications();
      return true;
    } catch (err) {
      console.error('Lỗi theo dõi chuyên mục:', err);
      return false;
    }
  },

  // =========================================================================
  // 13. Tính năng nâng cao: Báo cáo vi phạm (Report)
  // =========================================================================
  submitReport: async function (form, targetId, targetType) {
    const baseUrl = this.getBaseUrl();
    const csrfToken = this.getCsrfToken();
    const reasonInput = form.querySelector('[name="reason"]');
    const detailsInput = form.querySelector('[name="details"]');
    const submitBtn = form.querySelector('button[type="submit"]');

    const reason = reasonInput ? reasonInput.value.trim() : '';
    const details = detailsInput ? detailsInput.value.trim() : '';

    if (!reason) {
      if (typeof window.showToast === 'function') { window.showToast('Vui lòng chọn lý do báo cáo vi phạm.', 'error'); } else { alert('Vui lòng chọn lý do báo cáo vi phạm.'); }
      return false;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Đang gửi...';
    }

    try {
      const res = await fetch(`${baseUrl}/api/reports.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          target_id: targetId,
          target_type: targetType,
          reason: reason,
          details: details,
          _csrf: csrfToken,
        }),
      });

      const json = await res.json();
      if (typeof window.showToast === 'function') { window.showToast((json && json.message) || 'Đã gửi báo cáo vi phạm.', 'error'); } else { alert((json && json.message) || 'Đã gửi báo cáo vi phạm.'); }

      // Đóng modal Bootstrap nếu có
      const modalEl = form.closest('.modal');
      if (modalEl && window.bootstrap && window.bootstrap.Modal) {
        const m = window.bootstrap.Modal.getInstance(modalEl);
        if (m) m.hide();
      }
      form.reset();
      this.fetchNotifications();
      return true;
    } catch (err) {
      console.error('Lỗi gửi báo cáo:', err);
      if (typeof window.showToast === 'function') { window.showToast('Có lỗi xảy ra khi gửi báo cáo vi phạm.', 'error'); } else { alert('Có lỗi xảy ra khi gửi báo cáo vi phạm.'); }
      return false;
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Gửi báo cáo';
      }
    }
  },

  // =========================================================================
  // 14. Tính năng nâng cao: Thông báo thời gian thực (Notifications)
  // =========================================================================
  toggleNotificationsDropdown: function (e) {
    if (e && e.stopPropagation) {
      e.stopPropagation();
    }
    const menu = document.getElementById('notificationsDropdownMenu');
    if (!menu) return;

    const isVisible = (menu.style.display === 'block' || window.getComputedStyle(menu).display === 'block');
    if (isVisible) {
      menu.style.display = 'none';
    } else {
      menu.style.display = 'block';
      this.fetchNotifications();
    }
  },

  fetchNotifications: async function () {
    const baseUrl = this.getBaseUrl();
    const container = document.getElementById('notificationsListContainer');
    const badge = document.getElementById('navUnreadBadge');
    const self = this;

    try {
      let res = await fetch(`${baseUrl}/api/notifications.php`, { credentials: 'same-origin' });
      let json = null;
      try {
        json = await res.json();
      } catch (parseErr) {
        const resFallback = await fetch(`${baseUrl}/api/notifications`, { credentials: 'same-origin' });
        json = await resFallback.json();
      }

      if (!json || !json.success || !json.data) return;

      const unreadCount = Number(json.data.unread_count || 0);
      [document.getElementById('navUnreadBadge'), document.getElementById('mobileNavUnreadBadge')].forEach(b => {
        if (!b) return;
        if (unreadCount > 0) {
          b.textContent = unreadCount > 99 ? '99+' : unreadCount;
          b.style.display = 'inline-block';
        } else {
          b.style.display = 'none';
        }
      });

      if (!container) return;

      const items = json.data.notifications || [];
      if (items.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted small"><i class="bi bi-bell-slash d-block fs-3 mb-1 opacity-50"></i>Bạn chưa có thông báo mới nào.</div>';
        return;
      }

      let html = '';
      items.forEach(function (n) {
        const isUnread = Number(n.is_read) === 0;

        // Icon & màu sắc theo loại thông báo
        const typeConfig = {
          'like_post': { icon: 'bi-heart-fill', color: '#e63946' },
          'like_comment': { icon: 'bi-heart-fill', color: '#e63946' },
          'comment_post': { icon: 'bi-chat-dots-fill', color: '#3498db' },
          'reply_comment': { icon: 'bi-chat-quote-fill', color: '#1d6fa4' },
          'follow_user': { icon: 'bi-person-plus-fill', color: '#2ecc71' },
          'follow_category': { icon: 'bi-tags-fill', color: '#6f9e38' },
          'new_post_followed': { icon: 'bi-bell-fill', color: '#9bbd55' },
          'create_post': { icon: 'bi-pencil-square', color: '#9bbd55' },
          'update_post': { icon: 'bi-journal-check', color: '#4a7c59' },
          'delete_post': { icon: 'bi-trash3-fill', color: '#e63946' },
          'create_trip': { icon: 'bi-compass-fill', color: '#4a7c59' },
          'join_trip': { icon: 'bi-people-fill', color: '#2ecc71' },
          'leave_trip': { icon: 'bi-person-dash-fill', color: '#8c9185' },
          'trip_new_member': { icon: 'bi-person-check-fill', color: '#20c997' },
          'create_checklist': { icon: 'bi-check2-square', color: '#6f9e38' },
          'bookmark_post': { icon: 'bi-bookmark-fill', color: '#f4a261' },
          'review_place': { icon: 'bi-star-fill', color: '#f9c74f' },
          'update_profile': { icon: 'bi-person-gear', color: '#6c757d' },
          'admin_pending_post': { icon: 'bi-hourglass-split', color: '#f4a261' },
          'admin_report': { icon: 'bi-flag-fill', color: '#e63946' },
          'post_approved': { icon: 'bi-check-circle-fill', color: '#2ecc71' },
          'post_rejected': { icon: 'bi-x-circle-fill', color: '#e63946' },
          'admin_action': { icon: 'bi-shield-check', color: '#4a7c59' },
          'system': { icon: 'bi-info-circle-fill', color: '#0dcaf0' },
          'activity': { icon: 'bi-activity', color: '#9bbd55' },
        };
        const cfg = typeConfig[n.type] || { icon: 'bi-bell-fill', color: '#9bbd55' };
        const targetId = Number(n.target_id || 0);

        // URL đích theo loại thông báo
        let linkUrl;
        if (n.type === 'follow_user') {
          linkUrl = `${baseUrl}/users/${targetId > 0 ? targetId : n.actor_id}`;
        } else if (n.type === 'follow_category') {
          linkUrl = targetId > 0 ? `${baseUrl}/posts?category=${targetId}` : `${baseUrl}/categories`;
        } else if (n.type === 'delete_post') {
          linkUrl = `${baseUrl}/posts`;
        } else if (n.type === 'admin_pending_post') {
          linkUrl = `${baseUrl}/admin/posts?status=pending`;
        } else if (n.type === 'admin_report' || n.type === 'admin_action') {
          linkUrl = targetId > 0 ? `${baseUrl}/posts/${targetId}` : `${baseUrl}/admin/posts`;
        } else if (['create_trip', 'join_trip', 'leave_trip', 'trip_new_member'].includes(n.type)) {
          linkUrl = targetId > 0 ? `${baseUrl}/trips/${targetId}` : `${baseUrl}/trips`;
        } else if (n.type === 'create_checklist') {
          linkUrl = `${baseUrl}/checklists`;
        } else if (n.type === 'update_profile') {
          linkUrl = `${baseUrl}/profile`;
        } else if (targetId > 0) {
          linkUrl = `${baseUrl}/posts/${targetId}`;
        } else {
          linkUrl = `${baseUrl}/profile`;
        }

        html += `
          <a href="${linkUrl}" class="notification-item ${isUnread ? 'unread' : ''}" onclick="ForumAjax.markNotificationRead(${Number(n.id || 0)})">
            <div class="notification-icon" style="background: ${cfg.color}22; color: ${cfg.color};">
              <i class="bi ${cfg.icon}"></i>
            </div>
            <div class="notification-content">
              <div class="fw-medium">${self.escapeHtml(n.content)}</div>
              <div class="notification-time"><i class="bi bi-clock" style="font-size:0.68rem;margin-right:3px;"></i>${self.escapeHtml(n.created_at || 'Vừa xong')}</div>
            </div>
          </a>
        `;
      });

      container.innerHTML = html;
    } catch (err) {
      console.error('Lỗi tải thông báo:', err);
    }
  },

  markNotificationRead: function (id) {
    if (!id) return;
    const baseUrl = this.getBaseUrl();
    const csrfToken = this.getCsrfToken();
    try {
      fetch(`${baseUrl}/api/notifications.php?action=read`, {
        method: 'POST',
        keepalive: true,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ id: Number(id), _csrf: csrfToken }),
      });
    } catch (err) {
    }
  },

  markAllNotificationsRead: async function () {
    const baseUrl = this.getBaseUrl();
    const csrfToken = this.getCsrfToken();
    const badge = document.getElementById('navUnreadBadge');

    try {
      await fetch(`${baseUrl}/api/notifications.php?action=read`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ _csrf: csrfToken }),
      });

      [document.getElementById('navUnreadBadge'), document.getElementById('mobileNavUnreadBadge')].forEach(function (b) { if (b) b.style.display = 'none'; });
      const unreadItems = document.querySelectorAll('.notification-item.unread');
      unreadItems.forEach(function (item) {
        item.classList.remove('unread');
      });
    } catch (err) {
      console.error('Lỗi đánh dấu đã đọc thông báo:', err);
    }
  },

  startNotificationPolling: function () {
    const self = this;
    // Tải ngay lần đầu
    this.fetchNotifications();
    // Sau đó lặp lại mỗi 25 giây
    setInterval(function () {
      self.fetchNotifications();
    }, 25000);

    // Đóng dropdown thông báo khi bấm ra ngoài
    document.addEventListener('click', function (e) {
      const menu = document.getElementById('notificationsDropdownMenu');
      const btn = document.getElementById('btnNotificationToggle');
      if (menu && menu.style.display === 'block') {
        if (!menu.contains(e.target) && btn && !btn.contains(e.target)) {
          menu.style.display = 'none';
        }
      }
    });
  },

  // =========================================================================
  // 16. QUICKCHART / GOQR CODE API - CHIA SẺ CHUYẾN ĐI & BÀI VIẾT BẰNG ĐIỆN THOẠI
  // =========================================================================
  _qrModalData: null,

  openQrShareModal: async function (targetPathOrUrl, title, itemType) {
    const modalEl = document.getElementById('qrShareModal');
    if (!modalEl) return;

    const baseUrl = this.getBaseUrl();
    const titleEl = document.getElementById('qrModalItemTitle');
    const badgeEl = document.getElementById('qrModalTypeBadge');
    const loadingEl = document.getElementById('qrModalLoading');
    const imgEl = document.getElementById('qrModalImage');
    const urlInput = document.getElementById('qrModalShareUrlInput');
    const modeSwitchBox =
      document.getElementById('qrModalModeSwitch') ||
      document.getElementById('qrModalLanSwitchBox');
    const downloadBtn =
      document.getElementById('btnDownloadQrImage') ||
      document.getElementById('qrModalDownloadBtn');
    const openLinkBtn = document.getElementById('qrModalOpenLinkBtn');

    if (titleEl) {
      titleEl.textContent = title || 'Chia sẻ liên kết dã ngoại';
    }

    if (badgeEl) {
      if (itemType === 'trip') {
        badgeEl.innerHTML = '<i class="bi bi-compass-fill me-1"></i>Chuyến đi chung';
      } else if (itemType === 'checklist') {
        badgeEl.innerHTML = '<i class="bi bi-card-checklist me-1"></i>Checklist trang bị';
      } else {
        badgeEl.innerHTML = '<i class="bi bi-journal-richtext me-1"></i>Bài viết dã ngoại';
      }
    }

    // Xây dựng URL đầy đủ nếu truyền đường dẫn tương đối
    let fullTargetUrl = targetPathOrUrl || window.location.href;
    if (fullTargetUrl.indexOf('http://') !== 0 && fullTargetUrl.indexOf('https://') !== 0) {
      if (fullTargetUrl.charAt(0) !== '/') {
        fullTargetUrl = '/' + fullTargetUrl;
      }
      fullTargetUrl = window.location.origin + baseUrl + fullTargetUrl;
    }

    if (urlInput) urlInput.value = fullTargetUrl;
    if (openLinkBtn) openLinkBtn.href = fullTargetUrl;

    // Reset trạng thái hiển thị
    if (loadingEl) {
      loadingEl.style.removeProperty('display');
      loadingEl.style.display = 'flex';
    }

    // Mở Bootstrap Modal
    if (window.bootstrap && window.bootstrap.Modal) {
      const bsModal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
      bsModal.show();
    }

    // Tải ảnh QR siêu tốc ngay tức thì từ Web Service QuickChart (chỉ mất ~0.3s)
    const instantQrUrl =
      'https://quickchart.io/qr?text=' +
      encodeURIComponent(fullTargetUrl) +
      '&size=260&margin=2&ecLevel=M&dark=1e2b18&light=ffffff&format=png';

    if (imgEl) {
      imgEl.onload = function () {
        if (loadingEl) {
          loadingEl.style.setProperty('display', 'none', 'important');
        }
      };
      imgEl.onerror = function () {
        // Dự phòng GoQR REST API nếu mạng có vấn đề với QuickChart
        const fallbackUrl =
          'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' +
          encodeURIComponent(fullTargetUrl) +
          '&color=30-43-24&bgcolor=255-255-255&qzone=2&format=png';
        if (imgEl.src !== fallbackUrl) {
          imgEl.src = fallbackUrl;
        } else if (loadingEl) {
          loadingEl.innerHTML =
            '<span class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Không thể tải ảnh QR</span>';
        }
      };
      imgEl.src = instantQrUrl;
    }

    if (downloadBtn) {
      downloadBtn.href = instantQrUrl;
      const safeSlug = String(itemType || 'trip').replace(/[^a-z0-9_-]/gi, '');
      downloadBtn.setAttribute('download', 'Fieldcraft-QR-' + safeSlug + '.png');
    }

    // Gọi API backend lấy dữ liệu IP LAN để hỗ trợ quét mở trên cùng mạng Wi-Fi
    try {
      const apiUrl =
        baseUrl +
        '/api/qr?target_url=' +
        encodeURIComponent(fullTargetUrl) +
        '&title=' +
        encodeURIComponent(title || '') +
        '&type=' +
        encodeURIComponent(itemType || 'trip');

      const response = await fetch(apiUrl, {
        headers: { Accept: 'application/json' },
      });
      const result = await response.json();

      if (result && result.success && result.data) {
        const d = result.data;
        this._qrModalData = d;

        // Nếu backend đã tải xong Base64 ảnh vào cache thì gán trực tiếp
        if (d.qr_data_uri && imgEl) {
          imgEl.src = d.qr_data_uri;
        }

        // Bật nút chuyển IP LAN nếu đang chạy trên localhost và tự động kích hoạt IP Wi-Fi
        if (modeSwitchBox) {
          if (d.is_localhost && d.lan_ip && d.lan_url !== d.original_url) {
            modeSwitchBox.classList.remove('d-none');
            modeSwitchBox.style.display = 'block';
            const btnLan = document.getElementById('btnQrModeLan');
            if (btnLan) {
              btnLan.innerHTML = '<i class="bi bi-wifi me-1"></i>IP Wi-Fi';
              btnLan.setAttribute('title', 'Quét qua mạng Wi-Fi: ' + String(d.lan_ip || ''));
            }
            // Tự động kích hoạt chế độ IP Wi-Fi để camera điện thoại quét vào thẳng máy tính
            this.switchQrUrlMode('lan');
          } else {
            modeSwitchBox.style.display = 'none';
          }
        }
      }
    } catch (err) {
      console.warn('Backend QR metadata fetch error (ảnh QR vẫn hiển thị bình thường qua QuickChart API):', err);
    }
  },

  switchQrUrlMode: function (mode) {
    const d = this._qrModalData;
    if (!d) return;

    const imgEl = document.getElementById('qrModalImage');
    const loadingEl = document.getElementById('qrModalLoading');
    const urlInput = document.getElementById('qrModalShareUrlInput');
    const downloadBtn =
      document.getElementById('btnDownloadQrImage') ||
      document.getElementById('qrModalDownloadBtn');
    const btnLan = document.getElementById('btnQrModeLan');
    const btnStandard =
      document.getElementById('btnQrModeStandard') ||
      document.getElementById('btnQrModeLocal');

    const isLan = mode === 'lan';
    const chosenUrl = isLan
      ? d.lan_url || d.target_url
      : d.original_url || d.target_url;

    const newQrSrc =
      'https://quickchart.io/qr?text=' +
      encodeURIComponent(chosenUrl) +
      '&size=260&margin=2&ecLevel=M&dark=1e2b18&light=ffffff&format=png';

    if (loadingEl) {
      loadingEl.style.removeProperty('display');
      loadingEl.style.display = 'flex';
    }
    if (imgEl) {
      imgEl.onload = function () {
        if (loadingEl) {
          loadingEl.style.setProperty('display', 'none', 'important');
        }
      };
      imgEl.src = newQrSrc;
    }

    if (urlInput) {
      urlInput.value = chosenUrl;
    }
    if (downloadBtn) {
      downloadBtn.href = newQrSrc;
    }

    if (btnLan && btnStandard) {
      if (isLan) {
        btnLan.style.background = '#2f5e2e';
        btnLan.style.color = '#ffffff';
        btnStandard.style.background = 'transparent';
        btnStandard.style.color = '#3a4235';
      } else {
        btnStandard.style.background = '#2f5e2e';
        btnStandard.style.color = '#ffffff';
        btnLan.style.background = 'transparent';
        btnLan.style.color = '#3a4235';
      }
    }
  },

  copyQrShareUrl: function () {
    const urlInput = document.getElementById('qrModalShareUrlInput');
    const copyText = document.getElementById('qrModalCopyText');
    const copyBtn = document.getElementById('btnCopyQrUrl');
    if (!urlInput) return;

    const textToCopy = urlInput.value;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(textToCopy).then(function () {
        if (copyText) copyText.textContent = 'Đã chép!';
        if (copyBtn) copyBtn.style.background = '#1f5e39';
        if (window.ForumAjax) {
          window.ForumAjax.showToast('Đã sao chép liên kết chia sẻ vào bộ nhớ tạm!', 'success');
        }
        setTimeout(function () {
          if (copyText) copyText.textContent = 'Sao chép';
          if (copyBtn) copyBtn.style.background = '#233d2c';
        }, 2000);
      });
    } else {
      urlInput.select();
      document.execCommand('copy');
      if (copyText) copyText.textContent = 'Đã chép!';
      setTimeout(function () {
        if (copyText) copyText.textContent = 'Sao chép';
      }, 2000);
    }
  },
};

// Khởi chạy polling thông báo nếu có chuông thông báo trên trang
document.addEventListener('DOMContentLoaded', function () {
  if (document.getElementById('btnNotificationToggle') && window.ForumAjax) {
    window.ForumAjax.startNotificationPolling();
  }
});

window.ForumAjax = ForumAjax;
