<?php
require_once __DIR__ . '/Session.php';

/**
 * Helper Auth - Quản lý xác thực và Phân quyền RBAC (Role-Based Access Control)
 * Hỗ trợ 4 cấp độ phân quyền chuẩn:
 * 1. guest     (Khách vãng lai / chưa đăng nhập hoặc tài khoản bị giới hạn chỉ xem)
 * 2. member    (Thành viên: đăng bài chờ duyệt, sửa/xóa bài & bình luận của chính mình, like bài viết)
 * 3. moderator (Điều hành viên: đăng bài xuất bản ngay, duyệt/ẩn/xóa bài viết bất kỳ, xóa bình luận vi phạm)
 * 4. admin     (Quản trị viên: toàn quyền hệ thống, quản lý danh mục, dụng cụ, thành viên & phân quyền RBAC)
 */
class Auth
{
    public const ROLE_GUEST     = 'guest';
    public const ROLE_MEMBER    = 'member';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_ADMIN     = 'admin';

    public const ROLE_ID_MEMBER    = 1;
    public const ROLE_ID_ADMIN     = 2;
    public const ROLE_ID_MODERATOR = 3;
    public const ROLE_ID_GUEST     = 4;

    /**
     * Lấy thông tin người dùng hiện tại từ Session
     */
    public static function user(): ?array
    {
        Session::init();
        $user = Session::get('user');
        return is_array($user) ? $user : null;
    }

    /**
     * Lấy ID người dùng hiện tại (0 nếu là khách)
     */
    public static function id(): int
    {
        $user = self::user();
        return (int)($user['id'] ?? 0);
    }

    /**
     * Kiểm tra người dùng đã đăng nhập hay chưa
     */
    public static function check(): bool
    {
        return self::id() > 0;
    }

    /**
     * Lấy tên vai trò (role) hiện tại: 'guest' | 'member' | 'moderator' | 'admin'
     */
    public static function role(?array $user = null): string
    {
        $sessionUser = self::user();
        $user = $user ?? $sessionUser;

        if (!is_array($user)) {
            return self::ROLE_GUEST;
        }

        $hasIdentity = !empty($user['id']) || !empty($user['username']) || !empty($user['email']) || !empty($sessionUser['id']);
        if (!$hasIdentity) {
            return self::ROLE_GUEST;
        }

        $roleName = strtolower(trim((string)($user['role_name'] ?? ($sessionUser['role_name'] ?? ''))));
        $roleId = (int)($user['role_id'] ?? ($sessionUser['role_id'] ?? 0));

        // Ưu tiên kiểm tra theo tên role (role_name) trước để tránh lệch ID tự tăng trong CSDL
        if ($roleName !== '') {
            if ($roleName === self::ROLE_ADMIN) {
                return self::ROLE_ADMIN;
            }
            if ($roleName === self::ROLE_MODERATOR) {
                return self::ROLE_MODERATOR;
            }
            if ($roleName === self::ROLE_MEMBER) {
                return self::ROLE_MEMBER;
            }
            if ($roleName === self::ROLE_GUEST) {
                return self::ROLE_GUEST;
            }
        }

        if ($roleId === self::ROLE_ID_ADMIN) {
            return self::ROLE_ADMIN;
        }
        if ($roleId === self::ROLE_ID_MODERATOR) {
            return self::ROLE_MODERATOR;
        }
        if ($roleId === self::ROLE_ID_GUEST) {
            return self::ROLE_GUEST;
        }

        return self::ROLE_MEMBER;
    }

    /**
     * Kiểm tra có phải là Guest (chưa đăng nhập hoặc role guest)
     */
    public static function isGuest(?array $user = null): bool
    {
        return self::role($user) === self::ROLE_GUEST;
    }

    /**
     * Kiểm tra có quyền từ cấp Member trở lên (member, moderator, admin)
     */
    public static function isMember(?array $user = null): bool
    {
        $role = self::role($user);
        return in_array($role, [self::ROLE_MEMBER, self::ROLE_MODERATOR, self::ROLE_ADMIN], true);
    }

    /**
     * Kiểm tra có quyền Kiểm duyệt viên trở lên (moderator hoặc admin)
     */
    public static function isModerator(?array $user = null): bool
    {
        $role = self::role($user);
        return in_array($role, [self::ROLE_MODERATOR, self::ROLE_ADMIN], true);
    }

    /**
     * Kiểm tra có phải Quản trị viên cao nhất (admin)
     */
    public static function isAdmin(?array $user = null): bool
    {
        return self::role($user) === self::ROLE_ADMIN;
    }

    /**
     * Kiểm tra quyền đăng bài & tương tác (member, moderator, admin)
     */
    public static function canCreatePost(?array $user = null): bool
    {
        return self::isMember($user);
    }

    /**
     * Kiểm tra quyền xuất bản bài viết trực tiếp không cần chờ duyệt (moderator, admin)
     */
    public static function canPublishDirectly(?array $user = null): bool
    {
        return self::isModerator($user);
    }

    /**
     * Kiểm tra quyền Sửa / Xóa / Xem trước bài viết (Tác giả bài viết, Moderator hoặc Admin)
     */
    public static function canManagePost(array $post, ?array $user = null): bool
    {
        $user = $user ?? self::user();
        if (!is_array($user) || self::isGuest($user)) {
            return false;
        }

        if (self::isModerator($user)) {
            return true;
        }

        $userId = (int)($user['id'] ?? 0);
        $authorId = (int)($post['user_id'] ?? 0);
        return $userId > 0 && $userId === $authorId;
    }

    /**
     * Kiểm tra quyền xóa bình luận (Chủ bình luận, Moderator hoặc Admin)
     */
    public static function canDeleteComment(int $commentUserId, ?array $user = null): bool
    {
        $user = $user ?? self::user();
        if (!is_array($user) || self::isGuest($user)) {
            return false;
        }

        if (self::isModerator($user)) {
            return true;
        }

        $userId = (int)($user['id'] ?? 0);
        return $userId > 0 && $userId === $commentUserId;
    }

    /**
     * Nhãn hiển thị tiếng Việt cho từng role
     */
    public static function roleLabel(string $role): string
    {
        return match (strtolower(trim($role))) {
            self::ROLE_ADMIN     => 'Quản trị viên (Admin)',
            self::ROLE_MODERATOR => 'Điều hành viên (Moderator)',
            self::ROLE_MEMBER    => 'Thành viên (Member)',
            default              => 'Khách (Guest)',
        };
    }

    /**
     * Class CSS badge cho từng role
     */
    public static function roleBadgeClass(string $role): string
    {
        return match (strtolower(trim($role))) {
            self::ROLE_ADMIN     => 'text-bg-danger',
            self::ROLE_MODERATOR => 'text-bg-primary',
            self::ROLE_MEMBER    => 'text-bg-success',
            default              => 'text-bg-secondary',
        };
    }
}
