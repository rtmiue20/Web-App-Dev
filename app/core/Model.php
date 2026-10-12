<?php
require_once __DIR__ . '/Database.php';

class Model {
    protected $db;

    public function __construct() {
        // Tự động kết nối CSDL khi Model được khởi tạo
        $dbInstance = Database::getInstance();
        if (method_exists($dbInstance, 'getConnection')) {
            $this->db = $dbInstance->getConnection();
        } else {
            $this->db = $dbInstance;
        }
    }
}