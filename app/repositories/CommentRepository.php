<?php
require_once __DIR__ . '/../core/BaseRepository.php';

/**
 * CommentRepository (DAO) - Tầng truy cập dữ liệu Bình luận & Tương tác bình luận đa cấp.
 * Thực thi các Stored Procedures: sp_comment_list, sp_comment_create, sp_comment_delete, sp_comment_count_by_post.
 */
class CommentRepository extends BaseRepository
{
    protected string $table = 'comments';

    /**
     * Tìm thông tin 1 bình luận theo ID.
     */
    public function findById(int $id): ?array
    {
        try {
            return $this->queryOne(
                'SELECT c.id, c.post_id, c.user_id, c.parent_id, c.content, c.created_at, u.username
                 FROM comments c
                 JOIN users u ON u.id = c.user_id
                 WHERE c.id = :id
                 LIMIT 1',
                ['id' => $id]
            );
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Lấy danh sách bình luận phẳng của một bài viết kèm số lượt thích.
     */
    public function findByPostId(int $postId, int $currentUserId = 0): array
    {
        try {
            return $this->query(
                'SELECT c.id, c.post_id, c.user_id, c.parent_id, c.content, c.created_at,
                        u.username, r.name AS role_name,
                        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id) AS likes_count,
                        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.user_id = :current_user_id) AS user_liked
                 FROM comments c
                 JOIN users u ON c.user_id = u.id
                 LEFT JOIN roles r ON r.id = u.role_id
                 WHERE c.post_id = :post_id
                 ORDER BY c.created_at ASC, c.id ASC',
                ['post_id' => $postId, 'current_user_id' => $currentUserId]
            );
        } catch (PDOException $e) {
            try {
                $rows = $this->callProcedure('sp_comment_list', [$postId]);
                foreach ($rows as &$r) {
                    $r['likes_count'] = 0;
                    $r['user_liked'] = 0;
                }
                unset($r);
                return $rows;
            } catch (PDOException $ex) {
                return [];
            }
        }
    }

    /**
     * Thêm bình luận mới (hỗ trợ parent_id đa cấp).
     */
    public function createComment(int $postId, int $userId, string $content, ?int $parentId = null): int
    {
        try {
            $this->execute(
                'INSERT INTO comments (post_id, user_id, parent_id, content, created_at)
                 VALUES (:post_id, :user_id, :parent_id, :content, NOW())',
                [
                    'post_id'   => $postId,
                    'user_id'   => $userId,
                    'parent_id' => $parentId,
                    'content'   => $content,
                ]
            );
            return $this->lastInsertId();
        } catch (PDOException $e) {
            try {
                $rows = $this->callProcedure('sp_comment_create', [$postId, $userId, $content, $parentId]);
                return (int)($rows[0]['comment_id'] ?? 0);
            } catch (PDOException $ex) {
                return 0;
            }
        }
    }

    /**
     * Xóa đệ quy nhánh bình luận con và bình luận gốc.
     */
    public function deleteWithChildren(int $id): bool
    {
        try {
            $children = $this->query('SELECT id FROM comments WHERE parent_id = :pid', ['pid' => $id]);
            foreach ($children as $child) {
                $childId = (int)($child['id'] ?? 0);
                if ($childId > 0) {
                    $this->deleteWithChildren($childId);
                }
            }
            return $this->delete($id);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Thích / bỏ thích bình luận.
     */
    public function toggleLike(int $commentId, int $userId): array
    {
        if ($commentId <= 0 || $userId <= 0) {
            return ['liked' => false, 'likes_count' => 0];
        }

        try {
            $exists = $this->queryOne(
                'SELECT 1 FROM comment_likes WHERE comment_id = :cid AND user_id = :uid',
                ['cid' => $commentId, 'uid' => $userId]
            );
            if ($exists !== null) {
                $this->execute(
                    'DELETE FROM comment_likes WHERE comment_id = :cid AND user_id = :uid',
                    ['cid' => $commentId, 'uid' => $userId]
                );
                $liked = false;
            } else {
                $this->execute(
                    'INSERT IGNORE INTO comment_likes (comment_id, user_id, created_at) VALUES (:cid, :uid, NOW())',
                    ['cid' => $commentId, 'uid' => $userId]
                );
                $liked = true;
            }

            $res = $this->queryOne('SELECT COUNT(*) AS total FROM comment_likes WHERE comment_id = :cid', ['cid' => $commentId]);
            return ['liked' => $liked, 'likes_count' => (int)($res['total'] ?? 0)];
        } catch (PDOException $e) {
            return ['liked' => false, 'likes_count' => 0];
        }
    }

    /**
     * Đếm tổng số bình luận của 1 bài viết (sp_comment_count_by_post).
     */
    public function countByPost(int $postId): int
    {
        try {
            $rows = $this->callProcedure('sp_comment_count_by_post', [$postId]);
            return (int)($rows[0]['total'] ?? 0);
        } catch (PDOException $e) {
            $row = $this->queryOne('SELECT COUNT(*) AS total FROM comments WHERE post_id = :post_id', ['post_id' => $postId]);
            return (int)($row['total'] ?? 0);
        }
    }
}

