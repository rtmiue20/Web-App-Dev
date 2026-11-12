<?php

/**
 * Interface RepositoryInterface - Hợp đồng chuẩn cho tầng Repository / DAO (Data Access Object)
 * Tách biệt hoàn toàn logic truy vấn CSDL (MariaDB / Stored Procedures) khỏi Controller & Service.
 */
interface RepositoryInterface
{
    /**
     * Tìm bản ghi theo khóa chính (ID).
     */
    public function findById(int $id): mixed;

    /**
     * Lấy toàn bộ danh sách bản ghi.
     */
    public function findAll(): array;

    /**
     * Đếm tổng số bản ghi trong bảng.
     */
    public function count(): int;

    /**
     * Xóa bản ghi theo khóa chính (ID).
     */
    public function delete(int $id): bool;
}

