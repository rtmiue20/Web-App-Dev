<?php
require_once __DIR__ . '/Database.php';

/**
 * Lớp trừu tượng BaseService - Nền tảng cho tầng nghiệp vụ (Service Layer)
 * Đóng gói quy tắc nghiệp vụ (Business Logic), kiểm tra phân quyền, chuẩn hóa dữ liệu
 * và điều phối giao dịch (Transaction) giữa các Repository / DAO.
 */
abstract class BaseService
{
    /**
     * Thực thi nghiệp vụ trong một Transaction CSDL đảm bảo tính toàn vẹn dữ liệu.
     */
    protected function executeInTransaction(callable $operation): mixed
    {
        $pdo = Database::getConnection();
        $started = !$pdo->inTransaction();
        if ($started) {
            $pdo->beginTransaction();
        }

        try {
            $result = $operation();
            if ($started && $pdo->inTransaction()) {
                $pdo->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if ($started && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Tạo cấu trúc kết quả trả về chuẩn hóa từ Service cho Controller.
     */
    protected function success(mixed $data = null, string $message = ''): array
    {
        return [
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'errors'  => [],
        ];
    }

    /**
     * Tạo cấu trúc kết quả thất bại kèm danh sách lỗi và mã HTTP gợi ý.
     */
    protected function failure(string $message, array $errors = [], int $status = 400): array
    {
        return [
            'success' => false,
            'data'    => null,
            'message' => $message,
            'errors'  => $errors,
            'status'  => $status,
        ];
    }

    /**
     * Làm sạch chuỗi đầu vào cơ bản.
     */
    protected function cleanString(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}

