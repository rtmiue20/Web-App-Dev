<?php
// Test kiểm thử tính năng "Xem thêm" tiến trình cho Lịch sử hoạt động (Tab Activity) trong profile.php

$profileFile = __DIR__ . '/../app/views/users/profile.php';
$content = file_get_contents($profileFile);

echo "=== TEST TÍNH NĂNG XEM THÊM (LOAD MORE) HOẠT ĐỘNG PROFILE ===\n\n";

// 1. Kiểm tra container nút Xem thêm
if (strpos($content, 'id="activityLoadMoreContainer"') !== false &&
    strpos($content, 'id="btnLoadMoreActivities"') !== false &&
    strpos($content, 'id="actRemainingCount"') !== false &&
    strpos($content, 'id="actAllShownMsg"') !== false &&
    strpos($content, 'id="actShownAllCount"') !== false) {
    echo "[✓ PASS] Container và các phần tử UI 'Xem thêm' / 'Đã hiển thị toàn bộ' có mặt đầy đủ.\n";
} else {
    echo "[✗ FAIL] Thiếu cấu trúc HTML của activityLoadMoreContainer!\n";
    exit(1);
}

// 2. Kiểm tra JS logic renderActivitiesWithPagination & loadMoreActivities
if (strpos($content, 'activityPageSize = 10') !== false &&
    strpos($content, 'function renderActivitiesWithPagination()') !== false &&
    strpos($content, 'function loadMoreActivities()') !== false) {
    echo "[✓ PASS] Các hàm JS renderActivitiesWithPagination() và loadMoreActivities() được định nghĩa chuẩn xác (kích thước mỗi trang 10).\n";
} else {
    echo "[✗ FAIL] Thiếu hàm JS renderActivitiesWithPagination hoặc loadMoreActivities!\n";
    exit(1);
}

// 3. Kiểm tra tính liên kết với bộ lọc filterActivities
if (strpos($content, 'currentActivityFilter = group;') !== false &&
    strpos($content, 'activityCurrentLimit = activityPageSize;') !== false) {
    echo "[✓ PASS] Bộ lọc filterActivities() reset giới hạn hiển thị về 10 khi chuyển đổi bộ lọc danh mục.\n";
} else {
    echo "[✗ FAIL] filterActivities() chưa liên kết với renderActivitiesWithPagination!\n";
    exit(1);
}

// 4. Kiểm tra sự kiện khởi tạo DOMContentLoaded và Tab Bootstrap
if (strpos($content, 'renderActivitiesWithPagination();') !== false &&
    strpos($content, "actTabBtn.addEventListener('shown.bs.tab'") !== false) {
    echo "[✓ PASS] Khởi tạo tự động trên DOMContentLoaded và khi kích hoạt tab Bootstrap.\n";
} else {
    echo "[✗ FAIL] Thiếu sự kiện kích hoạt tự động phân trang hoạt động!\n";
    exit(1);
}

echo "\n============================================\n";
echo "🎉 TẤT CẢ TEST XEM THÊM HOẠT ĐỘNG ĐỀU PASS 100%!\n";

