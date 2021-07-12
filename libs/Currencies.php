<?php
class Currencies
{
    private static $cache = [];

    static function getCurrencies()
    {
        global $DB;
        $currencies = $DB->get('currencies', '', 'ORDER BY Sort, Name');
        $all = [];
        foreach ($currencies as $currency) {
            $all[$currency['Code']] = $currency;
        }
        return $all;
    }

    static function getExchange($Cur, $Cry)
    {
        $cid = $Cur . '>' . $Cry;
        if (isset(self::$cache[$cid])) return self::$cache[$cid];
        global $DB;
        $Currency = $DB->getOne('exchangerates', 'Currency = ' . $DB->string($Cur) . ' AND Crypto = ' . $DB->string($Cry));
        if (isset($Currency['Rate']) && !empty($Currency['Rate'])) {
            return self::$cache[$cid] = $Currency['Rate'];
        }
        return self::$cache[$cid] = false;
    }

    static function exchange($amount, $from, $to, $format = true)
    {
        global $User;
        $from = strtoupper($from);
        $to = strtoupper($to);
        $return = false;
        $precision = 2;
        if ($from == 'USER') $from = $User->getCurrency();
        if ($to == 'USER') $to = $User->getCurrency();
        if ($amount == 0) {
            $return = 0;
        } else {
            if ($from == $to) {
                $return = $amount;
            } else if ($from == 'XMR' || $from == 'BTC' || $from == 'LTC') {
                $precision = 2;
                $return = $amount * self::getExchange($to, $from);
            } else if ($to == 'XMR' || $to == 'BTC' || $to == 'LTC') {
                $precision = 8;
                $return = $amount / self::getExchange($from, $to);
            } else if ($from == 'USD') {
                $precision = 2;
                $return = $amount * self::getExchange($to, $from);
            } else if ($to == 'USD') {
                $precision = 2;
                $return = $amount / self::getExchange($from, $to);
            }
        }
        if ($format) {
            global $Language;
            $return = $Language->number($return, $precision);
        }
        return $return;
    }
}