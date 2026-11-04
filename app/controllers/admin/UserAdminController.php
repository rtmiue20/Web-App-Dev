<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Role.php';
require_once __DIR__ . '/../../helpers/Auth.php';

/**
 * Controller UserAdminController - Quản lý thành viên, Phân quyền RBAC & Cấp lại mật khẩu (FR14)
 * Bảo mật: Chỉ Quản trị viên (Admin) mới có quyền truy cập qua AdminMiddleware
 */
class UserAdminController extends Controller
{
    private User $userModel;
    private Role $roleModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    /**
     * Danh sách thành viên & Phân quyền RBAC (/admin/users)
     */
    public function index(): void
    {
        $allUsers = $this->userModel->getAll();
        $rolesList = $this->roleModel->getAllAsArray();

        // 1. Lọc theo từ khóa tìm kiếm (username, email) & role
        $q = trim((string)($_GET['q'] ?? ''));
        $role = trim((string)($_GET['role'] ?? 'all'));

        $filtered = [];
        foreach ($allUsers as $u) {
            $matchQuery = true;
            if ($q !== '') {
                $qLower = mb_strtolower($q);
                $nameMatch = str_contains(mb_strtolower($u['username'] ?? ''), $qLower);
                $emailMatch = str_contains(mb_strtolower($u['email'] ?? ''), $qLower);
                $matchQuery = $nameMatch || $emailMatch;
            }

            $matchRole = true;
            if ($role !== 'all' && $role !== '') {
                $matchRole = (($u['role_name'] ?? '') === $role);
            }

            if ($matchQuery && $matchRole) {
                $filtered[] = $u;
            }
        }

        // 2. Thống kê nhanh theo 4 cấp độ RBAC (guest / member / moderator / admin)
        $totalCount = count($allUsers);
        $adminCount = 0;
        $moderatorCount = 0;
        $memberCount = 0;
        $guestCount = 0;

        foreach ($allUsers as $u) {
            $rName = strtolower(trim((string)($u['role_name'] ?? 'member')));
            if ($rName === Auth::ROLE_ADMIN) {
                $adminCount++;
            } elseif ($rName === Auth::ROLE_MODERATOR) {
                $moderatorCount++;
            } elseif ($rName === Auth::ROLE_GUEST) {
                $guestCount++;
            } else {
                $memberCount++;
            }
        }

        $this->render('admin/users/index', [
            'title'          => 'Quản lý Thành viên & Phân quyền RBAC',
            'users'          => $filtered,
            'roles'          => $rolesList,
            'totalCount'     => $totalCount,
            'adminCount'     => $adminCount,
            'moderatorCount' => $moderatorCount,
            'memberCount'    => $memberCount,
            'guestCount'     => $guestCount,
            'searchQuery'    => $q,
            'selectedRole'   => $role,
            'resetResult'    => Session::flash('new_generated_password'),
        ], 'admin');
    }

    /**
     * Lấy thông tin chi tiết của 1 thành viên (hỗ trợ AJAX xem hồ sơ)
     */
    public function show(int $id): void
    {
        $user = $this->userModel->findUserDetail($id);
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                  || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

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
            'title'  => 'Chi tiết thành viên ' . htmlspecialchars($user['username']),
            'member' => $user,
            'roles'  => $this->roleModel->getAllAsArray(),
        ], 'admin');
    }

    /**
     * Thay đổi vai trò (Phân quyền RBAC: guest / member / moderator / admin)
     * POST /admin/users/{id}/role
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

        $targetUser = $this->userModel->findUserDetail($id);
        if (!$targetUser) {
            Session::flash('error', 'Không tìm thấy tài khoản người dùng cần phân quyền.');
            $this->redirect('/admin/users');
            return;
        }

        $newRoleId = (int)($_POST['role_id'] ?? 0);
        $roleObj = $this->roleModel->getById($newRoleId);
        if (!$roleObj) {
            Session::flash('error', 'Vai trò (Role) được chọn không hợp lệ.');
            $this->redirect('/admin/users');
            return;
        }

        $newRoleName = (string)$roleObj->getName();
        $currentAdminId = Auth::id();

        // Không cho phép Admin tự hạ quyền chính tài khoản đang đăng nhập của mình
        if ($currentAdminId === $id && $newRoleName !== Auth::ROLE_ADMIN) {
            Session::flash('error', 'Bạn không thể tự hạ quyền Quản trị viên (Admin) của chính mình.');
            $this->redirect('/admin/users');
            return;
        }

        $updated = $this->userModel->updateRole($id, $newRoleId);
        if ($updated) {
            // Nếu cập nhật chính mình (giữ nguyên admin), đồng bộ lại session
            if ($currentAdminId === $id) {
                $sessionUser = Session::get('user');
                if (is_array($sessionUser)) {
                    $sessionUser['role_id'] = $newRoleId;
                    $sessionUser['role_name'] = $newRoleName;
                    Session::set('user', $sessionUser);
                }
            }

            $roleLabel = Auth::roleLabel($newRoleName);
            Session::flash('success', "Đã cập nhật phân quyền (RBAC) cho tài khoản \"{$targetUser['username']}\" sang vai trò \"{$roleLabel}\" thành công!");
        } else {
            Session::flash('error', 'Không thể cập nhật vai trò người dùng. Vui lòng thử lại.');
        }

        $this->redirect('/admin/users');
    }

    /**
     * Cấp lại mật khẩu cho thành viên (POST /admin/users/{id}/reset-password)
     */
    public function resetPassword(int $id): void
    {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                  || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        // 1. Kiểm tra CSRF token
        if (!$this->verifyCsrfToken()) {
            $msg = 'Phiên làm việc đã hết hạn (CSRF). Vui lòng thử lại.';
            if ($isAjax) {
                http_response_code(419);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }
            Session::flash('error', $msg);
            $this->redirect('/admin/users');
            return;
        }

        // 2. Tìm người dùng cần cấp lại mật khẩu
        $targetUser = $this->userModel->findUserDetail($id);
        if (!$targetUser) {
            $msg = 'Thành viên yêu cầu cấp lại mật khẩu không tồn tại trên hệ thống.';
            if ($isAjax) {
                http_response_code(404);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit;
            }
            Session::flash('error', $msg);
            $this->redirect('/admin/users');
            return;
        }

        // 3. Xác định mật khẩu mới: Admin tự nhập hoặc hệ thống tự sinh ngẫu nhiên an toàn
        $inputPassword = trim((string)($_POST['new_password'] ?? ''));
        if ($inputPassword === '') {
            $randomDigits = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $newPassword = 'Forum@' . $randomDigits;
        } else {
            if (mb_strlen($inputPassword) < 6) {
                $msg = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['status' => 'error', 'message' => $msg]);
                    exit;
                }
                Session::flash('error', $msg);
                $this->redirect('/admin/users');
                return;
            }
            $newPassword = $inputPassword;
        }

        // 4. Băm mật khẩu và cập nhật vào Database
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = $this->userModel->resetPassword($id, $passwordHash);

        if ($success) {
            $successMsg = "Đã cấp lại mật khẩu thành công cho tài khoản \"{$targetUser['username']}\"! Mật khẩu mới: {$newPassword}";
            Session::flash('success', $successMsg);
            Session::flash('new_generated_password', [
                'userId' => $id,
                'username' => $targetUser['username'],
                'password' => $newPassword
            ]);

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'success',
                    'message' => $successMsg,
                    'username' => $targetUser['username'],
                    'new_password' => $newPassword
                ]);
                exit;
            }
        } else {
            $errMsg = "Không thể cấp lại mật khẩu do lỗi hệ thống CSDL. Vui lòng thử lại sau.";
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => $errMsg]);
                exit;
            }
            Session::flash('error', $errMsg);
        }

        $this->redirect('/admin/users');
    }
}
