<?php
require_once __DIR__ . '/Database.php';

class Model {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }
}
