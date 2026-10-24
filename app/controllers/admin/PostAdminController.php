<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Post.php';
require_once __DIR__ . '/../../models/Category.php';

/**
 * Controller PostAdminController - Quản trị bài viết diễn đàn (Tuần 4 - FR13 - Phúc)
 * Chức năng: Xem danh sách, Duyệt bài, Ẩn bài, Xóa bài
 * Bảo mật: Chỉ Quản trị viên (Admin) mới có quyền truy cập qua AdminMiddleware
 */
class PostAdminController extends Controller
{
    private Post $posts;
    private Category $categories;

    public function __construct()
    {
        $this->posts = new Post();
        $this->categories = new Category();
    }

    /**
     * Danh sách bài viết quản trị (/admin/posts)
     */
    public function index(): void
    {
        $status = null;
        if (isset($_GET['status']) && $_GET['status'] !== '' && $_GET['status'] !== 'all') {
            $status = (string)$_GET['status'];
        }

        $categoryId = null;
        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $categoryId = (int)$_GET['category'];
            if ($categoryId <= 0) {
                $categoryId = null;
            }
        }

        $page = 1;
        if (isset($_GET['page'])) {
            $page = (int)$_GET['page'];
            if ($page < 1) {
                $page = 1;
            }
        }

        $limit = 10; // 10 bài viết mỗi trang trong khu vực quản trị

        $totalPosts = $this->posts->countForAdmin($status, $categoryId);
        $totalPages = 1;
        if ($totalPosts > 0) {
            $totalPages = (int)ceil($totalPosts / $limit);
        }

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $postsList = $this->posts->getAllForAdmin($status, $categoryId, $page, $limit);
        $categoriesList = $this->categories->getAll();

        // Đếm số lượng bài viết theo từng trạng thái để làm badge trên các tab bộ lọc
        $countAll = $this->posts->count();
        $countPublished = $this->posts->countFiltered(null, 'published');
        $countPending = $this->posts->countFiltered(null, 'pending');
        $countHidden = $this->posts->countFiltered(null, 'hidden');

        $this->render('admin/posts/index', [
            'title' => 'Quản lý Bài viết',
            'posts' => $postsList,
            'categories' => $categoriesList,
            'selectedStatus' => $status ?? 'all',
            'selectedCategoryId' => $categoryId,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPosts' => $totalPosts,
            'countAll' => $countAll,
            'countPublished' => $countPublished,
            'countPending' => $countPending,
            'countHidden' => $countHidden
        ], 'admin');
    }

    /**
     * Duyệt bài viết (POST /admin/posts/{id}/approve) - FR13
     */
    public function approve(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        // Kiểm tra token CSRF
        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.');
            $this->redirect('/admin/posts');
            return;
        }

        $post = $this->posts->getById($id);
        if (!$post) {
            Session::flash('error', 'Bài viết không tồn tại.');
            $this->redirect('/admin/posts');
            return;
        }

        $result = $this->posts->updateStatus($id, 'published');
        if ($result) {
            Session::flash('success', 'Đã duyệt bài viết "' . $post['title'] . '" thành công.');
        } else {
            Session::flash('error', 'Không thể duyệt bài viết. Vui lòng thử lại.');
        }

        $this->redirect('/admin/posts');
    }

    /**
     * Ẩn bài viết khỏi diễn đàn (POST /admin/posts/{id}/hide) - FR13
     */
    public function hide(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        // Kiểm tra token CSRF
        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.');
            $this->redirect('/admin/posts');
            return;
        }

        $post = $this->posts->getById($id);
        if (!$post) {
            Session::flash('error', 'Bài viết không tồn tại.');
            $this->redirect('/admin/posts');
            return;
        }

        $result = $this->posts->updateStatus($id, 'hidden');
        if ($result) {
            Session::flash('success', 'Đã ẩn bài viết "' . $post['title'] . '" khỏi diễn đàn.');
        } else {
            Session::flash('error', 'Không thể ẩn bài viết. Vui lòng thử lại.');
        }

        $this->redirect('/admin/posts');
    }

    /**
     * Xóa bài viết vĩnh viễn (POST /admin/posts/{id}/delete) - FR13
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        // Kiểm tra token CSRF
        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc hết hạn hoặc mã xác thực không hợp lệ.');
            $this->redirect('/admin/posts');
            return;
        }

        $post = $this->posts->getById($id);
        if (!$post) {
            Session::flash('error', 'Bài viết không tồn tại.');
            $this->redirect('/admin/posts');
            return;
        }

        $result = $this->posts->delete($id);
        if ($result) {
            Session::flash('success', 'Đã xóa bài viết "' . $post['title'] . '" vĩnh viễn.');
        } else {
            Session::flash('error', 'Không thể xóa bài viết. Vui lòng thử lại.');
        }

        $this->redirect('/admin/posts');
    }
}
