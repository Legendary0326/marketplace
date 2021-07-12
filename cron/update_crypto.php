<?php
if (!defined('CRON_INCLUDED')) die('Permission denied');

$Coins = [
    'BTC',
    'LTC',
    'XMR'
];

foreach ($Coins as $Coin) {
    $cURL = curl_init();
    curl_setopt($cURL, CURLOPT_URL, 'https://rest.coinapi.io/v1/exchangerate/' . $Coin . '?invert=false');
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURL, CURLOPT_HTTPHEADER, ['X-CoinAPI-Key: ' . COIN_IO_KEY]);
    $Response = curl_exec($cURL);
    curl_close($cURL);
    $json = json_decode($Response, true);
    if (isset($json['rates'])) {
        foreach ($json['rates'] as $rate) {
            preg_match('/^(\d{4})\-(\d{2})\-(\d{2})T(\d{2}):(\d{2}):(\d{2})\.(\d+)Z$/', $rate['time'], $erg); //2020-12-08T23:11:52.2463553Z
            $Time = mktime($erg[4], $erg[5], $erg[6], $erg[2], $erg[3], $erg[1]);
            $DB->query('INSERT INTO exchangerates (Time, Rate, Currency, Crypto) VALUES (' . $DB->int($Time) . ', ' . $DB->float($rate['rate']) . ', ' . $DB->string($rate['asset_id_quote']) . ', ' . $DB->string($Coin) . ') ON DUPLICATE KEY UPDATE Time = ' . $DB->int($Time) . ', Rate = ' . $DB->float($rate['rate']));
        }
    }
    echo $Coin . ': ' . count($json['rates']) . "\r\n";
}
