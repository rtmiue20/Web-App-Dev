<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Tag - Quản lý thẻ bài viết
 * Gọi các Stored Procedures: sp_tag_find_or_create, sp_post_tags_get, sp_post_tag_add, sp_post_tag_remove
 */
class Tag extends Model {
    private ?int $id = null;
    private ?string $name = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setName(?string $name): void { $this->name = $name; }

    /**
     * Tìm thẻ theo tên hoặc tạo mới nếu chưa tồn tại (gọi routine sp_tag_find_or_create)
     */
    public function findOrCreate(): int|false {
        $tagName = trim(strtolower((string)$this->name));
        if ($tagName === '') {
            return false;
        }

        try {
            $rows = Database::call('sp_tag_find_or_create', [$tagName]);
            $tagId = (int)($rows[0]['tag_id'] ?? 0);
            if ($tagId > 0) {
                $this->id = $tagId;
                return $this->id;
            }
            return false;
        } catch (PDOException $e) {
            error_log('Notice: sp_tag_find_or_create fallback query: ' . $e->getMessage());
            try {
                $sql = "INSERT IGNORE INTO tags (name) VALUES (:name)";
                Database::execute($sql, ['name' => $tagName]);
                
                $row = Database::query("SELECT id FROM tags WHERE name = :name LIMIT 1", ['name' => $tagName]);
                if (!empty($row)) {
                    $this->id = (int)$row[0]['id'];
                    return $this->id;
                }
                return false;
            } catch (PDOException $ex) {
                return false;
            }
        }
    }

    /**
     * Lấy danh sách thẻ của bài viết (gọi routine sp_post_tags_get)
     */
    public function getByPostId(int $postId): array {
        try {
            return Database::call('sp_post_tags_get', [$postId]);
        } catch (PDOException $e) {
            try {
                $sql = "SELECT t.id, t.name
                        FROM post_tags pt
                        JOIN tags t ON t.id = pt.tag_id
                        WHERE pt.post_id = :post_id
                        ORDER BY t.name";
                return Database::query($sql, ['post_id' => $postId]);
            } catch (PDOException $ex) {
                return [];
            }
        }
    }

    /**
     * Gán thẻ vào bài viết (gọi routine sp_post_tag_add)
     */
    public function addTagToPost(int $postId, string $tagName): int|false {
        try {
            $rows = Database::call('sp_post_tag_add', [$postId, $tagName]);
            return (int)($rows[0]['tag_id'] ?? 0);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Gỡ thẻ khỏi bài viết (gọi routine sp_post_tag_remove)
     */
    public function removeTagFromPost(int $postId, int $tagId): bool {
        try {
            Database::call('sp_post_tag_remove', [$postId, $tagId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
