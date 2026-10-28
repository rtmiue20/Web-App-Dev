<?php
/**
 * Lớp kết nối CSDL (Singleton) dùng PDO.
 * Mọi Model lấy kết nối qua Database::getConnection().
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

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
            );

            try {
                self::$connection = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // lỗi SQL ném exception
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // trả về mảng kết hợp
                    PDO::ATTR_EMULATE_PREPARES   => false,                    // prepared statement thật
                ]);
            } catch (PDOException $e) {
                // Thử kết nối mật khẩu rỗng (chuẩn mặc định XAMPP) nếu cấu hình mật khẩu thất bại
                if (!empty($cfg['pass'])) {
                    try {
                        self::$connection = new PDO($dsn, $cfg['user'], '', [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                        ]);
                        return self::$connection;
                    } catch (PDOException $ex) {
                        // Tiếp tục báo lỗi như bên dưới
                    }
                }
                // Không in thông tin nhạy cảm ra màn hình
                error_log('DB connection failed: ' . $e->getMessage());
                http_response_code(500);
                exit('Không thể kết nối cơ sở dữ liệu.');
            }
        }

        return self::$connection;
    }

    /** SELECT / câu lệnh có tham số, trả về nhiều dòng. */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** INSERT / UPDATE / DELETE, trả về số dòng bị ảnh hưởng. */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Gọi stored procedure, ví dụ: Database::call('lk_users_indepartment') */
    public static function call(string $procedure, array $params = []): array
    {
        $marks = implode(',', array_fill(0, count($params), '?'));
        $stmt  = self::getConnection()->prepare("CALL {$procedure}({$marks})");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $stmt->closeCursor(); // bắt buộc sau CALL để chạy được câu lệnh tiếp theo
        return $rows;
    }
}
