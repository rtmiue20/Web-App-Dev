-- MIGRATION 008: THÊM SỐ MẮT XEM (VIEWS_COUNT) CHO BÀI VIẾT

-- 1. Bổ sung cột views_count vào bảng posts
ALTER TABLE posts ADD COLUMN IF NOT EXISTS views_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER status;

-- 2. Cập nhật lại View chi tiết bài viết tổng hợp (vw_post_details) để bao gồm views_count
CREATE OR REPLACE VIEW vw_post_details AS
SELECT
    p.id,
    p.user_id,
    p.category_id,
    p.title,
    p.content,
    p.location,
    p.route,
    p.trip_duration,
    p.estimated_cost,
    p.gear_needed,
    p.difficulty,
    p.region,
    p.activity_type,
    p.lat,
    p.lng,
    p.status,
    p.created_at,
    COALESCE(p.views_count, 0) AS views_count,
    u.username,
    u.avatar_path,
    r.name AS role_name,
    c.name AS category_name,
    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
    (SELECT COUNT(pi.id) FROM post_images pi WHERE pi.post_id = p.id) AS images_count,
    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS vote_score,
    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = 1) AS upvotes,
    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id = p.id AND l.vote = -1) AS downvotes,
    COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) AS likes,
    (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) AS comments_count,
    (COALESCE((SELECT SUM(l.vote) FROM post_likes l WHERE l.post_id = p.id), 0) * 2
     + (SELECT COUNT(*) FROM comments cm WHERE cm.post_id = p.id) * 3) AS hot_score
FROM posts p
INNER JOIN users u ON u.id = p.user_id
INNER JOIN roles r ON r.id = u.role_id
INNER JOIN categories c ON c.id = p.category_id;

