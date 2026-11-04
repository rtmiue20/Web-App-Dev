<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/UserService.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../helpers/Auth.php';

/**
 * Controller UserAdminController - Quản lý thành viên, Phân quyền RBAC & Cấp lại mật khẩu (FR14).
 * Ủy quyền xử lý nghiệp vụ cho UserService -> UserRepository.
 */
class UserAdminController extends Controller
{
    private UserService $userService;
    private Role $roleModel;

    public function __construct(?UserService $userService = null)
    {
        $this->userService = $userService ?? new UserService();
        $this->roleModel = new Role();
    }

    /**
     * Danh sách thành viên & Phân quyền RBAC (/admin/users).
     */
    public function index(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $role = trim((string)($_GET['role'] ?? 'all'));

        $data = $this->userService->getAdminUsersWithStats($q, $role);
        $data['title'] = 'Quản lý Thành viên & Phân quyền RBAC';
        $data['resetResult'] = Session::flash('new_generated_password');

        $this->render('admin/users/index', $data, 'admin');
    }

    /**
     * Lấy thông tin chi tiết của 1 thành viên (hỗ trợ AJAX xem hồ sơ).
     */
    public function show(int $id): void
    {
        $user = $this->userService->getUserDetail($id);
        $isAjax = $this->isAjaxRequest();

        if (!$user) {
            if ($isAjax) {
                http_response_code(404);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy thông tin thành viên.']);
                exit;
            }
            Session::flash('error', 'Không tìm thấy thông tin thành viên.');
            $this->redirect('/admin/users');
            return;
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'success', 'user' => $user]);
            exit;
        }

        $this->render('admin/users/show', [
            'title'  => 'Chi tiết thành viên ' . htmlspecialchars((string)$user['username'], ENT_QUOTES, 'UTF-8'),
            'member' => $user,
            'roles'  => $this->roleModel->getAllAsArray(),
        ], 'admin');
    }

    /**
     * Thay đổi vai trò (Phân quyền RBAC: guest / member / moderator / admin).
     */
    public function changeRole(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            Session::flash('error', 'Phiên làm việc đã hết hạn (CSRF). Vui lòng thử lại.');
            $this->redirect('/admin/users');
            return;
        }

        $newRoleId = (int)($_POST['role_id'] ?? 0);
        $result = $this->userService->changeUserRole($id, $newRoleId, Auth::id());

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('/admin/users');
    }

    /**
     * Cấp lại mật khẩu cho thành viên (POST /admin/users/{id}/reset-password).
     */
    public function resetPassword(int $id): void
    {
        $isAjax = $this->isAjaxRequest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            $msg = 'Phiên làm việc đã hết hạn (CSRF). Vui lòng thử lại.';
            if ($isAjax) {
                http_response_code(419);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }
            Session::flash('error', $msg);
            $this->redirect('/admin/users');
            return;
        }

        $customPassword = trim((string)($_POST['new_password'] ?? ''));
        $result = $this->userService->resetUserPassword($id, $customPassword);

        if ($result['success']) {
            $payload = $result['data'];
            Session::flash('success', $result['message']);
            Session::flash('new_generated_password', $payload);

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status'       => 'success',
                    'message'      => $result['message'],
                    'username'     => $payload['username'],
                    'new_password' => $payload['password'],
                ]);
                exit;
            }
        } else {
            $status = (int)($result['status'] ?? 400);
            if ($isAjax) {
                http_response_code($status === 422 ? 200 : $status);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => $result['message']]);
                exit;
            }
            Session::flash('error', $result['message']);
        }

        $this->redirect('/admin/users');
    }
}
