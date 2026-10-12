<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Category.php';

class PostController
{
    public function index(): void
    {
        $categoryId = null;
        if (isset($_GET['category'])) {
            $categoryId = filter_var($_GET['category'], FILTER_VALIDATE_INT);
            if ($categoryId === false || $categoryId < 1) {
                http_response_code(400);
                $categoryId = null;
            }
        }

        $posts = (new Post())->getAll($categoryId);
        $categories = (new Category())->getAll();
        $baseUrl = $this->baseUrl();
        require __DIR__ . '/../views/posts/index.php';
    }

    public function show(int $id): void
    {
        $post = (new Post())->getById($id);
        if ($post === false) {
            http_response_code(404);
        }

        $baseUrl = $this->baseUrl();
        require __DIR__ . '/../views/posts/show.php';
    }

    public function create(): void
    {
        $this->startSession();
        $categories = (new Category())->getAll();
        $errors = [];
        $form = [];
        $canCreate = $this->getUserId() !== null;
        $baseUrl = $this->baseUrl();
        $_SESSION['post_csrf_token'] ??= bin2hex(random_bytes(32));
        $csrfToken = $_SESSION['post_csrf_token'];
        require __DIR__ . '/../views/posts/create.php';
    }

    public function store(): void
    {
        $this->startSession();
        $userId = $this->getUserId();
        if ($userId === null) {
            http_response_code(401);
            $this->create();
            return;
        }

        $token = $_POST['_csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['post_csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo 'Yêu cầu không hợp lệ.';
            return;
        }

        $form = [
            'title' => trim($this->postValue('title')),
            'category_id' => $this->postValue('category_id'),
            'content' => trim($this->postValue('content')),
            'location' => trim($this->postValue('location')),
        ];
        $categoryId = filter_var($form['category_id'], FILTER_VALIDATE_INT);
        $errors = [];

        if ($form['title'] === '' || mb_strlen($form['title']) > 200) {
            $errors['title'] = 'Nhập tiêu đề, tối đa 200 ký tự.';
        }
        if ($categoryId === false || $categoryId < 1 || !(new Category())->exists((int) $categoryId)) {
            $errors['category_id'] = 'Vui lòng chọn danh mục hợp lệ.';
        }
        if ($form['content'] === '') {
            $errors['content'] = 'Nội dung không được để trống.';
        }
        if (mb_strlen($form['location']) > 150) {
            $errors['location'] = 'Địa điểm tối đa 150 ký tự.';
        }

        if ($errors) {
            http_response_code(422);
            $categories = (new Category())->getAll();
            $canCreate = true;
            $baseUrl = $this->baseUrl();
            $_SESSION['post_csrf_token'] = bin2hex(random_bytes(32));
            $csrfToken = $_SESSION['post_csrf_token'];
            require __DIR__ . '/../views/posts/create.php';
            return;
        }

        (new Post())->create(
            $userId,
            (int) $categoryId,
            $form['title'],
            $form['content'],
            $form['location'] === '' ? null : $form['location']
        );
        header('Location: ' . $this->baseUrl() . '/posts');
        exit;
    }

    private function getUserId(): ?int
    {
        $id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            return null;
        }

        return (new Post())->userExists($id) ? $id : null;
    }

    private function postValue(string $key): string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    private function baseUrl(): string
    {
        return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}