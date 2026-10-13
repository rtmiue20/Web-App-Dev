<?php

class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }

        return $token;
    }

    public static function verify($token): bool
    {
        $knownToken = Session::get('_csrf_token');
        return is_string($token)
            && is_string($knownToken)
            && hash_equals($knownToken, $token);
    }
}
