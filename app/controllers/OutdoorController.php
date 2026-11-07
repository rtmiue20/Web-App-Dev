<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Checklist.php';
require_once __DIR__ . '/../models/Trip.php';
require_once __DIR__ . '/../models/PlaceReview.php';
require_once __DIR__ . '/../models/Post.php';

class OutdoorController extends Controller
{
    private Checklist $checklists;
    private Trip $trips;
    private PlaceReview $reviews;
    private Post $posts;

    public function __construct()
    {
        $this->checklists = new Checklist();
        $this->trips = new Trip();
        $this->reviews = new PlaceReview();
        $this->posts = new Post();
    }

    public function checklists(): void
    {
        $user = Session::get('user');
        $userId = is_array($user) ? (int) $user['id'] : null;
        $tripType = trim((string) ($_GET['type'] ?? ''));

        $this->render('outdoor/checklists', [
            'title' => 'Checklist dã ngoại — Danh sách trang bị',
            'checklists' => $this->checklists->getVisibleForUser($userId, $tripType ?: null),
            'tripTypes' => $this->checklists->getTripTypes(),
            'currentType' => $tripType,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function storeChecklist(): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/checklists');
        }

        $user = Auth::user();
        if (!is_array($user) || !Auth::isMember($user)) {
            Session::flash('error', Auth::denyGuestMessage('tạo checklist dã ngoại'));
            $this->redirect(Auth::check() ? '/checklists' : '/login');
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $tripType = trim((string) ($_POST['trip_type'] ?? ''));
        $items = array_values(array_filter(
            array_map('trim', preg_split('/\R/', (string) ($_POST['items'] ?? '')) ?: []),
            static fn(string $item): bool => $item !== ''
        ));
        $errors = [];
        if (mb_strlen($title) < 3 || mb_strlen($title) > 120) {
            $errors['title'] = 'Tên checklist cần từ 3 đến 120 ký tự.';
        }
        if ($tripType === '' || mb_strlen($tripType) > 60) {
            $errors['trip_type'] = 'Vui lòng nhập loại chuyến đi (tối đa 60 ký tự).';
        }
        if (count($items) < 1 || count($items) > 50 || count(array_filter($items, static fn($item) => mb_strlen($item) > 120)) > 0) {
            $errors['items'] = 'Nhập từ 1 đến 50 món đồ, mỗi dòng không quá 120 ký tự.';
        }

        if ($errors !== []) {
            $this->render('outdoor/checklists', [
                'title' => 'Checklist dã ngoại — Danh sách trang bị',
                'checklists' => $this->checklists->getVisibleForUser((int) $user['id']),
                'tripTypes' => $this->checklists->getTripTypes(),
                'currentType' => '',
                'errors' => $errors,
                'old' => $_POST,
            ]);
            return;
        }

        try {
            $checklistId = $this->checklists->create(
                (int) $user['id'],
                $title,
                $tripType,
                isset($_POST['is_public']),
                $items
            );
            if (class_exists('Notification') && isset($user['id'])) {
                Notification::send((int)$user['id'], (int)$user['id'], 'create_checklist', $checklistId, "Bạn đã tạo checklist dã ngoại: '" . mb_substr($title, 0, 38) . "'.");
            }
            Session::flash('success', 'Đã tạo checklist dã ngoại thành công.');
            $this->redirect('/checklists');
        } catch (Throwable $e) {
            error_log('Create checklist failed: ' . $e->getMessage());
            Session::flash('error', 'Không thể lưu checklist vào lúc này. Vui lòng thử lại.');
            $this->redirect('/checklists');
        }
    }

    public function deleteChecklist(int $id): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/checklists');
        }

        $user = Auth::user();
        if (!is_array($user) || !Auth::isMember($user)) {
            Session::flash('error', Auth::denyGuestMessage('xóa checklist'));
            $this->redirect(Auth::check() ? '/checklists' : '/login');
        }

        $isAdmin = Auth::isModerator($user);
        $success = $this->checklists->delete($id, (int) $user['id'], $isAdmin);

        if ($success) {
            Session::flash('success', 'Đã xóa checklist thành công.');
        } else {
            Session::flash('error', 'Bạn không có quyền xóa checklist này.');
        }
        $this->redirect('/checklists');
    }

    public function trips(): void
    {
        $user = Session::get('user');
        $userId = is_array($user) ? (int) $user['id'] : null;
        $filter = trim((string) ($_GET['filter'] ?? 'upcoming'));

        $this->render('outdoor/trips', [
            'title' => 'Nhóm đi chung — Rủ bạn đồng hành dã ngoại',
            'trips' => $this->trips->getAll($filter, $userId),
            'currentFilter' => $filter,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function storeTrip(): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/trips');
        }

        $user = Auth::user();
        if (!is_array($user) || !Auth::isMember($user)) {
            Session::flash('error', Auth::denyGuestMessage('đăng chuyến đi chung'));
            $this->redirect(Auth::check() ? '/trips' : '/login');
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $dateInput = trim((string) ($_POST['starts_at'] ?? ''));
        $maxParticipants = filter_var($_POST['max_participants'] ?? null, FILTER_VALIDATE_INT);
        $latInput = trim((string) ($_POST['lat'] ?? ''));
        $lngInput = trim((string) ($_POST['lng'] ?? ''));
        $lat = $latInput === '' ? null : filter_var($latInput, FILTER_VALIDATE_FLOAT);
        $lng = $lngInput === '' ? null : filter_var($lngInput, FILTER_VALIDATE_FLOAT);
        $errors = [];

        // Parse datetime linh hoạt hỗ trợ cả Y-m-d\TH:i và Y-m-d\TH:i:s
        $startsAt = DateTime::createFromFormat('Y-m-d\TH:i', $dateInput)
            ?: DateTime::createFromFormat('Y-m-d\TH:i:s', $dateInput);
        if (!$startsAt) {
            try {
                $startsAt = new DateTime($dateInput);
            } catch (Throwable $e) {
                $startsAt = null;
            }
        }

        if (mb_strlen($title) < 5 || mb_strlen($title) > 150) {
            $errors['title'] = 'Tên chuyến đi cần từ 5 đến 150 ký tự.';
        }
        if (mb_strlen($description) < 10 || mb_strlen($description) > 3000) {
            $errors['description'] = 'Mô tả cần từ 10 đến 3000 ký tự.';
        }
        if ($location === '' || mb_strlen($location) > 150) {
            $errors['location'] = 'Vui lòng nhập địa điểm (tối đa 150 ký tự).';
        }
        if (!$startsAt || $startsAt <= new DateTime()) {
            $errors['starts_at'] = 'Thời gian khởi hành phải ở trong tương lai.';
        }
        if ($maxParticipants === false || $maxParticipants < 2 || $maxParticipants > 100) {
            $errors['max_participants'] = 'Số người tối đa từ 2 đến 100 (đã tính người tổ chức).';
        }
        if (($latInput !== '' && $lat === false) || ($lngInput !== '' && $lng === false)
            || (($lat === null) !== ($lng === null))
            || ($lat !== null && ($lat < -90 || $lat > 90))
            || ($lng !== null && ($lng < -180 || $lng > 180))) {
            $errors['coordinates'] = 'Vĩ độ/kinh độ phải hợp lệ và nhập cùng lúc.';
        }

        if ($errors !== []) {
            $this->render('outdoor/trips', [
                'title' => 'Nhóm đi chung — Rủ bạn đồng hành dã ngoại',
                'trips' => $this->trips->getAll('upcoming'),
                'currentFilter' => 'upcoming',
                'errors' => $errors,
                'old' => $_POST,
            ]);
            return;
        }

        try {
            $tripId = $this->trips->create(
                (int) $user['id'],
                $title,
                $description,
                $location,
                $lat === null ? null : (float) $lat,
                $lng === null ? null : (float) $lng,
                $startsAt->format('Y-m-d H:i:s'),
                (int) $maxParticipants
            );
                        if (class_exists('Notification') && isset($user['id']) && $tripId > 0) {
                Notification::send((int)$user['id'], (int)$user['id'], 'create_trip', $tripId, "Bạn đã đăng nhóm đi chung mới: '" . mb_substr($title, 0, 35) . "'.");
            }
            Session::flash('success', 'Đã đăng chuyến đi chung thành công.');
            $this->redirect('/trips/' . $tripId);
        } catch (Throwable $e) {
            error_log('Create trip failed: ' . $e->getMessage());
            Session::flash('error', 'Không thể lưu chuyến đi vào lúc này. Vui lòng thử lại.');
            $this->redirect('/trips');
        }
    }

    public function showTrip(int $id): void
    {
        $trip = $this->trips->getById($id);
        if (!$trip) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy chuyến đi']);
            return;
        }
        $user = Session::get('user');
        $userId = is_array($user) ? (int) $user['id'] : null;
        $joined = $userId !== null && $this->trips->hasJoined($id, $userId);
        $isOrganizer = $userId !== null && (int) $trip['user_id'] === $userId;
        $isAdmin = is_array($user) && (((int) ($user['role_id'] ?? 0) === 2) || (($user['role_name'] ?? '') === 'admin'));
        $participants = $this->trips->getParticipants($id);

        $this->render('outdoor/trip-show', [
            'title' => $trip['title'] . ' — Nhóm đi chung',
            'trip' => $trip,
            'joined' => $joined,
            'isOrganizer' => $isOrganizer,
            'isAdmin' => $isAdmin,
            'participants' => $participants,
        ]);
    }

    public function joinTrip(int $id): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/trips/' . $id);
        }

        $user = Auth::user();
        if (!is_array($user) || !Auth::isMember($user)) {
            Session::flash('error', Auth::denyGuestMessage('đăng ký tham gia chuyến đi'));
            $this->redirect(Auth::check() ? '/trips/' . $id : '/login');
        }

        try {
            $result = $this->trips->join($id, (int) $user['id']);
            $messages = [
                'joined' => ['success', 'Đăng ký tham gia chuyến đi thành công!'],
                'already' => ['success', 'Bạn đã đăng ký tham gia chuyến đi này rồi.'],
                'full' => ['error', 'Rất tiếc, chuyến đi này đã đủ số lượng thành viên.'],
                'missing' => ['error', 'Không tìm thấy chuyến đi yêu cầu.'],
                'closed' => ['error', 'Chuyến đi đã bắt đầu, không thể đăng ký thêm.'],
            ];
            [$type, $message] = $messages[$result] ?? ['error', 'Không thể xử lý yêu cầu.'];
            Session::flash($type, $message);

            if ($result === 'joined' && class_exists('Notification')) {
                $trip = $this->trips->getById($id);
                $tripTitle = mb_substr((string)($trip['title'] ?? 'chuyến đi'), 0, 35);
                Notification::send(
                    (int) $user['id'],
                    (int) $user['id'],
                    'join_trip',
                    $id,
                    "Bạn đã đăng ký tham gia chuyến đi: '{$tripTitle}'."
                );
                if (!empty($trip['user_id']) && (int)$trip['user_id'] !== (int)$user['id']) {
                    Notification::send(
                        (int) $trip['user_id'],
                        (int) $user['id'],
                        'trip_new_member',
                        $id,
                        htmlspecialchars($user['username'] ?? 'Một thành viên') . " đã đăng ký tham gia chuyến đi của bạn: '{$tripTitle}'."
                    );
                }
            }
        } catch (Throwable $e) {
            error_log('Join trip failed: ' . $e->getMessage());
            Session::flash('error', 'Không thể đăng ký tham gia lúc này. Vui lòng thử lại.');
        }
        $this->redirect('/trips/' . $id);
    }

    public function leaveTrip(int $id): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/trips/' . $id);
        }

        $user = Auth::user();
        if (!is_array($user) || !Auth::isMember($user)) {
            Session::flash('error', Auth::denyGuestMessage('hủy tham gia chuyến đi'));
            $this->redirect(Auth::check() ? '/trips/' . $id : '/login');
        }

        $success = $this->trips->leave($id, (int) $user['id']);
        if ($success) {
            Session::flash('success', 'Bạn đã hủy đăng ký tham gia chuyến đi này.');
            if (class_exists('Notification')) {
                $trip = $this->trips->getById($id);
                $tripTitle = mb_substr((string)($trip['title'] ?? 'chuyến đi'), 0, 35);
                Notification::send(
                    (int) $user['id'],
                    (int) $user['id'],
                    'leave_trip',
                    $id,
                    "Bạn đã hủy tham gia chuyến đi: '{$tripTitle}'."
                );
            }
        } else {
            Session::flash('error', 'Không thể hủy tham gia (bạn là người tổ chức hoặc chưa đăng ký).');
        }
        $this->redirect('/trips/' . $id);
    }

    public function deleteTrip(int $id): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/trips/' . $id);
        }

        $user = Auth::user();
        if (!is_array($user) || !Auth::isMember($user)) {
            Session::flash('error', Auth::denyGuestMessage('hủy chuyến đi'));
            $this->redirect(Auth::check() ? '/trips/' . $id : '/login');
        }

        $isAdmin = Auth::isModerator($user);
        $success = $this->trips->delete($id, (int) $user['id'], $isAdmin);

        if ($success) {
            Session::flash('success', 'Đã hủy chuyến đi thành công.');
            $this->redirect('/trips');
        } else {
            Session::flash('error', 'Bạn không có quyền hủy chuyến đi này.');
            $this->redirect('/trips/' . $id);
        }
    }

    public function map(): void
    {
        $this->render('outdoor/map', [
            'title' => 'Bản đồ địa điểm dã ngoại & Khám phá cung đường',
            'places' => $this->reviews->getPopularPlaces(),
        ]);
    }

    public function storeReview(int $postId): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/posts/' . $postId);
        }

        $user = Auth::user();
        if (!is_array($user) || !Auth::isMember($user)) {
            Session::flash('error', Auth::denyGuestMessage('gửi đánh giá địa điểm'));
            $this->redirect(Auth::check() ? '/posts/' . $postId : '/login');
        }

        $post = $this->posts->getById($postId);
        $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
        $review = trim((string) ($_POST['review'] ?? ''));
        if (!$post || trim((string) ($post['location'] ?? '')) === '') {
            Session::flash('error', 'Bài viết này chưa có địa điểm để đánh giá.');
            $this->redirect('/posts/' . $postId);
        }

        // Tác giả không được tự đánh giá bài đăng của mình (chỉ Admin và user khác được đánh giá)
        $isAdmin = (int) ($user['role_id'] ?? 0) === 2 || ($user['role_name'] ?? '') === 'admin';
        $isAuthor = (int) ($post['user_id'] ?? 0) === (int) $user['id'];
        if ($isAuthor && !$isAdmin) {
            Session::flash('error', 'Bạn là tác giả bài viết nên không thể tự đánh giá địa điểm của chính mình.');
            $this->redirect('/posts/' . $postId);
        }

        if ($rating === false || $rating < 1 || $rating > 5 || mb_strlen($review) < 5 || mb_strlen($review) > 1000) {
            Session::flash('error', 'Vui lòng chọn từ 1 đến 5 sao và viết nhận xét từ 5 đến 1000 ký tự.');
            $this->redirect('/posts/' . $postId);
        }

        try {
            $this->reviews->save($postId, (int) $user['id'], $rating, $review);
            Session::flash('success', 'Cảm ơn bạn! Đã lưu đánh giá địa điểm dã ngoại thành công.');
            if (class_exists('Notification')) {
                $loc = mb_substr((string) ($post['location'] ?? 'địa điểm'), 0, 35);
                Notification::send(
                    (int) $user['id'],
                    (int) $user['id'],
                    'review_place',
                    $postId,
                    "Bạn đã gửi đánh giá cho địa điểm: '{$loc}'."
                );
                if (!empty($post['user_id']) && (int)$post['user_id'] !== (int)$user['id']) {
                    Notification::send(
                        (int) $post['user_id'],
                        (int) $user['id'],
                        'review_place',
                        $postId,
                        htmlspecialchars($user['username'] ?? 'Một thành viên') . " đã đánh giá địa điểm bài viết của bạn: '{$loc}'."
                    );
                }
            }
        } catch (Throwable $e) {
            error_log('Save place review failed: ' . $e->getMessage());
            Session::flash('error', 'Không thể lưu đánh giá vào lúc này. Vui lòng thử lại.');
        }
        $this->redirect('/posts/' . $postId);
    }
}
