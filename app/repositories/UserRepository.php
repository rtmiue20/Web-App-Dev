<?php
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../models/User.php';

/**
 * UserRepository (DAO) - Tầng truy cập dữ liệu Người dùng & Xác thực.
 * Thực thi các Stored Procedures: sp_user_get_by_email, sp_user_get_by_id, sp_user_exists,
 * sp_user_register, sp_user_update_profile, sp_user_change_password, sp_user_update_role,
 * sp_user_count, sp_user_list.
 */
class UserRepository extends BaseRepository
{
    protected string $table = 'users';

    /**
     * Tìm thông tin người dùng (dạng mảng) theo ID qua Stored Procedure sp_user_get_by_id.
     */
    public function findById(int $id): ?array
    {
        try {
            $rows = $this->callProcedure('sp_user_get_by_id', [$id]);
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $e) {
            error_log('Notice: sp_user_get_by_id fallback: ' . $e->getMessage());
            try {
                return $this->queryOne(
                    'SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
                            u.bio, u.avatar_path, u.created_at
                     FROM users u
                     INNER JOIN roles r ON r.id = u.role_id
                     WHERE u.id = :id
                     LIMIT 1',
                    ['id' => $id]
                );
            } catch (PDOException $ex) {
                error_log('UserRepository::findById error: ' . $ex->getMessage());
                return null;
            }
        }
    }

    /**
     * Tìm thông tin người dùng (dạng mảng) theo Email qua Stored Procedure sp_user_get_by_email.
     */
    public function findRowByEmail(string $email): ?array
    {
        try {
            $rows = $this->callProcedure('sp_user_get_by_email', [$email]);
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $e) {
            error_log('Notice: sp_user_get_by_email fallback: ' . $e->getMessage());
            try {
                return $this->queryOne(
                    'SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
                            u.bio, u.avatar_path, u.created_at
                     FROM users u
                     INNER JOIN roles r ON r.id = u.role_id
                     WHERE u.email = :email
                     LIMIT 1',
                    ['email' => $email]
                );
            } catch (PDOException $ex) {
                error_log('UserRepository::findRowByEmail error: ' . $ex->getMessage());
                return null;
            }
        }
    }

    /**
     * Tìm thông tin người dùng (dạng mảng) theo Username.
     */
    public function findRowByUsername(string $username): ?array
    {
        try {
            return $this->queryOne(
                'SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
                        u.bio, u.avatar_path, u.created_at
                 FROM users u
                 INNER JOIN roles r ON r.id = u.role_id
                 WHERE u.username = :username
                 LIMIT 1',
                ['username' => $username]
            );
        } catch (PDOException $ex) {
            error_log('UserRepository::findRowByUsername error: ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Kiểm tra trùng email hoặc username qua sp_user_exists.
     */
    public function existsByEmailOrUsername(string $email, string $username): bool
    {
        try {
            $rows = $this->callProcedure('sp_user_exists', [$email, $username]);
            return !empty($rows) && (bool)($rows[0]['is_exists'] ?? false);
        } catch (PDOException $e) {
            try {
                $row = $this->queryOne(
                    'SELECT 1 FROM users WHERE email = :email OR username = :username LIMIT 1',
                    ['email' => $email, 'username' => $username]
                );
                return $row !== null;
            } catch (PDOException $ex) {
                return false;
            }
        }
    }

    /**
     * Đăng ký người dùng mới qua sp_user_register, trả về ID người dùng mới.
     */
    public function createUser(string $username, string $email, string $passwordHash): int
    {
        try {
            $rows = $this->callProcedure('sp_user_register', [$username, $email, $passwordHash]);
            return (int)($rows[0]['user_id'] ?? 0);
        } catch (PDOException $e) {
            error_log('Notice: sp_user_register fallback: ' . $e->getMessage());
            $this->execute(
                "INSERT INTO users (username, email, password_hash, role_id, created_at)
                 VALUES (:username, :email, :password_hash,
                         (SELECT id FROM roles WHERE name = 'member' LIMIT 1), NOW())",
                [
                    'username'      => $username,
                    'email'         => $email,
                    'password_hash' => $passwordHash,
                ]
            );
            return $this->lastInsertId();
        }
    }

    /**
     * Lấy danh sách toàn bộ người dùng kèm role và tổng số bài viết qua sp_user_list.
     */
    public function findAll(): array
    {
        try {
            return $this->callProcedure('sp_user_list');
        } catch (PDOException $e) {
            try {
                return $this->query(
                    'SELECT u.id, u.username, u.email, u.role_id, r.name AS role_name, u.created_at,
                            COUNT(p.id) AS total_posts
                     FROM users u
                     INNER JOIN roles r ON r.id = u.role_id
                     LEFT JOIN posts p  ON p.user_id = u.id
                     GROUP BY u.id, u.username, u.email, u.role_id, r.name, u.created_at
                     ORDER BY u.id ASC'
                );
            } catch (PDOException $ex) {
                return [];
            }
        }
    }

    /**
     * Đếm tổng số người dùng qua sp_user_count.
     */
    public function count(): int
    {
        try {
            $rows = $this->callProcedure('sp_user_count');
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            return parent::count();
        }
    }

    /**
     * Cập nhật hồ sơ người dùng qua sp_user_update_profile.
     */
    public function updateProfile(int $userId, string $username, string $bio, ?string $avatarPath): bool
    {
        try {
            $this->callProcedure('sp_user_update_profile', [$userId, $username, $bio, $avatarPath]);
            return true;
        } catch (PDOException $e) {
            return $this->execute(
                'UPDATE users SET username = :username, bio = :bio, avatar_path = :avatar WHERE id = :id',
                ['username' => $username, 'bio' => $bio, 'avatar' => $avatarPath, 'id' => $userId]
            ) >= 0;
        }
    }

    /**
     * Đổi mật khẩu qua sp_user_change_password.
     */
    public function updatePassword(int $userId, string $passwordHash): bool
    {
        try {
            $this->callProcedure('sp_user_change_password', [$userId, $passwordHash]);
            return true;
        } catch (PDOException $e) {
            return $this->execute(
                'UPDATE users SET password_hash = :hash WHERE id = :id',
                ['hash' => $passwordHash, 'id' => $userId]
            ) >= 0;
        }
    }

    /**
     * Cập nhật vai trò RBAC qua sp_user_update_role.
     */
    public function updateRole(int $userId, int $roleId): bool
    {
        try {
            $this->callProcedure('sp_user_update_role', [$userId, $roleId]);
            return true;
        } catch (PDOException $e) {
            return $this->execute(
                'UPDATE users SET role_id = :role_id WHERE id = :id',
                ['role_id' => $roleId, 'id' => $userId]
            ) >= 0;
        }
    }

    /**
     * Tìm thông tin chi tiết người dùng kèm tổng bài viết (cho Admin).
     */
    public function findDetailById(int $userId): ?array
    {
        try {
            return $this->queryOne(
                'SELECT u.id, u.username, u.email, u.role_id, r.name AS role_name, u.bio, u.avatar_path, u.created_at,
                        COUNT(p.id) AS total_posts
                 FROM users u
                 INNER JOIN roles r ON r.id = u.role_id
                 LEFT JOIN posts p  ON p.user_id = u.id
                 WHERE u.id = :id
                 GROUP BY u.id, u.username, u.email, u.role_id, r.name, u.bio, u.avatar_path, u.created_at
                 LIMIT 1',
                ['id' => $userId]
            );
        } catch (PDOException $ex) {
            return null;
        }
    }
}

