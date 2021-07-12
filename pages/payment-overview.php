<?php
if ($User->get('BlockTransactions')) {
    echo Alerts::danger('<lang>Transactions Are Currently Not Possible!</lang>', 'mt-3');
    return;
}

$ItemID = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
if (empty($ItemID)) {
    echo Alerts::danger('<lang>No Item Selected!</lang>', 'mt-3');
    return;
}
$item = Market::getItemData($ItemID);
if (empty($item)) {
    echo Alerts::danger('<lang>No Item Selected!</lang>', 'mt-3');
    return;
}
if (!isset($_POST['Quantity']) || !isset($_POST['ShippingID']) || !isset($_POST['PayWith'])) {
    echo Alerts::danger('<lang>Entries Not Complete!</lang>', 'mt-3');
} else if ($_POST['PayWith'] != 'xmr' && $_POST['PayWith'] != 'btc' && $_POST['PayWith'] != 'ltc') {
    echo Alerts::danger('<lang>Entries Incorrect!</lang>', 'mt-3');
} else if (intval($_POST['Quantity']) <= 0) {
    echo Alerts::danger('<lang>Quantity Cannot Be Zero!</lang>', 'mt-3');
}
$Quantity = (int) $_POST['Quantity'];
$ShippingID = (int) $_POST['ShippingID'];
$PayWith = $_POST['PayWith'];
$SubTotal = $item['Price'] * $Quantity;
$ShippingCosts = (isset($item['Shipping'][$ShippingID]['Price']) ? $item['Shipping'][$ShippingID]['Price'] : 0);
$Total = $SubTotal + $ShippingCosts;
$Shippings = Market::getShippings();
$PayAmount = Currencies::exchange($Total, 'USD', strtoupper($PayWith));
if ($User->get(strtoupper($PayWith)) < $PayAmount) {
    $Pages->redirect('item-details/' . $ItemID . '/erroramount');
}

if (isset($_POST['Process']) && $_POST['Process'] == '1') {
    if (Captcha::verify()) {
        if ($item['OnlyPGP'] && (!preg_match('/^\-\-\-\-\-BEGIN PGP MESSAGE\-\-\-\-\-.+\-\-\-\-\-END PGP MESSAGE\-\-\-\-\-$/s', $_POST['Note']) || strlen($_POST['Note']) <= 199)) {
            echo Alerts::danger('<lang>This vendor ONLY accepts encrypted notes!</lang>', 'mt-3');
        } else {
            $NewOrder = [];
            $NewOrder['Item'] = $ItemID;
            $NewOrder['Vendor'] = $item['Vendor'];
            $NewOrder['Buyer'] = $User->getID();
            $NewOrder['Created'] = time();
            $NewOrder['StatusChanged'] = time();
            $NewOrder['Quantity'] = $Quantity;
            $NewOrder['Price'] = $item['Price'];
            if (isset($item['Shipping'][$ShippingID])) {
                $NewOrder['Shipping'] = $Shippings[$item['Shipping'][$ShippingID]['ID']]['ID'];
                $NewOrder['ShippingType'] = $Shippings[$item['Shipping'][$ShippingID]['ID']]['Type'];
                $NewOrder['ShippingPrice'] = (isset($item['Shipping'][$ShippingID]['Price']) ? $item['Shipping'][$ShippingID]['Price'] : 0);
            }
            $NewOrder['PayWith'] = $PayWith;
            $NewOrder['PayAmount'] = $PayAmount;
            $NewOrder['Note'] = (isset($_POST['Note']) ? trim($_POST['Note']) : null);
            $NewOrder['Name'] = $item['Name'];
            $NewOrder['Category'] = $item['Category'];
            $NewOrder['Class'] = $item['Class'];
            $NewOrder['Payment'] = $item['Payment'];
            $DB->insert('orders', $NewOrder);
            Market::transfer($PayAmount, strtoupper($PayWith), $User->getID(), 'escrow');
            $Pages->redirect('item-details/' . $ItemID . '/orderplaced');
        }
    } else {
        echo Alerts::danger('<lang>Sorry, the CAPTCHA Is Incorrect. Please Try Again.</lang>', 'mt-3');
    }
}
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><lang>Payment Overview</lang>:</h3>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">
        <div class="col-md-12">
            <span class="font-weight-light text-muted"><lang>Item</lang>:</span>&nbsp;&nbsp;
            <?php echo htmlentities($item['Name']); ?>
        </div>
        <div class="col-md-12">
            <span class="font-weight-light text-muted"><lang>Category</lang>:</span>&nbsp;&nbsp;
            <?php
$CategoryTree = Categories::getCategoryTree($item['Category']);
echo '<span class="text-nowrap">';
foreach ($CategoryTree as $i => $Category) {
    if ($i != 0) echo ' / ';
    echo $Category['Name'];
}
?>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-7 col-md-4">
            <span class="font-weight-light text-muted"><lang>Item Nr.</lang>:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo Market::ItemID($item['ItemID']); ?></span>
        </div>
        <div class="col-7 col-md-4">
            <span class="font-weight-light text-muted"><lang>Item Class</lang>:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php echo $Language->translate($item['Class']); ?>
        </div>
        <div class="col-7 col-md-4">
            <span class="font-weight-light text-muted"><lang>Payment Processing</lang>:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php echo $Language->translate($item['Payment']); ?>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-7 col-md-4">
            <span class="font-weight-light text-muted text-nowrap"><lang>Date</lang>:</span>
        </div>
        <div class="col-auto text-nowrap">
            <?php echo $Language->date($item['Created']); ?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-7 col-md-4">
            <span class="font-weight-light text-muted text-nowrap"><lang>Quantity</lang>:</span>
        </div>
        <div class="col-auto">
            <?php echo $Quantity; ?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-8 col-md-4">
            <span class="font-weight-light text-muted text-nowrap"><lang>Singel Price</lang>:</span>
        </div>
        <div class="col-auto text-nowrap">
<?php
echo $Language->number($item['Price']) . ' US-$&nbsp;' . nl;
if ($User->getCurrency() != 'USD') echo '<span style="font-size: 80%;">(' . Currencies::exchange($item['Price'], 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>' . nl;
?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-8 col-md-4">
            <span class="font-weight-light text-muted text-nowrap"><lang>Price Subtotal</lang>:</span>
        </div>
        <div class="col-auto text-nowrap">
<?php
echo $Language->number($SubTotal) . ' US-$&nbsp;' . nl;
if ($User->getCurrency() != 'USD') echo '<span style="font-size: 80%;">(' . Currencies::exchange($SubTotal, 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>' . nl;
?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-8 col-md-4">
            <span class="font-weight-light text-muted text-nowrap"><lang>Shipping Method</lang>:</span>
        </div>
        <div class="col-auto">
<?php
echo (isset($item['Shipping'][$ShippingID]) && isset($Shippings[$item['Shipping'][$ShippingID]['ID']]['Name']) ? htmlentities($Shippings[$item['Shipping'][$ShippingID]['ID']]['Name']) : '');
?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-8 col-md-4">
            <span class="font-weight-light text-muted text-nowrap"><lang>Shipping Costs</lang>:</span>
        </div>
        <div class="col-auto text-nowrap">
<?php
echo $Language->number($ShippingCosts) . ' US-$&nbsp;' . nl;
if ($User->getCurrency() != 'USD') echo '<span style="font-size: 80%;">(' . Currencies::exchange($ShippingCosts, 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>' . nl;
?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-8 col-md-4">
            <span class="text-muted text-nowrap font-weight-bold"><lang>Price Total</lang>:</span>
        </div>
        <div class="col-auto text-nowrap">
<?php
echo '<span class="text-danger font-weight-bold" style="font-size: 120%;">' . $Language->number($Total) . ' US-$</span>&nbsp;' . nl;
if ($User->getCurrency() != 'USD') echo '<span class="text-success font-weight-bold" style="font-size: 100%;">(' . Currencies::exchange($Total, 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>' . nl;
?>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-10 col-md-4">
            <span class="font-weight-light text-muted"><lang>Selected Cryptocurrency</lang>:</span>
        </div>
        <div class="col-auto align-text-bottom">
            <img class="mb-1" src="/img/<?php echo $PayWith; ?>_color.png" alt="Logo" title="" width="16px" height="16px"/>
            <span class="pl-1" id="color-<?php echo $PayWith; ?>"><?php
if ($PayWith == 'xmr') {
    echo 'Monero';
} else if ($PayWith == 'btc') {
    echo 'Bitcoin';
} else if ($PayWith == 'ltc') {
    echo 'Litecoin';
}
?></span>
        </div>
    </div>
    <div class="row py-1">
        <div class="col-8 col-md-4">
            <span class="font-weight-light text-muted text-nowrap"><lang>Payable Amount</lang>:</span>
        </div>
        <div class="col-auto text-nowrap">
            <span class="font-weight-bold" id="color-<?php echo $PayWith; ?>" style="font-size: 120%;"><?php echo Currencies::exchange($Total, 'USD', strtoupper($PayWith)); ?></span>&nbsp;&nbsp;<span id="color-<?php echo $PayWith; ?>"><?php echo strtoupper($PayWith); ?></span>
        </div>
    </div>
</div>

<form action="/payment-overview/<?php echo $ItemID; ?>" method="post">
<?php if ($item['OnlyPGP']) { ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12">
                <span class="text-danger text-italic"><lang>This vendor ONLY accepts encrypted notes!</lang></span>
                <br>
                <span class="text-danger text-italic"><lang>Please use PGP encryption!</lang></span>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Vendor&rsquo;s PGP Public Key</lang>:
            </div>
            <div class="col-12 overflow-auto col-md-12">
                <span class="text-muted" style="font-size: 80%;"><?php echo nl2br(htmlentities($User->getByUserID('PGP', $item['Vendor']))); ?></span>
            </div>
        </div>
    </div>
<?php } ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Note</lang>:
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control" rows="3" placeholder="(<lang>Shipping Address and Other Informations</lang>)" name="Note"></textarea>
                </div>
            </div>
        </div>
    </div>
<?php if (Captcha::showCaptcha()) { ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 mb-3 form-group">
            <img src="<?php echo Captcha::get(); ?>" alt="" title="Captcha" />
        </div>
        <div class="col-md-3 mb-3 form-group">
            <label for="Captcha">CAPTCHA</label>
            <input type="text" class="form-control" id="Captcha" name="Captcha" autocomplete="off"<?php devCodeCAPTCHA(); ?>>
        </div>
    </div>
<?php } ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-12 col-md-6 mb-3">
                <input type="hidden" name="Quantity" value="<?php echo $Quantity; ?>">
                <input type="hidden" name="ShippingID" value="<?php echo $ShippingID; ?>">
                <input type="hidden" name="PayWith" value="<?php echo $PayWith; ?>">
                <input type="hidden" name="Process" value="1">
                <button type="submit" class="btn btn-success btn-block float-left"><lang>Confirm Order</lang></button>
            </div>
            <div class="col-12 col-md-6">
                <a href="/item-details/<?php echo $ItemID; ?>" class="btn btn-danger btn-block float-right"><lang>Cancel Order</lang></a>
            </div>
        </div>
    </div>
</form>
