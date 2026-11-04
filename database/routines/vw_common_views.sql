-- ==============================================================================
-- FORUM DÃ NGOẠI - CÁC SQL VIEWS CHO TRUY VẤN HAY DÙNG (VIEWS)
-- 1. vw_post_details: Chi tiết bài viết + tác giả + chuyên mục + ảnh + vote + bình luận + điểm hot
-- 2. vw_category_summary: Chuyên mục + tổng số bài + số bài đã duyệt + số bài chờ duyệt
-- 3. vw_user_summary: Thành viên + vai trò + tổng bài viết + bình luận + điểm karma
-- 4. vw_popular_tags: Thẻ phổ biến + số bài viết
-- ==============================================================================

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
    u.username,
    u.avatar_path,
    r.name AS role_name,
    c.name AS category_name,
    (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
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

CREATE OR REPLACE VIEW vw_category_summary AS
SELECT
    c.id,
    c.name,
    c.description,
    COUNT(p.id) AS post_count,
    SUM(CASE WHEN p.status = 'published' THEN 1 ELSE 0 END) AS published_count,
    SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
    MAX(p.created_at) AS latest_post_at
FROM categories c
LEFT JOIN posts p ON p.category_id = c.id
GROUP BY c.id, c.name, c.description;

CREATE OR REPLACE VIEW vw_user_summary AS
SELECT
    u.id,
    u.username,
    u.email,
    u.role_id,
    r.name AS role_name,
    u.bio,
    u.avatar_path,
    u.created_at,
    (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS total_posts,
    (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id AND p.status = 'published') AS published_posts,
    (SELECT COUNT(*) FROM comments cm WHERE cm.user_id = u.id) AS total_comments,
    COALESCE((
        SELECT SUM(pl.vote)
        FROM post_likes pl
        INNER JOIN posts up ON up.id = pl.post_id
        WHERE up.user_id = u.id
    ), 0) AS total_karma
FROM users u
INNER JOIN roles r ON r.id = u.role_id;

CREATE OR REPLACE VIEW vw_popular_tags AS
SELECT
    t.id,
    t.name,
    COUNT(pt.post_id) AS post_count
FROM tags t
INNER JOIN post_tags pt ON pt.tag_id = t.id
GROUP BY t.id, t.name
ORDER BY post_count DESC, t.name ASC;

