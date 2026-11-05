<?php
require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/Session.php';

/**
 * UserService - Tầng nghiệp vụ Quản trị Thành viên, Phân quyền RBAC & Cấp lại mật khẩu (Service Layer - FR14).
 */
class UserService extends BaseService
{
    private UserRepository $userRepository;
    private Role $roleModel;

    public function __construct(?UserRepository $userRepository = null)
    {
        $this->userRepository = $userRepository ?? new UserRepository();
        $this->roleModel = new Role();
    }

    /**
     * Lấy danh sách người dùng kèm lọc và thống kê theo 4 cấp độ RBAC.
     */
    public function getAdminUsersWithStats(string $query = '', string $roleFilter = 'all'): array
    {
        $allUsers = $this->userRepository->findAll();
        $rolesList = $this->roleModel->getAllAsArray();

        $q = $this->cleanString($query);
        $role = $this->cleanString($roleFilter);
        if ($role === '') {
            $role = 'all';
        }

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
            if ($role !== 'all') {
                $matchRole = (($u['role_name'] ?? '') === $role);
            }

            if ($matchQuery && $matchRole) {
                $filtered[] = $u;
            }
        }

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

        return [
            'users'          => $filtered,
            'roles'          => $rolesList,
            'totalCount'     => count($allUsers),
            'adminCount'     => $adminCount,
            'moderatorCount' => $moderatorCount,
            'memberCount'    => $memberCount,
            'guestCount'     => $guestCount,
            'searchQuery'    => $q,
            'selectedRole'   => $role,
        ];
    }

    /**
     * Tìm chi tiết thành viên theo ID.
     */
    public function getUserDetail(int $userId): ?array
    {
        return $this->userRepository->findDetailById($userId);
    }

    /**
     * Nghiệp vụ cập nhật phân quyền RBAC cho thành viên.
     */
    public function changeUserRole(int $targetUserId, int $newRoleId, int $currentAdminId): array
    {
        $targetUser = $this->userRepository->findDetailById($targetUserId);
        if (!$targetUser) {
            return $this->failure('Không tìm thấy tài khoản người dùng cần phân quyền.', [], 404);
        }

        $roleObj = $this->roleModel->getById($newRoleId);
        if (!$roleObj) {
            return $this->failure('Vai trò (Role) được chọn không hợp lệ.', [], 400);
        }

        $newRoleName = (string)$roleObj->getName();

        // Quy tắc bảo mật: Không cho phép Admin tự hạ quyền của chính mình
        if ($currentAdminId === $targetUserId && $newRoleName !== Auth::ROLE_ADMIN) {
            return $this->failure('Bạn không thể tự hạ quyền Quản trị viên (Admin) của chính mình.', [], 403);
        }

        $updated = $this->userRepository->updateRole($targetUserId, $newRoleId);
        if (!$updated) {
            return $this->failure('Không thể cập nhật vai trò người dùng. Vui lòng thử lại.', [], 500);
        }

        if ($currentAdminId === $targetUserId) {
            $sessionUser = Session::get('user');
            if (is_array($sessionUser)) {
                $sessionUser['role_id'] = $newRoleId;
                $sessionUser['role_name'] = $newRoleName;
                Session::set('user', $sessionUser);
            }
        }

        $roleLabel = Auth::roleLabel($newRoleName);
        return $this->success(
            ['user_id' => $targetUserId, 'role_id' => $newRoleId, 'role_name' => $newRoleName],
            "Đã cập nhật phân quyền (RBAC) cho tài khoản \"{$targetUser['username']}\" sang vai trò \"{$roleLabel}\" thành công!"
        );
    }

    /**
     * Nghiệp vụ cấp lại mật khẩu cho thành viên (tự nhập hoặc sinh ngẫu nhiên an toàn).
     */
    public function resetUserPassword(int $targetUserId, string $customPassword = ''): array
    {
        $targetUser = $this->userRepository->findDetailById($targetUserId);
        if (!$targetUser) {
            return $this->failure('Thành viên yêu cầu cấp lại mật khẩu không tồn tại trên hệ thống.', [], 404);
        }

        $inputPassword = $this->cleanString($customPassword);
        if ($inputPassword === '') {
            $randomDigits = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $newPassword = 'Forum@' . $randomDigits;
        } else {
            if (mb_strlen($inputPassword) < 6) {
                return $this->failure('Mật khẩu mới phải có ít nhất 6 ký tự!', [], 422);
            }
            $newPassword = $inputPassword;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updated = $this->userRepository->updatePassword($targetUserId, $passwordHash);
        if (!$updated) {
            return $this->failure('Không thể cấp lại mật khẩu do lỗi hệ thống CSDL. Vui lòng thử lại sau.', [], 500);
        }

        $successMsg = "Đã cấp lại mật khẩu thành công cho tài khoản \"{$targetUser['username']}\"! Mật khẩu mới: {$newPassword}";
        return $this->success([
            'userId'   => $targetUserId,
            'username' => $targetUser['username'],
            'password' => $newPassword,
        ], $successMsg);
    }
}

