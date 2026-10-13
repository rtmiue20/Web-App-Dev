<?php
class Tag extends Model {
    private $id;
    private $name;

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }

    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }

    public function findOrCreate() {
        $tagName = trim(strtolower($this->name));
        $sql = "INSERT IGNORE INTO tags (name) VALUES (:name)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $tagName]);
        
        $sqlGet = "SELECT id FROM tags WHERE name = :name LIMIT 1";
        $stmtGet = $this->db->prepare($sqlGet);
        $stmtGet->execute([':name' => $tagName]);
        $row = $stmtGet->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            return $this->id;
        }
        return false;
    }
}
?>