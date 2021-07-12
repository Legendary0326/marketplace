<?php
if (!defined('CRON_INCLUDED')) die('Permission denied');

$Today = mktime(0, 0, 0);
$date = new DateTime();
$date->sub(new DateInterval('P7D'));
$Last7Days = $date->format('U');
$date = new DateTime();
$date->sub(new DateInterval('P30D'));
$Last30Days = $date->format('U');


$DB->query('UPDATE stats_marketplace_profit SET
XMR = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('XMR') . ' AND Recipient = ' . $DB->string('fee') . ' AND Date >= ' . $DB->int($Today) . '),
BTC = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('BTC') . ' AND Recipient = ' . $DB->string('fee') . ' AND Date >= ' . $DB->int($Today) . '),
LTC = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('LTC') . ' AND Recipient = ' . $DB->string('fee') . ' AND Date >= ' . $DB->int($Today) . '),
USD = (SELECT SUM(USD) FROM transactions WHERE Recipient = ' . $DB->string('fee') . ' AND Date >= ' . $DB->int($Today) . ')
WHERE Period = ' . $DB->string('today'));
$DB->query('UPDATE stats_marketplace_profit SET
XMR = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('XMR') . ' AND Recipient = ' . $DB->string('fee') . ' AND Date >= ' . $DB->int($Last30Days) . '),
BTC = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('BTC') . ' AND Recipient = ' . $DB->string('fee') . ' AND Date >= ' . $DB->int($Last30Days) . '),
LTC = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('LTC') . ' AND Recipient = ' . $DB->string('fee') . ' AND Date >= ' . $DB->int($Last30Days) . '),
USD = (SELECT SUM(USD) FROM transactions WHERE Recipient = ' . $DB->string('fee') . ' AND Date >= ' . $DB->int($Last30Days) . ')
WHERE Period = ' . $DB->string('30days'));
$DB->query('UPDATE stats_marketplace_profit SET
XMR = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('XMR') . ' AND Recipient = ' . $DB->string('fee') . '),
BTC = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('BTC') . ' AND Recipient = ' . $DB->string('fee') . '),
LTC = (SELECT SUM(Amount) FROM transactions WHERE Currency = ' . $DB->string('LTC') . ' AND Recipient = ' . $DB->string('fee') . '),
USD = (SELECT SUM(USD) FROM transactions WHERE Recipient = ' . $DB->string('fee') . ')
WHERE Period = ' . $DB->string('total'));

$DB->query('UPDATE stats_marketplace_cash_flow SET
XMR = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('XMR') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Today) . '),
BTC = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('BTC') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Today) . '),
LTC = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('LTC') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Today) . '),
USD = (SELECT SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Today) . ')
WHERE Period = ' . $DB->string('today'));
$DB->query('UPDATE stats_marketplace_cash_flow SET
XMR = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('XMR') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . '),
BTC = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('BTC') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . '),
LTC = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('LTC') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . '),
USD = (SELECT SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . ')
WHERE Period = ' . $DB->string('30days'));
$DB->query('UPDATE stats_marketplace_cash_flow SET
XMR = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('XMR') . ' AND Status = ' . $DB->string('Finalized') . '),
BTC = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('BTC') . ' AND Status = ' . $DB->string('Finalized') . '),
LTC = (SELECT SUM(PayAmount) FROM orders WHERE PayWith = ' . $DB->string('LTC') . ' AND Status = ' . $DB->string('Finalized') . '),
USD = (SELECT SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ')
WHERE Period = ' . $DB->string('total'));

$DB->query('UPDATE stats_new_user_vendor SET
NewUser = (SELECT COUNT(*) FROM user WHERE (Role = ' . $DB->string('user') . ' OR Role = ' . $DB->string('vendor') . ') AND Registered >= ' . $DB->int($Today) . '),
NewVendor = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . ' AND VendorSince >= ' . $DB->int($Today) . '),
VendorPaidBond = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . ' AND VendorInvited = 0 AND VendorSince >= ' . $DB->int($Today) . '),
VendorInvited = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . ' AND VendorInvited = 1 AND VendorSince >= ' . $DB->int($Today) . ')
WHERE Period = ' . $DB->string('today'));
$DB->query('UPDATE stats_new_user_vendor SET
NewUser = (SELECT COUNT(*) FROM user WHERE (Role = ' . $DB->string('user') . ' OR Role = ' . $DB->string('vendor') . ') AND Registered >= ' . $DB->int($Last30Days) . '),
NewVendor = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . ' AND VendorSince >= ' . $DB->int($Last30Days) . '),
VendorPaidBond = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . ' AND VendorInvited = 0 AND VendorSince >= ' . $DB->int($Last30Days) . '),
VendorInvited = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . ' AND VendorInvited = 1 AND VendorSince >= ' . $DB->int($Last30Days) . ')
WHERE Period = ' . $DB->string('30days'));
$DB->query('UPDATE stats_new_user_vendor SET
NewUser = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('user') . ' OR Role = ' . $DB->string('vendor') . '),
NewVendor = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . '),
VendorPaidBond = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . ' AND VendorInvited = 0),
VendorInvited = (SELECT COUNT(*) FROM user WHERE Role = ' . $DB->string('vendor') . ' AND VendorInvited = 1)
WHERE Period = ' . $DB->string('total'));

$DB->query('TRUNCATE stats_vendor_most_items_online');
$DB->query('INSERT INTO stats_vendor_most_items_online (User, NumItems)
SELECT Vendor, COUNT(*) FROM items WHERE Active = 1 AND Blocked = 0 GROUP BY Vendor ORDER BY COUNT(*) DESC, Vendor LIMIT 10');

$DB->query('TRUNCATE stats_vendor_highest_cash_flow_7days');
$DB->query('INSERT INTO stats_vendor_highest_cash_flow_7days (User, USD)
SELECT Vendor, SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last7Days) . ' GROUP BY Vendor ORDER BY SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) DESC, Vendor LIMIT 10');

$DB->query('TRUNCATE stats_vendor_highest_cash_flow_30days');
$DB->query('INSERT INTO stats_vendor_highest_cash_flow_30days (User, USD)
SELECT Vendor, SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . ' GROUP BY Vendor ORDER BY SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) DESC, Vendor LIMIT 10');

$DB->query('TRUNCATE stats_vendor_highest_cash_flow_total');
$DB->query('INSERT INTO stats_vendor_highest_cash_flow_total (User, USD)
SELECT Vendor, SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ' GROUP BY Vendor ORDER BY SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) DESC, Vendor LIMIT 10');

$DB->query('TRUNCATE stats_user_highest_cash_flow_7days');
$DB->query('INSERT INTO stats_user_highest_cash_flow_7days (User, USD)
SELECT Buyer, SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last7Days) . ' GROUP BY Buyer ORDER BY SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) DESC, Buyer LIMIT 10');

$DB->query('TRUNCATE stats_user_highest_cash_flow_30days');
$DB->query('INSERT INTO stats_user_highest_cash_flow_30days (User, USD)
SELECT Buyer, SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . ' GROUP BY Buyer ORDER BY SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) DESC, Buyer LIMIT 10');

$DB->query('TRUNCATE stats_user_highest_cash_flow_total');
$DB->query('INSERT INTO stats_user_highest_cash_flow_total (User, USD)
SELECT Buyer, SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Status = ' . $DB->string('Finalized') . ' GROUP BY Buyer ORDER BY SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) DESC, Buyer LIMIT 10');

$DB->query('UPDATE user SET CashFlow = (SELECT SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE (Vendor = UserID OR Buyer = UserID) AND Status = ' . $DB->string('Finalized') . ')');

$DB->query('UPDATE user SET StaffProcessedMessages = (SELECT COUNT(*) FROM messages WHERE Moderator = UserID) WHERE Role = ' . $DB->string('staff'));

$DB->query('UPDATE user SET StaffProcessedDisputes = (SELECT COUNT(*) FROM orders WHERE Moderator = UserID AND (Status = ' . $DB->string('Finalized') . ' OR Status = ' . $DB->string('Canceled') . ') AND DisputeOpened = 1) WHERE Role = ' . $DB->string('staff'));

$DB->query('UPDATE user SET FeedbackRate = ((SELECT COUNT(*) FROM orders WHERE Vendor = UserID AND Status = ' . $DB->string('Finalized') . ' AND VendorReview IS NOT NULL) + (SELECT COUNT(*) FROM orders WHERE Buyer = UserID AND Status = ' . $DB->string('Finalized') . ' AND BuyerReview IS NOT NULL)) / (SELECT COUNT(*) FROM orders WHERE (Vendor = UserID OR Buyer = UserID) AND Status = ' . $DB->string('Finalized') . ') * 100');

echo 'OK';