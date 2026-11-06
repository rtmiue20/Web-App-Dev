<?php
/**
 * Lớp kết nối CSDL (Singleton) dùng PDO.
 * Phục vụ tầng Repository / DAO và Model qua Database::getConnection().
 * Bảo mật: Bắt buộc sử dụng Prepared Statements thật (ATTR_EMULATE_PREPARES = false) chống SQL Injection.
 */
class Database
{
    private static ?PDO $connection = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $cfg = require __DIR__ . '/../../config/database.php';
            if (file_exists(__DIR__ . '/../../config.local.php')) {
                $localCfg = require __DIR__ . '/../../config.local.php';
                if (is_array($localCfg)) {
                    $cfg = array_merge($cfg, $localCfg);
                }
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'],
                $cfg['name'],
                $cfg['charset'] ?? 'utf8mb4'
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // lỗi SQL ném exception
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // trả về mảng kết hợp
                PDO::ATTR_EMULATE_PREPARES   => false,                    // prepared statement thật chống SQLi
            ];

            try {
                self::$connection = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);

                try { self::$connection->exec("SET time_zone = '+07:00'"); } catch (Throwable $tzEx) {}
            } catch (PDOException $e) {
                // Thử kết nối mật khẩu rỗng (chuẩn mặc định XAMPP) nếu cấu hình mật khẩu thất bại
                if (!empty($cfg['pass'])) {
                    try {
                        self::$connection = new PDO($dsn, $cfg['user'], '', $options);

                        try { self::$connection->exec("SET time_zone = '+07:00'"); } catch (Throwable $tzEx) {}
                        return self::$connection;
                    } catch (PDOException $ex) {
                        // Tiếp tục xử lý báo lỗi bên dưới
                    }
                }
                // Không in thông tin nhạy cảm (mật khẩu/cấu trúc DB) ra màn hình người dùng
                error_log('DB connection failed: ' . $e->getMessage());
                http_response_code(500);
                exit('Không thể kết nối cơ sở dữ liệu.');
            }
        }

        return self::$connection;
    }

    /** Alias chuẩn Singleton tương thích tài liệu kiến trúc OOP/MVC. */
    public static function getInstance(): PDO
    {
        return self::getConnection();
    }

    /** SELECT / câu lệnh có tham số, trả về nhiều dòng. */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** SELECT trả về 1 dòng duy nhất hoặc null. */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        $stmt->closeCursor();
        return is_array($row) ? $row : null;
    }

    /** INSERT / UPDATE / DELETE, trả về số dòng bị ảnh hưởng. */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Gọi stored procedure, ví dụ: Database::call('sp_category_list') */
    public static function call(string $procedure, array $params = []): array
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $procedure)) {
            throw new InvalidArgumentException('Tên Stored Procedure không hợp lệ.');
        }
        $marks = implode(',', array_fill(0, count($params), '?'));
        $stmt  = self::getConnection()->prepare("CALL {$procedure}({$marks})");
        $stmt->execute(array_values($params));
        $rows = $stmt->fetchAll();
        $stmt->closeCursor(); // bắt buộc sau CALL để chạy được câu lệnh tiếp theo trên MariaDB
        return $rows;
    }

    /** Bắt đầu Transaction (phục vụ Service Layer & Repository). */
    public static function beginTransaction(): bool
    {
        $pdo = self::getConnection();
        return !$pdo->inTransaction() ? $pdo->beginTransaction() : true;
    }

    /** Xác nhận Transaction. */
    public static function commit(): bool
    {
        $pdo = self::getConnection();
        return $pdo->inTransaction() ? $pdo->commit() : false;
    }

    /** Hoàn tác Transaction khi có lỗi. */
    public static function rollBack(): bool
    {
        $pdo = self::getConnection();
        return $pdo->inTransaction() ? $pdo->rollBack() : false;
    }
}