<?php
require_once __DIR__ . '/../core/BaseRepository.php';
require_once __DIR__ . '/../models/Post.php';

/**
 * PostRepository (DAO) - Tầng truy cập dữ liệu Bài viết, Phân trang, Tìm kiếm & Vote.
 * Thực thi các Stored Procedures: sp_post_list, sp_post_get, sp_post_create, sp_post_update,
 * sp_post_delete, sp_post_count, sp_post_count_filtered, sp_post_update_status,
 * sp_post_vote, sp_post_toggle_like, sp_post_tags_get, sp_post_tag_add.
 */
class PostRepository extends BaseRepository
{
    protected string $table = 'posts';

    private Post $postModel;

    public function __construct(?PDO $db = null)
    {
        parent::__construct($db);
        $this->postModel = new Post();
    }

    /**
     * Lấy bài viết theo ID.
     */
    public function findById(int $id): ?array
    {
        return $this->postModel->getById($id);
    }

    /**
     * Lấy tất cả bài viết đã duyệt theo bộ lọc chuyên mục & tiêu chí sắp xếp ('hot', 'new', 'top').
     */
    public function findAll(?int $categoryId = null, string $sort = 'hot'): array
    {
        return $this->postModel->getAll($categoryId, $sort);
    }

    /**
     * Lấy danh sách bài viết phân trang.
     */
    public function findPaginated(
        ?int $categoryId = null,
        int $page = 1,
        int $limit = 6,
        ?string $status = 'published',
        string $sort = 'hot'
    ): array {
        return $this->postModel->getPaginated($categoryId, $page, $limit, $status, $sort);
    }

    /**
     * Tìm kiếm nâng cao (Full-text, độ khó, vùng miền, hoạt động, tag).
     */
    public function searchAdvanced(array $filters, int $page = 1, int $limit = 6): array
    {
        return $this->postModel->advancedSearch($filters, $page, $limit);
    }

    /**
     * Đếm tổng số bài viết khớp bộ lọc nâng cao.
     */
    public function countAdvanced(array $filters): int
    {
        return $this->postModel->countAdvancedSearch($filters);
    }

    /**
     * Đếm số bài viết theo chuyên mục & trạng thái (sp_post_count_filtered).
     */
    public function countFiltered(?int $categoryId = null, ?string $status = 'published'): int
    {
        return $this->postModel->countFiltered($categoryId, $status);
    }

    /**
     * Đếm tổng số bài viết trong hệ thống (sp_post_count).
     */
    public function count(): int
    {
        return $this->postModel->count();
    }

    /**
     * Lấy danh sách thẻ (tags) phổ biến nhất kèm số lượng bài viết.
     */
    public function findPopularTags(int $limit = 12): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT t.id, t.name, COUNT(pt.post_id) AS post_count
                 FROM tags t
                 JOIN post_tags pt ON pt.tag_id = t.id
                 GROUP BY t.id, t.name
                 ORDER BY post_count DESC
                 LIMIT :limit'
            );
            $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lấy danh sách bài viết cho trang Quản trị (Admin/Moderator).
     */
    public function findAllForAdmin(?string $status = null, ?int $categoryId = null, int $page = 1, int $limit = 10): array
    {
        return $this->postModel->getAllForAdmin($status, $categoryId, $page, $limit);
    }

    /**
     * Đếm số bài viết cho trang Quản trị.
     */
    public function countForAdmin(?string $status = null, ?int $categoryId = null): int
    {
        return $this->postModel->countForAdmin($status, $categoryId);
    }

    /**
     * Cập nhật trạng thái bài viết (sp_post_update_status).
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->postModel->updateStatus($id, $status);
    }

    /**
     * Xóa bài viết theo ID (sp_post_delete).
     */
    public function delete(int $id): bool
    {
        return $this->postModel->delete($id);
    }

    /**
     * Bình chọn Upvote (+1) / Downvote (-1) cho bài viết (sp_post_vote).
     */
    public function vote(int $postId, int $userId, int $vote = 1): array
    {
        return $this->postModel->vote($postId, $userId, $vote);
    }
}

