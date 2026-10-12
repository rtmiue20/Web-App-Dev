<?php
class PostImage extends Model {
    public function add($postId, $url) {
        $sql = "INSERT INTO post_images (post_id, image_url) VALUES (:post_id, :image_url)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':post_id' => $postId,
            ':image_url' => $url
        ]);
        return $this->db->lastInsertId();
    }
}
?>