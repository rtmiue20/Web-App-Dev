<?php
require_once __DIR__ . '/Database.php';

class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
}
