<?php

class UserController extends Controller
{
    public function profile(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            return;
        }

        $this->renderProfile($user);
    }

    public function updateProfile(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            return;
        }

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $this->renderProfile($user, ['form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'], $_POST);
            return;
        }

        $errors = Validator::profile($_POST);
        $username = trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : '');
        $bio = trim(is_string($_POST['bio'] ?? null) ? $_POST['bio'] : '');
        if ($errors !== []) {
            $this->renderProfile($user, $errors, $_POST);
            return;
        }

        $avatarPath = $user->getAvatarPath();
        $avatarCropped = trim(is_string($_POST['avatar_cropped'] ?? null) ? $_POST['avatar_cropped'] : '');

        if ($avatarCropped !== '' && str_starts_with($avatarCropped, 'data:image/')) {
            try {
                $avatarPath = Upload::fromBase64($avatarCropped, 'avatars');
            } catch (Exception $exception) {
                $this->renderProfile($user, ['avatar' => $exception->getMessage()], $_POST);
                return;
            }
        } else {
            $avatarUpload = $_FILES['avatar'] ?? null;
            if ($avatarUpload !== null && !is_array($avatarUpload)) {
                $this->renderProfile($user, ['avatar' => 'File ảnh tải lên không hợp lệ.'], $_POST);
                return;
            }
            if (is_array($avatarUpload) && ($avatarUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                try {
                    $avatarPath = Upload::image($avatarUpload, 'avatars');
                } catch (Exception $exception) {
                    $this->renderProfile($user, ['avatar' => $exception->getMessage()], $_POST);
                    return;
                }
            }
        }

        try {
            $user->updateProfile($username, $bio, $avatarPath);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $this->renderProfile($user, ['username' => 'Tên người dùng này đã được sử dụng.'], $_POST);
                return;
            }

            error_log('Error updating user profile: ' . $exception->getMessage());
            http_response_code(500);
            $this->renderProfile($user, ['form' => 'Không thể cập nhật hồ sơ. Vui lòng thử lại sau.'], $_POST);
            return;
        }

        $sessionUser = Session::get('user', []);
        $sessionUser['username'] = $username;
        $sessionUser['bio'] = $bio;
        $sessionUser['avatar_path'] = $avatarPath;
        Session::set('user', $sessionUser);
        if (class_exists('Notification')) {
            Notification::send((int) $user->getId(), (int) $user->getId(), 'update_profile', (int) $user->getId(), 'Bạn đã cập nhật thông tin hồ sơ cá nhân thành công.');
        }
        Session::flash('success', 'Cập nhật hồ sơ thành công.');
        $this->redirect('/profile');
    }

    public function changePassword(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            return;
        }

        if (!$this->verifyCsrfToken()) {
            http_response_code(419);
            $this->renderProfile($user, ['password_form' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.']);
            return;
        }

        $errors = Validator::passwordChange($_POST);
        $currentPassword = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
        $newPassword = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
        if ($currentPassword !== '' && !password_verify($currentPassword, (string) $user->getPasswordHash())) {
            $errors['current_password'] = 'Mật khẩu hiện tại không chính xác.';
        }
        if ($errors !== []) {
            $this->renderProfile($user, $errors);
            return;
        }

        try {
            $user->changePassword(password_hash($newPassword, PASSWORD_DEFAULT));
        } catch (PDOException $exception) {
            error_log('Error changing user password: ' . $exception->getMessage());
            http_response_code(500);
            $this->renderProfile($user, ['password_form' => 'Không thể đổi mật khẩu. Vui lòng thử lại sau.']);
            return;
        }

        Session::regenerate();
        if (class_exists('Notification')) {
            Notification::send((int) $user->getId(), (int) $user->getId(), 'update_profile', (int) $user->getId(), 'Mật khẩu tài khoản của bạn đã được thay đổi thành công.');
        }
        Session::flash('success', 'Đổi mật khẩu thành công.');
        $this->redirect('/profile');
    }

    private function currentUser(): ?User
    {
        $sessionUser = Session::get('user');
        $user = (new User())->findById((int) ($sessionUser['id'] ?? 0));
        if ($user === null) {
            Session::destroy();
            $this->redirect('/login');
            return null;
        }

        // Đồng bộ role_id và role_name mới nhất từ CSDL vào Session
        if (is_array($sessionUser)) {
            $sessionUser['id'] = $user->getId();
            $sessionUser['role_id'] = $user->getRoleId();
            $sessionUser['role_name'] = $user->getRoleName();
            Session::set('user', $sessionUser);
        }

        return $user;
    }

    public function show(int $id): void
    {
        $targetUser = (new User())->findById($id);
        if ($targetUser === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Không tìm thấy người dùng']);
            return;
        }

        $currentUserId = Auth::check() ? (int)Auth::id() : 0;
        $gamification = Gamification::calculate($id);

        $followModel = new Follow();
        $isFollowing = $followModel->isFollowingUser($currentUserId, $id);
        $followersCount = $followModel->getFollowersCount($id);
        $followingCount = $followModel->getFollowingCount($id);

        $posts = [];
        try {
            $posts = Database::query(
                "SELECT p.*, c.name AS category_name,
                        (SELECT image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                        COALESCE((SELECT SUM(vote) FROM post_likes WHERE post_id = p.id), 0) AS vote_score,
                        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count
                 FROM posts p
                 JOIN categories c ON c.id = p.category_id
                 WHERE p.user_id = :uid AND p.status = 'published'
                 ORDER BY p.created_at DESC",
                ['uid' => $id]
            );
        } catch (Exception $e) {}

        $this->render('users/show', [
            'title'          => 'Hồ sơ của ' . $targetUser->getUsername(),
            'targetUser'     => [
                'id'          => $targetUser->getId(),
                'username'    => $targetUser->getUsername(),
                'email'       => $targetUser->getEmail(),
                'bio'         => $targetUser->getBio(),
                'avatar_path' => $targetUser->getAvatarPath(),
                'role_name'   => $targetUser->getRoleName(),
                'created_at'  => $targetUser->getCreatedAt(),
            ],
            'gamification'   => $gamification,
            'isFollowing'    => $isFollowing,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'posts'          => $posts,
            'isOwnProfile'   => ($currentUserId === $id),
        ]);
    }

    private function renderProfile(User $user, array $errors = [], array $old = []): void
    {
        $userId = $user->getId();
        $gamification = Gamification::calculate($userId);

        $bookmarkModel = new Bookmark();
        $bookmarks = $bookmarkModel->getByUserId($userId, 1, 20);

        $followModel = new Follow();
        $followersCount = $followModel->getFollowersCount($userId);
        $followingCount = $followModel->getFollowingCount($userId);
        $followingUsers = $followModel->getFollowingUsers($userId, 20);
        $followingCategories = $followModel->getFollowingCategories($userId);

        $myPosts = [];
        try {
            $myPosts = Database::query(
                "SELECT p.*, c.name AS category_name,
                        (SELECT image_url FROM post_images pi WHERE pi.post_id = p.id LIMIT 1) AS image_url,
                        COALESCE((SELECT SUM(vote) FROM post_likes WHERE post_id = p.id), 0) AS vote_score,
                        (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count
                 FROM posts p
                 JOIN categories c ON c.id = p.category_id
                 WHERE p.user_id = :uid
                 ORDER BY p.created_at DESC",
                ['uid' => $userId]
            );
        } catch (Exception $e) {}

        $activities = [];
        try {
            $notifModel = new Notification();
            $activities = $notifModel->getByUserId($userId, 60);
        } catch (Throwable $notifEx) {
            error_log('Error loading activities for profile: ' . $notifEx->getMessage());
        }

        $activeTab = trim((string)($_GET['tab'] ?? 'edit'));
        if (!in_array($activeTab, ['edit', 'posts', 'bookmarks', 'following', 'activity'], true)) {
            $activeTab = 'edit';
        }

        $this->render('users/profile', [
            'user' => [
                'id'          => $user->getId(),
                'username'    => $user->getUsername(),
                'email'       => $user->getEmail(),
                'role_id'     => $user->getRoleId(),
                'role_name'   => $user->getRoleName(),
                'created_at'  => $user->getCreatedAt(),
                'bio'         => $user->getBio(),
                'avatar_path' => $user->getAvatarPath(),
            ],
            'gamification'        => $gamification,
            'bookmarks'           => $bookmarks,
            'followersCount'      => $followersCount,
            'followingCount'      => $followingCount,
            'followingUsers'      => $followingUsers,
            'followingCategories' => $followingCategories,
            'myPosts'             => $myPosts,
            'activities'          => $activities,
            'activeTab'           => $activeTab,
            'errors'              => $errors,
            'old'                 => $old,
        ]);
    }
}
