<?php
if (!defined('CRON_INCLUDED')) die('Permission denied');

$cURL = curl_init();
curl_setopt($cURL, CURLOPT_URL, 'https://api.exchangerate-api.com/v4/latest/USD');
curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
$Response = curl_exec($cURL);
curl_close($cURL);
$json = json_decode($Response, true);
if (isset($json['rates'])) {
    foreach ($json['rates'] as $cur => $rate) {
        $DB->query('INSERT INTO exchangerates (Time, Rate, Currency, Crypto) VALUES (' . $DB->int($json['time_last_updated']) . ', ' . $DB->float($rate) . ', ' . $DB->string($cur) . ', ' . $DB->string('USD') . ') ON DUPLICATE KEY UPDATE Time = ' . $DB->int($json['time_last_updated']) . ', Rate = ' . $DB->float($rate));
    }
}
echo 'USD: ' . count($json['rates']) . "\r\n";
