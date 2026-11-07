<?php
require_once __DIR__ . '/../core/Model.php';

class PlaceReview extends Model
{
    public function getSummary(int $postId): array
    {
        $rows = Database::query(
            'SELECT ROUND(AVG(rating), 1) AS average_rating, COUNT(*) AS review_count
             FROM place_reviews WHERE post_id = :post_id',
            ['post_id' => $postId]
        );
        return $rows[0] ?? ['average_rating' => null, 'review_count' => 0];
    }

    public function getByPost(int $postId): array
    {
        try {
            return Database::query(
                'SELECT r.*, u.username, u.avatar_path
                 FROM place_reviews r
                 JOIN users u ON u.id = r.user_id
                 WHERE r.post_id = :post_id
                 ORDER BY r.created_at DESC',
                ['post_id' => $postId]
            );
        } catch (Throwable $e) {
            return Database::query(
                'SELECT r.*, u.username
                 FROM place_reviews r
                 JOIN users u ON u.id = r.user_id
                 WHERE r.post_id = :post_id
                 ORDER BY r.created_at DESC',
                ['post_id' => $postId]
            );
        }
    }

    public function save(int $postId, int $userId, int $rating, string $review): void
    {
        Database::execute(
            'INSERT INTO place_reviews (post_id, user_id, rating, review)
             VALUES (:post_id, :user_id, :rating, :review)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review), updated_at = CURRENT_TIMESTAMP',
            ['post_id' => $postId, 'user_id' => $userId, 'rating' => $rating, 'review' => $review]
        );
    }

    public function getPopularPlaces(): array
    {
        return Database::query(
            "SELECT p.id, p.title, p.location, p.lat, p.lng,
                    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                    (SELECT c.name FROM categories c WHERE c.id = p.category_id) AS category_name,
                    COALESCE(ROUND(AVG(r.rating), 1), 0) AS average_rating,
                    COUNT(DISTINCT r.id) AS review_count
             FROM posts p
             LEFT JOIN place_reviews r ON r.post_id = p.id
             WHERE p.status = 'published' AND p.lat IS NOT NULL AND p.lng IS NOT NULL
             GROUP BY p.id, p.title, p.location, p.lat, p.lng, p.category_id
             ORDER BY review_count DESC, average_rating DESC, MAX(p.created_at) DESC"
        );
    }
}
