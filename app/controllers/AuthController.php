<?php

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function showLogin(): void
    {
        if (Session::has('user')) {
            $this->redirect('/profile');
        }

        $this->render('auth/login', [
            'errors' => [],
            'old' => [],
            'message' => Session::flash('error'),
        ]);
    }

    public function login(): void
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || isset($_POST['ajax']);

        if (!$this->verifyCsrfToken()) {
            if ($isAjax) {
                Response::fail('Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.', 419);
                return;
            }
            http_response_code(419);
            $this->render('auth/login', [
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => [],
            ]);
            return;
        }

        $errors = Validator::login($_POST);
        $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        if ($errors !== []) {
            if ($isAjax) {
                Response::fail((string) reset($errors), 422, ['errors' => $errors]);
                return;
            }
            $this->render('auth/login', ['errors' => $errors, 'old' => ['email' => $email]]);
            return;
        }

        // Tìm người dùng qua email hoặc username
        $user = $this->users->findByEmail($email);
        if ($user === null) {
            $user = $this->users->findByUsername($email);
        }

        if ($user === null || !password_verify($password, (string) $user->getPasswordHash())) {
            if ($isAjax) {
                Response::fail('Tài khoản hoặc mật khẩu không chính xác.', 401);
                return;
            }
            $this->render('auth/login', [
                'errors' => ['form' => 'Tài khoản hoặc mật khẩu không chính xác.'],
                'old' => ['email' => $email],
            ]);
            return;
        }

        Session::regenerate();
        Session::set('user', [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'role_id' => $user->getRoleId(),
            'role_name' => $user->getRoleName(),
        ]);

        if ($isAjax) {
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            Response::ok([
                'user' => [
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                ],
                'redirect' => $baseUrl . '/',
            ], 'Đăng nhập thành công');
            return;
        }

        $this->redirect('/');
    }

    public function showRegister(): void
    {
        if (Session::has('user')) {
            $this->redirect('/profile');
        }

        $this->render('auth/register', [
            'errors' => [],
            'old' => [],
            'message' => Session::flash('error'),
        ]);
    }

    public function register(): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $this->render('auth/register', [
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old' => [],
            ]);
            return;
        }

        $errors = Validator::registration($_POST);
        $username = trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : '');
        $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        if ($errors !== []) {
            $this->render('auth/register', [
                'errors' => $errors,
                'old' => ['username' => $username, 'email' => $email],
            ]);
            return;
        }

        if ($this->users->existsByEmailOrUsername($email, $username)) {
            $this->render('auth/register', [
                'errors' => ['form' => 'Email hoặc tên người dùng đã được sử dụng.'],
                'old' => ['username' => $username, 'email' => $email],
            ]);
            return;
        }

        try {
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPasswordHash(password_hash((string) $_POST['password'], PASSWORD_DEFAULT));
            $user->create();
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            $this->render('auth/register', [
                'errors' => ['form' => 'Email hoặc tên người dùng đã được sử dụng.'],
                'old' => ['username' => $username, 'email' => $email],
            ]);
            return;
        }

        Session::flash('success', 'Đăng ký thành công. Bạn có thể đăng nhập.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            return;
        }

        Session::destroy();
        header('Location: ' . BASE_URL . '/login', true, 303);
    }
}