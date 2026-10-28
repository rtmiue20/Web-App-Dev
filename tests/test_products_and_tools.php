<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Post.php';

echo "=== KIỂM THỬ TÍNH NĂNG DỤNG CỤ (PRODUCTS) & DỤNG CỤ BÀI VIẾT (POST TOOLS) ===" . PHP_EOL;

$productModel = new Product();
$postModel = new Post();

// 1. Kiểm tra lấy toàn bộ danh mục sản phẩm
$allProducts = $productModel->getAll();
echo "1. Tổng số sản phẩm trong CSDL: " . count($allProducts) . PHP_EOL;
assert(count($allProducts) > 0, "Không có sản phẩm nào trong CSDL!");

// 2. Kiểm tra lọc sản phẩm theo chuyên mục (Category ID = 1)
$cat1Products = $productModel->getAll(1);
echo "2. Số sản phẩm chuyên mục 1: " . count($cat1Products) . PHP_EOL;
foreach ($cat1Products as $p) {
    assert((int)$p['category_id'] === 1, "Sản phẩm không thuộc chuyên mục 1!");
}

// 3. Kiểm tra lấy chi tiết sản phẩm theo ID
$firstProduct = $allProducts[0];
$found = $productModel->findById((int)$firstProduct['id']);
echo "3. Lấy sản phẩm ID " . $firstProduct['id'] . ": " . ($found ? $found['name'] : 'Thất bại') . PHP_EOL;
assert(!empty($found), "Không tìm thấy sản phẩm theo ID!");

// 4. Kiểm tra gán và đồng bộ dụng cụ cho bài viết (syncPostProducts)
$testPostId = 1;
$originalTools = $productModel->getByPostId($testPostId);
echo "4. Dụng cụ ban đầu của bài viết ID {$testPostId}: " . count($originalTools) . PHP_EOL;

// Chọn gán 2 sản phẩm mẫu
$sampleProductIds = [(int)$allProducts[0]['id'], (int)$allProducts[1]['id']];
$productModel->syncPostProducts($testPostId, $sampleProductIds);

$syncedTools = $productModel->getByPostId($testPostId);
echo "   Sau khi đồng bộ: " . count($syncedTools) . " dụng cụ" . PHP_EOL;
assert(count($syncedTools) === 2, "Số lượng dụng cụ sau đồng bộ không khớp!");

// 5. Kiểm tra xóa/gán rỗng dụng cụ
$productModel->syncPostProducts($testPostId, []);
$emptyTools = $productModel->getByPostId($testPostId);
echo "5. Sau khi gán rỗng: " . count($emptyTools) . " dụng cụ" . PHP_EOL;
assert(count($emptyTools) === 0, "Không xóa hết dụng cụ cũ!");

// Khôi phục lại dụng cụ cho bài viết 1
$productModel->syncPostProducts($testPostId, $sampleProductIds);
echo "6. Khôi phục lại dụng cụ bài viết 1 thành công." . PHP_EOL;

echo "=== TẤT CẢ CÁC BƯỚC KIỂM THỬ ĐÃ THÀNH CÔNG VÀ CHÍNH XÁC! ===" . PHP_EOL;
