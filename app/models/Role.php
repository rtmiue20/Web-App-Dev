<?php
require_once __DIR__ . '/../core/Model.php';

class Role extends Model
{
    private ?int $id = null;
    private ?string $name = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): void { $this->name = $name; }

    /** @return Role[] */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT id, name FROM roles ORDER BY id');
        $roles = [];
        foreach ($stmt->fetchAll() as $row) {
            $roles[] = self::fromRow($row);
        }

        return $roles;
    }

    /**
     * Lấy danh sách tất cả vai trò dạng mảng kết hợp cho form phân quyền RBAC
     */
    public function getAllAsArray(): array
    {
        try {
            $stmt = $this->db->query('SELECT id, name FROM roles ORDER BY id ASC');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [
                ['id' => 1, 'name' => 'member'],
                ['id' => 2, 'name' => 'admin'],
                ['id' => 3, 'name' => 'moderator'],
                ['id' => 4, 'name' => 'guest'],
            ];
        }
    }

    public function getById(int $id): ?Role
    {
        $stmt = $this->db->prepare('SELECT id, name FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? self::fromRow($row) : null;
    }

    public function getByName(string $name): ?Role
    {
        $stmt = $this->db->prepare('SELECT id, name FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $name]);
        $row = $stmt->fetch();

        return $row ? self::fromRow($row) : null;
    }

    private static function fromRow(array $row): Role
    {
        $role = new self();
        $role->setId((int) $row['id']);
        $role->setName($row['name']);
        return $role;
    }
}
