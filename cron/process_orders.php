<?php
if (!defined('CRON_INCLUDED')) die('Permission denied');

//1209600

$Orders = $DB->get('orders', '((StatusChanged <= UNIX_TIMESTAMP() - 1209600 AND Class = ' . $DB->string('physical') . ') OR (StatusChanged <= UNIX_TIMESTAMP() - 259200 AND Class = ' . $DB->string('digital') . ')) AND DisputeRequested = 0 AND Status = ' . $DB->string('Shipped'));
if (count($Orders) >= 1) {
    require '../libs/Currencies.php';
    require '../libs/Market.php';
    require '../libs/Ranking.php';
    foreach ($Orders as $Order) {
        echo 'Finalizing order ' . $Order['OrderID'] . '... ';
        $Subtotal = $Order['Price'] * $Order['Quantity'];
        $Total = $Subtotal + $Order['ShippingPrice'];
        $User = $DB->getOne('user', 'UserID = ' . $DB->int($Order['Vendor']));
        $PayoutCrypto = round($Order['PayAmount'] / 100 * (100 - $User['VendorFee']), 8);
        $FeeCrypto = $Order['PayAmount'] - $PayoutCrypto;
        $DB->update('orders', ['Status' => 'Finalized', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($Order['OrderID']));
        Market::transfer($FeeCrypto, $Order['PayWith'], $Order['Payment'], 'fee'); //Transfer fees to fee account
        Market::transfer($PayoutCrypto, $Order['PayWith'], $Order['Payment'], $Order['Vendor']); //Pay vendor
        $DB->query('UPDATE user SET Orders = Orders + 1 WHERE UserID = ' . $DB->int($Order['Buyer']));
        $DB->query('UPDATE user SET Sales = Sales + 1 WHERE UserID = ' . $DB->int($Order['Vendor']));
        $DB->query('UPDATE items SET Sales = Sales + 1 WHERE ItemID = ' . $DB->int($Order['Item']));
        if ($Total >= 100) {
            Ranking::addScore($Order['Vendor'], 1.5);
            Ranking::addScore($Order['Buyer'], 1.5);
        } else if ($Total >= 50) {
            Ranking::addScore($Order['Vendor'], 1);
            Ranking::addScore($Order['Buyer'], 1);
        } else {
            Ranking::addScore($Order['Vendor'], 0.5);
            Ranking::addScore($Order['Buyer'], 0.5);
        }
        echo 'Done!' . nl;
    }
} else {
    echo 'Nothing to do!';
}
