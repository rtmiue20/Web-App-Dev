<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/AuthService.php';

/**
 * AuthController - Điều khiển luồng Đăng nhập, Đăng ký, Đăng xuất (FR01).
 * Ủy quyền toàn bộ nghiệp vụ xác thực, chống Brute-force và băm mật khẩu cho AuthService.
 */
class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?? new AuthService();
    }

    public function showLogin(): void
    {
        if (Session::has('user')) {
            $this->redirect('/profile');
        }

        $this->render('auth/login', [
            'errors'  => [],
            'old'     => [],
            'message' => Session::flash('error'),
        ]);
    }

    public function login(): void
    {
        $isAjax = $this->isAjaxRequest();

        if (!$this->verifyCsrfToken()) {
            if ($isAjax) {
                Response::fail('Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.', 419);
                return;
            }
            http_response_code(419);
            $this->render('auth/login', [
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old'    => [],
            ]);
            return;
        }

        $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $result = $this->authService->attemptLogin($_POST);

        if (!$result['success']) {
            $status = (int)($result['status'] ?? 401);
            if ($isAjax) {
                Response::fail($result['message'], $status, ['errors' => $result['errors']]);
                return;
            }
            http_response_code($status === 422 ? 200 : $status);
            $this->render('auth/login', [
                'errors' => $result['errors'],
                'old'    => ['email' => $email],
            ]);
            return;
        }

        $userData = $result['data'];
        if ($isAjax) {
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            Response::ok([
                'user' => [
                    'username' => $userData['username'],
                    'email'    => $userData['email'],
                ],
                'redirect' => $baseUrl . '/',
            ], $result['message']);
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
            'errors'  => [],
            'old'     => [],
            'message' => Session::flash('error'),
        ]);
    }

    public function register(): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $this->render('auth/register', [
                'errors' => ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'],
                'old'    => [],
            ]);
            return;
        }

        $username = trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : '');
        $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');

        $result = $this->authService->registerUser($_POST);
        if (!$result['success']) {
            $this->render('auth/register', [
                'errors' => $result['errors'],
                'old'    => ['username' => $username, 'email' => $email],
            ]);
            return;
        }

        Session::flash('success', $result['message']);
        $this->redirect('/login');
    }

    public function logout(): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            echo 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            return;
        }

        $this->authService->logout();
        header('Location: ' . BASE_URL . '/login', true, 303);
    }
}
