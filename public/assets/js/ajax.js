/**
 * AJAX xử lý tương tác cho Diễn Đàn Dã Ngoại (Tuần 4 - Vương)
 * Bao gồm:
 * - FR07: Bình luận bằng AJAX (CommentApi: thêm mới, tải thêm Load More, xóa bình luận của mình)
 * - FR08: Thích / bỏ thích bài viết bằng AJAX (LikeApi: cập nhật số lượt ngay trên trang, gửi kèm CSRF token)
 * - FR06: Tải thông tin thời tiết địa điểm
 * Lưu ý: Toàn bộ các hàm được định nghĩa tường minh (named functions), không sử dụng hàm ẩn danh / lambda.
 */

function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content') || '';
}

function showError(message) {
    alert(message);
}

function updateCommentsBadge(total) {
    var badge = $('#comments-total-badge');
    if (badge.length > 0 && typeof total === 'number' && total >= 0) {
        badge.text(total);
    }
}

// ==========================================
// 1. XỬ LÝ THÍCH / BỎ THÍCH BÀI VIẾT (FR08 - LikeApi)
// ==========================================
function handleLikePostClick() {
    var button = $(this);
    var postId = button.data('post-id');

    button.prop('disabled', true);

    function onLikeSuccess(response) {
        if (response && response.success && response.data) {
            var count = (typeof response.data.likes !== 'undefined') ? response.data.likes : response.data.count;
            button.find('.like-count').text(count);

            var icon = button.find('i');
            if (response.data.liked) {
                icon.removeClass('bi-heart').addClass('bi-heart-fill');
                button.removeClass('btn-outline-danger').addClass('btn-danger');
                button.attr('aria-pressed', 'true');
            } else {
                icon.removeClass('bi-heart-fill').addClass('bi-heart');
                button.removeClass('btn-danger').addClass('btn-outline-danger');
                button.attr('aria-pressed', 'false');
            }
        } else {
            showError((response && response.message) ? response.message : 'Không thể thực hiện thao tác.');
        }
    }

    function onLikeError(xhr) {
        var message = 'Lỗi kết nối máy chủ. Vui lòng thử lại.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        showError(message);
    }

    function onLikeComplete() {
        button.prop('disabled', false);
    }

    $.ajax({
        url: window.BASE_URL + '/api/posts/' + postId + '/like',
        type: 'POST',
        dataType: 'json',
        data: { _csrf: getCsrfToken() },
        success: onLikeSuccess,
        error: onLikeError,
        complete: onLikeComplete
    });
}

// ==========================================
// 2. DỰNG THẺ BÌNH LUẬN TỪ TEMPLATE (FR07)
// ==========================================
function buildCommentCard(comment) {
    var templateContent = $('#comment-template').prop('content');
    var card = $(templateContent).children('.comment-card').clone();

    card.attr('data-comment-id', comment.id);
    card.find('.js-username').text(comment.username);
    card.find('.js-time').text(comment.created_at);
    card.find('.js-content').text(comment.content);

    var deleteButton = card.find('.btn-delete-comment');
    if (comment.can_delete) {
        deleteButton.attr('data-comment-id', comment.id).removeClass('d-none');
    } else {
        deleteButton.remove();
    }

    return card;
}

// ==========================================
// 3. GỬI BÌNH LUẬN MỚI BẰNG AJAX (FR07 - CommentApi::store)
// ==========================================
function handleCommentFormSubmit(e) {
    e.preventDefault();
    var form = $(this);
    var button = form.find('button[type="submit"]');
    var content = $.trim($('#comment-content').val());
    var postId = $('#comments-list').data('post-id');

    form.find('.comment-error-msg').remove();

    if (content.length < 2) {
        form.prepend('<div class="alert alert-danger py-2 mb-3 comment-error-msg"><i class="bi bi-exclamation-circle me-1"></i>Bình luận phải có ít nhất 2 ký tự.</div>');
        return;
    }

    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang gửi...');

    function onCommentStoreSuccess(response) {
        if (response && response.success && response.data) {
            $('#no-comments-msg').remove();

            var newComment = {
                id: response.data.id,
                username: response.data.username,
                created_at: response.data.created_at,
                content: response.data.content,
                can_delete: true
            };

            var item = buildCommentCard(newComment);
            $('#comments-list').prepend(item);
            $('#comment-content').val('');

            if (typeof response.data.total_comments === 'number') {
                updateCommentsBadge(response.data.total_comments);
            }
        } else {
            var msg = (response && response.message) ? response.message : 'Không thể gửi bình luận.';
            form.prepend('<div class="alert alert-danger py-2 mb-3 comment-error-msg">' + msg + '</div>');
        }
    }

    function onCommentStoreError(xhr) {
        var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Lỗi kết nối máy chủ.';
        form.prepend('<div class="alert alert-danger py-2 mb-3 comment-error-msg">' + errorMsg + '</div>');
    }

    function onCommentStoreComplete() {
        button.prop('disabled', false).html('<i class="bi bi-send me-1"></i> Gửi bình luận');
    }

    $.ajax({
        url: window.BASE_URL + '/api/posts/' + postId + '/comments',
        type: 'POST',
        dataType: 'json',
        data: {
            content: content,
            _csrf: getCsrfToken()
        },
        success: onCommentStoreSuccess,
        error: onCommentStoreError,
        complete: onCommentStoreComplete
    });
}

// ==========================================
// 4. TẢI THÊM BÌNH LUẬN (FR07 - CommentApi::list)
// ==========================================
function handleLoadMoreCommentsClick() {
    var button = $(this);
    var list = $('#comments-list');
    var postId = list.data('post-id');
    var nextPage = parseInt(list.attr('data-page'), 10) + 1;

    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang tải...');

    function onLoadMoreSuccess(response) {
        if (!response || !response.success || !response.data) {
            showError((response && response.message) ? response.message : 'Không thể tải bình luận.');
            return;
        }

        var comments = response.data.comments || [];
        for (var i = 0; i < comments.length; i++) {
            if (list.find('.comment-card[data-comment-id="' + comments[i].id + '"]').length === 0) {
                list.append(buildCommentCard(comments[i]));
            }
        }

        list.attr('data-page', nextPage);

        if (!response.data.has_more) {
            button.parent().remove();
        }
    }

    function onLoadMoreError() {
        showError('Lỗi kết nối máy chủ. Vui lòng thử lại.');
    }

    function onLoadMoreComplete() {
        button.prop('disabled', false).html('<i class="bi bi-chevron-down me-1"></i> Xem thêm bình luận');
    }

    $.ajax({
        url: window.BASE_URL + '/api/posts/' + postId + '/comments',
        type: 'GET',
        dataType: 'json',
        data: { page: nextPage },
        success: onLoadMoreSuccess,
        error: onLoadMoreError,
        complete: onLoadMoreComplete
    });
}

// ==========================================
// 5. XÓA BÌNH LUẬN CỦA CHÍNH MÌNH (FR07 - CommentApi::delete)
// ==========================================
function handleDeleteCommentClick() {
    var button = $(this);
    var commentId = button.attr('data-comment-id');

    if (!confirm('Bạn có chắc chắn muốn xóa bình luận này không?')) {
        return;
    }

    button.prop('disabled', true);

    function onFadeOutComplete() {
        $(this).remove();
        if ($('#comments-list .comment-card').length === 0) {
            $('#comments-list').html(
                '<div class="alert alert-dark border-secondary border-opacity-25 rounded-4 text-white-50 fst-italic mb-0" id="no-comments-msg">' +
                'Chưa có bình luận nào. Hãy để lại bình luận đầu tiên!' +
                '</div>'
            );
        }
    }

    function onDeleteSuccess(response) {
        if (response && response.success) {
            if (response.data && typeof response.data.total_comments === 'number') {
                updateCommentsBadge(response.data.total_comments);
            }
            button.closest('.comment-card').fadeOut(200, onFadeOutComplete);
        } else {
            showError((response && response.message) ? response.message : 'Không thể xóa bình luận.');
            button.prop('disabled', false);
        }
    }

    function onDeleteError(xhr) {
        var message = 'Lỗi kết nối máy chủ. Vui lòng thử lại.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        showError(message);
        button.prop('disabled', false);
    }

    $.ajax({
        url: window.BASE_URL + '/api/comments/' + commentId + '/delete',
        type: 'POST',
        dataType: 'json',
        data: { _csrf: getCsrfToken() },
        success: onDeleteSuccess,
        error: onDeleteError
    });
}

// ==========================================
// 6. TẢI THÔNG TIN THỜI TIẾT (FR06)
// ==========================================
function loadPostWeatherInfo() {
    var weatherBadge = $('#weather-badge');
    var weatherInfo = $('#weather-info');

    if (weatherBadge.length === 0 || weatherInfo.length === 0) {
        return;
    }

    var lat = weatherBadge.attr('data-lat');
    var lng = weatherBadge.attr('data-lng');

    function onWeatherSuccess(res) {
        if (res && res.success && res.data) {
            weatherInfo.html(' | <i class="bi bi-cloud-sun me-1"></i> ' + res.data.temp + '°C, ' + res.data.condition);
        }
    }

    $.ajax({
        url: window.BASE_URL + '/../api/weather.php',
        type: 'GET',
        data: { lat: lat, lng: lng },
        dataType: 'json',
        success: onWeatherSuccess
    });
}

// ==========================================
// 7. KHỞI TẠO SỰ KIỆN KHI TRANG TẢI XONG
// ==========================================
function initForumAjax() {
    $('#btn-like-post').on('click', handleLikePostClick);
    $('#comment-form').on('submit', handleCommentFormSubmit);
    $('#btn-load-more').on('click', handleLoadMoreCommentsClick);
    $('#comments-list').on('click', '.btn-delete-comment', handleDeleteCommentClick);
    loadPostWeatherInfo();
}

$(document).ready(initForumAjax);