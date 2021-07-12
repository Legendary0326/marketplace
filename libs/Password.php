<?php
class Password
{
    static function checkComplexity($pass)
    {
        if (preg_match("/ /", $pass)) {
            return false;
        } else if (empty($pass) || !preg_match("/\d+/", $pass) || !preg_match("/[a-zA-Z]+/", $pass)) {
            return false;
        } else if (strlen($pass) <= 7) {
            return false;
        } else if (strlen($pass) >= 41) {
            return false;
        } else {
            return true;
        }
    }

    static function hash($pass)
    {
        return password_hash(base64_encode($pass), PASSWORD_BCRYPT);
    }

    static function verify($pass, $hash)
    {
        return password_verify(base64_encode($pass), $hash);
    }

    static function generate($num = 32, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_=?!')
    {
        if (!isset($chars)) return false;
        $pw = '';
        for($i = 0; $i < $num; $i++) {
            $pw .= $chars[rand(0, strlen($chars)-1)];
        }
        return $pw;
    }
}