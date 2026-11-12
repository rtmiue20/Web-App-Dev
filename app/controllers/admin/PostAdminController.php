<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/PostService.php';
require_once __DIR__ . '/../../repositories/PostRepository.php';

/**
 * Controller PostAdminController - Quản trị bài viết diễn đàn (Tuần 4 - FR13 - Phúc)
 * Ủy quyền xử lý nghiệp vụ kiểm duyệt cho PostService -> PostRepository.
 */
class PostAdminController extends Controller
{
    private PostService $postService;
    private PostRepository $postRepository;

    public function __construct(?PostService $postService = null, ?PostRepository $postRepository = null)
    {
        $this->postService = $postService ?? new PostService();
        $this->postRepository = $postRepository ?? new PostRepository();
    }

    /**
     * Danh sách bài viết quản trị (/admin/posts).
     */
    public function index(): void
    {
        $data = $this->postService->getAdminPostListing($_GET, 10);
        $data['title'] = 'Quản lý Bài viết';

        $this->render('admin/posts/index', $data, 'admin');
    }

    /**
     * Chuyển trạng thái bài viết qua menu xổ xuống (POST /admin/posts/{id}/status).
     */
    public function changeStatus($id): void
    {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        $isAjax = $this->isAjaxRequest();

        if (!$this->verifyCsrfToken()) {
            if ($isAjax) {
                http_response_code(419);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.',
                ]);
                return;
            }
            Session::flash('error', 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.');
            $this->redirect('/admin/posts');
            return;
        }

        $newStatus = trim((string)($_POST['status'] ?? ''));
        $result = $this->postService->changeStatus($id, $newStatus);

        if ($result['success']) {
            $payload = $result['data'];
            if ($isAjax) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'status'     => 'success',
                    'message'    => $result['message'],
                    'post_id'    => $payload['post_id'],
                    'new_status' => $payload['new_status'],
                    'new_label'  => $payload['new_label'],
                    'counts'     => $payload['counts'],
                ]);
                return;
            }
            Session::flash('success', $result['message']);
        } else {
            if ($isAjax) {
                http_response_code((int)($result['status'] ?? 400));
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'status'  => 'error',
                    'message' => $result['message'],
                ]);
                return;
            }
            Session::flash('error', $result['message']);
        }

        $this->redirect('/admin/posts');
    }

    /**
     * Duyệt bài viết (POST /admin/posts/{id}/approve) - FR13.
     */
    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.');
            $this->redirect('/admin/posts');
            return;
        }

        $result = $this->postService->changeStatus($id, 'published');
        if ($result['success']) {
            $post = $result['data']['post'];
            Session::flash('success', 'Đã duyệt bài viết "' . $post['title'] . '" thành công.');
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('/admin/posts');
    }

    /**
     * Ẩn bài viết khỏi diễn đàn (POST /admin/posts/{id}/hide) - FR13.
     */
    public function hide(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.');
            $this->redirect('/admin/posts');
            return;
        }

        $result = $this->postService->changeStatus($id, 'hidden');
        if ($result['success']) {
            $post = $result['data']['post'];
            Session::flash('success', 'Đã ẩn bài viết "' . $post['title'] . '" khỏi diễn đàn.');
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('/admin/posts');
    }

    /**
     * Xóa bài viết vĩnh viễn (POST /admin/posts/{id}/delete) - FR13.
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.');
            $this->redirect('/admin/posts');
            return;
        }

        $post = $this->postRepository->findById($id);
        if (!$post) {
            Session::flash('error', 'Bài viết không tồn tại.');
            $this->redirect('/admin/posts');
            return;
        }

        if ($this->postRepository->delete($id)) {
            if (class_exists('Notification')) {
                $adminId = (int)Auth::id();
                $authorId = (int)($post['user_id'] ?? 0);
                $shortTitle = mb_substr((string)($post['title'] ?? 'bài viết'), 0, 38);
                if ($adminId > 0) {
                    Notification::send($adminId, $adminId, 'admin_action', 0, "Bạn đã xóa vĩnh viễn bài viết: '{$shortTitle}'.");
                }
                if ($authorId > 0 && $authorId !== $adminId) {
                    Notification::send($authorId, $adminId > 0 ? $adminId : $authorId, 'post_rejected', 0, "Ban quản trị đã xóa bài viết '{$shortTitle}' của bạn.");
                }
            }
            Session::flash('success', 'Đã xóa bài viết "' . $post['title'] . '" vĩnh viễn.');
        } else {
            Session::flash('error', 'Không thể xóa bài viết. Vui lòng thử lại.');
        }

        $this->redirect('/admin/posts');
    }
}
