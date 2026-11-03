<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Database.php';

/**
 * Model Comment - Quản lý bình luận bài viết & Bình luận lồng nhau nhiều cấp (Vương - Tuần 2 & 4)
 * Gọi các Stored Procedures: sp_comment_list, sp_comment_create, sp_comment_delete, sp_comment_count_by_post
 * Hỗ trợ cấu trúc cây đa cấp (parent_id -> children) không giới hạn số tầng trả lời.
 */
class Comment extends Model {
    private ?int $id = null;
    private ?int $postId = null;
    private ?int $userId = null;
    private ?int $parentId = null;
    private ?string $content = null;
    private ?string $createdAt = null;
    private ?string $username = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getPostId(): ?int { return $this->postId; }
    public function setPostId(?int $postId): void { $this->postId = $postId; }

    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): void { $this->userId = $userId; }

    public function getParentId(): ?int { return $this->parentId; }
    public function setParentId(?int $parentId): void { $this->parentId = $parentId; }

    public function getContent(): ?string { return $this->content; }
    public function setContent(?string $content): void { $this->content = $content; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(?string $username): void { $this->username = $username; }

    /**
     * Lấy danh sách bình luận phẳng theo ID bài viết (có đầy đủ parent_id và role_name)
     */
    public function getByPostId(int $postId): array {
        try {
            $sql = "SELECT c.id, c.post_id, c.user_id, c.parent_id, c.content, c.created_at,
                           u.username, r.name AS role_name
                    FROM comments c
                    JOIN users u ON c.user_id = u.id
                    LEFT JOIN roles r ON r.id = u.role_id
                    WHERE c.post_id = :post_id
                    ORDER BY c.created_at ASC, c.id ASC";
            return Database::query($sql, ['post_id' => $postId]);
        } catch (PDOException $e) {
            error_log('Notice: getByPostId fallback query: ' . $e->getMessage());
            try {
                return Database::call('sp_comment_list', [$postId]);
            } catch (PDOException $ex) {
                error_log('Error getting comments: ' . $ex->getMessage());
                return [];
            }
        }
    }

    /**
     * Xây dựng cấu trúc cây bình luận lồng nhau nhiều cấp (Recursive Tree) cho bài viết
     * Bình luận gốc (parent_id = NULL) xếp mới nhất lên trên, các câu trả lời con xếp theo thứ tự thời gian.
     */
    public function getTreeByPostId(int $postId): array {
        $rows = $this->getByPostId($postId);
        if (empty($rows)) {
            return [];
        }

        $itemsById = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $row['id'] = $id;
            $row['parent_id'] = !empty($row['parent_id']) ? (int)$row['parent_id'] : null;
            $row['parent_username'] = null;
            $row['children'] = [];
            $itemsById[$id] = $row;
        }

        // Gắn tên người được trả lời (@parent_username) và gom nhóm theo parent_id
        $childrenByParent = [];
        $roots = [];
        foreach ($itemsById as $id => &$item) {
            $pid = $item['parent_id'];
            if ($pid !== null && isset($itemsById[$pid])) {
                $item['parent_username'] = $itemsById[$pid]['username'] ?? null;
                $childrenByParent[$pid][] = &$item;
            } else {
                $roots[] = &$item;
            }
        }
        unset($item);

        // Đệ quy gắn danh sách con nhiều cấp vào từng nút
        $attachChildren = function (array &$node, int $depth = 0) use (&$attachChildren, &$childrenByParent): void {
            $nodeId = (int)$node['id'];
            $node['depth'] = $depth;
            if (!empty($childrenByParent[$nodeId])) {
                $node['children'] = $childrenByParent[$nodeId];
                foreach ($node['children'] as &$childNode) {
                    $attachChildren($childNode, $depth + 1);
                }
                unset($childNode);
            }
        };

        foreach ($roots as &$rootNode) {
            $attachChildren($rootNode, 0);
        }
        unset($rootNode);

        // Bình luận gốc mới nhất hiển thị lên đầu, các nhánh reply bên trong giữ thứ tự cuộc hội thoại
        return array_reverse($roots);
    }

    /**
     * Tìm thông tin 1 bình luận theo ID
     */
    public function findById(int $id): ?array {
        try {
            $rows = Database::query(
                "SELECT c.id, c.post_id, c.user_id, c.parent_id, c.content, c.created_at, u.username
                 FROM comments c
                 JOIN users u ON u.id = c.user_id
                 WHERE c.id = :id
                 LIMIT 1",
                ['id' => $id]
            );
            return !empty($rows) ? $rows[0] : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Thêm bình luận mới hoặc trả lời bình luận (reply nhiều cấp)
     */
    public function create(int $postId, int $userId, string $content, ?int $parentId = null): int {
        // Kiểm tra tính hợp lệ của bình luận cha (nếu là reply)
        if ($parentId !== null && $parentId > 0) {
            $parentComment = $this->findById($parentId);
            if (!$parentComment || (int)($parentComment['post_id'] ?? 0) !== $postId) {
                $parentId = null;
            }
        } else {
            $parentId = null;
        }

        try {
            $sql = "INSERT INTO comments (post_id, user_id, parent_id, content, created_at) 
                    VALUES (:post_id, :user_id, :parent_id, :content, NOW())";
            $stmt = $this->db->prepare($sql);
            $ok = $stmt->execute([
                ':post_id'   => $postId,
                ':user_id'   => $userId,
                ':parent_id' => $parentId,
                ':content'   => $content
            ]);
            if ($ok) {
                $this->id = (int)$this->db->lastInsertId();
                $this->parentId = $parentId;
                return $this->id;
            }
            return 0;
        } catch (PDOException $e) {
            error_log('Notice: create comment with parent_id fallback: ' . $e->getMessage());
            try {
                $rows = Database::call('sp_comment_create', [$postId, $userId, $content]);
                $commentId = (int)($rows[0]['comment_id'] ?? 0);
                if ($commentId > 0) {
                    $this->id = $commentId;
                }
                return $commentId;
            } catch (PDOException $ex) {
                error_log('Error creating comment: ' . $ex->getMessage());
                return 0;
            }
        }
    }

    /**
     * Xóa bình luận (kèm toàn bộ các bình luận trả lời con thuộc nhánh đó)
     */
    public function delete(int $id, int $userId, bool $isAdmin = false): bool {
        try {
            $target = $this->findById($id);
            if (!$target) {
                return false;
            }
            if (!$isAdmin && (int)($target['user_id'] ?? 0) !== $userId) {
                return false;
            }

            // Xóa đệ quy các bình luận con (phòng trường hợp bảng cũ chưa có ON DELETE CASCADE trên parent_id)
            $this->deleteChildrenRecursive($id);

            $sql = "DELETE FROM comments WHERE id = :id";
            return Database::execute($sql, ['id' => $id]) > 0;
        } catch (PDOException $e) {
            error_log('Notice: delete comment error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa đệ quy các câu trả lời con của 1 bình luận
     */
    private function deleteChildrenRecursive(int $parentId): void {
        try {
            $children = Database::query("SELECT id FROM comments WHERE parent_id = :pid", ['pid' => $parentId]);
            foreach ($children as $child) {
                $childId = (int)($child['id'] ?? 0);
                if ($childId > 0) {
                    $this->deleteChildrenRecursive($childId);
                    Database::execute("DELETE FROM comments WHERE id = :id", ['id' => $childId]);
                }
            }
        } catch (PDOException $e) {
            // Bỏ qua nếu không có nhánh con
        }
    }

    /**
     * Đếm tổng số bình luận (bao gồm cả bình luận gốc và tất cả các cấp trả lời) của bài viết
     */
    public function countByPost(int $postId): int {
        try {
            $rows = Database::call('sp_comment_count_by_post', [$postId]);
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            try {
                $res = Database::query("SELECT COUNT(*) AS total FROM comments WHERE post_id = :post_id", ['post_id' => $postId]);
                return (int)($res[0]['total'] ?? 0);
            } catch (PDOException $ex) {
                return 0;
            }
        }
    }
}
