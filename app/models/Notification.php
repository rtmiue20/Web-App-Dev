<?php

class Notification extends Model
{
    private static bool $tableEnsured = false;
    private static array $usersNotifiedInRequest = [];
    private static array $syncedUsers = [];

    /**
     * Đảm bảo bảng notifications luôn tồn tại trong MariaDB / MySQL / SQLite
     */
    public static function ensureTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }
        try {
            $db = Database::getConnection();
            $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $db->exec("
                    CREATE TABLE IF NOT EXISTS notifications (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL,
                        actor_id INTEGER NOT NULL,
                        type VARCHAR(50) NOT NULL,
                        target_id INTEGER NOT NULL DEFAULT 0,
                        content VARCHAR(255) NOT NULL,
                        is_read INTEGER NOT NULL DEFAULT 0,
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } else {
                $db->exec("
                    CREATE TABLE IF NOT EXISTS notifications (
                        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        user_id    INT UNSIGNED NOT NULL,
                        actor_id   INT UNSIGNED NOT NULL,
                        type       VARCHAR(50) NOT NULL,
                        target_id  INT UNSIGNED NOT NULL DEFAULT 0,
                        content    VARCHAR(255) NOT NULL,
                        is_read    TINYINT(1) NOT NULL DEFAULT 0,
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_notif_user_read (user_id, is_read, created_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
            self::$tableEnsured = true;
        } catch (Throwable $e) {
            error_log('Notification::ensureTable error: ' . $e->getMessage());
        }
    }

    /**
     * Tạo thông báo mới cho người dùng
     */
    public static function send(
        int $userId,
        int $actorId,
        string $type,
        int $targetId,
        string $content
    ): int {
        if ($userId <= 0 || $actorId <= 0 || trim($content) === '') {
            return 0;
        }

        self::ensureTable();

        $safeContent = mb_substr(trim($content), 0, 250);
        $cleanTargetId = max(0, $targetId);
        $now = date('Y-m-d H:i:s');

        try {
            // Với các thao tác bật/tắt (like, bookmark, follow) trên cùng 1 đối tượng, cập nhật lại bản ghi hiện có
            $toggleTypes = ['like_post', 'like_comment', 'bookmark_post', 'follow_user', 'follow_category'];
            if (in_array($type, $toggleTypes, true) && $cleanTargetId > 0) {
                $checkSql = "SELECT id FROM notifications 
                             WHERE user_id = :uid AND actor_id = :aid AND type = :t AND target_id = :tid 
                             LIMIT 1";
                $existing = Database::query($checkSql, [
                    'uid' => $userId,
                    'aid' => $actorId,
                    't'   => $type,
                    'tid' => $cleanTargetId,
                ]);
            } else {
                // Với bình luận, bài viết, thông báo hệ thống: chỉ gộp nếu trùng hoàn toàn nội dung
                $checkSql = "SELECT id FROM notifications 
                             WHERE user_id = :uid AND actor_id = :aid AND type = :t AND target_id = :tid AND content = :content
                             LIMIT 1";
                $existing = Database::query($checkSql, [
                    'uid'     => $userId,
                    'aid'     => $actorId,
                    't'       => $type,
                    'tid'     => $cleanTargetId,
                    'content' => $safeContent,
                ]);
            }

            if (!empty($existing)) {
                $upSql = "UPDATE notifications SET is_read = 0, created_at = :now, content = :content WHERE id = :id";
                Database::execute($upSql, [
                    'now'     => $now,
                    'content' => $safeContent,
                    'id'      => (int)$existing[0]['id'],
                ]);
                self::$usersNotifiedInRequest[$userId] = true;
                return (int)$existing[0]['id'];
            }

            $insSql = "INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
                       VALUES (:uid, :aid, :t, :tid, :content, 0, :now)";
            $db = Database::getConnection();
            $stmt = $db->prepare($insSql);
            $stmt->execute([
                ':uid'     => $userId,
                ':aid'     => $actorId,
                ':t'       => $type,
                ':tid'     => $cleanTargetId,
                ':content' => $safeContent,
                ':now'     => $now,
            ]);
            self::$usersNotifiedInRequest[$userId] = true;
            return (int)$db->lastInsertId();
        } catch (Throwable $e) {
            error_log('Error sending notification: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gửi thông báo đến những người theo dõi tác giả và theo dõi chuyên mục khi có bài viết mới được duyệt/đăng
     */
    public static function notifyFollowersOnNewPost(int $authorId, string $authorName, int $categoryId, int $postId, string $postTitle): void
    {
        if ($postId <= 0 || $authorId <= 0) {
            return;
        }

        self::ensureTable();
        $shortTitle = mb_substr(trim($postTitle), 0, 38);
        $notifiedUserIds = [$authorId => true];

        // 1. Người theo dõi tác giả (user_follows)
        try {
            $followers = Database::query(
                "SELECT follower_id FROM user_follows WHERE following_id = :aid",
                ['aid' => $authorId]
            );
            foreach ($followers as $row) {
                $fid = (int)($row['follower_id'] ?? 0);
                if ($fid > 0 && empty($notifiedUserIds[$fid])) {
                    self::send(
                        $fid,
                        $authorId,
                        'new_post_followed',
                        $postId,
                        "{$authorName} vừa đăng bài viết mới: '{$shortTitle}'."
                    );
                    $notifiedUserIds[$fid] = true;
                }
            }
        } catch (Throwable $e) {
        }

        // 2. Người theo dõi chuyên mục (category_follows)
        if ($categoryId > 0) {
            try {
                $catFollowers = Database::query(
                    "SELECT cf.user_id, c.name AS category_name
                     FROM category_follows cf
                     JOIN categories c ON c.id = cf.category_id
                     WHERE cf.category_id = :cid",
                    ['cid' => $categoryId]
                );
                foreach ($catFollowers as $row) {
                    $uid = (int)($row['user_id'] ?? 0);
                    $catName = mb_substr((string)($row['category_name'] ?? 'Chuyên mục'), 0, 28);
                    if ($uid > 0 && empty($notifiedUserIds[$uid])) {
                        self::send(
                            $uid,
                            $authorId,
                            'new_post_followed',
                            $postId,
                            "Chuyên mục '{$catName}' có bài viết mới từ {$authorName}: '{$shortTitle}'."
                        );
                        $notifiedUserIds[$uid] = true;
                    }
                }
            } catch (Throwable $e) {
            }
        }
    }

    /**
     * Đồng bộ thông báo Flash Toast (Thành công / Cảnh báo / Thông tin) vào Chuông thông báo
     * Đảm bảo mỗi khi hiện Toast trên màn hình thì trong "Thông báo của bạn" luôn có bản ghi tương ứng.
     */
    public static function recordFlashMessage(int $userId, string $flashType, string $message): void
    {
        if ($userId <= 0 || trim($message) === '') {
            return;
        }

        // Nếu trong request hiện tại controller đã tạo thông báo chuyên biệt cho user này rồi thì không tạo trùng
        if (!empty(self::$usersNotifiedInRequest[$userId])) {
            return;
        }

        self::ensureTable();

        try {
            $notifType = $flashType === 'warning' ? 'system' : 'activity';
            self::send($userId, $userId, $notifType, 0, trim($message));
        } catch (Throwable $e) {
            error_log('Error recording flash notification: ' . $e->getMessage());
        }
    }

    /**
     * Đồng bộ dữ liệu hoạt động thực tế (bài viết, bình luận, lượt thích, lưu bài, đánh giá địa điểm, checklist, chuyến đi)
     * vào bảng notifications để đảm bảo tính nhất quán dữ liệu.
     */
    public static function syncUserActivities(int $userId): void
    {
        if ($userId <= 0 || !empty(self::$syncedUsers[$userId])) {
            return;
        }
        self::$syncedUsers[$userId] = true;

        self::ensureTable();

        try {
            $isMod = class_exists('Auth') && Auth::isModerator();

            // 1. Đồng bộ Đánh giá địa điểm (place_reviews)
            try {
                $reviews = Database::query(
                    "SELECT r.post_id, r.user_id, r.rating, r.updated_at, p.location, p.title, p.user_id AS post_owner_id, u.username
                     FROM place_reviews r
                     JOIN posts p ON p.id = r.post_id
                     JOIN users u ON u.id = r.user_id
                     WHERE r.user_id = :uid1 OR p.user_id = :uid2" . ($isMod ? " OR 1=1" : "") . "
                     ORDER BY r.updated_at DESC LIMIT 6",
                    ['uid1' => $userId, 'uid2' => $userId]
                );
                foreach ($reviews as $rev) {
                    $postId = (int)($rev['post_id'] ?? 0);
                    $revUserId = (int)($rev['user_id'] ?? 0);
                    $loc = mb_substr((string)($rev['location'] ?: $rev['title'] ?: 'địa điểm dã ngoại'), 0, 35);
                    $rating = (int)($rev['rating'] ?? 5);
                    $createdAt = $rev['updated_at'] ?? date('Y-m-d H:i:s');

                    if ($revUserId === $userId) {
                        $content = "Cảm ơn bạn! Đã lưu đánh giá {$rating} sao cho địa điểm: '{$loc}'.";
                    } else {
                        $actorName = $rev['username'] ?? 'Thành viên';
                        $content = "{$actorName} đã đánh giá {$rating} sao cho địa điểm: '{$loc}'.";
                    }

                    $exists = Database::query(
                        "SELECT id FROM notifications WHERE user_id = :uid AND actor_id = :aid AND type = 'review_place' AND target_id = :tid LIMIT 1",
                        ['uid' => $userId, 'aid' => $revUserId, 'tid' => $postId]
                    );
                    if (empty($exists)) {
                        Database::execute(
                            "INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
                             VALUES (:uid, :aid, 'review_place', :tid, :content, 0, :cat)",
                            [
                                'uid'     => $userId,
                                'aid'     => $revUserId,
                                'tid'     => $postId,
                                'content' => mb_substr($content, 0, 250),
                                'cat'     => $createdAt,
                            ]
                        );
                    }
                }
            } catch (Throwable $revEx) {
            }

            // 2. Đồng bộ Bài viết (posts) của user hoặc bài viết cần kiểm duyệt cho Admin
            try {
                $posts = Database::query(
                    "SELECT p.id, p.user_id, p.title, p.status, p.created_at, u.username
                     FROM posts p
                     JOIN users u ON u.id = p.user_id
                     WHERE p.user_id = :uid" . ($isMod ? " OR p.status = 'pending' OR p.id <= 5" : "") . "
                     ORDER BY p.created_at DESC LIMIT 6",
                    ['uid' => $userId]
                );
                foreach ($posts as $p) {
                    $pid = (int)($p['id'] ?? 0);
                    $pAuthorId = (int)($p['user_id'] ?? 0);
                    $shortTitle = mb_substr((string)($p['title'] ?? ''), 0, 38);
                    $status = (string)($p['status'] ?? 'published');
                    $createdAt = $p['created_at'] ?? date('Y-m-d H:i:s');

                    if ($pAuthorId === $userId) {
                        $type = 'create_post';
                        $content = $status === 'pending'
                            ? "Bài viết '{$shortTitle}' của bạn đang chờ duyệt."
                            : "Bạn đã đăng bài viết chia sẻ: '{$shortTitle}'.";
                    } elseif ($status === 'pending' && $isMod) {
                        $type = 'admin_pending_post';
                        $content = ($p['username'] ?? 'Thành viên') . " đã gửi bài viết mới chờ duyệt: '{$shortTitle}'.";
                    } else {
                        $type = 'create_post';
                        $content = ($p['username'] ?? 'Thành viên') . " đã chia sẻ bài viết: '{$shortTitle}'.";
                    }

                    $exists = Database::query(
                        "SELECT id FROM notifications WHERE user_id = :uid AND type = :t AND target_id = :tid LIMIT 1",
                        ['uid' => $userId, 't' => $type, 'tid' => $pid]
                    );
                    if (empty($exists)) {
                        Database::execute(
                            "INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
                             VALUES (:uid, :aid, :t, :tid, :content, 0, :cat)",
                            [
                                'uid'     => $userId,
                                'aid'     => $pAuthorId > 0 ? $pAuthorId : $userId,
                                't'       => $type,
                                'tid'     => $pid,
                                'content' => mb_substr($content, 0, 250),
                                'cat'     => $createdAt,
                            ]
                        );
                    }
                }
            } catch (Throwable $postEx) {
            }

            // 3. Đồng bộ Bình luận bài viết (comments)
            try {
                $comments = Database::query(
                    "SELECT c.id, c.post_id, c.user_id, c.parent_id, c.created_at, p.title, p.user_id AS post_owner_id, u.username
                     FROM comments c
                     JOIN posts p ON p.id = c.post_id
                     JOIN users u ON u.id = c.user_id
                     WHERE c.user_id = :uid1 OR p.user_id = :uid2
                     ORDER BY c.created_at DESC LIMIT 6",
                    ['uid1' => $userId, 'uid2' => $userId]
                );
                foreach ($comments as $cm) {
                    $pid = (int)($cm['post_id'] ?? 0);
                    $cmUserId = (int)($cm['user_id'] ?? 0);
                    $shortTitle = mb_substr((string)($cm['title'] ?? 'bài viết'), 0, 35);
                    $createdAt = $cm['created_at'] ?? date('Y-m-d H:i:s');
                    $type = !empty($cm['parent_id']) ? 'reply_comment' : 'comment_post';

                    if ($cmUserId === $userId) {
                        $content = !empty($cm['parent_id'])
                            ? "Bạn đã trả lời bình luận trong bài viết: '{$shortTitle}'."
                            : "Bạn đã bình luận tại bài viết: '{$shortTitle}'.";
                    } else {
                        $actorName = $cm['username'] ?? 'Thành viên';
                        $content = "{$actorName} đã bình luận về bài viết '{$shortTitle}' của bạn.";
                    }

                    $exists = Database::query(
                        "SELECT id FROM notifications WHERE user_id = :uid AND actor_id = :aid AND type = :t AND target_id = :tid LIMIT 1",
                        ['uid' => $userId, 'aid' => $cmUserId, 't' => $type, 'tid' => $pid]
                    );
                    if (empty($exists)) {
                        Database::execute(
                            "INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
                             VALUES (:uid, :aid, :t, :tid, :content, 0, :cat)",
                            [
                                'uid'     => $userId,
                                'aid'     => $cmUserId,
                                't'       => $type,
                                'tid'     => $pid,
                                'content' => mb_substr($content, 0, 250),
                                'cat'     => $createdAt,
                            ]
                        );
                    }
                }
            } catch (Throwable $cmEx) {
            }

            // 4. Đồng bộ Lượt thích / bình chọn bài viết (post_likes) & Lưu bài viết (post_bookmarks)
            try {
                $likes = Database::query(
                    "SELECT pl.post_id, pl.user_id, pl.vote, pl.created_at, p.title, p.user_id AS post_owner_id, u.username
                     FROM post_likes pl
                     JOIN posts p ON p.id = pl.post_id
                     JOIN users u ON u.id = pl.user_id
                     WHERE pl.user_id = :uid1 OR p.user_id = :uid2
                     ORDER BY pl.created_at DESC LIMIT 5",
                    ['uid1' => $userId, 'uid2' => $userId]
                );
                foreach ($likes as $lk) {
                    $pid = (int)($lk['post_id'] ?? 0);
                    $lkUserId = (int)($lk['user_id'] ?? 0);
                    $vote = (int)($lk['vote'] ?? 1);
                    $shortTitle = mb_substr((string)($lk['title'] ?? 'bài viết'), 0, 35);
                    $createdAt = $lk['created_at'] ?? date('Y-m-d H:i:s');

                    if ($lkUserId === $userId) {
                        $content = $vote >= 0
                            ? "Bạn đã thích (Upvote) bài viết: '{$shortTitle}'."
                            : "Bạn đã đánh giá (Downvote) bài viết: '{$shortTitle}'.";
                    } else {
                        $actorName = $lk['username'] ?? 'Thành viên';
                        $content = "{$actorName} đã thích bài viết '{$shortTitle}' của bạn.";
                    }

                    $exists = Database::query(
                        "SELECT id FROM notifications WHERE user_id = :uid AND actor_id = :aid AND type = 'like_post' AND target_id = :tid LIMIT 1",
                        ['uid' => $userId, 'aid' => $lkUserId, 'tid' => $pid]
                    );
                    if (empty($exists)) {
                        Database::execute(
                            "INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
                             VALUES (:uid, :aid, 'like_post', :tid, :content, 0, :cat)",
                            [
                                'uid'     => $userId,
                                'aid'     => $lkUserId,
                                'tid'     => $pid,
                                'content' => mb_substr($content, 0, 250),
                                'cat'     => $createdAt,
                            ]
                        );
                    }
                }
            } catch (Throwable $lkEx) {
            }

            // 5. Đồng bộ Checklist (trip_checklists) của user
            try {
                $checklists = Database::query(
                    "SELECT id, title, created_at FROM trip_checklists WHERE user_id = :uid ORDER BY created_at DESC LIMIT 3",
                    ['uid' => $userId]
                );
                foreach ($checklists as $chk) {
                    $cid = (int)($chk['id'] ?? 0);
                    $shortTitle = mb_substr((string)($chk['title'] ?? ''), 0, 38);
                    $exists = Database::query(
                        "SELECT id FROM notifications WHERE user_id = :uid AND type = 'create_checklist' AND target_id = :tid LIMIT 1",
                        ['uid' => $userId, 'tid' => $cid]
                    );
                    if (empty($exists)) {
                        Database::execute(
                            "INSERT INTO notifications (user_id, actor_id, type, target_id, content, is_read, created_at)
                             VALUES (:uid, :aid, 'create_checklist', :tid, :content, 0, :cat)",
                            [
                                'uid'     => $userId,
                                'aid'     => $userId,
                                'tid'     => $cid,
                                'content' => "Bạn đã tạo checklist dã ngoại: '{$shortTitle}'.",
                                'cat'     => $chk['created_at'] ?? date('Y-m-d H:i:s'),
                            ]
                        );
                    }
                }
            } catch (Throwable $chkEx) {
            }
        } catch (Throwable $e) {
            error_log('Notification::syncUserActivities error: ' . $e->getMessage());
        }
    }

    /**
     * Gửi thông báo đến toàn bộ Admin & Moderator trong hệ thống
     */
    public static function notifyAdmins(
        int $actorId,
        string $type,
        int $targetId,
        string $content
    ): void {
        self::ensureTable();
        try {
            $admins = Database::query(
                "SELECT u.id FROM users u
                 LEFT JOIN roles r ON r.id = u.role_id
                 WHERE r.name IN ('admin', 'moderator') OR u.role_id IN (2, 3)"
            );
            foreach ($admins as $adm) {
                $admId = (int)($adm['id'] ?? 0);
                if ($admId > 0) {
                    self::send($admId, $actorId > 0 ? $actorId : $admId, $type, $targetId, $content);
                }
            }
        } catch (Throwable $e) {
            error_log('Error notifying admins: ' . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách thông báo của 1 user
     */
    public function getByUserId(int $userId, int $limit = 20): array
    {
        if ($userId <= 0) {
            return [];
        }

        self::ensureTable();
        self::syncUserActivities($userId);

        try {
            $sql = "SELECT n.*, COALESCE(u.username, 'Hệ thống') AS actor_username
                    FROM notifications n
                    LEFT JOIN users u ON u.id = n.actor_id
                    WHERE n.user_id = :uid
                    ORDER BY n.created_at DESC, n.id DESC
                    LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('Error getting notifications: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Đếm số thông báo chưa đọc
     */
    public function getUnreadCount(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        self::ensureTable();

        try {
            $rows = Database::query(
                "SELECT COUNT(*) AS total FROM notifications WHERE user_id = :uid AND is_read = 0",
                ['uid' => $userId]
            );
            return (int)($rows[0]['total'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(int $id, int $userId): bool
    {
        self::ensureTable();
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid";
            Database::execute($sql, ['id' => $id, 'uid' => $userId]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Đánh dấu tất cả thông báo của user là đã đọc
     */
    public function markAllAsRead(int $userId): bool
    {
        self::ensureTable();
        try {
            $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = :uid";
            Database::execute($sql, ['uid' => $userId]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}
