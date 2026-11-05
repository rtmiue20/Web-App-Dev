<?php
/**
 * Kiểm thử Tự động SQL Views (cho truy vấn hay dùng) & Stored Procedures Thống kê
 * - Kiểm tra 4 SQL Views: vw_post_details, vw_category_summary, vw_user_summary, vw_popular_tags
 * - Kiểm tra 5 Stored Procedures thống kê: sp_dashboard_stats, sp_stats_top_posts,
 *   sp_stats_posts_by_category (kèm lk_posts_incategory), sp_stats_top_users (kèm lk_posts_byuser), sp_stats_posts_by_status
 * - Kiểm tra tích hợp tầng DAO (StatsRepository, PostRepository, CategoryRepository) & Service (PostService)
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$passed = 0;
$failed = 0;

function assertDbFeature(bool $cond, string $label): void
{
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failed++;
    }
}

echo "=========================================================================\n";
echo " KIỂM THỬ STORED PROCEDURES THỐNG KÊ & SQL VIEWS CHO TRUY VẤN HAY DÙNG\n";
echo "=========================================================================\n\n";

$schemaSql = (string)file_get_contents(__DIR__ . '/../database/schema.sql');
$migrationSql = (string)file_get_contents(__DIR__ . '/../database/migrations/007_add_views_and_stats_procedures.sql');

// -------------------------------------------------------------------------
// 1. Kiểm tra định nghĩa 4 SQL Views trong Schema & Migration
// -------------------------------------------------------------------------
echo "1. Kiểm tra định nghĩa SQL Views cho truy vấn hay dùng:\n";
$views = [
    'vw_post_details'     => 'View tổng hợp chi tiết bài viết (tác giả, chuyên mục, ảnh, vote_score, comments_count, hot_score)',
    'vw_category_summary' => 'View thống kê danh mục kèm tổng số bài, bài đã duyệt & bài chờ duyệt',
    'vw_user_summary'     => 'View tổng hợp thành viên kèm vai trò, số bài viết, bình luận & điểm karma',
    'vw_popular_tags'     => 'View danh sách thẻ (tags) phổ biến kèm số lượng bài viết',
];

foreach ($views as $viewName => $desc) {
    assertDbFeature(
        str_contains($schemaSql, "VIEW {$viewName}") && str_contains($migrationSql, "VIEW {$viewName}"),
        "{$viewName}: {$desc}"
    );
}

// -------------------------------------------------------------------------
// 2. Kiểm tra định nghĩa 5 Stored Procedures Thống kê trong Schema & Routines
// -------------------------------------------------------------------------
echo "\n2. Kiểm tra định nghĩa Stored Procedures cho thống kê:\n";
$procedures = [
    'sp_dashboard_stats'         => 'database/routines/sp_dashboard_stats.sql',
    'sp_stats_top_posts'         => 'database/routines/sp_stats_top_posts.sql',
    'sp_stats_posts_by_category' => 'database/routines/sp_stats_posts_by_category.sql',
    'sp_stats_top_users'         => 'database/routines/sp_stats_top_users.sql',
    'sp_stats_posts_by_status'   => 'database/routines/sp_stats_posts_by_status.sql',
    'sp_stats_daily_activity'    => 'database/routines/sp_stats_daily_activity.sql',
];

foreach ($procedures as $procName => $routineFile) {
    $fileExists = is_file(__DIR__ . '/../' . $routineFile);
    assertDbFeature(
        str_contains($schemaSql, "CREATE PROCEDURE {$procName}") && $fileExists,
        "Stored Procedure `{$procName}` tồn tại trong schema.sql và file riêng `{$routineFile}`"
    );
}

// -------------------------------------------------------------------------
// 3. Kiểm thử thực thi thực tế 4 SQL Views & StatsRepository trên PDO
// -------------------------------------------------------------------------
echo "\n3. Kiểm thử thực thi truy vấn trên 4 SQL Views & StatsRepository:\n";

$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// Tạo bảng và dữ liệu mẫu
$pdo->exec("
    CREATE TABLE roles (id INTEGER PRIMARY KEY, name TEXT);
    CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, email TEXT, password_hash TEXT, role_id INTEGER, bio TEXT, avatar_path TEXT, created_at TEXT);
    CREATE TABLE categories (id INTEGER PRIMARY KEY, name TEXT, description TEXT);
    CREATE TABLE posts (
        id INTEGER PRIMARY KEY, user_id INTEGER, category_id INTEGER, title TEXT, content TEXT,
        location TEXT, route TEXT, trip_duration TEXT, estimated_cost TEXT, gear_needed TEXT,
        difficulty TEXT, region TEXT, activity_type TEXT, lat REAL, lng REAL, status TEXT, created_at TEXT
    );
    CREATE TABLE post_images (id INTEGER PRIMARY KEY, post_id INTEGER, image_url TEXT);
    CREATE TABLE post_likes (post_id INTEGER, user_id INTEGER, vote INTEGER, PRIMARY KEY (post_id, user_id));
    CREATE TABLE comments (id INTEGER PRIMARY KEY, post_id INTEGER, user_id INTEGER, parent_id INTEGER, content TEXT, created_at TEXT);
    CREATE TABLE tags (id INTEGER PRIMARY KEY, name TEXT);
    CREATE TABLE post_tags (post_id INTEGER, tag_id INTEGER, PRIMARY KEY (post_id, tag_id));
    CREATE TABLE products (id INTEGER PRIMARY KEY, category_id INTEGER, name TEXT, price REAL, is_shared INTEGER DEFAULT 1);

    INSERT INTO roles VALUES (1, 'member'), (2, 'admin');
    INSERT INTO users VALUES
        (1, 'admin_user', 'admin@outdoor.vn', 'hash', 2, 'Admin Bio', NULL, '2026-10-01 08:00:00'),
        (2, 'trekker_minh', 'minh@outdoor.vn', 'hash', 1, 'Trekker Bio', NULL, '2026-10-02 09:00:00');
    INSERT INTO categories VALUES
        (1, 'Cắm trại', 'Kinh nghiệm cắm trại'),
        (2, 'Trekking', 'Cung đường leo núi');
    INSERT INTO posts VALUES
        (10, 2, 2, 'Săn mây Tà Xùa 2N1Đ', 'Nội dung chi tiết...', 'Sơn La', NULL, NULL, NULL, NULL, 'medium', 'Tây Bắc', 'trekking', 21.3, 104.4, 'published', '2026-10-10 10:00:00'),
        (11, 2, 1, 'Cắm trại hồ Trị An', 'Nội dung chi tiết...', 'Đồng Nai', NULL, NULL, NULL, NULL, 'easy', 'Nam', 'camping', 11.1, 107.0, 'published', '2026-10-12 11:00:00'),
        (12, 1, 1, 'Bài viết đang chờ duyệt', 'Nội dung...', 'Đà Lạt', NULL, NULL, NULL, NULL, 'easy', 'Tây Nguyên', 'camping', 11.9, 108.4, 'pending', '2026-10-15 12:00:00');
    INSERT INTO post_likes VALUES (10, 1, 1), (10, 2, 1), (11, 1, 1);
    INSERT INTO comments VALUES (1, 10, 1, NULL, 'Bài hay quá!', '2026-10-10 12:00:00'), (2, 10, 2, 1, 'Cảm ơn bạn!', '2026-10-10 12:05:00');
    INSERT INTO tags VALUES (1, 'taxua'), (2, 'sanmay');
    INSERT INTO post_tags VALUES (10, 1), (10, 2), (11, 1);

    CREATE VIEW vw_post_details AS
    SELECT
        p.*, u.username, u.avatar_path, r.name AS role_name, c.name AS category_name,
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

    CREATE VIEW vw_category_summary AS
    SELECT
        c.id, c.name, c.description,
        COUNT(p.id) AS post_count,
        SUM(CASE WHEN p.status = 'published' THEN 1 ELSE 0 END) AS published_count,
        SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
        MAX(p.created_at) AS latest_post_at
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id
    GROUP BY c.id, c.name, c.description;

    CREATE VIEW vw_user_summary AS
    SELECT
        u.id, u.username, u.email, u.role_id, r.name AS role_name, u.bio, u.avatar_path, u.created_at,
        (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id) AS total_posts,
        (SELECT COUNT(*) FROM posts p WHERE p.user_id = u.id AND p.status = 'published') AS published_posts,
        (SELECT COUNT(*) FROM comments cm WHERE cm.user_id = u.id) AS total_comments,
        COALESCE((SELECT SUM(pl.vote) FROM post_likes pl INNER JOIN posts up ON up.id = pl.post_id WHERE up.user_id = u.id), 0) AS total_karma
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id;

    CREATE VIEW vw_popular_tags AS
    SELECT t.id, t.name, COUNT(pt.post_id) AS post_count
    FROM tags t
    INNER JOIN post_tags pt ON pt.tag_id = t.id
    GROUP BY t.id, t.name
    ORDER BY post_count DESC, t.name ASC;
");

$statsRepo = new StatsRepository($pdo);
$postRepo  = new PostRepository($pdo);

$summary = $statsRepo->getDashboardStats();
assertDbFeature(
    $summary['total_users'] === 2 && $summary['total_posts'] === 3 && $summary['published_posts'] === 2 && $summary['pending_posts'] === 1,
    'StatsRepository::getDashboardStats() trả về chính xác tổng số users (2), tổng bài (3), đã duyệt (2), chờ duyệt (1)'
);

$topPosts = $statsRepo->getTopPosts(5);
assertDbFeature(
    count($topPosts) === 2 && (int)$topPosts[0]['id'] === 10 && (int)$topPosts[0]['hot_score'] === 10,
    'StatsRepository::getTopPosts() xếp hạng chính xác Top bài viết #10 (2 votes * 2 + 2 comments * 3 = 10 điểm hot)'
);

$catStats = $statsRepo->getPostsByCategory();
assertDbFeature(
    count($catStats) === 2 && (int)$catStats[0]['total_posts'] === 2 && (int)$catStats[0]['published_count'] === 1 && (int)$catStats[0]['pending_count'] === 1,
    'StatsRepository::getPostsByCategory() thống kê chính xác số bài theo danh mục (Cắm trại: 2 bài = 1 đã duyệt + 1 chờ duyệt)'
);

$topUsers = $statsRepo->getTopUsers(5);
assertDbFeature(
    count($topUsers) === 2 && $topUsers[0]['username'] === 'trekker_minh' && (int)$topUsers[0]['total_karma'] === 3,
    'StatsRepository::getTopUsers() thống kê chính xác Top thành viên đóng góp (trekker_minh: 2 bài, +3 karma)'
);

$statusStats = $statsRepo->getPostsByStatus();
assertDbFeature(
    $statusStats['all'] === 3 && $statusStats['published'] === 2 && $statusStats['pending'] === 1,
    'StatsRepository::getPostsByStatus() thống kê chính xác số lượng bài theo từng trạng thái kiểm duyệt'
);

$popularTags = $postRepo->findPopularTags(5);
assertDbFeature(
    count($popularTags) === 2 && $popularTags[0]['name'] === 'taxua' && (int)$popularTags[0]['post_count'] === 2,
    'PostRepository::findPopularTags() truy vấn SQL View vw_popular_tags trả về thẻ phổ biến nhất (#taxua: 2 bài)'
);

// -------------------------------------------------------------------------
// 4. Biểu đồ Chart.js: thống kê người dùng mới / bài viết mới theo ngày
// -------------------------------------------------------------------------
echo "\n4. Kiểm thử thống kê theo ngày cho biểu đồ Chart.js (sp_stats_daily_activity):\n";

$chartPdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);
$chartPdo->exec("
    CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, created_at TEXT);
    CREATE TABLE posts (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT, created_at TEXT);
    CREATE TABLE comments (id INTEGER PRIMARY KEY, post_id INTEGER, user_id INTEGER, created_at TEXT);
");

$dayAgo = static fn(int $n, string $time = '10:30:00'): string => date('Y-m-d', strtotime("-{$n} days")) . ' ' . $time;
$insUser    = $chartPdo->prepare('INSERT INTO users (username, created_at) VALUES (?, ?)');
$insPost    = $chartPdo->prepare('INSERT INTO posts (user_id, title, created_at) VALUES (1, ?, ?)');
$insComment = $chartPdo->prepare('INSERT INTO comments (post_id, user_id, created_at) VALUES (1, 1, ?)');

// Hôm nay: 2 user, 1 bài | Hôm qua: 1 user | 3 ngày trước: 2 bài, 1 bình luận | 40 ngày trước: nằm ngoài mọi khoảng thống kê
$insUser->execute(['u_today_1', $dayAgo(0, '08:00:00')]);
$insUser->execute(['u_today_2', $dayAgo(0, '23:59:59')]);
$insUser->execute(['u_yesterday', $dayAgo(1)]);
$insUser->execute(['u_old', $dayAgo(40)]);
$insPost->execute(['P today', $dayAgo(0)]);
$insPost->execute(['P d3 a', $dayAgo(3, '09:00:00')]);
$insPost->execute(['P d3 b', $dayAgo(3, '18:00:00')]);
$insPost->execute(['P old', $dayAgo(40)]);
$insComment->execute([$dayAgo(3)]);

$chartRepo = new StatsRepository($chartPdo);
$daily = $chartRepo->getDailyActivity(7);

assertDbFeature(
    count($daily) === 7
        && $daily[0]['stat_date'] === date('Y-m-d', strtotime('-6 days'))
        && $daily[6]['stat_date'] === date('Y-m-d'),
    'getDailyActivity(7) trả đủ 7 ngày liên tiếp, sắp xếp từ cũ đến mới (ngày cuối là hôm nay)'
);
assertDbFeature(
    $daily[6]['new_users'] === 2 && $daily[5]['new_users'] === 1 && $daily[6]['new_posts'] === 1,
    'Đếm đúng người dùng mới (hôm nay: 2, hôm qua: 1) và bài viết mới hôm nay (1)'
);
assertDbFeature(
    $daily[3]['new_posts'] === 2 && $daily[3]['new_comments'] === 1 && $daily[2]['new_users'] === 0,
    'Ngày có 2 bài/1 bình luận được đếm đúng; ngày không phát sinh dữ liệu trả về 0 (biểu đồ liên tục)'
);
assertDbFeature(
    array_sum(array_column($daily, 'new_users')) === 3 && array_sum(array_column($daily, 'new_posts')) === 3,
    'Dữ liệu cũ hơn khoảng thống kê (40 ngày trước) bị loại khỏi kết quả 7 ngày'
);
assertDbFeature(
    count($chartRepo->getDailyActivity(30)) === 30 && count($chartRepo->getDailyActivity(500)) === 30 && count($chartRepo->getDailyActivity(14)) === 14,
    'getDailyActivity() giới hạn tối đa 30 ngày và hỗ trợ 14 / 30 ngày'
);
assertDbFeature(
    is_int($daily[0]['new_users']) && is_string($daily[0]['date_label']) && preg_match('/^\d{2}\/\d{2}$/', $daily[0]['date_label']) === 1,
    'Mỗi dòng có kiểu dữ liệu chuẩn JSON: số nguyên cho số lượng, nhãn ngày dạng dd/mm'
);

// Kiểm tra đồng bộ định nghĩa SQL, route API & giao diện Dashboard
$appSource       = (string)file_get_contents(__DIR__ . '/../app/core/App.php');
$apiSource       = (string)file_get_contents(__DIR__ . '/../app/controllers/ApiController.php');
$dashboardView   = (string)file_get_contents(__DIR__ . '/../app/views/admin/dashboard.php');
$allRoutinesSql  = (string)file_get_contents(__DIR__ . '/../database/routines/all_routines.sql');

assertDbFeature(
    str_contains($migrationSql, 'sp_stats_daily_activity') && str_contains($allRoutinesSql, 'sp_stats_daily_activity'),
    'sp_stats_daily_activity có trong migration 007, all_routines.sql và schema.sql'
);
assertDbFeature(
    str_contains($appSource, "'/stats/daily'") && str_contains($apiSource, 'function dailyStats')
        && str_contains($apiSource, 'requireModeratorJson'),
    'Endpoint GET /api/stats/daily được đăng ký route và chỉ Moderator/Admin truy cập (JSON 401/403)'
);
assertDbFeature(
    str_contains($dashboardView, 'chart.umd.min.js') && str_contains($dashboardView, 'id="dailyActivityChart"')
        && str_contains($dashboardView, '/api/stats/daily'),
    'Dashboard nhúng Chart.js, canvas biểu đồ và tải dữ liệu AJAX từ /api/stats/daily'
);

echo "\n=========================================================================\n";
echo " KẾT QUẢ KIỂM THỬ VIEWS & STORED PROCEDURES: {$passed} PASSED / {$failed} FAILED\n";
echo "=========================================================================\n";

exit($failed > 0 ? 1 : 0);

