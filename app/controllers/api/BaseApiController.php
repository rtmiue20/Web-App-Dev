<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Session.php';
require_once __DIR__ . '/../../helpers/Auth.php';
require_once __DIR__ . '/../../helpers/Csrf.php';

/**
 * BaseApiController - Lớp cơ sở cho toàn bộ RESTful API Nội bộ trả JSON cho AJAX.
 *
 * Đặc điểm chuẩn RESTful:
 *  - Đọc dữ liệu đầu vào linh hoạt từ JSON body (`php://input`), `$_POST`, hoặc `$_GET`.
 *  - Xác thực CSRF token bắt buộc trên mọi phương thức thay đổi trạng thái (`POST`, `PUT`, `PATCH`, `DELETE`).
 *  - Trả về JSON đồng nhất theo cấu trúc: `{"success": bool, "data": mixed, "message": string}`.
 *  - Sử dụng đúng mã trạng thái HTTP (`200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `419 CSRF Expired`, `422 Unprocessable Entity`).
 */
abstract class BaseApiController extends Controller
{
    /**
     * Đọc và giải mã dữ liệu JSON gửi từ `fetch()` (`Content-Type: application/json`) kết hợp `$_POST`.
     */
    protected function getJsonInput(): array
    {
        $rawInput = file_get_contents('php://input');
        $decoded = (is_string($rawInput) && $rawInput !== '') ? json_decode($rawInput, true) : [];
        if (!is_array($decoded)) {
            $decoded = [];
        }
        return array_merge($_POST, $decoded);
    }

    /**
     * Kiểm tra CSRF token từ JSON body (`_csrf`), `$_POST['_csrf']` hoặc HTTP Header `X-CSRF-TOKEN`.
     */
    protected function requireCsrf(array $input = []): void
    {
        $token = (string)($input['_csrf'] ?? $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!Csrf::verify($token)) {
            Response::fail('CSRF token không hợp lệ hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.', 419);
        }
    }

    /**
     * Yêu cầu người dùng đã đăng nhập (trả HTTP 401 nếu chưa đăng nhập).
     */
    protected function requireAuth(string $message = 'Vui lòng đăng nhập để thực hiện thao tác này.'): array
    {
        if (!Auth::check()) {
            Response::fail($message, 401);
        }
        return (array)Session::get('user', []);
    }

    /**
     * Yêu cầu tài khoản Thành viên hợp lệ (trả HTTP 401 nếu là khách).
     */
    protected function requireMember(string $message = 'Vui lòng đăng nhập tài khoản Thành viên để thực hiện thao tác này.'): array
    {
        if (!Auth::isMember()) {
            Response::fail($message, 401);
        }
        return (array)Session::get('user', []);
    }
}

