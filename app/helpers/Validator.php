<?php

class Validator
{
    public static function registration(array $input): array
    {
        $errors = [];
        $username = trim(self::stringValue($input, 'username'));
        $email = trim(self::stringValue($input, 'email'));
        $password = self::stringValue($input, 'password');

        if ($username === '' || strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Tên người dùng phải có từ 3 đến 50 ký tự.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
            $errors['email'] = 'Vui lòng nhập địa chỉ email hợp lệ.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
        }
        if ($password !== self::stringValue($input, 'password_confirmation')) {
            $errors['password_confirmation'] = 'Mật khẩu xác nhận không khớp.';
        }

        return $errors;
    }

    public static function login(array $input): array
    {
        $errors = [];
        if (trim(self::stringValue($input, 'email')) === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        }
        if (self::stringValue($input, 'password') === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        }

        return $errors;
    }

    private static function stringValue(array $input, string $key): string
    {
        return isset($input[$key]) && is_string($input[$key]) ? $input[$key] : '';
    }
}