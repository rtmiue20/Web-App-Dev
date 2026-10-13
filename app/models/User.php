<?php
require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    private ?int $id = null;
    private ?string $username = null;
    private ?string $email = null;
    private ?string $passwordHash = null;
    private ?int $roleId = null;
    private ?string $roleName = null;
    private ?string $createdAt = null;

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

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.username, u.email, u.password_hash, u.role_id, r.name AS role_name, u.created_at
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        return $row ? self::fromRow($row) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.username, u.email, u.role_id, r.name AS role_name, u.created_at
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? self::fromRow($row) : null;
    }

    public function existsByEmailOrUsername(string $email, string $username): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM users WHERE email = :email OR username = :username LIMIT 1'
        );
        $stmt->execute([':email' => $email, ':username' => $username]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(): int
    {
        if ($this->username === null || $this->email === null || $this->passwordHash === null) {
            throw new LogicException('Username, email, and password hash must be set before creating a user.');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash, role_id)
             VALUES (:username, :email, :password_hash,
                     (SELECT id FROM roles WHERE name = 'member' LIMIT 1))"
        );
        $stmt->execute([
            ':username' => $this->username,
            ':email' => $this->email,
            ':password_hash' => $this->passwordHash,
        ]);

        $this->id = (int) $this->db->lastInsertId();
        return $this->id;
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
        return $user;
    }
}
