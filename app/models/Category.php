<?php
require_once __DIR__ . '/../core/Model.php';

class Category extends Model {
    private $id;
    private $name;
    private $description;

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }

    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setDescription($description) { $this->description = $description; }

    public function create(): bool {
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([
                ':name' => $this->name, 
                ':description' => $this->description
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAll(): array {
        try {
            $sql = "SELECT c.*, COUNT(p.id) AS post_count 
                    FROM categories c 
                    LEFT JOIN posts p ON p.category_id = c.id 
                    GROUP BY c.id, c.name, c.description 
                    ORDER BY c.name ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting categories: ' . $e->getMessage());
            return [];
        }
    }

    public function count(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM categories");
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($res['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }
}