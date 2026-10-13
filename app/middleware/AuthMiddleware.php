<?php

class AuthMiddleware
{
    public static function handle(): bool
    {
        if (Session::has('user')) {
            return true;
        }

        Session::flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        header('Location: ' . BASE_URL . '/login', true, 303);
        return false;
    }
}