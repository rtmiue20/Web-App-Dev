<?php
class Category extends Model {
    // 1. Attributes (Thuộc tính)
    private $id;
    private $name;
    private $description;

    // 2. Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }

    // 3. Setters
    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setDescription($description) { $this->description = $description; }

    // 4. Function riêng (Không nhận tham số, dùng trực tiếp thuộc tính)
    public function create() {
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([
                ':name' => $this->name, 
                ':description' => $this->description
            ]);
        } catch (PDOException $e) {
            return false; // Báo lỗi nếu trùng tên do UNIQUE
        }
    }
}
?>