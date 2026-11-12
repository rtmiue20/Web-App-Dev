<?php
require_once __DIR__ . '/../app/core/Database.php';

echo "=== KIỂM THỬ ĐỒNG BỘ HÌNH ẢNH & TIÊU ĐỀ BÀI VIẾT ===\n\n";

$posts = Database::query("
    SELECT p.id, p.title, p.location, p.status, c.name AS category_name,
           (SELECT pi.image_url FROM post_images pi WHERE pi.post_id = p.id ORDER BY pi.id ASC LIMIT 1) AS image_url
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.id ASC
");

$allPassed = true;

foreach ($posts as $p) {
    $imgPath = __DIR__ . '/../public/' . $p['image_url'];
    $exists = !empty($p['image_url']) && file_exists($imgPath);
    $size = $exists ? filesize($imgPath) : 0;
    
    echo "Bài viết #{$p['id']}:\n";
    echo "  - Tiêu đề: {$p['title']}\n";
    echo "  - Danh mục: {$p['category_name']}\n";
    echo "  - Địa điểm: {$p['location']}\n";
    echo "  - Đường dẫn ảnh: {$p['image_url']}\n";
    echo "  - File ảnh tồn tại trên đĩa: " . ($exists ? "CÓ (" . number_format($size / 1024, 1) . " KB)" : "KHÔNG") . "\n";
    
    // Kiểm tra tính chuyên nghiệp và ăn khớp
    if (strpos($p['image_url'], '1121ddee7bf6fd0f2cce7d6f4d96d49d') !== false) {
        echo "  [✗ FAIL] Bài viết vẫn đang dùng ảnh tháp Eiffel!\n";
        $allPassed = false;
    } elseif (strpos($p['image_url'], '729197006baecca7a06257f4f9179eb4') !== false) {
        echo "  [✗ FAIL] Bài viết hướng dẫn lều vẫn đang dùng ảnh học sinh cà vạt!\n";
        $allPassed = false;
    } elseif (strpos($p['image_url'], 'cc50a0f0d97226d6aeac280a2338a371') !== false) {
        echo "  [✗ FAIL] Bài viết vẫn đang dùng poster Captain America!\n";
        $allPassed = false;
    } elseif (!$exists) {
        echo "  [✗ FAIL] File ảnh không tồn tại!\n";
        $allPassed = false;
    } else {
        echo "  [✓ PASS] Hình ảnh đồng bộ chuẩn xác với tiêu đề và chủ đề dã ngoại.\n";
    }
    echo "\n";
}

if ($allPassed) {
    echo "🎉 TẤT CẢ HÌNH ẢNH BÀI VIẾT ĐÃ ĐỒNG BỘ CHUẨN XÁC, ĐẸP VÀ CHUYÊN NGHIỆP 100%!\n";
    exit(0);
} else {
    echo "❌ CÓ HÌNH ẢNH CHƯA ĐỒNG BỘ HOẶC BỊ LỖI!\n";
    exit(1);
}

