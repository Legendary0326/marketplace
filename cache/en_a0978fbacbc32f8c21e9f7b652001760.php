<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/item-details.php***/
$ItemID = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
if (empty($ItemID)) {
    echo Alerts::danger('No Item Selected!', 'mt-3');
    return;
}
$item = Market::getItemData($ItemID);
if (empty($item)) {
    echo Alerts::danger('No Item Selected!', 'mt-3');
    return;
}
$DB->query('UPDATE items SET Views = Views + 1 WHERE ItemID = ' . $DB->int($ItemID));
$Paths = $Pages->getPath();
if (isset($Paths['1'])) {
    if ($Paths['1'] == 'orderplaced') {
        echo Alerts::success('Order Is Placed.', 'mt-3');
    } else if ($Paths['1'] == 'erroramount') {
        echo Alerts::danger('Your Account Balance Is Not Sufficient!', 'mt-3');
    }
}

$Shippings = Market::getShippings();
$MaxQuantity = 149;
$SelectedQuantity = 1;
if (isset($_POST['Quantity'])) $SelectedQuantity = (int) $_POST['Quantity'];
$SelectedQuantity = min(max($SelectedQuantity, 1), $MaxQuantity);

if (count($item['Shipping']) == 0) {
    $PriceShipping = 0;
    $SelectedShipping = 0;
} else {
    $PriceShipping = (isset($item['Shipping'][0]['Price']) ? $item['Shipping'][0]['Price'] : 0);
    $SelectedShipping = 0;
    if (isset($_POST['Shipping']) && isset($item['Shipping'][(int) $_POST['Shipping']])) {
        $SelectedShipping = (int) $_POST['Shipping'];
        $PriceShipping = $item['Shipping'][$SelectedShipping]['Price'];
    }
}

$Total = ($SelectedQuantity * $item['Price']) + $PriceShipping;
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><?php echo htmlentities($item['Name']); ?></h3>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">
        <div class="col-md-12">
            <span class="text-muted">Category:</span>&nbsp;&nbsp;
<?php
$Tree = Categories::getCategoryTree($item['Category']);
$CategoriesNum = count($Tree);
foreach ($Tree as $i => $Category) {
    echo htmlentities($Category['Name']) . ($i < $CategoriesNum - 1 ? '&nbsp;/&nbsp;' : '') . nl;
}
?>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">
        <div class="col-12 col-md-6">
            <span class="text-muted">Item Nr.</span>:&nbsp;&nbsp;
            <span class="text-nowrap"><?php echo Market::ItemID($item['ItemID']); ?></span>
        </div>
        <div class="col-12 col-md-6">
            <span class="text-muted">Item Class:</span>&nbsp;&nbsp;
            <span class="text-success"><?php echo $Language->translate($item['Class']); ?></span>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">	
        <div class="col-12 col-md-6">
            <span class="text-muted">Payment Processing:</span>&nbsp;&nbsp;
            <span class="text-success"><?php echo $Language->translate($item['Payment']); ?></span>
        </div>
        <div class="col-12 col-md-6">
            <span class="text-muted">Item Review:</span>&nbsp;&nbsp;
            <?php echo Market::processStars($item['Rating']); ?>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">
        <div class="col-12 col-md-12">
            <span class="text-muted">Country of Dispatch:</span>&nbsp;&nbsp;
            <span class="text-nowrap"><?php echo htmlentities($item['ShipFromName']);?></span>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">
        <div class="col-md-4">
            <span class="text-muted">Vendor:</span>&nbsp;&nbsp;
            <a href="/profile/<?php echo $item['Vendor']; ?>" class="font-weight-bold text-info"><?php echo $item['VendorName']; ?></a>
        </div>
        <div class="col-md-4">
            <span class="text-muted">Status:</span>&nbsp;&nbsp;
            <?php echo $item['VendorRankName']; ?>
        </div>
        <div class="col-md-4">
            <span class="text-muted">Scoring:</span>&nbsp;&nbsp;
            <?php echo $Language->number($User->getByUserID('Scoring', $item['Vendor']), 1); ?> %
        </div>
    </div>
</div>

<div class="col-md-12 mt-3">
    <div style="width: 100%; height: 145px; border: 1px solid silver; overflow: auto;">
<?php
for ($ImageNo = 0; $ImageNo <= 10; $ImageNo++) {
    if (!empty($item['Image' . $ImageNo])) {
        $item['Image' . $ImageNo] = unserialize($item['Image' . $ImageNo]);
?>
            <img class="p-1" src="/image/<?php echo $item['Image' . $ImageNo]['MD5'] . '.' . $item['Image' . $ImageNo]['Ext']; ?>" alt="item" title="" style="max-width: 190px; height: auto;"/>
<?php
    }
}
?>
    </div>
</div>

<div class="container mt-0 mt-3">
    <div class="row py-1">
        <div class="col-8 col-md-3">
            <span class="text-muted">Item Description:</span>
        </div>
        <div class="col-auto col-md-9">
            <?php echo htmlentities($item['Description']); ?>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">
        <div class="col-6 col-md-3">
            <span class="text-muted text-nowrap">Item Online Since:</span>
        </div>
        <div class="col-auto text-nowrap">
<?php echo $Language->date($item['Created']); ?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-6 col-md-3">
            <span class="text-muted text-nowrap">Total Views:</span>
        </div>
        <div class="col-auto">
<?php echo $item['Views']; ?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-6 col-md-3">
            <span class="text-muted text-nowrap">Total Sales:</span>
        </div>
        <div class="col-auto">
<?php echo $item['Sales']; ?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-6 col-md-3">
            <span class="text-muted text-nowrap">Quantity Left:</span>
        </div>
        <div class="col-auto">
<?php echo (is_null($item['Quantity']) ? $Language->translate('unlimited') : $item['Quantity']); ?>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted">
            <h5>Review Overview:</h5>
        </div>
        <div class="col-md-12 font-weight-light text-muted mb-3">
            <span class="small">(You find the detailed reviews at the bottom of this page!)</span>
        </div>
        <div class="table-responsive-md ml-3">
            <table class="table table-bordered-standard">
                <tr>
                    <th scope="col" style="background: none; border-top: 1px solid transparent; border-left: 1px solid transparent;"></th>
                    <th scope="col">30 Days</th>
                    <th scope="col">6 Months</th>
                    <th scope="col">Total Period</th>
                </tr>
<?php $ReviewOverview = Market::getReviewOverviewVendor($item['Vendor']); ?>
                <tr>
                    <td class="text-success">Positive</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Positive'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Positive'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Positive'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td class="text-primary">Neutral</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Neutral'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Neutral'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Neutral'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td class="text-danger">Negative</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Negative'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Negative'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Negative'] ?? 0; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-sm-4 col-md-3 col-lg-2">
            <span class="text-muted">Singel Price:</span>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <h5 class="text-danger font-italic"><?php echo $Language->number($item['Price'], 2); ?> US-$</h5>
        </div>
<?php if ($User->getCurrency() != 'USD') { ?>
        <div class="col-5 col-sm-4 col-md-3 col-lg-2 pt-0">
            <h6 class="text-success font-italic">(<?php echo Currencies::exchange($item['Price'], 'USD', 'USER') . ' ' . $User->getCurrency(); ?>)</h6>
        </div>
<?php } ?>
    </div>
</div>

<form action="/item-details/<?php echo $ItemID; ?>" method="post">
    <div class="container mt-0">
        <div class="row py-1">
            <div class="col-md-2 pb-2">
                <span class="text-muted">Quantity:</span>
            </div>
            <div class="col-4 col-md-2">
                <div class="form-group">
                    <select class="form-control" name="Quantity">
<?php
for ($i = 1; $i <= $MaxQuantity; $i++) {
    echo '<option value="' . $i . '"' . Forms::selected('Quantity', $i) . '>' . $i . '</option>' . nl;
}
?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-0">
        <div class="row py-1">
            <div class="col-md-2 pb-2">
                <span class="text-muted">Shipping Method:</span>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <select class="form-control" name="Shipping">
<?php
if (count($item['Shipping']) >= 1) {
    foreach ($item['Shipping'] as $ID => $Shipping) {
        echo '<option value="' . $ID . '"' . Forms::selected('Shipping', $ID) . '>' . $Shippings[$Shipping['ID']]['Name'] . ' = ' . $Language->number($Shipping['Price'], 2) . ' US-$';
        if ($User->getCurrency() != 'USD') {
            echo ' | ' . Currencies::exchange($Shipping['Price'], 'USD', 'USER') . ' ' . $User->getCurrency();
        }
        echo '</option>' . nl;
    }
}
?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-0">
        <div class="row py-1">
            <div class="col-12 col-md-3">
                <div class="input-group-append">
                    <button class="btn-sm btn-secondary" type="submit">Calculate Total Price</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-2">
            <span class="text-muted">Total Price:</span>
        </div>
        <div class="col-6 col-md-2">
            <h5 class="text-danger font-weight-bold"><?php echo $Language->number($Total, 2); ?> US-$</h5>
        </div>
<?php if ($User->getCurrency() != 'USD') {?>
        <div class="col-5 col-md-2 pt-0">
            <h6 class="text-success font-weight-bold">(<?php echo Currencies::exchange($Total, 'USD', 'USER') . ' ' . $User->getCurrency(); ?>)</h6>
        </div>
<?php } ?>
    </div>
</div>

<div class="container mt-1 mb-3">
    <div class="row py-1">
<?php if ($item['Monero']) { ?>
        <div class="col-md-3">
            <div class="mb-2">
                <span class="text-muted">XMR:</span>&nbsp;&nbsp;<?php echo Currencies::exchange($Total, 'USD', 'XMR'); ?>
                <br>
                <form action="/payment-overview/<?php echo $item['ItemID']; ?>" method="post">
                    <input type="hidden" name="Quantity" value="<?php echo $SelectedQuantity; ?>">
                    <input type="hidden" name="ShippingID" value="<?php echo $SelectedShipping; ?>">
                    <input type="hidden" name="PayWith" value="xmr">
                    <button class="btn-xmr btn-xmr-primary btn-xmr-block p-1 mt-2" type="submit"><img class="" src="/img/xmr_white.png" alt="Logo" title="" width="20px" height="20px"/>&nbsp;Order Now</button>
                </form>
            </div>
        </div>
<?php
}
if ($item['Bitcoin']) {
?>
        <div class="col-md-3">
            <div class="mb-2">
                <span class="text-muted">BTC:</span>&nbsp;&nbsp;<?php echo Currencies::exchange($Total, 'USD', 'BTC'); ?>
                <br>
                <form action="/payment-overview/<?php echo $item['ItemID']; ?>" method="post">
                    <input type="hidden" name="Quantity" value="<?php echo $SelectedQuantity; ?>">
                    <input type="hidden" name="ShippingID" value="<?php echo $SelectedShipping; ?>">
                    <input type="hidden" name="PayWith" value="btc">
                    <button type="submit" class="btn-btc btn-btc-primary btn-btc-block p-1 mt-2" href="#"><img class="" src="/img/btc_white.png" alt="Logo" title="" width="20px" height="20px"/>&nbsp;Order Now</button>
                </form>
            </div>
        </div>
<?php
}
if ($item['Litecoin']) {
?>
        <div class="col-md-3">
            <div class="mb-2">
                <span class="text-muted">LTC:</span>&nbsp;&nbsp;<?php echo Currencies::exchange($Total, 'USD', 'LTC'); ?>
                <br>
                <form action="/payment-overview/<?php echo $item['ItemID']; ?>" method="post">
                    <input type="hidden" name="Quantity" value="<?php echo $SelectedQuantity; ?>">
                    <input type="hidden" name="ShippingID" value="<?php echo $SelectedShipping; ?>">
                    <input type="hidden" name="PayWith" value="ltc">
                    <button type="submit" class="btn-ltc btn-ltc-primary btn-ltc-block p-1 mt-2" href="#"><img class="" src="/img/ltc_white.png" alt="Logo" title="" width="20px" height="20px"/>&nbsp;Order Now</button>
                </form>
            </div>
        </div>
<?php } ?>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 text-muted mb-2">
            Vendor&rsquo;s Terms and Conditions:
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" rows="3"><?php echo htmlentities($item['VendorTerms']); ?></textarea>
            </div>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 text-muted mb-2">
            Vendor&rsquo;s Refund Policy:
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control" rows="3"><?php echo htmlentities($item['VendorRefunds']); ?></textarea>
            </div>
        </div>
    </div>
</div>
<?php
$LastReviews = Market::getLastReviewsVendor($item['Vendor']);
if (count($LastReviews['Positive'] ?? []) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Positive Reviews:</h5>
        </div>
        <div class="col-12 overflow-auto col-md-12">
            <table class="table-sm table-bordered-positive overflow-auto">
                <thead>
                    <tr>
                        <th scope="col" class="text-center text-nowrap">Date</th>
                        <th scope="col" class="text-center text-nowrap w-25">Item</th>
                        <th scope="col" class="text-center text-nowrap">Rating</th>
                        <th scope="col" class="text-center text-nowrap">Buyer</th>
                        <th scope="col" class="text-left text-nowrap pl-4 w-75">Feedback</th>
                    </tr>
                </thead>
                <tbody>
<?php
foreach (($LastReviews['Positive'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
}
if (count($LastReviews['Neutral'] ?? []) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Neutral Reviews:</h5>
        </div>
        <div class="col-12 overflow-auto col-md-12">
            <table class="table-sm table-bordered-neutral overflow-auto">
                <thead>
                    <tr>
                        <th scope="col" class="text-center text-nowrap">Date</th>
                        <th scope="col" class="text-center text-nowrap">Item</th>
                        <th scope="col" class="text-center text-nowrap">Rating</th>
                        <th scope="col" class="text-center text-nowrap">Buyer</th>
                        <th scope="col" class="text-left text-nowrap pl-4 w-75">Feedback</th>
                    </tr>
                </thead>
                <tbody>
<?php
foreach (($LastReviews['Neutral'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
}
if (count($LastReviews['Negative'] ?? []) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Negative Reviews:</h5>
        </div>
        <div class="col-12 overflow-auto col-md-12">
            <table class="table-sm table-bordered-negative overflow-auto">
                <thead>
                    <tr>
                        <th scope="col" class="text-center text-nowrap">Date</th>
                        <th scope="col" class="text-center text-nowrap">Item</th>
                        <th scope="col" class="text-center text-nowrap">Rating</th>
                        <th scope="col" class="text-center text-nowrap">Buyer</th>
                        <th scope="col" class="text-left text-nowrap pl-4 w-75">Feedback</th>
                    </tr>
                </thead>
                <tbody>
<?php
foreach (($LastReviews['Negative'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
}
