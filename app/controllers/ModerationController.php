<?php

class ModerationController extends Controller
{
    private Report $reports;
    private Post $posts;
    private Comment $comments;

    public function __construct()
    {
        $this->reports = new Report();
        $this->posts = new Post();
        $this->comments = new Comment();
    }

    /**
     * Giao diện Hàng chờ kiểm duyệt (Dành cho Moderator & Admin)
     */
    public function index(): void
    {
        $status = $_GET['status'] ?? 'pending';
        $tab = $_GET['tab'] ?? 'reports'; // 'reports' hoặc 'posts'

        $reportsList = $this->reports->getAll($status);
        $pendingReportsCount = $this->reports->countPending();

        $pendingPosts = $this->posts->getAllForAdmin('pending');
        $pendingPostsCount = $this->posts->countForAdmin('pending');

        $this->render('admin/moderation/index', [
            'title'               => 'Hàng chờ kiểm duyệt & Báo cáo vi phạm',
            'reports'             => $reportsList,
            'pendingPosts'        => $pendingPosts,
            'pendingReportsCount' => $pendingReportsCount,
            'pendingPostsCount'   => $pendingPostsCount,
            'currentStatus'       => $status,
            'currentTab'          => $tab,
        ]);
    }

    /**
     * Xử lý báo cáo vi phạm: Đánh dấu Đã xử lý (resolved) hoặc Bác bỏ (dismissed)
     */
    public function resolve(int $id): void
    {
        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/admin/moderation');
            return;
        }

        $action = $_POST['action'] ?? 'resolve'; // 'resolve', 'dismiss', 'hide_target'
        $resolvedBy = (int)Auth::id();

        if ($action === 'hide_target') {
            $targetType = $_POST['target_type'] ?? '';
            $targetId = (int)($_POST['target_id'] ?? 0);

            if ($targetType === 'post' && $targetId > 0) {
                $post = $this->posts->getById($targetId);
                $this->posts->updateStatus($targetId, 'hidden');
                if ($post && class_exists('Notification') && (int)($post['user_id'] ?? 0) !== $resolvedBy) {
                    $shortTitle = mb_substr((string)($post['title'] ?? 'bài viết'), 0, 38);
                    Notification::send((int)$post['user_id'], $resolvedBy, 'post_rejected', $targetId, "Ban quản trị đã ẩn bài viết '{$shortTitle}' của bạn sau khi xử lý báo cáo vi phạm.");
                }
            } elseif ($targetType === 'comment' && $targetId > 0) {
                $this->comments->delete($targetId, $resolvedBy, true);
            }
            $this->reports->updateStatus($id, 'resolved', $resolvedBy);
            if (class_exists('Notification') && $resolvedBy > 0) {
                Notification::send($resolvedBy, $resolvedBy, 'admin_action', 0, 'Bạn đã xử lý báo cáo vi phạm #' . $id . ' và ẩn/xóa nội dung vi phạm.');
            }
            Session::flash('success', 'Đã xử lý vi phạm và ẩn/xóa nội dung thành công.');
        } elseif ($action === 'dismiss') {
            $this->reports->updateStatus($id, 'dismissed', $resolvedBy);
            if (class_exists('Notification') && $resolvedBy > 0) {
                Notification::send($resolvedBy, $resolvedBy, 'admin_action', 0, 'Bạn đã bác bỏ báo cáo vi phạm #' . $id . '.');
            }
            Session::flash('success', 'Đã bác bỏ báo cáo vi phạm.');
        } else {
            $this->reports->updateStatus($id, 'resolved', $resolvedBy);
            if (class_exists('Notification') && $resolvedBy > 0) {
                Notification::send($resolvedBy, $resolvedBy, 'admin_action', 0, 'Bạn đã đánh dấu xử lý xong báo cáo vi phạm #' . $id . '.');
            }
            Session::flash('success', 'Đã đánh dấu xử lý xong báo cáo vi phạm.');
        }

        $this->redirect('/admin/moderation');
    }
}
