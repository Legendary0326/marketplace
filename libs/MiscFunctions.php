<?php
if (!defined('nl')) define('nl', "\r\n");

function ifDebug($string, $return = false)
{
    if (defined('DEVELOPMENT') && DEVELOPMENT === true) {
        if ($return) {
            return $string;
        } else {
            echo $string;
        }
    }
}

function toFloat($text)
{
    return (float) preg_replace("/\,/", ".", preg_replace("/\./", "", $text));
}

function devCodeGPG()
{
    if (!defined('DEVELOPMENT') || DEVELOPMENT !== true) return false;
    if (!isset($_SESSION['gpg_code'])) return false;
    echo ' value="' . htmlentities($_SESSION['gpg_code'] ?? '') . '"';
}

function devCodeCAPTCHA()
{
    if (!defined('DEVELOPMENT') || DEVELOPMENT !== true) return false;
    if (!isset($_SESSION['Captcha_Token'])) return false;
    echo ' value="' . htmlentities($_SESSION['Captcha_Token'] ?? '') . '"';
}

function guessMoney($num)
{
    if (preg_match('/[^\d\,\.]+/', $num)) return 0;
    if (preg_match('/[\,\.]{2,}/', $num)) return 0;
    if (preg_match('/^[\,\.]$/', $num)) return 0;
    if (preg_match('/[\,\.]$/', $num)) return 0;
    preg_match_all('/([\,\.])/u', $num, $erg);
    if (!isset($erg[1])) return floatval($num);
    $dsep = array_pop($erg[1]);
    if (count($erg[1]) == 0) {
        if ($dsep == ',') $num = preg_replace('/\,/', '.', $num);
        return floatval($num);
    }
    if (preg_match_all('/' . preg_quote($dsep) . '/', $num) !== 1) return 0;
    if ($dsep == ',') {
        $num = preg_replace('/\./', '', $num);
        $num = preg_replace('/\,/', '.', $num);
    } else if ($dsep == '.') {
        $num = preg_replace('/\,/', '', $num);
    }
    return floatval($num);
}