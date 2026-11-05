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
        $this->render('outdoor/checklists', [
            'title' => 'Checklist dã ngoại',
            'checklists' => $this->checklists->getVisibleForUser(
                is_array($user) ? (int) $user['id'] : null
            ),
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
            $user = Session::get('user');
            $this->render('outdoor/checklists', [
                'title' => 'Checklist dã ngoại',
                'checklists' => $this->checklists->getVisibleForUser((int) $user['id']),
                'errors' => $errors,
                'old' => $_POST,
            ]);
            return;
        }

        try {
            $user = Session::get('user');
            $this->checklists->create(
                (int) $user['id'],
                $title,
                $tripType,
                isset($_POST['is_public']),
                $items
            );
            Session::flash('success', 'Đã tạo checklist.');
            $this->redirect('/checklists');
        } catch (PDOException $e) {
            error_log('Create checklist failed: ' . $e->getMessage());
            Session::flash('error', 'Không thể lưu checklist. Hãy chạy migration 004 trước.');
            $this->redirect('/checklists');
        }
    }

    public function trips(): void
    {
        $this->render('outdoor/trips', [
            'title' => 'Nhóm đi chung',
            'trips' => $this->trips->getAll(),
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

        $startsAt = DateTime::createFromFormat('Y-m-d\TH:i', $dateInput);
        if (mb_strlen($title) < 5 || mb_strlen($title) > 150) {
            $errors['title'] = 'Tên chuyến đi cần từ 5 đến 150 ký tự.';
        }
        if (mb_strlen($description) < 10 || mb_strlen($description) > 3000) {
            $errors['description'] = 'Mô tả cần từ 10 đến 3000 ký tự.';
        }
        if ($location === '' || mb_strlen($location) > 150) {
            $errors['location'] = 'Vui lòng nhập địa điểm (tối đa 150 ký tự).';
        }
        if (!$startsAt || $startsAt->format('Y-m-d\TH:i') !== $dateInput || $startsAt <= new DateTime()) {
            $errors['starts_at'] = 'Ngày đi phải hợp lệ và ở trong tương lai.';
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
                'title' => 'Nhóm đi chung',
                'trips' => $this->trips->getAll(),
                'errors' => $errors,
                'old' => $_POST,
            ]);
            return;
        }

        try {
            $user = Session::get('user');
            $this->trips->create(
                (int) $user['id'],
                $title,
                $description,
                $location,
                $lat === null ? null : (float) $lat,
                $lng === null ? null : (float) $lng,
                $startsAt->format('Y-m-d H:i:s'),
                (int) $maxParticipants
            );
            Session::flash('success', 'Đã đăng chuyến đi.');
            $this->redirect('/trips');
        } catch (PDOException $e) {
            error_log('Create trip failed: ' . $e->getMessage());
            Session::flash('error', 'Không thể lưu chuyến đi. Hãy chạy migration 004 trước.');
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
        $joined = is_array($user) && $this->trips->hasJoined($id, (int) $user['id']);
        $this->render('outdoor/trip-show', [
            'title' => $trip['title'],
            'trip' => $trip,
            'joined' => $joined,
        ]);
    }

    public function joinTrip(int $id): void
    {
        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/trips/' . $id);
        }

        try {
            $user = Session::get('user');
            $result = $this->trips->join($id, (int) $user['id']);
            $messages = [
                'joined' => ['success', 'Đăng ký tham gia thành công.'],
                'already' => ['success', 'Bạn đã đăng ký chuyến đi này.'],
                'full' => ['error', 'Chuyến đi đã đủ số người.'],
                'missing' => ['error', 'Không tìm thấy chuyến đi.'],
                'closed' => ['error', 'Chuyến đi đã bắt đầu, không thể đăng ký thêm.'],
            ];
            [$type, $message] = $messages[$result];
            Session::flash($type, $message);
        } catch (PDOException $e) {
            error_log('Join trip failed: ' . $e->getMessage());
            Session::flash('error', 'Không thể đăng ký tham gia. Hãy kiểm tra database.');
        }
        $this->redirect('/trips/' . $id);
    }

    public function map(): void
    {
        $this->render('outdoor/map', [
            'title' => 'Bản đồ địa điểm',
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

        $post = $this->posts->getById($postId);
        $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
        $review = trim((string) ($_POST['review'] ?? ''));
        if (!$post || trim((string) ($post['location'] ?? '')) === '') {
            Session::flash('error', 'Bài viết này chưa có địa điểm để đánh giá.');
            $this->redirect('/posts/' . $postId);
        }
        if ($rating === false || $rating < 1 || $rating > 5 || mb_strlen($review) < 5 || mb_strlen($review) > 1000) {
            Session::flash('error', 'Chọn từ 1 đến 5 sao và viết đánh giá từ 5 đến 1000 ký tự.');
            $this->redirect('/posts/' . $postId);
        }

        try {
            $user = Session::get('user');
            $this->reviews->save($postId, (int) $user['id'], $rating, $review);
            Session::flash('success', 'Đã lưu đánh giá địa điểm.');
        } catch (PDOException $e) {
            error_log('Save place review failed: ' . $e->getMessage());
            Session::flash('error', 'Không thể lưu đánh giá. Hãy chạy migration 004 trước.');
        }
        $this->redirect('/posts/' . $postId);
    }
}
