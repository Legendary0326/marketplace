<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/vendorshop.php***/
if (!$User->hasRole('vendor') && !$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
$CountOrders = Market::countMyVendorOrders();
$CountDisputes = Market::countMyVendorDisputes();

if (Forms::isPost()) {
    if (isset($_POST['OnHoliday'])) {
        $User->set('OnHoliday', 1);
    } else {
        $User->set('OnHoliday', 0);
    }
    $Pages->reload();
}
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md px-2" id="borderPageHeaderFirstLineFirstRow">
            <a class="text-white" href="/terms" class="btn btn-link">Terms & Conditions</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderFirstLineSecondRow">
            <a class="text-white" href="/item-create" class="btn btn-link">Upload Item</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineFirstRow">
            <a class="text-white" href="/item-all" class="btn btn-link">My Items</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineSecondRow">
            <a class="text-white" href="/vendororders" class="btn btn-link">Orders<?php if ($CountOrders >= 1) { echo '<span class="badge badge-order badge-pill ml-1">' . $CountOrders . '</span>'; } ?></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderThirdLine">
            <a class="text-white" href="/dispute-vendor" class="btn btn-link">Disputes<?php if ($CountDisputes >= 1) { echo '<span class="badge badge-dispute badge-pill ml-1">' . $CountDisputes . '</span>'; } ?></a>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
<?php
if ($User->get('OnHoliday')) {
    echo '<span class="text-danger mt-4 mb-4" style="margin-left: auto; margin-right: auto;"><h3>Vacation Mode Is Activated!</h3></span>' . nl;
}
?>
        <div class="col-md-12 text-info">
            <h3>Your Vendor-Shop...</h3>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-muted font-weight-bold">
            <h4>General Statistics</h4>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">
        <div class="col-8 col-md-3">
            <span class="font-weight-light text-muted text-nowrap">Vendor Status:</span>
        </div>
        <div class="col-4 col-md-3">
            <?php echo Ranking::getVendorRank($User->get('VendorRank')); ?>
        </div>
        <div class="col-8 col-md-3">
            <span class="font-weight-light text-muted text-nowrap">Number of Customers:</span>
        </div>
        <div class="col-4 col-md-3">
<?php
$NumBuyer = $DB->fetch_assoc($DB->query('SELECT COUNT(DISTINCT Buyer) AS Num FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND Status = ' . $DB->string('Finalized')));
echo $NumBuyer['Num'];
?>
        </div>
        <div class="col-8 col-md-3">
            <span class="font-weight-light text-muted text-nowrap">Current Commission Fee:</span>
        </div>
        <div class="col-4 col-md-3">
            <?php echo $Language->number($User->get('VendorFee'), 1); ?>%
        </div>
        <div class="col-8 col-md-3">
            <span class="font-weight-light text-muted text-nowrap">Orders Total:</span>
        </div>
        <div class="col-4 col-md-3">
<?php
$NumOrders = $DB->fetch_assoc($DB->query('SELECT COUNT(*) AS Num FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND Status = ' . $DB->string('Finalized')));
echo $NumOrders['Num'];
?>
        </div>
        <div class="col-8 col-md-3">
            <span class="font-weight-light text-muted text-nowrap">Open Disputes:</span>
        </div>
        <div class="col-4 col-md-3">
            <span class="<?php if ($CountDisputes >= 1) { echo 'text-danger'; } ?>"><?php echo $CountDisputes; ?></span>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Sales Overview:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left text-nowrap">Period</th>
                    <th scope="col" class="text-center text-nowrap">XMR</th>
                    <th scope="col" class="text-center text-nowrap">BTC</th>
                    <th scope="col" class="text-center text-nowrap">LTC</th>
                    <th scope="col" class="text-center text-nowrap">US-$</th>
                    <th scope="col" class="text-center text-nowrap" id="pastel-green"><?php echo $User->get('Currency'); ?></th>
                </tr>
<?php
$Today = mktime(0, 0, 0);
$date = new DateTime();
$date->sub(new DateInterval('P30D'));
$Last30Days = $date->format('U');

$TodayData = $DB->fetch_assoc($DB->query('SELECT
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('XMR') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Today) . ') AS XMR,
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('BTC') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Today) . ') AS BTC,
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('LTC') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Today) . ') AS LTC,
(SELECT SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Today) . ') AS USD'));
$Last30DaysData = $DB->fetch_assoc($DB->query('SELECT
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('XMR') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . ') AS XMR,
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('BTC') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . ') AS BTC,
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('LTC') . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . ') AS LTC,
(SELECT SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND Status = ' . $DB->string('Finalized') . ' AND StatusChanged >= ' . $DB->int($Last30Days) . ') AS USD'));
$TotalData = $DB->fetch_assoc($DB->query('SELECT
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('XMR') . ' AND Status = ' . $DB->string('Finalized') . ') AS XMR,
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('BTC') . ' AND Status = ' . $DB->string('Finalized') . ') AS BTC,
(SELECT SUM(PayAmount) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND PayWith = ' . $DB->string('LTC') . ' AND Status = ' . $DB->string('Finalized') . ') AS LTC,
(SELECT SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Vendor = ' . $DB->int($User->getID()) . ' AND Status = ' . $DB->string('Finalized') . ') AS USD'));
?>
                <tr>
                    <td class="text-muted text-nowrap">Today</td>
                    <td class="text-right"><?php echo $Language->number($TodayData['XMR'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($TodayData['BTC'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($TodayData['LTC'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($TodayData['USD'], 2); ?></td>
                    <td class="text-right"><?php echo Currencies::exchange($TodayData['USD'], 'USD', 'USER'); ?></td>
                </tr>
                <tr>
                    <td class="text-muted text-nowrap">30 Days</td>
                    <td class="text-right"><?php echo $Language->number($Last30DaysData['XMR'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Last30DaysData['BTC'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Last30DaysData['LTC'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Last30DaysData['USD'], 2); ?></td>
                    <td class="text-right"><?php echo Currencies::exchange($Last30DaysData['USD'], 'USD', 'USER'); ?></td>
                </tr>
                <tr>
                    <td class="text-muted text-nowrap">Total</td>
                    <td class="text-right"><?php echo $Language->number($TotalData['XMR'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($TotalData['BTC'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($TotalData['LTC'], 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($TotalData['USD'], 2); ?></td>
                    <td class="text-right"><?php echo Currencies::exchange($TotalData['USD'], 'USD', 'USER'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Top Selling Items:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left text-nowrap">Item</th>
                    <th scope="col" class="text-center text-nowrap">Number of Sales</th>
                    <th scope="col" class="text-center text-nowrap">Total Sales Proceeds&nbsp;US-$</th>
                    <th scope="col" class="text-center text-nowrap" id="pastel-green">Total Sales Proceeds&nbsp;<?php echo $User->get('Currency');?></th>
                </tr>
<?php
$Top10 = $DB->fetch_all($DB->query('SELECT Name, (SELECT COUNT(*) FROM orders WHERE Item = ItemID AND Status = ' . $DB->string('Finalized') . ') AS NumSales, (SELECT SUM(((Quantity * Price) + ShippingPrice) * DisputeSplitMultiplier) FROM orders WHERE Item = ItemID AND Status = ' . $DB->string('Finalized') . ') AS NumSalesUSD FROM items WHERE Vendor = ' . $DB->int($User->getID()) . ' AND Active = 1 AND Blocked = 0 AND (SELECT COUNT(*) FROM orders WHERE Item = ItemID AND Status = ' . $DB->string('Finalized') . ') >= 1 ORDER BY NumSalesUSD DESC LIMIT 10'), MYSQLI_ASSOC);
foreach ($Top10 as $Item) {
    echo '<tr>
<td class="text-nowrap">' . htmlentities(substr($Item['Name'], 0, 35)) . '</td>
<td class="text-right">' . $Item['NumSales'] . '</td>
<td class="text-right">' . $Language->number($Item['NumSalesUSD'], 2) . '</td>
<td class="text-right">' . Currencies::exchange($Item['NumSalesUSD'], 'USD', 'USER') . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Average Rating of All Items:</h5>
        </div>
        <div class="col-md-12 overflow-auto pb-5" id="separator">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left text-nowrap">Item</th>
                    <th scope="col" class="text-center text-nowrap">Last 30 Days</th>
                    <th scope="col" class="text-center text-nowrap">Last 6 Months</th>
                    <th scope="col" class="text-center text-nowrap">Total Period</th>
                    <th scope="col" class="text-center text-nowrap">Disputes</th>
                </tr>
<?php
$date = new DateTime();
$date->sub(new DateInterval('P180D'));
$Last6Months = $date->format('U');

$Items = $DB->fetch_all($DB->query('SELECT Name,
(SELECT AVG(Rating) FROM items_reviews WHERE SenderType = ' . $DB->string('Buyer') . ' AND Item = ItemID AND items_reviews.Created >= ' . $DB->int($Last30Days) . ') AS AvgLast30Days,
(SELECT AVG(Rating) FROM items_reviews WHERE SenderType = ' . $DB->string('Buyer') . ' AND Item = ItemID AND items_reviews.Created >= ' . $DB->int($Last6Months) . ') AS AvgLast6Months,
(SELECT AVG(Rating) FROM items_reviews WHERE SenderType = ' . $DB->string('Buyer') . ' AND Item = ItemID) AS AvgTotal,
(SELECT COUNT(*) FROM orders WHERE DisputeOpened = 1 AND Item = ItemID) AS NumDisputes
FROM items WHERE (SELECT COUNT(*) FROM items_reviews WHERE Item = ItemID) >= 1 AND Vendor = ' . $DB->int($User->getID()) . ' ORDER BY AvgTotal DESC'), MYSQLI_ASSOC);
foreach ($Items as $Item) {
    echo '<tr>
<td class="text-nowrap">' . htmlentities(substr($Item['Name'], 0, 35)) . '</td>
<td class="text-right text-nowrap">' . Market::processStars($Item['AvgLast30Days']) . '</td>
<td class="text-right text-nowrap">' . Market::processStars($Item['AvgLast6Months']) . '</td>
<td class="text-right text-nowrap">' . Market::processStars($Item['AvgTotal']) . '</td>
<td class="text-right">' . $Item['NumDisputes'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>Shop Vacation...</h3>
        </div>
    </div>
</div>

<form action="/vendorshop" method="post">
    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-12 col-md-12 font-weight-light text-info mb-4">
                In the event that you are absent for some time and you don&rsquo;t want to accept any orders during this period, you have the option to temporarily &quot;close&quot; your shop.<br>Your items will be invisible to all users and you will not receive any orders during this time.<br>We recommend that you publish the period of your absence in your general terms and conditions!
            </div>
            <div class="col-12 col-md-12 mb-2">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" name="OnHoliday" value="1"<?php if ($User->get('OnHoliday')) echo ' checked'; ?>></input>
                    <span class="ml-4">Make Items Invisible</span>
                </div>
            </div>
            <div class="col-12 col-md-12 font-weight-light text-danger mb-2">
                Don&rsquo;t forget to make your items visible again when you back to business!
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-12 col-md-12 pb-4">
                <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
            </div>
        </div>
    </div>
</form>
