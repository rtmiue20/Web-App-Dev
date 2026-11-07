<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

/**
 * Controller ProductController - Quản lý giao diện và danh mục Dụng cụ / Thiết bị dã ngoại
 * Hỗ trợ người dùng chia sẻ, chỉnh sửa và xóa dụng cụ (dành cho người chia sẻ & admin)
 * Tuân thủ MVC & OOP, hoàn toàn không dùng lambda/arrow function
 */
class ProductController extends Controller
{
    private Product $products;
    private Category $categories;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
    }

    /**
     * Danh sách sản phẩm / dụng cụ dã ngoại (công khai cho tất cả thành viên)
     * Có hỗ trợ lọc theo chuyên mục
     */
    public function index(): void
    {
        $selectedCategoryId = null;
        if (isset($_GET['category']) && $_GET['category'] !== '') {
            $selectedCategoryId = (int)$_GET['category'];
            if ($selectedCategoryId <= 0) {
                $selectedCategoryId = null;
            }
        }

        $allCategories = $this->categories->getAll();
        $allProducts = $this->products->getAll($selectedCategoryId);
        $currentUser = Session::get('user');

        // Phân trang 6 dụng cụ / trang
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        $perPage = 6;
        $totalProducts = count($allProducts);
        $totalPages = (int)ceil($totalProducts / $perPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }
        if ($currentPage > $totalPages && $totalProducts > 0) {
            $currentPage = $totalPages;
        }

        $offset = ($currentPage - 1) * $perPage;
        $paginatedProducts = array_slice($allProducts, $offset, $perPage);

        $this->render('products/index', [
            'title' => 'Dụng cụ & Trang thiết bị dã ngoại',
            'products' => $paginatedProducts,
            'categories' => $allCategories,
            'selectedCategoryId' => $selectedCategoryId,
            'currentUser' => $currentUser,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts,
            'perPage' => $perPage
        ]);
    }

    /**
     * Chi tiết sản phẩm / dụng cụ theo ID
     */
    public function show(int $id): void
    {
        $product = $this->products->findById($id);
        if (!$product) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy dụng cụ']);
            return;
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'product' => $product
            ]);
            return;
        }

        $allCategories = $this->categories->getAll();
        $currentUser = Session::get('user');
        $this->render('products/index', [
            'title' => $product['name'] . ' - Dụng cụ dã ngoại',
            'products' => [$product],
            'categories' => $allCategories,
            'selectedCategoryId' => (int)($product['category_id'] ?? 0),
            'currentUser' => $currentUser
        ]);
    }

    /**
     * Thêm mới dụng cụ vào kho cộng đồng
     */
    public function store(): void
    {
        if (!Auth::isMember()) {
            $this->respondOrRedirect(false, Auth::denyGuestMessage('chia sẻ dụng cụ'), Auth::check() ? '/products' : '/login');
            return;
        }

        if (!Csrf::verify($_POST['_csrf'] ?? $_POST['csrf_token'] ?? null)) {
            $this->respondOrRedirect(false, 'Phiên bảo mật đã hết hạn. Vui lòng tải lại trang!', '/products');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            $this->respondOrRedirect(false, 'Tên dụng cụ không được để trống!', '/products');
            return;
        }

        $catId = (int)($_POST['category_id'] ?? 0);
        $priceRaw = (string)($_POST['price'] ?? '0');
        $price = (float)preg_replace('/[^\d.]/', '', $priceRaw);

        $link = trim((string)($_POST['link'] ?? ''));
        if ($link !== '' && !preg_match('#^https?://#i', $link)) {
            $link = 'https://' . $link;
        }

        $desc = trim((string)($_POST['description'] ?? ''));
        $isShared = isset($_POST['is_shared']) ? 1 : 0;
        $imgUrl = trim((string)($_POST['image_url'] ?? ''));

        // Kiểm tra upload file ảnh
        if (!empty($_FILES['image']['name']) && ($_FILES['image']['error'] ?? null) === UPLOAD_ERR_OK) {
            try {
                $savedPath = Upload::image($_FILES['image'], 'products');
                if (!empty($savedPath)) {
                    $imgUrl = $savedPath;
                }
            } catch (Exception $e) {
                error_log('Upload tool image failed: ' . $e->getMessage());
            }
        }

        if ($imgUrl === '') {
            $imgUrl = 'assets/img/tent-forest.png';
        }

        $userId = Auth::id();
        $productId = $this->products->create(
            $catId > 0 ? $catId : null,
            $name,
            $price,
            $imgUrl,
            $desc ?: 'Dụng cụ được chia sẻ bởi thành viên cộng đồng.',
            $link !== '' ? $link : null,
            $isShared,
            $userId
        );

        if ($productId > 0) {
            $this->respondOrRedirect(true, 'Đã chia sẻ dụng cụ mới vào kho cộng đồng thành công!', '/products');
        } else {
            $this->respondOrRedirect(false, 'Có lỗi xảy ra khi lưu dụng cụ. Vui lòng thử lại!', '/products');
        }
    }

    /**
     * Cập nhật thông tin dụng cụ (người chia sẻ hoặc admin)
     */
    public function update(): void
    {
        if (!Auth::isMember()) {
            $this->respondOrRedirect(false, Auth::denyGuestMessage('chỉnh sửa dụng cụ'), Auth::check() ? '/products' : '/login');
            return;
        }

        if (!Csrf::verify($_POST['_csrf'] ?? $_POST['csrf_token'] ?? null)) {
            $this->respondOrRedirect(false, 'Phiên bảo mật đã hết hạn. Vui lòng thử lại!', '/products');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $product = $this->products->findById($id);
        if (!$product) {
            $this->respondOrRedirect(false, 'Không tìm thấy dụng cụ cần chỉnh sửa!', '/products');
            return;
        }

        // Kiểm tra quyền: Tác giả tạo dụng cụ hoặc Admin
        $isOwner = (int)($product['user_id'] ?? 0) === (int)Auth::id();
        $isAdmin = Auth::isAdmin();
        if (!$isOwner && !$isAdmin) {
            $this->respondOrRedirect(false, 'Bạn không có quyền chỉnh sửa dụng cụ này!', '/products');
            return;
        }

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            $this->respondOrRedirect(false, 'Tên dụng cụ không được để trống!', '/products');
            return;
        }

        $catId = (int)($_POST['category_id'] ?? 0);
        $priceRaw = (string)($_POST['price'] ?? '0');
        $price = (float)preg_replace('/[^\d.]/', '', $priceRaw);

        $link = trim((string)($_POST['link'] ?? ''));
        if ($link !== '' && !preg_match('#^https?://#i', $link)) {
            $link = 'https://' . $link;
        }

        $desc = trim((string)($_POST['description'] ?? ''));
        $isShared = isset($_POST['is_shared']) ? 1 : 0;
        $imgUrl = trim((string)($_POST['image_url'] ?? ''));

        // Kiểm tra file ảnh tải lên mới
        if (!empty($_FILES['image']['name']) && ($_FILES['image']['error'] ?? null) === UPLOAD_ERR_OK) {
            try {
                $savedPath = Upload::image($_FILES['image'], 'products');
                if (!empty($savedPath)) {
                    $imgUrl = $savedPath;
                }
            } catch (Exception $e) {
                error_log('Upload tool image failed: ' . $e->getMessage());
            }
        }

        if ($imgUrl === '') {
            $imgUrl = $product['image_url'] ?? 'assets/img/tent-forest.png';
        }

        $success = $this->products->update(
            $id,
            $catId > 0 ? $catId : null,
            $name,
            $price,
            $imgUrl,
            $desc,
            $link !== '' ? $link : null,
            $isShared
        );

        if ($success) {
            $this->respondOrRedirect(true, 'Đã cập nhật thông tin dụng cụ thành công!', '/products');
        } else {
            $this->respondOrRedirect(false, 'Có lỗi xảy ra khi cập nhật dụng cụ!', '/products');
        }
    }

    /**
     * Xóa dụng cụ khỏi kho (người chia sẻ hoặc admin)
     */
    public function destroy(): void
    {
        if (!Auth::isMember()) {
            $this->respondOrRedirect(false, Auth::denyGuestMessage('xóa dụng cụ'), Auth::check() ? '/products' : '/login');
            return;
        }

        if (!Csrf::verify($_POST['_csrf'] ?? $_POST['csrf_token'] ?? null)) {
            $this->respondOrRedirect(false, 'Phiên bảo mật đã hết hạn. Vui lòng thử lại!', '/products');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $product = $this->products->findById($id);
        if (!$product) {
            $this->respondOrRedirect(false, 'Không tìm thấy dụng cụ cần xóa!', '/products');
            return;
        }

        // Kiểm tra quyền: Tác giả tạo dụng cụ hoặc Admin
        $isOwner = (int)($product['user_id'] ?? 0) === (int)Auth::id();
        $isAdmin = Auth::isAdmin();
        if (!$isOwner && !$isAdmin) {
            $this->respondOrRedirect(false, 'Bạn không có quyền xóa dụng cụ này!', '/products');
            return;
        }

        $success = $this->products->delete($id);
        if ($success) {
            $this->respondOrRedirect(true, 'Đã xóa dụng cụ khỏi kho cộng đồng!', '/products');
        } else {
            $this->respondOrRedirect(false, 'Có lỗi xảy ra khi xóa dụng cụ!', '/products');
        }
    }

    /**
     * Phản hồi dạng JSON (cho fetch/AJAX) hoặc Redirect flash message
     */
    private function respondOrRedirect(bool $success, string $message, string $redirectUrl): void
    {
        if ($success && class_exists('Notification')) {
            $uid = (int)Auth::id();
            if ($uid > 0) {
                Notification::send($uid, $uid, 'activity', 0, $message);
            }
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => $success,
                'message' => $message,
                'redirect' => $redirectUrl
            ]);
            return;
        }

        if ($success) {
            Session::flash('success', $message);
        } else {
            Session::flash('error', $message);
        }
        $this->redirect($redirectUrl);
    }
}
