<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/RepositoryInterface.php';

/**
 * Lớp trừu tượng BaseRepository (Data Access Object - DAO cơ sở)
 * Cung cấp các phương thức truy cập CSDL an toàn qua PDO Prepared Statements và Stored Procedures.
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Gọi Stored Procedure trong MariaDB và tự động đóng con trỏ (closeCursor).
     */
    protected function callProcedure(string $procedure, array $params = []): array
    {
        if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $procedure)) {
            throw new InvalidArgumentException('Tên Stored Procedure không hợp lệ.');
        }

        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("CALL {$procedure}({$placeholders})");
        $stmt->execute(array_values($params));
        $rows = $stmt->fetchAll();

        while ($stmt->nextRowset()) {
            // Đọc hết các tập kết quả phụ nếu có
        }
        $stmt->closeCursor();

        return $rows;
    }

    /**
     * Thực thi câu lệnh SELECT Prepared Statement trả về danh sách dòng.
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Thực thi câu lệnh SELECT Prepared Statement trả về 1 dòng duy nhất hoặc null.
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Thực thi câu lệnh INSERT / UPDATE / DELETE Prepared Statement.
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Lấy ID tự tăng vừa chèn vào CSDL.
     */
    protected function lastInsertId(): int
    {
        return (int)$this->db->lastInsertId();
    }

    /**
     * Thực thi một khối thao tác dữ liệu trong Transaction an toàn (ACID).
     */
    public function transaction(callable $callback): mixed
    {
        $started = !$this->db->inTransaction();
        if ($started) {
            $this->db->beginTransaction();
        }

        try {
            $result = $callback($this);
            if ($started && $this->db->inTransaction()) {
                $this->db->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if ($started && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function findById(int $id): mixed
    {
        if ($this->table === '') {
            return null;
        }
        $sql = sprintf('SELECT * FROM `%s` WHERE `%s` = :id LIMIT 1', $this->table, $this->primaryKey);
        return $this->queryOne($sql, ['id' => $id]);
    }

    public function findAll(): array
    {
        if ($this->table === '') {
            return [];
        }
        $sql = sprintf('SELECT * FROM `%s` ORDER BY `%s` DESC', $this->table, $this->primaryKey);
        return $this->query($sql);
    }

    public function count(): int
    {
        if ($this->table === '') {
            return 0;
        }
        $sql = sprintf('SELECT COUNT(*) AS total FROM `%s`', $this->table);
        $row = $this->queryOne($sql);
        return (int)($row['total'] ?? 0);
    }

    public function delete(int $id): bool
    {
        if ($this->table === '') {
            return false;
        }
        $sql = sprintf('DELETE FROM `%s` WHERE `%s` = :id', $this->table, $this->primaryKey);
        return $this->execute($sql, ['id' => $id]) > 0;
    }
}

