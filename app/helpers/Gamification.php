<?php

class Gamification
{
    /**
     * Tính toán tổng điểm uy tín và danh hiệu huy hiệu của thành viên
     * Công thức điểm:
     * - Mỗi bài viết được duyệt/đăng: +20 điểm
     * - Mỗi upvote / like nhận được từ người khác: +5 điểm
     * - Mỗi bình luận chia sẻ: +2 điểm
     * - Mỗi like trên bình luận: +3 điểm
     */
    public static function calculate(int $userId): array
    {
        try {
            // 1. Số bài viết được duyệt
            $postRows = Database::query(
                "SELECT COUNT(*) AS total FROM posts WHERE user_id = :uid AND status = 'published'",
                ['uid' => $userId]
            );
            $postsCount = (int)($postRows[0]['total'] ?? 0);

            // 2. Tổng số upvotes bài viết nhận được từ người khác
            $likeRows = Database::query(
                "SELECT COUNT(*) AS total 
                 FROM post_likes pl 
                 JOIN posts p ON p.id = pl.post_id 
                 WHERE p.user_id = :uid AND pl.user_id != :actor_id AND pl.vote = 1",
                ['uid' => $userId, 'actor_id' => $userId]
            );
            $postLikesCount = (int)($likeRows[0]['total'] ?? 0);

            // 3. Số bình luận
            $cmRows = Database::query(
                "SELECT COUNT(*) AS total FROM comments WHERE user_id = :uid",
                ['uid' => $userId]
            );
            $commentsCount = (int)($cmRows[0]['total'] ?? 0);

            // 4. Số like trên bình luận nhận được
            $cmLikeRows = Database::query(
                "SELECT COUNT(*) AS total 
                 FROM comment_likes cl 
                 JOIN comments c ON c.id = cl.comment_id 
                 WHERE c.user_id = :uid AND cl.user_id != :actor_id",
                ['uid' => $userId, 'actor_id' => $userId]
            );
            $commentLikesCount = (int)($cmLikeRows[0]['total'] ?? 0);

            $totalPoints = ($postsCount * 20) + ($postLikesCount * 5) + ($commentsCount * 2) + ($commentLikesCount * 3);

            return self::getBadgeInfo($totalPoints, [
                'posts_count'         => $postsCount,
                'post_likes_count'    => $postLikesCount,
                'comments_count'      => $commentsCount,
                'comment_likes_count' => $commentLikesCount,
            ]);
        } catch (Exception $e) {
            error_log('Error calculating gamification: ' . $e->getMessage());
            return self::getBadgeInfo(0);
        }
    }

    /**
     * Xác định huy hiệu, cấp bậc và tiến độ điểm
     */
    public static function getBadgeInfo(int $points, array $stats = []): array
    {
        $tiers = [
            [
                'min' => 500,
                'level' => 4,
                'title' => 'Đại sứ dã ngoại',
                'sub' => 'Wilderness Legend',
                'icon' => 'bi-gem',
                'color' => '#f39c12',
                'bg' => 'rgba(243, 156, 18, 0.15)',
                'border' => '#f39c12',
                'next' => 1000
            ],
            [
                'min' => 200,
                'level' => 3,
                'title' => 'Nhà thám hiểm dày dạn',
                'sub' => 'Trail Master',
                'icon' => 'bi-award-fill',
                'color' => '#9bbd55',
                'bg' => 'rgba(155, 189, 85, 0.18)',
                'border' => '#9bbd55',
                'next' => 500
            ],
            [
                'min' => 50,
                'level' => 2,
                'title' => 'Người đi rừng năng nổ',
                'sub' => 'Active Camper',
                'icon' => 'bi-tree-fill',
                'color' => '#3498db',
                'bg' => 'rgba(52, 152, 219, 0.15)',
                'border' => '#3498db',
                'next' => 200
            ],
            [
                'min' => 0,
                'level' => 1,
                'title' => 'Tân thủ dã ngoại',
                'sub' => 'Novice Trekker',
                'icon' => 'bi-compass-fill',
                'color' => '#95a5a6',
                'bg' => 'rgba(149, 165, 166, 0.15)',
                'border' => '#95a5a6',
                'next' => 50
            ],
        ];

        $currentTier = $tiers[3];
        foreach ($tiers as $tier) {
            if ($points >= $tier['min']) {
                $currentTier = $tier;
                break;
            }
        }

        $min = $currentTier['min'];
        $next = $currentTier['next'];
        $progress = 100;
        if ($next > $min) {
            $progress = (int)round((($points - $min) / ($next - $min)) * 100);
            if ($progress > 100) {
                $progress = 100;
            } elseif ($progress < 0) {
                $progress = 0;
            }
        }

        return [
            'points'           => $points,
            'level'            => $currentTier['level'],
            'title'            => $currentTier['title'],
            'subtitle'         => $currentTier['sub'],
            'icon'             => $currentTier['icon'],
            'color'            => $currentTier['color'],
            'bg'               => $currentTier['bg'],
            'border'           => $currentTier['border'],
            'next_tier_points' => $next,
            'progress_percent' => $progress,
            'stats'            => $stats,
        ];
    }
}
