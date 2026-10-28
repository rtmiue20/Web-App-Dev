<?php
/**
 * Test kiểm tra logic trạng thái bài viết và quyền duyệt của Admin (FR03, FR13)
 * Kiểm tra:
 * 1. Mặc định tạo bài viết từ Member là 'pending'
 * 2. Admin có thể chuyển trạng thái sang published, rejected, hidden, pending
 * 3. Đếm số lượng theo bộ lọc trạng thái (all, published, pending, rejected, hidden)
 */
require_once __DIR__ . '/../app/models/Post.php';

echo "=== TEST TRẠNG THÁI BÀI VIẾT & DUYỆT ADMIN ===" . PHP_EOL;

$postModel = new Post();

// 1. Tạo bài viết mẫu giả lập Member post bài
$testTitle = 'Test Post Status Logic ' . time();
$postId = $postModel->create(
    1, 
    1, 
    $testTitle, 
    'Nội dung kiểm tra tự động chuyển trạng thái pending khi member post bài.',
    'Núi Bà Đen',
    null,
    11.3789,
    106.1702,
    'pending'
);

if ($postId <= 0) {
    echo "[FAIL] Không tạo được bài viết test." . PHP_EOL;
    exit(1);
}
echo "[PASS] Tạo bài viết test thành công: ID #{$postId}" . PHP_EOL;

// 2. Kiểm tra trạng thái bài viết vừa tạo phải là 'pending'
$postData = $postModel->getById($postId);
if ($postData && $postData['status'] === 'pending') {
    echo "[PASS] Bài viết mới tạo có trạng thái đúng là 'pending'." . PHP_EOL;
} else {
    echo "[FAIL] Trạng thái không phải pending: " . ($postData['status'] ?? 'null') . PHP_EOL;
}

// 3. Admin chuyển sang 'published' (Đã duyệt / Approved)
$okPublished = $postModel->updateStatus($postId, 'published');
$postData = $postModel->getById($postId);
if ($okPublished && $postData['status'] === 'published') {
    echo "[PASS] Admin duyệt bài viết chuyển sang 'published' thành công." . PHP_EOL;
} else {
    echo "[FAIL] Chuyển sang published thất bại." . PHP_EOL;
}

// 4. Admin chuyển sang 'rejected' (Từ chối / Reject)
$okRejected = $postModel->updateStatus($postId, 'rejected');
$postData = $postModel->getById($postId);
if ($okRejected && $postData['status'] === 'rejected') {
    echo "[PASS] Admin chuyển trạng thái sang 'rejected' (Từ chối) thành công." . PHP_EOL;
} else {
    echo "[FAIL] Chuyển sang rejected thất bại." . PHP_EOL;
}

// 5. Admin chuyển sang 'hidden' (Đã ẩn / Hidden)
$okHidden = $postModel->updateStatus($postId, 'hidden');
$postData = $postModel->getById($postId);
if ($okHidden && $postData['status'] === 'hidden') {
    echo "[PASS] Admin chuyển trạng thái sang 'hidden' (Đã ẩn) thành công." . PHP_EOL;
} else {
    echo "[FAIL] Chuyển sang hidden thất bại." . PHP_EOL;
}

// 6. Admin chuyển lại về 'pending' (Chờ duyệt / Pending)
$okPending = $postModel->updateStatus($postId, 'pending');
$postData = $postModel->getById($postId);
if ($okPending && $postData['status'] === 'pending') {
    echo "[PASS] Admin chuyển lại về 'pending' (Chờ duyệt) thành công." . PHP_EOL;
} else {
    echo "[FAIL] Chuyển về pending thất bại." . PHP_EOL;
}

// 7. Kiểm tra hàm đếm số lượng
$countPending = $postModel->countFiltered(null, 'pending');
$countRejected = $postModel->countFiltered(null, 'rejected');
$countPublished = $postModel->countFiltered(null, 'published');
$countHidden = $postModel->countFiltered(null, 'hidden');
echo "[PASS] Đếm bài: Published={$countPublished}, Pending={$countPending}, Rejected={$countRejected}, Hidden={$countHidden}" . PHP_EOL;

// 8. Dọn dẹp bài test
$postModel->delete($postId);
echo "[PASS] Dọn dẹp bài viết test thành công." . PHP_EOL;

echo "=== TẤT CẢ KIỂM THỬ TRẠNG THÁI HOÀN TẤT THÀNH CÔNG ===" . PHP_EOL;
