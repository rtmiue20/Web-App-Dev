<?php
require_once __DIR__ . '/Database.php';

/**
 * Lớp cơ sở Model (BaseModel) trong kiến trúc MVC.
 * Đại diện thực thể dữ liệu (Entity) và kết nối PDO chuẩn.
 */
class Model
{
    protected PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }
}

if (!class_exists('BaseModel', false)) {
    class_alias(Model::class, 'BaseModel');
}
