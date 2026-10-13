<?php
class PostImage extends Model {
    private $id;
    private $post_id;
    private $image_url;

    public function getId() { return $this->id; }
    public function getPostId() { return $this->post_id; }
    public function getImageUrl() { return $this->image_url; }

    public function setId($id) { $this->id = $id; }
    public function setPostId($post_id) { $this->post_id = $post_id; }
    public function setImageUrl($image_url) { $this->image_url = $image_url; }

    public function add() {
        $sql = "INSERT INTO post_images (post_id, image_url) VALUES (:post_id, :image_url)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':post_id' => $this->post_id,
            ':image_url' => $this->image_url
        ]);
        
        $this->id = $this->db->lastInsertId();
        return $this->id;
    }
}
?>