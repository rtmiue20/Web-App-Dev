<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model User - Quản lý tài khoản và người dùng (Khang - Tuần 2 đến 4)
 * Gọi các Stored Procedures: sp_user_get_by_email, sp_user_get_by_id, 
 * sp_user_exists, sp_user_register, sp_user_update_profile,
 * sp_user_change_password, sp_user_count, sp_user_list
 */
class User extends Model
{
    private ?int $id = null;
    private ?string $username = null;
    private ?string $email = null;
    private ?string $passwordHash = null;
    private ?int $roleId = null;
    private ?string $roleName = null;
    private ?string $createdAt = null;
    private ?string $bio = null;
    private ?string $avatarPath = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(?string $username): void { $this->username = $username; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }

    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function setPasswordHash(?string $passwordHash): void { $this->passwordHash = $passwordHash; }

    public function getRoleId(): ?int { return $this->roleId; }
    public function setRoleId(?int $roleId): void { $this->roleId = $roleId; }

    public function getRoleName(): ?string { return $this->roleName; }
    public function setRoleName(?string $roleName): void { $this->roleName = $roleName; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

    public function getBio(): ?string { return $this->bio; }
    public function getAvatarPath(): ?string { return $this->avatarPath; }

    /**
     * Tìm người dùng qua email (gọi routine sp_user_get_by_email)
     */
    public function findByEmail(string $email): ?User
    {
        try {
            $rows = Database::call('sp_user_get_by_email', [$email]);
            return !empty($rows) ? self::fromRow($rows[0]) : null;
        } catch (PDOException $e) {
            error_log('Notice: sp_user_get_by_email fallback query: ' . $e->getMessage());
            try {
                $sql = 'SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
                               u.bio, u.avatar_path, u.created_at
                        FROM users u
                        INNER JOIN roles r ON r.id = u.role_id
                        WHERE u.email = :email
                        LIMIT 1';
                $rows = Database::query($sql, ['email' => $email]);
                return !empty($rows) ? self::fromRow($rows[0]) : null;
            } catch (PDOException $ex) {
                error_log('Error finding user by email: ' . $ex->getMessage());
                return null;
            }
        }
    }

    /**
     * Tìm người dùng qua ID (gọi routine sp_user_get_by_id)
     */
    public function findById(int $id): ?User
    {
        try {
            $rows = Database::call('sp_user_get_by_id', [$id]);
            return !empty($rows) ? self::fromRow($rows[0]) : null;
        } catch (PDOException $e) {
            error_log('Notice: sp_user_get_by_id fallback query: ' . $e->getMessage());
            try {
                $sql = 'SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name,
                               u.bio, u.avatar_path, u.created_at
                        FROM users u
                        INNER JOIN roles r ON r.id = u.role_id
                        WHERE u.id = :id
                        LIMIT 1';
                $rows = Database::query($sql, ['id' => $id]);
                return !empty($rows) ? self::fromRow($rows[0]) : null;
            } catch (PDOException $ex) {
                error_log('Error finding user by ID: ' . $ex->getMessage());
                return null;
            }
        }
    }

    /**
     * Kiểm tra sự tồn tại của email hoặc username (gọi routine sp_user_exists)
     */
    public function existsByEmailOrUsername(string $email, string $username): bool
    {
        try {
            $rows = Database::call('sp_user_exists', [$email, $username]);
            return !empty($rows) && (bool)($rows[0]['is_exists'] ?? false);
        } catch (PDOException $e) {
            error_log('Notice: sp_user_exists fallback query: ' . $e->getMessage());
            try {
                $sql = 'SELECT 1 FROM users WHERE email = :email OR username = :username LIMIT 1';
                $rows = Database::query($sql, ['email' => $email, 'username' => $username]);
                return !empty($rows);
            } catch (PDOException $ex) {
                error_log('Error checking user existence: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Tạo tài khoản người dùng mới (gọi routine sp_user_register)
     */
    public function create(): int
    {
        if ($this->username === null || $this->email === null || $this->passwordHash === null) {
            throw new LogicException('Username, email, and password hash must be set before creating a user.');
        }

        try {
            $rows = Database::call('sp_user_register', [$this->username, $this->email, $this->passwordHash]);
            $this->id = (int)($rows[0]['user_id'] ?? 0);
            return $this->id;
        } catch (PDOException $e) {
            error_log('Notice: sp_user_register fallback query: ' . $e->getMessage());
            try {
                $stmt = $this->db->prepare(
                    "INSERT INTO users (username, email, password_hash, role_id, created_at)
                     VALUES (:username, :email, :password_hash,
                             (SELECT id FROM roles WHERE name = 'member' LIMIT 1), NOW())"
                );
                $stmt->execute([
                    ':username' => $this->username,
                    ':email' => $this->email,
                    ':password_hash' => $this->passwordHash,
                ]);

                $this->id = (int) $this->db->lastInsertId();
                return $this->id;
            } catch (PDOException $ex) {
                error_log('Error creating user: ' . $ex->getMessage());
                return 0;
            }
        }
    }

    /**
     * Đếm tổng số người dùng trong hệ thống (gọi routine sp_user_count)
     */
    public function count(): int
    {
        try {
            $rows = Database::call('sp_user_count');
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            try {
                $rows = Database::query('SELECT COUNT(*) AS total FROM users');
                return (int)($rows[0]['total'] ?? 0);
            } catch (PDOException $ex) {
                return 0;
            }
        }
    }

    /**
     * Lấy danh sách toàn bộ người dùng kèm vai trò và số bài viết (gọi routine sp_user_list)
     */
    public function getAll(): array
    {
        try {
            return Database::call('sp_user_list');
        } catch (PDOException $e) {
            error_log('Notice: sp_user_list fallback query: ' . $e->getMessage());
            try {
                $sql = 'SELECT u.id, u.username, u.email, u.role_id, r.name AS role_name, u.created_at,
                               COUNT(p.id) AS total_posts
                        FROM users u
                        INNER JOIN roles r ON r.id = u.role_id
                        LEFT JOIN posts p  ON p.user_id = u.id
                        GROUP BY u.id, u.username, u.email, u.role_id, r.name, u.created_at
                        ORDER BY u.id ASC';
                return Database::query($sql);
            } catch (PDOException $ex) {
                return [];
            }
        }
    }

    public function updateProfile(string $username, string $bio, ?string $avatarPath): void
    {
        if ($this->id === null) {
            throw new LogicException('User ID must be set before updating a profile.');
        }

        Database::call('sp_user_update_profile', [$this->id, $username, $bio, $avatarPath]);
        $this->username = $username;
        $this->bio = $bio;
        $this->avatarPath = $avatarPath;
    }

    public function changePassword(string $passwordHash): void
    {
        if ($this->id === null) {
            throw new LogicException('User ID must be set before changing a password.');
        }

        Database::call('sp_user_change_password', [$this->id, $passwordHash]);
        $this->passwordHash = $passwordHash;
    }

    /**
     * Cấp lại mật khẩu cho thành viên (dành cho Admin khi thành viên quên mật khẩu)
     */
    public function resetPassword(int $userId, string $newPasswordHash): bool
    {
        try {
            Database::call('sp_user_change_password', [$userId, $newPasswordHash]);
            return true;
        } catch (PDOException $e) {
            error_log('Notice: sp_user_change_password fallback: ' . $e->getMessage());
            try {
                $sql = 'UPDATE users SET password_hash = :hash WHERE id = :id';
                return Database::execute($sql, [
                    'hash' => $newPasswordHash,
                    'id' => $userId
                ]) >= 0;
            } catch (PDOException $ex) {
                error_log('Error resetting password: ' . $ex->getMessage());
                return false;
            }
        }
    }

    /**
     * Lấy đầy đủ thông tin chi tiết một người dùng cho khu vực Admin
     */
    public function findUserDetail(int $userId): ?array
    {
        try {
            $sql = 'SELECT u.id, u.username, u.email, u.role_id, r.name AS role_name, u.bio, u.avatar_path, u.created_at,
                           COUNT(p.id) AS total_posts
                    FROM users u
                    INNER JOIN roles r ON r.id = u.role_id
                    LEFT JOIN posts p  ON p.user_id = u.id
                    WHERE u.id = :id
                    GROUP BY u.id, u.username, u.email, u.role_id, r.name, u.bio, u.avatar_path, u.created_at
                    LIMIT 1';
            $rows = Database::query($sql, ['id' => $userId]);
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $ex) {
            error_log('Error finding user detail: ' . $ex->getMessage());
            return null;
        }
    }

    private static function fromRow(array $row): User
    {
        $user = new self();
        $user->setId((int) $row['id']);
        $user->setUsername($row['username']);
        $user->setEmail($row['email']);
        $user->setPasswordHash($row['password_hash'] ?? null);
        $user->setRoleId((int) $row['role_id']);
        $user->setRoleName($row['role_name']);
        $user->setCreatedAt($row['created_at']);
        $user->bio = $row['bio'] ?? null;
        $user->avatarPath = $row['avatar_path'] ?? null;
        return $user;
    }
}
