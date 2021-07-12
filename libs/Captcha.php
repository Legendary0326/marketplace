<?php
class Captcha
{
    static function get()
    {
        $width = 250;
        $height = 80;
        $font_size = 30;
        $font = MAINPATH . 'misc/verdana.ttf';
        $chars_length = 5;

        $captcha_characters = '123456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

        $image = imagecreatetruecolor($width, $height);
        $bg_color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
        imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

        //background ellipse
        for ($i=0; $i < 30; $i++) { 
            $color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
            imageellipse($image, rand(0,$width), rand(0,$height), rand(5,100), rand(5,50), $color);
        }

        $xw = ($width/$chars_length);
        $x = 0;
        $font_gap = $xw/2-$font_size/2;
        $digit = '';
        for($i = 0; $i < $chars_length; $i++) {
            $letter = $captcha_characters[rand(0, strlen($captcha_characters)-1)];
            $digit .= $letter;
            $font_color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
            if ($i == 0) {
                $x = 0;
            }else {
                $x = $xw*$i;
            }
            imagettftext($image, $font_size, rand(-20,20), $x+$font_gap, rand(33, $height-5), $font_color, $font, $letter);
        }

        // record token in session variable
        $_SESSION['Captcha_Token'] = $digit;

        ob_start();
        imagepng($image);
        $img = ob_get_clean();
        imagedestroy($image);
        return 'data:image/png;base64,' . base64_encode($img);
    }

    static function verify()
    {
        global $User;
        if (defined('DISABLEADMINCAPTCHA') && DISABLEADMINCAPTCHA && ($User->hasRole('admin') || $User->hasRole('staff'))) return true;
        if (!isset($_SESSION['Captcha_Token'])) return false;
        if (!isset($_POST['Captcha'])) return false;
        if (!preg_match('/^[a-zA-Z0-9]{5}$/', $_POST['Captcha'])) return false;
        if ($_SESSION['Captcha_Token'] == $_POST['Captcha']) {
            unset($_SESSION['Captcha_Token']);
            return true;
        }
        return false;
    }

    static function showCaptcha()
    {
        global $User;
        if (defined('DISABLEADMINCAPTCHA') && DISABLEADMINCAPTCHA && ($User->hasRole('admin') || $User->hasRole('staff'))) return false;
        return true;
    }

    static function issetCaptcha()
    {
        if (isset($_POST['Captcha']) || !self::showCaptcha()) return true;
        return false;
    }
}
