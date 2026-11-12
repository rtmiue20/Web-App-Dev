<?php
require_once __DIR__ . '/../app/core/Database.php';

$pdo = Database::getConnection();

// 1. Add column if not exists
$cols = $pdo->query("SHOW COLUMNS FROM posts LIKE 'views_count'")->fetchAll();
if (empty($cols)) {
    $pdo->exec("ALTER TABLE posts ADD COLUMN views_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER status");
    echo "[OK] Added column views_count to posts table.\n";
} else {
    echo "[INFO] Column views_count already exists.\n";
}

// 2. Recreate vw_post_details
$viewSql = "CREATE OR REPLACE VIEW vw_post_details AS
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
INNER JOIN categories c ON c.id = p.category_id";

$pdo->exec($viewSql);
echo "[OK] Recreated view vw_post_details with views_count.\n";

// 3. Seed realistic view counts for any posts where views_count = 0
$posts = $pdo->query("SELECT id, views_count FROM posts")->fetchAll(PDO::FETCH_ASSOC);
$baseViews = [142, 95, 210, 78, 320, 185, 430, 65, 112, 250, 190, 85, 340, 125, 410];
foreach ($posts as $idx => $p) {
    if ((int)$p['views_count'] === 0) {
        $v = $baseViews[$idx % count($baseViews)] + ($p['id'] * 7);
        $stmt = $pdo->prepare("UPDATE posts SET views_count = :v WHERE id = :id");
        $stmt->execute([':v' => $v, ':id' => $p['id']]);
    }
}
echo "[OK] Seeded realistic view counts for posts.\n";

// 4. Verify
$row = $pdo->query("SELECT id, title, views_count FROM vw_post_details LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($row as $r) {
    echo "  Post #{$r['id']}: {$r['title']} -> {$r['views_count']} views\n";
}
