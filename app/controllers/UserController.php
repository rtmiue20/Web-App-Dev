<?php

class UserController extends Controller
{
    public function profile(): void
    {
        $sessionUser = Session::get('user');
        $user = (new User())->findById((int) $sessionUser['id']);
        if ($user === null) {
            Session::destroy();
            $this->redirect('/login');
        }

        $this->render('users/profile', [
            'user' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role_name' => $user->getRoleName(),
                'created_at' => $user->getCreatedAt(),
            ],
            'success' => Session::flash('success'),
        ]);
    }
}