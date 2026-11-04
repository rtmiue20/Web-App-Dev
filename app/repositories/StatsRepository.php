<?php
require_once __DIR__ . '/../core/BaseRepository.php';

/**
 * StatsRepository (DAO) - Tầng truy cập dữ liệu Thống kê & Báo cáo hệ thống.
 *
 * Kết hợp chặt chẽ:
 *  - Các Stored Procedures thống kê:
 *      + sp_dashboard_stats(): Tổng quan số lượng (users, posts, published_posts, pending_posts, categories, comments, products)
 *      + sp_stats_top_posts(p_limit): Top bài viết nổi bật nhất (theo điểm hot_score, vote_score, comments_count)
 *      + sp_stats_posts_by_category() / lk_posts_incategory(): Thống kê số bài viết theo từng danh mục
 *      + sp_stats_top_users(p_limit) / lk_posts_byuser(): Top thành viên đóng góp nhiều bài viết & tương tác nhất
 *      + sp_stats_posts_by_status(): Thống kê số bài viết theo từng trạng thái kiểm duyệt
 *  - Các SQL Views cho truy vấn hay dùng:
 *      + vw_post_details
 *      + vw_category_summary
 *      + vw_user_summary
 *      + vw_popular_tags
 */
class StatsRepository extends BaseRepository
{
    protected string $table = 'posts';
    private static bool $viewsAndRoutinesChecked = false;

    public function __construct(?PDO $db = null)
    {
        parent::__construct($db);
        $this->ensureDatabaseViewsAndProcedures();
    }

    /**
     * Đảm bảo các SQL Views (vw_*) và Stored Procedures thống kê (sp_stats_*) đã được khởi tạo trong MariaDB.
     * Giúp ứng dụng chạy ngay trên CSDL hiện hành mà không bắt buộc người dùng phải import lại toàn bộ schema.
     */
    public function ensureDatabaseViewsAndProcedures(): void
    {
        if (self::$viewsAndRoutinesChecked) {
            return;
        }
        self::$viewsAndRoutinesChecked = true;

        try {
            $driver = (string)$this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver !== 'mysql') {
                return;
            }

            // Chỉ tạo khi còn thiếu View / Procedure — tránh chạy DDL (DROP/CREATE) ở mọi request.
            if ($this->requiredObjectsExist()) {
                return;
            }

            // 1. Tạo các SQL Views cho truy vấn hay dùng
            $this->db->exec("
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
                INNER JOIN categories c ON c.id = p.category_id
            ");

            $this->db->exec("
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
                GROUP BY c.id, c.name, c.description
            ");

            $this->db->exec("
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
                INNER JOIN roles r ON r.id = u.role_id
            ");

            $this->db->exec("
                CREATE OR REPLACE VIEW vw_popular_tags AS
                SELECT
                    t.id,
                    t.name,
                    COUNT(pt.post_id) AS post_count
                FROM tags t
                INNER JOIN post_tags pt ON pt.tag_id = t.id
                GROUP BY t.id, t.name
                ORDER BY post_count DESC, t.name ASC
            ");

            // 2. Tạo các Stored Procedures cho thống kê
            $this->db->exec("DROP PROCEDURE IF EXISTS sp_dashboard_stats");
            $this->db->exec("
                CREATE PROCEDURE sp_dashboard_stats()
                BEGIN
                    SELECT 
                        (SELECT COUNT(*) FROM users) AS total_users,
                        (SELECT COUNT(*) FROM posts) AS total_posts,
                        (SELECT COUNT(*) FROM posts WHERE status = 'published') AS published_posts,
                        (SELECT COUNT(*) FROM posts WHERE status = 'pending') AS pending_posts,
                        (SELECT COUNT(*) FROM categories) AS total_categories,
                        (SELECT COUNT(*) FROM comments) AS total_comments,
                        (SELECT COUNT(*) FROM products WHERE is_shared = 1) AS total_products;
                END
            ");

            $this->db->exec("DROP PROCEDURE IF EXISTS sp_stats_top_posts");
            $this->db->exec("
                CREATE PROCEDURE sp_stats_top_posts(IN p_limit INT)
                BEGIN
                    DECLARE v_limit INT DEFAULT 5;
                    SET v_limit = IF(p_limit IS NULL OR p_limit <= 0, 5, p_limit);

                    SELECT
                        id, user_id, category_id, title, location, status, created_at,
                        username, category_name, image_url,
                        vote_score, upvotes, downvotes, likes, comments_count, hot_score
                    FROM vw_post_details
                    WHERE status = 'published'
                    ORDER BY hot_score DESC, vote_score DESC, comments_count DESC, created_at DESC
                    LIMIT v_limit;
                END
            ");

            $this->db->exec("DROP PROCEDURE IF EXISTS sp_stats_posts_by_category");
            $this->db->exec("
                CREATE PROCEDURE sp_stats_posts_by_category()
                BEGIN
                    SELECT
                        id, name, name AS category, description,
                        post_count AS total_posts, post_count, published_count, pending_count, latest_post_at
                    FROM vw_category_summary
                    ORDER BY post_count DESC, id ASC;
                END
            ");

            $this->db->exec("DROP PROCEDURE IF EXISTS sp_stats_top_users");
            $this->db->exec("
                CREATE PROCEDURE sp_stats_top_users(IN p_limit INT)
                BEGIN
                    DECLARE v_limit INT DEFAULT 5;
                    SET v_limit = IF(p_limit IS NULL OR p_limit <= 0, 5, p_limit);

                    SELECT
                        id, username, email, role_name, avatar_path, created_at,
                        total_posts, published_posts, total_comments, total_karma
                    FROM vw_user_summary
                    ORDER BY total_posts DESC, total_karma DESC, total_comments DESC, id ASC
                    LIMIT v_limit;
                END
            ");

            $this->db->exec("DROP PROCEDURE IF EXISTS sp_stats_posts_by_status");
            $this->db->exec("
                CREATE PROCEDURE sp_stats_posts_by_status()
                BEGIN
                    SELECT
                        COUNT(*) AS total_all,
                        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS total_published,
                        SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS total_pending,
                        SUM(CASE WHEN status = 'rejected'  THEN 1 ELSE 0 END) AS total_rejected,
                        SUM(CASE WHEN status = 'hidden'    THEN 1 ELSE 0 END) AS total_hidden
                    FROM posts;
                END
            ");

            $this->db->exec("DROP PROCEDURE IF EXISTS sp_stats_daily_activity");
            $this->db->exec("
                CREATE PROCEDURE sp_stats_daily_activity(IN p_days INT)
                BEGIN
                    DECLARE v_days INT DEFAULT 7;
                    SET v_days = IF(p_days IS NULL OR p_days <= 0, 7, IF(p_days > 30, 30, p_days));

                    SELECT
                        d.stat_date,
                        DATE_FORMAT(d.stat_date, '%d/%m') AS date_label,
                        COALESCE(u.new_users, 0) AS new_users,
                        COALESCE(p.new_posts, 0) AS new_posts,
                        COALESCE(c.new_comments, 0) AS new_comments
                    FROM (
                        SELECT DATE_SUB(CURDATE(), INTERVAL seq.n DAY) AS stat_date
                        FROM (
                            SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                            UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
                            UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
                            UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
                            UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
                        ) AS seq
                        WHERE seq.n < v_days
                    ) AS d
                    LEFT JOIN (
                        SELECT DATE(created_at) AS reg_date, COUNT(*) AS new_users
                        FROM users GROUP BY DATE(created_at)
                    ) AS u ON u.reg_date = d.stat_date
                    LEFT JOIN (
                        SELECT DATE(created_at) AS post_date, COUNT(*) AS new_posts
                        FROM posts GROUP BY DATE(created_at)
                    ) AS p ON p.post_date = d.stat_date
                    LEFT JOIN (
                        SELECT DATE(created_at) AS comment_date, COUNT(*) AS new_comments
                        FROM comments GROUP BY DATE(created_at)
                    ) AS c ON c.comment_date = d.stat_date
                    ORDER BY d.stat_date ASC;
                END
            ");
        } catch (PDOException $e) {
            error_log('Notice: ensureDatabaseViewsAndProcedures skipped: ' . $e->getMessage());
        }
    }

    /**
     * Gọi Stored Procedure `sp_stats_daily_activity(p_days)` để thống kê số người dùng mới,
     * bài viết mới và bình luận mới theo từng ngày (dữ liệu cho biểu đồ Chart.js trên Admin Dashboard).
     *
     * Luôn trả về đủ `$days` ngày liên tiếp (ngày không phát sinh dữ liệu có giá trị 0).
     * Mỗi phần tử: stat_date (Y-m-d), date_label (d/m), new_users, new_posts, new_comments.
     */
    public function getDailyActivity(int $days = 7): array
    {
        $days = max(1, min(30, $days));

        try {
            $rows = $this->callProcedure('sp_stats_daily_activity', [$days]);
            if (count($rows) === $days) {
                return $this->normalizeDailyRows($rows);
            }
        } catch (PDOException $e) {
            error_log('Notice: sp_stats_daily_activity fallback: ' . $e->getMessage());
        }

        return $this->buildDailyActivityFallback($days);
    }

    /**
     * Chuẩn hóa kiểu dữ liệu số nguyên cho từng dòng thống kê theo ngày.
     */
    private function normalizeDailyRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'stat_date'    => (string)($row['stat_date'] ?? ''),
                'date_label'   => (string)($row['date_label'] ?? ''),
                'new_users'    => (int)($row['new_users'] ?? 0),
                'new_posts'    => (int)($row['new_posts'] ?? 0),
                'new_comments' => (int)($row['new_comments'] ?? 0),
            ];
        }
        return $result;
    }

    /**
     * Phương án dự phòng (khi chưa tạo được Stored Procedure): gom nhóm bằng Prepared Statement
     * rồi điền đủ các ngày trống bằng PHP.
     */
    private function buildDailyActivityFallback(int $days): array
    {
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days')) . ' 00:00:00';
        $counts = [
            'new_users'    => $this->countPerDay('users', $start),
            'new_posts'    => $this->countPerDay('posts', $start),
            'new_comments' => $this->countPerDay('comments', $start),
        ];

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $ts = strtotime('-' . $i . ' days');
            $date = date('Y-m-d', $ts);
            $result[] = [
                'stat_date'    => $date,
                'date_label'   => date('d/m', $ts),
                'new_users'    => $counts['new_users'][$date] ?? 0,
                'new_posts'    => $counts['new_posts'][$date] ?? 0,
                'new_comments' => $counts['new_comments'][$date] ?? 0,
            ];
        }
        return $result;
    }

    /**
     * Đếm số bản ghi mới theo từng ngày của một bảng (tên bảng nằm trong whitelist cố định).
     *
     * @return array<string,int> Map 'Y-m-d' => số lượng
     */
    private function countPerDay(string $table, string $start): array
    {
        if (!in_array($table, ['users', 'posts', 'comments'], true)) {
            return [];
        }

        try {
            $rows = $this->query(
                "SELECT DATE(created_at) AS day_key, COUNT(*) AS total
                 FROM {$table}
                 WHERE created_at >= :start
                 GROUP BY DATE(created_at)",
                ['start' => $start]
            );
        } catch (PDOException $e) {
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $map[(string)$row['day_key']] = (int)$row['total'];
        }
        return $map;
    }

    /**
     * Kiểm tra 4 SQL Views và 6 Stored Procedures thống kê đã tồn tại trong CSDL hiện hành chưa.
     * Trả về false khi thiếu hoặc không đọc được information_schema (khi đó sẽ thử tạo).
     */
    private function requiredObjectsExist(): bool
    {
        $views = ['vw_post_details', 'vw_category_summary', 'vw_user_summary', 'vw_popular_tags'];
        $procedures = [
            'sp_dashboard_stats', 'sp_stats_top_posts', 'sp_stats_posts_by_category',
            'sp_stats_top_users', 'sp_stats_posts_by_status', 'sp_stats_daily_activity',
        ];

        try {
            $viewMarks = implode(',', array_fill(0, count($views), '?'));
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM information_schema.VIEWS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$viewMarks})"
            );
            $stmt->execute($views);
            $viewCount = (int)$stmt->fetchColumn();

            $procMarks = implode(',', array_fill(0, count($procedures), '?'));
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM information_schema.ROUTINES
                 WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'PROCEDURE' AND ROUTINE_NAME IN ({$procMarks})"
            );
            $stmt->execute($procedures);
            $procCount = (int)$stmt->fetchColumn();

            return $viewCount === count($views) && $procCount === count($procedures);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Gọi Stored Procedure `sp_dashboard_stats()` để lấy toàn bộ chỉ số tổng quan hệ thống.
     */
    public function getDashboardStats(): array
    {
        $defaults = [
            'total_users'      => 0,
            'total_posts'      => 0,
            'published_posts'  => 0,
            'pending_posts'    => 0,
            'total_categories' => 0,
            'total_comments'   => 0,
            'total_products'   => 0,
        ];

        try {
            $rows = $this->callProcedure('sp_dashboard_stats');
            if (!empty($rows[0])) {
                return array_merge($defaults, array_map('intval', $rows[0]));
            }
        } catch (PDOException $e) {
            try {
                $row = $this->queryOne("
                    SELECT 
                        (SELECT COUNT(*) FROM users) AS total_users,
                        (SELECT COUNT(*) FROM posts) AS total_posts,
                        (SELECT COUNT(*) FROM posts WHERE status = 'published') AS published_posts,
                        (SELECT COUNT(*) FROM posts WHERE status = 'pending') AS pending_posts,
                        (SELECT COUNT(*) FROM categories) AS total_categories,
                        (SELECT COUNT(*) FROM comments) AS total_comments,
                        (SELECT COUNT(*) FROM products) AS total_products
                ");
                if ($row) {
                    return array_merge($defaults, array_map('intval', $row));
                }
            } catch (PDOException $ex) {
                error_log('StatsRepository::getDashboardStats fallback error: ' . $ex->getMessage());
            }
        }

        return $defaults;
    }

    /**
     * Gọi Stored Procedure `sp_stats_top_posts(p_limit)` (trên View `vw_post_details`)
     * để lấy danh sách Top bài viết có điểm tương tác cao nhất.
     */
    public function getTopPosts(int $limit = 5): array
    {
        $limit = max(1, $limit);
        try {
            return $this->callProcedure('sp_stats_top_posts', [$limit]);
        } catch (PDOException $e) {
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM vw_post_details
                    WHERE status = 'published'
                    ORDER BY hot_score DESC, vote_score DESC, comments_count DESC, created_at DESC
                    LIMIT :limit
                ");
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            } catch (PDOException $ex) {
                return [];
            }
        }
    }

    /**
     * Gọi Stored Procedure `sp_stats_posts_by_category()` (hoặc `lk_posts_incategory()`)
     * kết hợp View `vw_category_summary` để thống kê số lượng bài viết theo từng danh mục.
     */
    public function getPostsByCategory(): array
    {
        try {
            return $this->callProcedure('sp_stats_posts_by_category');
        } catch (PDOException $e) {
            try {
                return $this->callProcedure('lk_posts_incategory');
            } catch (PDOException $e2) {
                try {
                    return $this->query('SELECT *, post_count AS total_posts FROM vw_category_summary ORDER BY post_count DESC, id ASC');
                } catch (PDOException $e3) {
                    return [];
                }
            }
        }
    }

    /**
     * Gọi Stored Procedure `sp_stats_top_users(p_limit)` (hoặc `lk_posts_byuser()`)
     * kết hợp View `vw_user_summary` để thống kê Top thành viên đóng góp nhiều bài viết nhất.
     */
    public function getTopUsers(int $limit = 5): array
    {
        $limit = max(1, $limit);
        try {
            return $this->callProcedure('sp_stats_top_users', [$limit]);
        } catch (PDOException $e) {
            try {
                return array_slice($this->callProcedure('lk_posts_byuser'), 0, $limit);
            } catch (PDOException $e2) {
                try {
                    $stmt = $this->db->prepare('
                        SELECT * FROM vw_user_summary
                        ORDER BY total_posts DESC, total_karma DESC, total_comments DESC, id ASC
                        LIMIT :limit
                    ');
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->execute();
                    return $stmt->fetchAll();
                } catch (PDOException $e3) {
                    return [];
                }
            }
        }
    }

    /**
     * Gọi Stored Procedure `sp_stats_posts_by_status()` để thống kê số bài viết theo từng trạng thái.
     */
    public function getPostsByStatus(): array
    {
        $defaults = [
            'all'       => 0,
            'published' => 0,
            'pending'   => 0,
            'rejected'  => 0,
            'hidden'    => 0,
        ];

        try {
            $rows = $this->callProcedure('sp_stats_posts_by_status');
            if (!empty($rows[0])) {
                return [
                    'all'       => (int)($rows[0]['total_all'] ?? 0),
                    'published' => (int)($rows[0]['total_published'] ?? 0),
                    'pending'   => (int)($rows[0]['total_pending'] ?? 0),
                    'rejected'  => (int)($rows[0]['total_rejected'] ?? 0),
                    'hidden'    => (int)($rows[0]['total_hidden'] ?? 0),
                ];
            }
        } catch (PDOException $e) {
            try {
                $row = $this->queryOne("
                    SELECT
                        COUNT(*) AS total_all,
                        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS total_published,
                        SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS total_pending,
                        SUM(CASE WHEN status = 'rejected'  THEN 1 ELSE 0 END) AS total_rejected,
                        SUM(CASE WHEN status = 'hidden'    THEN 1 ELSE 0 END) AS total_hidden
                    FROM posts
                ");
                if ($row) {
                    return [
                        'all'       => (int)($row['total_all'] ?? 0),
                        'published' => (int)($row['total_published'] ?? 0),
                        'pending'   => (int)($row['total_pending'] ?? 0),
                        'rejected'  => (int)($row['total_rejected'] ?? 0),
                        'hidden'    => (int)($row['total_hidden'] ?? 0),
                    ];
                }
            } catch (PDOException $ex) {
                // Giữ mặc định
            }
        }

        return $defaults;
    }
}

