<?php
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = Database::getInstance();
    
    // 1. Update view vw_post_details
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
    $db->exec($viewSql);
    echo "[✓] Updated vw_post_details view successfully!\n";

    // 2. Add extra photos for post 7 if it only has 1
    $p7Count = (int)$db->query("SELECT COUNT(*) FROM post_images WHERE post_id = 7")->fetchColumn();
    if ($p7Count < 3) {
        $extraImgs7 = [
            'uploads/posts/post_da_lat_san_may.jpg',
            'uploads/posts/338c3415e2eb26f4650ecb888117196e.jpg',
            'uploads/posts/post_tuyen_lam_glamping.jpg'
        ];
        $ins = $db->prepare("INSERT INTO post_images (post_id, image_url) VALUES (?, ?)");
        foreach ($extraImgs7 as $img) {
            $ins->execute([7, $img]);
        }
        echo "[✓] Added extra images to Post #7 (Now has 4 images)!\n";
    }

    // 3. Add extra photos for post 29 if it exists and has 1
    $p29Count = (int)$db->query("SELECT COUNT(*) FROM post_images WHERE post_id = 29")->fetchColumn();
    if ($p29Count === 1) {
        $extraImgs29 = [
            'uploads/posts/post_ta_nang_phan_dung.jpg',
            'uploads/posts/post_da_lat_san_may.jpg'
        ];
        $ins = $db->prepare("INSERT INTO post_images (post_id, image_url) VALUES (?, ?)");
        foreach ($extraImgs29 as $img) {
            $ins->execute([29, $img]);
        }
        echo "[✓] Added extra images to Post #29 (Now has 3 images)!\n";
    }

    // 4. Verify post 7
    $p7Images = $db->query("SELECT * FROM post_images WHERE post_id = 7")->fetchAll(PDO::FETCH_ASSOC);
    echo "Post #7 has " . count($p7Images) . " images:\n";
    foreach ($p7Images as $img) {
        echo "  - ID {$img['id']}: {$img['image_url']}\n";
    }

    echo "\n[DONE] Everything synchronized successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
