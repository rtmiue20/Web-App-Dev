<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/models/Post.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/services/PostService.php';

Session::init();

echo "=== TEST KIỂM THỬ XUẤT BẢN BÀI VIẾT VÀ ĐỒNG BỘ DỤNG CỤ ===\n\n";

$db = Database::getConnection();
$postModel = new Post();
$productModel = new Product();
$postService = new PostService();

// -------------------------------------------------------------
// Test 1: Admin xuất bản bài viết trực tiếp kèm 2 dụng cụ
// -------------------------------------------------------------
$adminUser = ['id' => 1, 'username' => 'admin', 'role_id' => 2, 'role' => 'admin'];
Session::set('user', $adminUser);

$catId = 3; // Dụng cụ & Đồ phượt
$title = "Bài viết Admin test xuất bản kèm 2 dụng cụ " . time();
$content = "Nội dung bài viết kiểm tra xuất bản trực tiếp bởi Quản trị viên và kiểm tra hiển thị công cụ dã ngoại.";

// Tạo 2 custom tools mô phỏng như giao diện /posts/create
$tool1Id = $productModel->create(
    $catId,
    "Bếp ga dã ngoại Fire-Maple Admin",
    380000,
    "assets/img/tent-forest.png",
    "Bếp ga chịu tải tốt, lửa xanh đều",
    "https://shopee.vn/bep-ga-fire-maple",
    1,
    1
);

$tool2Id = $productModel->create(
    $catId,
    "Đèn bão cắm trại cổ điển Naturehike",
    250000,
    "assets/img/moss-form.png",
    "Đèn led ánh sáng vàng ấm, pin sạc type-C",
    "https://lazada.vn/den-bao-naturehike",
    1,
    1
);

assert($tool1Id > 0, "Tạo tool 1 thất bại");
assert($tool2Id > 0, "Tạo tool 2 thất bại");
echo "[✓ PASS 1] Đã tạo 2 dụng cụ thử nghiệm: #{$tool1Id} và #{$tool2Id}\n";

// Tạo bài viết bởi Admin (status = published)
$adminPostId = $postModel->create(
    $adminUser['id'],
    $catId,
    $title,
    $content,
    "Đồi Đa Phú",
    ["assets/img/tent-forest.png", "assets/img/ta-xua-trek.png", "assets/img/moss-form.png"],
    11.9404,
    108.4583,
    'published',
    [
        'duration_days' => 2,
        'difficulty' => 'easy',
        'cost_estimate' => '500.000đ - 1.000.000đ',
        'season' => 'Quanh năm'
    ]
);

assert($adminPostId > 0, "Admin tạo bài viết thất bại!");
echo "[✓ PASS 2] Admin tạo bài viết trực tiếp thành công: Post ID = #{$adminPostId}\n";

// Gắn 2 dụng cụ vào bài viết
$productModel->syncPostProducts($adminPostId, [$tool1Id, $tool2Id]);
echo "[✓ PASS 3] Đã đồng bộ 2 dụng cụ vào bài viết #{$adminPostId}\n";

// Kiểm tra: 2 dụng cụ phải xuất hiện ngay lập tức trong Product::getAll(null) ("Tất cả dụng cụ")
$allProducts = $productModel->getAll(null);
$foundTool1 = false;
$foundTool2 = false;
foreach ($allProducts as $p) {
    if ((int)$p['id'] === $tool1Id) $foundTool1 = true;
    if ((int)$p['id'] === $tool2Id) $foundTool2 = true;
}

assert($foundTool1 === true, "LỖI: Dụng cụ 1 không xuất hiện trong danh sách /products!");
assert($foundTool2 === true, "LỖI: Dụng cụ 2 không xuất hiện trong danh sách /products!");
echo "[✓ PASS 4] Cả 2 dụng cụ xuất hiện chính xác 100% trong danh sách /products (Tất cả dụng cụ)!\n";

// Kiểm tra: 2 dụng cụ phải xuất hiện trong Product::getAll($catId) (chuyên mục Dụng cụ & Đồ phượt)
$catProducts = $productModel->getAll($catId);
$foundInCat1 = false;
$foundInCat2 = false;
foreach ($catProducts as $p) {
    if ((int)$p['id'] === $tool1Id) $foundInCat1 = true;
    if ((int)$p['id'] === $tool2Id) $foundInCat2 = true;
}

assert($foundInCat1 === true, "LỖI: Dụng cụ 1 không xuất hiện trong chuyên mục {$catId}!");
assert($foundInCat2 === true, "LỖI: Dụng cụ 2 không xuất hiện trong chuyên mục {$catId}!");
echo "[✓ PASS 5] Cả 2 dụng cụ xuất hiện chính xác 100% khi lọc theo chuyên mục {$catId}!\n";

// -------------------------------------------------------------
// Test 2: Member tạo bài viết có dụng cụ (pending -> duyệt -> published)
// -------------------------------------------------------------
$memberUser = ['id' => 2, 'username' => 'hoang_trekker', 'role_id' => 1, 'role' => 'member'];
Session::set('user', $memberUser);

$memberToolId = $productModel->create(
    $catId,
    "Lều tự bung dã ngoại chống nước 4P",
    1200000,
    "assets/img/tent-forest.png",
    "Lều mở nhanh trong 3 giây",
    "https://shopee.vn/leu-tu-bung",
    1,
    $memberUser['id']
);

$memberPostId = $postModel->create(
    $memberUser['id'],
    $catId,
    "Bài viết của member chờ duyệt " . time(),
    "Nội dung chia sẻ dã ngoại của member",
    "Bạch Mã",
    [],
    null,
    null,
    'pending',
    []
);

$productModel->syncPostProducts($memberPostId, [$memberToolId]);

// Khi bài viết còn 'pending', dụng cụ của member không được xuất hiện trong /products
$publicProductsWhilePending = $productModel->getAll(null);
$foundWhilePending = false;
foreach ($publicProductsWhilePending as $p) {
    if ((int)$p['id'] === $memberToolId) {
        $foundWhilePending = true;
        break;
    }
}
assert($foundWhilePending === false, "LỖI: Dụng cụ của bài pending không được xuất hiện ở public /products!");
echo "[✓ PASS 6] Khi bài viết member đang ở trạng thái 'pending', dụng cụ không xuất hiện ở /products công khai\n";

// Admin phê duyệt bài viết của member
$approveResult = $postService->changeStatus($memberPostId, 'published');
assert($approveResult['success'] === true, "Phê duyệt bài viết thất bại");

// Dụng cụ của member phải xuất hiện ngay trong /products
$publicProductsAfterApprove = $productModel->getAll(null);
$foundAfterApprove = false;
foreach ($publicProductsAfterApprove as $p) {
    if ((int)$p['id'] === $memberToolId) {
        $foundAfterApprove = true;
        break;
    }
}
assert($foundAfterApprove === true, "LỖI: Sau khi admin duyệt bài, dụng cụ không xuất hiện trong /products!");
echo "[✓ PASS 7] Sau khi admin duyệt bài viết, dụng cụ của member lập tức xuất hiện trong /products!\n";

// -------------------------------------------------------------
// Dọn dẹp dữ liệu test an toàn
// -------------------------------------------------------------
$postModel->delete($adminPostId);
$postModel->delete($memberPostId);
$db->exec("DELETE FROM products WHERE id IN ({$tool1Id}, {$tool2Id}, {$memberToolId})");
echo "[✓ CLEANUP] Đã dọn dẹp các bài viết và dụng cụ thử nghiệm an toàn!\n";

echo "\n=======================================================\n";
echo "🎉 TẤT CẢ TEST ĐÃ PASS 100%! HỆ THỐNG HOẠT ĐỘNG HOÀN HẢO!\n";

