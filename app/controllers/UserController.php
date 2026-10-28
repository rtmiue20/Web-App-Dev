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
        Session::set('user', $sessionUser);
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

        return $user;
    }

    private function renderProfile(User $user, array $errors = [], array $old = []): void
    {
        $this->render('users/profile', [
            'user' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role_name' => $user->getRoleName(),
                'created_at' => $user->getCreatedAt(),
                'bio' => $user->getBio(),
                'avatar_path' => $user->getAvatarPath(),
            ],
            'errors' => $errors,
            'old' => $old,
        ]);
    }
}