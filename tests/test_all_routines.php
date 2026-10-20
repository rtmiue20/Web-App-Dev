<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Post.php';
require_once __DIR__ . '/../app/models/Comment.php';
require_once __DIR__ . '/../app/models/Tag.php';

$pass = 0;
$fail = 0;

function assertTest($condition, $name) {
    global $pass, $fail;
    if ($condition) {
        echo "[✓ PASS] $name\n";
        $pass++;
    } else {
        echo "[✗ FAIL] $name\n";
        $fail++;
    }
}

echo "=== KIỂM THỬ TÍCH HỢP TOÀN BỘ ROUTINES & MODELS ===\n\n";

// 1. Category model
echo "--- 1. CATEGORY ROUTINES ---\n";
$catModel = new Category();

// sp_category_count
$initCatCount = $catModel->count();
assertTest(is_int($initCatCount), "sp_category_count trả về số nguyên ($initCatCount)");

// sp_category_create
$testCatName = "Chuyên mục Test " . time();
$catModel->setName($testCatName);
$catModel->setDescription("Mô tả test");
$created = $catModel->create();
$catId = $catModel->getId();
assertTest($created && $catId > 0, "sp_category_create tạo thành công, id = $catId");

// sp_category_get
$catDetail = $catModel->findById($catId);
assertTest($catDetail && $catDetail['name'] === $testCatName, "sp_category_get lấy đúng danh mục");

// sp_category_get_by_name
$catByName = $catModel->findByName($testCatName);
assertTest($catByName && (int)$catByName['id'] === $catId, "sp_category_get_by_name tìm thấy đúng tên");

// sp_category_update
$updated = $catModel->update($catId, $testCatName . " (Updated)", "Mô tả cập nhật");
$catDetailUpdated = $catModel->findById($catId);
assertTest($updated && strpos($catDetailUpdated['name'], '(Updated)') !== false, "sp_category_update cập nhật thành công");

// sp_category_list
$allCats = $catModel->getAll();
assertTest(is_array($allCats) && count($allCats) > 0, "sp_category_list trả về mảng danh mục (" . count($allCats) . " mục)");

// sp_category_count_posts
$postCount = $catModel->countPosts($catId);
assertTest($postCount === 0, "sp_category_count_posts trả về 0 cho mục mới");

// sp_category_delete
$deleted = $catModel->delete($catId);
assertTest($deleted, "sp_category_delete xóa thành công danh mục vừa tạo");


// 2. User model
echo "\n--- 2. USER ROUTINES ---\n";
$userModel = new User();

// sp_user_count
$initUserCount = $userModel->count();
assertTest(is_int($initUserCount), "sp_user_count trả về số nguyên ($initUserCount)");

// sp_user_register
$testEmail = "user_test_" . time() . "@example.com";
$testUsername = "testuser_" . time();
$userModel->setUsername($testUsername);
$userModel->setEmail($testEmail);
$userModel->setPasswordHash(password_hash("password123", PASSWORD_DEFAULT));
$newUserId = $userModel->create();
assertTest($newUserId > 0, "sp_user_register đăng ký user thành công, id = $newUserId");

// sp_user_exists
$exists = $userModel->existsByEmailOrUsername($testEmail, "nonexistent");
assertTest($exists === true, "sp_user_exists kiểm tra đúng email đã tồn tại");

// sp_user_get_by_email
$userByEmail = $userModel->findByEmail($testEmail);
assertTest($userByEmail && $userByEmail->getId() === $newUserId && $userByEmail->getRoleName() === 'member', "sp_user_get_by_email trả về đúng user và role = member");

// sp_user_get_by_id
$userById = $userModel->findById($newUserId);
assertTest($userById && $userById->getUsername() === $testUsername, "sp_user_get_by_id trả về đúng username");

// sp_user_list
$allUsers = $userModel->getAll();
assertTest(is_array($allUsers) && count($allUsers) >= 1, "sp_user_list trả về danh sách người dùng (" . count($allUsers) . " người dùng)");


// 3. Post model & Comments & Likes
echo "\n--- 3. POST, COMMENT, LIKE ROUTINES ---\n";
$postModel = new Post();

// sp_post_count
$initPostCount = $postModel->count();
assertTest(is_int($initPostCount), "sp_post_count trả về số nguyên ($initPostCount)");

// sp_post_create
$firstCatId = (int)($allCats[0]['id'] ?? 1);
$postTitle = "Bài viết dã ngoại Test " . time();
$postContent = "Nội dung bài viết dã ngoại mẫu để kiểm tra Stored Procedures";
$newPostId = $postModel->create($newUserId, $firstCatId, $postTitle, $postContent, "Đà Lạt", null, 11.94, 108.45, "published");
assertTest($newPostId > 0, "sp_post_create tạo bài viết thành công, id = $newPostId");

// sp_post_get
$postDetail = $postModel->getById($newPostId);
assertTest($postDetail && $postDetail['title'] === $postTitle, "sp_post_get lấy đúng thông tin bài viết");

// sp_post_list
$posts = $postModel->getAll();
assertTest(is_array($posts) && count($posts) > 0, "sp_post_list lấy danh sách bài viết thành công");

// sp_post_search
$searchRes = $postModel->search("Đà Lạt");
assertTest(count($searchRes) > 0, "sp_post_search tìm thấy bài viết theo từ khóa 'Đà Lạt'");

// sp_post_search_suggest
$suggests = $postModel->suggest("Bài viết");
assertTest(is_array($suggests) && count($suggests) > 0, "sp_post_search_suggest gợi ý từ khóa thành công");

// sp_post_toggle_like
$likes1 = $postModel->toggleLike($newPostId, $newUserId);
assertTest($likes1 === 1, "sp_post_toggle_like thích bài viết thành công (likes = 1)");

// sp_post_is_liked
$isLiked = $postModel->isLiked($newPostId, $newUserId);
assertTest($isLiked === true, "sp_post_is_liked xác nhận user đã like bài");

$likes0 = $postModel->toggleLike($newPostId, $newUserId);
assertTest($likes0 === 0, "sp_post_toggle_like bỏ thích thành công (likes = 0)");

// Comment routines
echo "\n--- 4. COMMENT ROUTINES ---\n";
$commentModel = new Comment();
$cmtId = $commentModel->create($newPostId, $newUserId, "Bình luận thử nghiệm routine");
assertTest($cmtId > 0, "sp_comment_create thêm bình luận thành công, id = $cmtId");

$comments = $commentModel->getByPostId($newPostId);
assertTest(count($comments) === 1 && $comments[0]['content'] === "Bình luận thử nghiệm routine", "sp_comment_list lấy danh sách bình luận thành công");

$cmtCount = $commentModel->countByPost($newPostId);
assertTest($cmtCount === 1, "sp_comment_count_by_post đếm đúng số bình luận");

$cmtDeleted = $commentModel->delete($cmtId, $newUserId);
assertTest($cmtDeleted, "sp_comment_delete xóa bình luận thành công");


// Tag routines
echo "\n--- 5. TAG ROUTINES ---\n";
$tagModel = new Tag();
$tagModel->setName("Camping");
$tId = $tagModel->findOrCreate();
assertTest($tId > 0, "sp_tag_find_or_create tạo/tìm tag thành công, id = $tId");

$addTag = $tagModel->addTagToPost($newPostId, "Trekking");
assertTest($addTag > 0, "sp_post_tag_add gán tag vào bài viết thành công");

$postTags = $tagModel->getByPostId($newPostId);
assertTest(count($postTags) >= 1, "sp_post_tags_get lấy đúng tag của bài viết");


// Dashboard Routine
echo "\n--- 6. DASHBOARD ROUTINE ---\n";
$stats = Database::call('sp_dashboard_stats');
assertTest(!empty($stats) && isset($stats[0]['total_users'], $stats[0]['total_posts']), "sp_dashboard_stats trả về đầy đủ thống kê tổng quan");


// Cleanup Post & User
$postModel->delete($newPostId);
Database::execute("DELETE FROM users WHERE id = :id", ['id' => $newUserId]);

echo "\n============================================\n";
echo "KẾT QUẢ: $pass PASS / $fail FAIL\n";
echo "============================================\n";
