<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/User.php';

/**
 * Controller UserAdminController - Quản lý thành viên & Cấp lại mật khẩu (FR14)
 * Bảo mật: Chỉ Quản trị viên (Admin) mới có quyền truy cập qua AdminMiddleware
 */
class UserAdminController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Danh sách thành viên (/admin/users)
     */
    public function index(): void
    {
        $allUsers = $this->userModel->getAll();

        // 1. Lọc theo từ khóa tìm kiếm (username, email)
        $q = trim((string)($_GET['q'] ?? ''));
        $role = trim((string)($_GET['role'] ?? 'all'));

        $filtered = array_filter($allUsers, function ($u) use ($q, $role) {
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

            return $matchQuery && $matchRole;
        });

        // 2. Thống kê nhanh
        $totalCount = count($allUsers);
        $adminCount = count(array_filter($allUsers, fn($u) => ($u['role_name'] ?? '') === 'admin'));
        $memberCount = $totalCount - $adminCount;

        $this->render('admin/users/index', [
            'title' => 'Quản lý Thành viên',
            'users' => array_values($filtered),
            'totalCount' => $totalCount,
            'adminCount' => $adminCount,
            'memberCount' => $memberCount,
            'searchQuery' => $q,
            'selectedRole' => $role,
            'resetResult' => Session::flash('new_generated_password'),
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
            'title' => 'Chi tiết thành viên ' . htmlspecialchars($user['username']),
            'member' => $user,
        ], 'admin');
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
                header('Content-Type: application/json; charset=utf-8');
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
            // Tự động sinh mật khẩu ngẫu nhiên: ví dụ Outdoor@682491
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
