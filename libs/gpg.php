<?php
class gpg
{
    static function encrypt($msg)
    {
        global $User;
        try {
            $PGP = $User->get('PGP');
            if (empty($PGP)) return false;
            putenv('GNUPGHOME=' . TMPPATH);
            $gpg = new gnupg();
            $key = $gpg->import($PGP);
            $gpg->addencryptkey($key['fingerprint']);
            $enc = $gpg->encrypt($msg);
            $gpg->clearencryptkeys();
            return $enc;
        } catch(Exception $e) {
            return false;
        }
    }

    static function newCode()
    {
        $chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';
        for($i = 0; $i < 10; $i++) {
            $code .= $chars[rand(0, strlen($chars)-1)];
        }
        $_SESSION['gpg_code'] = $code;
        return self::encrypt($code);
    }

    static function validateCode($code)
    {
        if (isset($_SESSION['gpg_code']) && $_SESSION['gpg_code'] === $code) {
            $_SESSION['gpg_code'] = Password::generate();
            return true;
        }
        return false;
    }
}
