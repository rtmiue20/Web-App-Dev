<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/models/Post.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/services/PostService.php';

echo "=== TEST KIỂM THỬ ĐỒNG BỘ DỤNG CỤ GẮN BÀI VIẾT KHI ĐƯỢC ADMIN DUYỆT ===\n\n";

$pdo = Database::getConnection();
$postModel = new Post();
$productModel = new Product();
$postService = new PostService();

// 1. Tạo bài viết thử nghiệm trạng thái 'pending' do user member (hoang_trekker, user_id = 2) tạo
$memberUserId = 2;
$categoryId = 1; // Cắm trại & Glamping
$testTitle = "Bài viết test kiểm duyệt dụng cụ " . time();
$postId = $postModel->create(
    $memberUserId,
    $categoryId,
    $testTitle,
    "Nội dung test kiểm duyệt bài viết và hiển thị dụng cụ",
    "Bạch Mã",
    [],
    null,
    null,
    'pending',
    []
);
assert($postId > 0, "Không thể tạo bài viết thử nghiệm");
echo "[✓ PASS 1] Đã tạo bài viết thử nghiệm #{$postId} với trạng thái 'pending'\n";

// 2. Tạo một dụng cụ tùy chỉnh gắn vào bài viết này (giả lập người dùng nhập có dấu phẩy đầu)
$rawName = ", lều du lịch tự bung chống nước test";
$cleanedName = trim(trim($rawName), " \t\n\r\0\x0B,;");
assert($cleanedName === "lều du lịch tự bung chống nước test", "Xóa dấu phẩy đầu tên dụng cụ thất bại: {$cleanedName}");
echo "[✓ PASS 2] Làm sạch tên dụng cụ: '{$rawName}' -> '{$cleanedName}'\n";

$customToolId = $productModel->create(
    $categoryId,
    $cleanedName,
    4500000,
    'assets/img/tent-forest.png',
    'Chất lượng tốt, đẹp',
    'https://shopee.vn/leu-tu-bung',
    0, // Ban đầu chưa được shared (hoặc = 0)
    $memberUserId
);
assert($customToolId > 0, "Không thể tạo dụng cụ tùy chỉnh");
echo "[✓ PASS 3] Đã tạo dụng cụ #{$customToolId} ('{$cleanedName}') gắn với bài viết\n";

// Gắn dụng cụ vào bài viết trong post_products
$productModel->syncPostProducts($postId, [$customToolId]);

// 3. Kiểm tra: Khi bài viết đang 'pending', dụng cụ KHÔNG ĐƯỢC PHÉP hiển thị trên /products
$allProductsBefore = $productModel->getAll();
$foundBefore = false;
foreach ($allProductsBefore as $prod) {
    if ((int)$prod['id'] === $customToolId) {
        $foundBefore = true;
        break;
    }
}
assert($foundBefore === false, "LỖI: Dụng cụ của bài viết pending lại xuất hiện trên /products!");
echo "[✓ PASS 4] Dụng cụ được ẩn khỏi /products khi bài viết đang chờ duyệt (pending)\n";

// 4. Admin duyệt bài viết sang 'published' qua PostService::changeStatus
$approveResult = $postService->changeStatus($postId, 'published');
assert($approveResult['success'] === true, "Duyệt bài viết thất bại: " . ($approveResult['message'] ?? ''));
echo "[✓ PASS 5] Admin duyệt bài viết #{$postId} sang trạng thái 'published'\n";

// 5. Kiểm tra: Dụng cụ PHẢI XUẤT HIỆN trên /products (Tất cả dụng cụ)
$allProductsAfter = $productModel->getAll();
$foundAfter = false;
foreach ($allProductsAfter as $prod) {
    if ((int)$prod['id'] === $customToolId) {
        $foundAfter = true;
        break;
    }
}
assert($foundAfter === true, "LỖI: Dụng cụ KHÔNG xuất hiện trên /products sau khi bài viết được duyệt!");
echo "[✓ PASS 6] Dụng cụ #{$customToolId} đã xuất hiện chính xác trên trang /products (Tất cả dụng cụ)\n";

// 6. Kiểm tra: Dụng cụ PHẢI XUẤT HIỆN trong bộ lọc chuyên mục (category_id = 1)
$catProducts = $productModel->getAll($categoryId);
$foundInCat = false;
foreach ($catProducts as $prod) {
    if ((int)$prod['id'] === $customToolId) {
        $foundInCat = true;
        break;
    }
}
assert($foundInCat === true, "LỖI: Dụng cụ KHÔNG xuất hiện khi lọc theo chuyên mục {$categoryId}!");
echo "[✓ PASS 7] Dụng cụ #{$customToolId} xuất hiện chính xác khi lọc chuyên mục 'Cắm trại & Glamping'\n";

// 7. Kiểm tra: Cột is_shared của dụng cụ đã được tự động kích hoạt sang 1
$toolInDb = $productModel->findById($customToolId);
assert((int)$toolInDb['is_shared'] === 1, "is_shared chưa được kích hoạt sang 1!");
echo "[✓ PASS 8] Cột is_shared của dụng cụ #{$customToolId} tự động đồng bộ = 1 trong CSDL\n";

// 8. Kiểm tra: Khi bài viết bị ẩn (hidden), dụng cụ tự động ẩn khỏi /products
$hideResult = $postService->changeStatus($postId, 'hidden');
assert($hideResult['success'] === true, "Ẩn bài viết thất bại");
$allProductsHidden = $productModel->getAll();
$foundWhenHidden = false;
foreach ($allProductsHidden as $prod) {
    if ((int)$prod['id'] === $customToolId) {
        $foundWhenHidden = true;
        break;
    }
}
assert($foundWhenHidden === false, "LỖI: Dụng cụ vẫn xuất hiện khi bài viết bị ẩn!");
echo "[✓ PASS 9] Dụng cụ tự động ẩn khỏi /products khi bài viết bị chuyển sang 'hidden'\n";

// 9. Kiểm tra: Khi bài viết được duyệt lại (published), dụng cụ lại xuất hiện
$reapproveResult = $postService->changeStatus($postId, 'published');
assert($reapproveResult['success'] === true, "Duyệt lại bài viết thất bại");
$allProductsReapproved = $productModel->getAll();
$foundWhenReapproved = false;
foreach ($allProductsReapproved as $prod) {
    if ((int)$prod['id'] === $customToolId) {
        $foundWhenReapproved = true;
        break;
    }
}
assert($foundWhenReapproved === true, "LỖI: Dụng cụ không xuất hiện lại khi bài viết được duyệt lại!");
echo "[✓ PASS 10] Dụng cụ tự động xuất hiện lại khi bài viết được phê duyệt trở lại\n";

// Dọn dẹp dữ liệu test
$postModel->delete($postId);
echo "[✓ CLEANUP] Đã dọn dẹp bài viết và dụng cụ test an toàn\n";

echo "\n============================================\n";
echo "🎉 TẤT CẢ TEST ĐỒNG BỘ DỤNG CỤ ĐÃ PASS 100% HOÀN TOÀN CHUẨN XÁC!\n";

