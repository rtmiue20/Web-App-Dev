<?php
class Category extends Model {
    public function create($name, $description) {
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([
                ':name' => $name,
                ':description' => $description
            ]);
        } catch (PDOException $e) {
            return false; // Trả về false nếu trùng tên (UNIQUE)
        }
    }
}
?>