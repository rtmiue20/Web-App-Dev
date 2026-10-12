<?php
class Tag extends Model {
    // Thêm tag nếu chưa tồn tại (IGNORE giúp không báo lỗi nếu trùng)
    public function findOrCreate($name) {
        $name = trim(strtolower($name));
        $sql = "INSERT IGNORE INTO tags (name) VALUES (:name)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $name]);
        
        // Truy vấn lấy lại ID của tag vừa thêm hoặc đã có sẵn
        $sqlGet = "SELECT id FROM tags WHERE name = :name LIMIT 1";
        $stmtGet = $this->db->prepare($sqlGet);
        $stmtGet->execute([':name' => $name]);
        $row = $stmtGet->fetch(PDO::FETCH_ASSOC);
        
        return $row['id'];
    }
}
?>