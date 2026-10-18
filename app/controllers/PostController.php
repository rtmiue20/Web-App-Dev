<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../helpers/Upload.php';
require_once __DIR__ . '/../helpers/Response.php';

class PostController extends Controller {
    private Post $posts;
    private Category $categories;
    private Comment $comments;

    public function __construct() {
        $this->posts = new Post();
        $this->categories = new Category();
        $this->comments = new Comment();
    }

    public function index(): void {
        $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $postsList = $this->posts->getAll($categoryId);
        $categoriesList = $this->categories->getAll();

        $this->render('posts/index', [
            'title' => 'Bài viết dã ngoại',
            'posts' => $postsList,
            'categories' => $categoriesList,
            'selectedCategoryId' => $categoryId
        ]);
    }

    public function create(): void {
        if (!Session::has('user')) {
            Session::flash('error', 'Vui lòng đăng nhập để viết bài chia sẻ.');
            $this->redirect('/login');
            return;
        }

        $categoriesList = $this->categories->getAll();
        $this->render('posts/create', [
            'title' => 'Viết bài chia sẻ',
            'categories' => $categoriesList,
            'errors' => [],
            'old' => []
        ]);
    }

    public function store(): void {
        if (!Session::has('user')) {
            Session::flash('error', 'Vui lòng đăng nhập để viết bài.');
            $this->redirect('/login');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $categoriesList = $this->categories->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => $_POST
            ]);
            return;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $location = trim((string)($_POST['location'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));

        $errors = [];
        if (mb_strlen($title) < 5) {
            $errors['title'] = 'Tiêu đề bài viết phải từ 5 ký tự trở lên.';
        }
        if ($categoryId <= 0) {
            $errors['category_id'] = 'Vui lòng chọn một chuyên mục.';
        }
        if (mb_strlen($content) < 10) {
            $errors['content'] = 'Nội dung bài viết phải từ 10 ký tự trở lên.';
        }

        $imageUrl = null;
        if (!empty($_FILES['image']['name'])) {
            try {
                $imageUrl = Upload::image($_FILES['image'], 'posts');
            } catch (Exception $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $categoriesList = $this->categories->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 1);

        $postId = $this->posts->create($userId, $categoryId, $title, $content, $location ?: null, $imageUrl);
        if ($postId > 0) {
            Session::flash('success', 'Đăng bài viết thành công!');
            $this->redirect('/posts/' . $postId);
        } else {
            $categoriesList = $this->categories->getAll();
            $this->render('posts/create', [
                'title' => 'Viết bài chia sẻ',
                'categories' => $categoriesList,
                'errors' => ['form' => 'Không thể lưu bài viết. Vui lòng thử lại.'],
                'old' => $_POST
            ]);
        }
    }

    public function show(int $id): void {
        $post = $this->posts->getById($id);
        if (!$post) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy bài viết']);
            return;
        }

        $commentsList = $this->comments->getByPostId($id);

        $this->render('posts/show', [
            'title' => $post['title'],
            'post' => $post,
            'comments' => $commentsList
        ]);
    }

    public function like(int $id): void {
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::has('user')) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thích bài viết.']);
            exit;
        }

        $user = Session::get('user');
        $userId = (int)($user['id'] ?? 0);
        $newLikes = $this->posts->toggleLike($id, $userId);

        echo json_encode([
            'success' => true,
            'data' => [
                'likes' => $newLikes,
                'liked' => true
            ]
        ]);
        exit;
    }
}
