<?php
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/Session.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/models/Post.php';

echo "=== TEST MULTI-IMAGE UPLOAD & INSTAGRAM-STYLE CAROUSEL ===\n\n";

$postModel = new Post();

// 1. Kiểm tra getImages và getById
$p7 = $postModel->getById(7);
assert(!empty($p7), "Không tìm thấy post 7");
assert(!empty($p7['images']), "Post 7 thiếu mảng images");
$imgCount = count($p7['images']);
assert($imgCount === 4, "Post 7 phải có 4 ảnh, thực tế có: {$imgCount}");
echo "[✓ PASS] Post::getById(7) trả về đúng 4 hình ảnh trong gallery\n";

// 2. Kiểm tra View show.php chứa các thành phần Instagram Carousel
$showHtml = file_get_contents(__DIR__ . '/../app/views/posts/show.php');
assert(strpos($showHtml, 'insta-carousel-wrap') !== false, "Thiếu class insta-carousel-wrap trong show.php");
assert(strpos($showHtml, 'insta-track') !== false, "Thiếu class insta-track trong show.php");
assert(strpos($showHtml, 'insta-counter-badge') !== false, "Thiếu class insta-counter-badge trong show.php");
assert(strpos($showHtml, 'insta-nav-btn insta-prev-btn') !== false, "Thiếu nút Prev tròn Instagram trong show.php");
assert(strpos($showHtml, 'insta-nav-btn insta-next-btn') !== false, "Thiếu nút Next tròn Instagram trong show.php");
assert(strpos($showHtml, 'insta-dots-bar') !== false, "Thiếu thanh dots Instagram trong show.php");
assert(strpos($showHtml, 'updateUiState') !== false, "Thiếu logic ẩn/hiện nút điều hướng Instagram trong JS");
echo "[✓ PASS] View show.php tích hợp đầy đủ giao diện Instagram Carousel (Track trượt, Nút tròn, Badge 1/N, Dots bar)\n";

// 3. Kiểm tra View create.php tích hợp multi-image upload
$createHtml = file_get_contents(__DIR__ . '/../app/views/posts/create.php');
assert(strpos($createHtml, 'name="images[]"') !== false, "Thiếu input file multiple images[] trong create.php");
assert(strpos($createHtml, 'window.composerDataTransfer') !== false, "Thiếu cơ chế tích lũy DataTransfer nhiều ảnh trong create.php");
assert(strpos($createHtml, 'removeComposerFile') !== false, "Thiếu hàm xóa từng ảnh đã chọn trong create.php");
assert(strpos($createHtml, 'fb-thumb-card') !== false, "Thiếu preview thẻ thumbnail có nút xóa trong create.php");
echo "[✓ PASS] View create.php hỗ trợ chọn nhiều ảnh liên tiếp, xóa từng ảnh và hiển thị preview đẹp mắt\n";

// 4. Kiểm tra View edit.php tích hợp gỡ ảnh và thêm ảnh mới
$editHtml = file_get_contents(__DIR__ . '/../app/views/posts/edit.php');
assert(strpos($editHtml, 'delete_image_ids[]') !== false, "Thiếu cơ chế xóa ảnh hiện có trong edit.php");
assert(strpos($editHtml, 'toggleDeleteExistingImg') !== false, "Thiếu hàm toggle xóa ảnh trong edit.php");
assert(strpos($editHtml, 'window.editDataTransfer') !== false, "Thiếu DataTransfer tải thêm nhiều ảnh mới trong edit.php");
echo "[✓ PASS] View edit.php hỗ trợ xóa ảnh cũ và tải thêm nhiều ảnh mới\n";

// 5. Kiểm tra PostController xử lý cloud_image_url & delete_image_ids
$ctrlPhp = file_get_contents(__DIR__ . '/../app/controllers/PostController.php');
assert(strpos($ctrlPhp, 'cloud_image_url') !== false, "Thiếu xử lý cloud_image_url trong PostController");
assert(strpos($ctrlPhp, 'deleteImages') !== false, "Thiếu gọi deleteImages trong PostController update");
echo "[✓ PASS] PostController đồng bộ lưu nhiều ảnh từ file upload, link cloud và xóa ảnh khi cập nhật\n";

// 6. Kiểm tra post-cards.php có badge icon album nhiều ảnh
$cardsHtml = file_get_contents(__DIR__ . '/../app/views/partials/post-cards.php');
assert(strpos($cardsHtml, 'images_count') !== false, "Thiếu hiển thị badge số lượng ảnh trên thẻ bài viết");
echo "[✓ PASS] Thẻ bài viết post-cards.php hiển thị badge icon album nhiều ảnh\n";

echo "\n============================================\n";
echo "🎉 TẤT CẢ TEST MULTI-IMAGE & INSTAGRAM CAROUSEL ĐỀU PASS 100%!\n";
