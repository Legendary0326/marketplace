<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/item-edit.php***/
if (!$User->hasRole('vendor') && !$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$currencies = Currencies::getCurrencies();
$HTMLHead->addNav('cat_search');
$ShipsTo = Market::getShipsTo();
$Shippings = Market::getShippings();
$ItemID = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
if (empty($ItemID)) {
    echo Alerts::danger('No Item Selected!', 'mt-3');
    return;
}
$item = Market::getItemData($ItemID, true);
if (empty($item)) {
    echo Alerts::danger('No Item Selected!', 'mt-3');
    return;
}
$CountOrders = Market::countMyVendorOrders();
$CountDisputes = Market::countMyVendorDisputes();
$Categories = Categories::getCategories();
$Paths = $Pages->getPath();
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md px-2" id="borderPageHeaderFirstLineFirstRow">
            <a class="text-white" href="/vendorshop" class="btn btn-link">Statistics</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderFirstLineSecondRow">
            <a class="text-white" href="/terms" class="btn btn-link">Terms & Conditions</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineFirstRow">
            <a class="text-white" href="/item-create" class="btn btn-link">Upload Item</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineSecondRow">
            <a class="text-white" href="/vendororders" class="btn btn-link">Orders<?php if ($CountOrders >= 1) { echo '<span class="badge badge-order badge-pill ml-1">' . $CountOrders . '</span>'; } ?></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderThirdLine">
            <a class="text-white" href="/dispute-vendor" class="btn btn-link">Disputes<?php if ($CountDisputes >= 1) { echo '<span class="badge badge-dispute badge-pill ml-1">' . $CountDisputes . '</span>'; } ?></a>
        </div>
    </div>
</div>

<?php
if ((empty($User->get('VendorTerms')) || empty($User->get('VendorRefunds'))) && !$User->hasRole('staff') && !$User->hasRole('admin')) {
    echo Alerts::danger('Please Upload Your Terms & Conditions and/or Refund Policy First!', 'mt-3');
    return;
} else if ($User->get('2FA') == 0) {
    echo Alerts::danger('Please Activate 2FA First!', 'mt-3');
    return;
}

if ($Pages->inPath('askdelete')) {
    echo '<div class="row justify-contend-md-center">
    <div class="col-10 offset-1 col-md-6 offset-md-3 border p-2 mt-5 mb-5">
        <div class="text-muted text-center">
            You Definitely Want to Delete this Item?
        </div>
        <div class="mt-3">
            <a class="btn btn-sm btn-success w-25 float-left ml-3" href="/item-edit/' . $ItemID . '/delete">YES</a>
            <a class="btn btn-sm btn-danger w-25 float-right mr-3" href="/item-edit/' . $ItemID . '">NO</a>
        </div>
    </div>
</div>' . nl;
    return;
} else if ($Pages->inPath('delete')) {
    $DB->delete('items', 'Vendor = ' . $DB->int($User->getID()) . ' AND ItemID = ' . $DB->int($ItemID));
    $Pages->redirect('item-all/okdelete');
} else if ($Pages->inPath('delete-image')) {
    $DeleteImage = $Paths[2] ?? false;
    if ($DeleteImage !== false && isset($item['Image' . $DeleteImage])) {
        $Image = unserialize($item['Image' . $DeleteImage]);
        $DB->update('items', ['Image' . $DeleteImage => null], 'ItemID = ' . $DB->int($ItemID));
        $Pages->redirect('item-edit/' . $ItemID . '/image-deleted');
    }
} else if ($Pages->inPath('image-deleted')) {
    echo Alerts::success('Image Deleted!', 'mt-3');
}
if ($item['Blocked'] == 1) {
    echo Alerts::danger('Your item has been temporarily blocked! Please contact the support team!', 'mt-3');
} else if ($User->hasRole('vendor')) {
    if (isset($Paths[1]) && $Paths[1] == 'promo') {
        $Weeks = Market::getNextWeeks();
        $WeeksPeriod = Market::getWeeksPeriod();
        $FreePromoteSlots = Market::getFreePromoteSlots();
        $MyPromoteSlots = Market::getMyPromoteSlots($item['ItemID']);
        $CategoryTree = Categories::getCategoryTree($item['Category']);
        $Total = 0;
        $TotalXMR = 0;
        $TotalBTC = 0;
        $TotalLTC = 0;
        $Orders = [];
        if (Forms::isPost()) {
            if (isset($_POST['layer0'])) {
                foreach ($_POST['layer0'] as $ID) {
                    if (isset($FreePromoteSlots[0][$ID]) && $FreePromoteSlots[0][$ID]) {
                        $Orders['0_' . $ID] = ['Week' => $ID, 'Layer' => 0];
                        $Total += 20;
                    }
                }
            }
            if (isset($_POST['layer1'])) {
                foreach ($_POST['layer1'] as $ID) {
                    if (isset($FreePromoteSlots[1][$ID]) && $FreePromoteSlots[1][$ID]) {
                        $Orders['1_' . $ID] = ['Week' => $ID, 'Layer' => 1, 'Category' => $CategoryTree[0]['CategoryID']];
                        $Total += 15;
                    }
                }
            }
            if (isset($_POST['layer2']) && count($CategoryTree) >= 2) {
                foreach ($_POST['layer2'] as $ID) {
                    if (isset($FreePromoteSlots[2][$ID]) && $FreePromoteSlots[2][$ID]) {
                        $Orders['2_' . $ID] = ['Week' => $ID, 'Layer' => 2, 'Category' => $CategoryTree[1]['CategoryID']];
                        $Total += 10;
                    }
                }
            }
            if (isset($_POST['layer3']) && count($CategoryTree) >= 3) {
                foreach ($_POST['layer3'] as $ID) {
                    if (isset($FreePromoteSlots[3][$ID]) && $FreePromoteSlots[3][$ID]) {
                        $Orders['3_' . $ID] = ['Week' => $ID, 'Layer' => 3, 'Category' => $CategoryTree[2]['CategoryID']];
                        $Total += 5;
                    }
                }
            }
            $TotalXMR = Currencies::exchange($Total, 'USD', 'XMR');
            $TotalBTC = Currencies::exchange($Total, 'USD', 'BTC');
            $TotalLTC = Currencies::exchange($Total, 'USD', 'LTC');
            if ($Total != 0 && isset($_POST['Action'])) {
                $SaveOrders = false;
                if ($_POST['Action'] == 'XMR') {
                    if ($User->get('XMR') >= $TotalXMR) {
                        Market::transfer($TotalXMR, 'XMR', $User->getID(), 'fee');
                        $SaveOrders = true;
                    } else {
                        echo Alerts::danger('Your Account Balance Is Not Sufficient!', 'mt-3');
                    }
                } else if ($_POST['Action'] == 'BTC') {
                    if ($User->get('BTC') >= $TotalBTC) {
                        Market::transfer($TotalBTC, 'BTC', $User->getID(), 'fee');
                        $SaveOrders = true;
                    } else {
                        echo Alerts::danger('Your Account Balance Is Not Sufficient!', 'mt-3');
                    }
                } else if ($_POST['Action'] == 'LTC') {
                    if ($User->get('LTC') >= $TotalLTC) {
                        Market::transfer($TotalLTC, 'LTC', $User->getID(), 'fee');
                        $SaveOrders = true;
                    } else {
                        echo Alerts::danger('Your Account Balance Is Not Sufficient!', 'mt-3');
                    }
                }
                if ($SaveOrders) {
                    foreach ($Orders as $Order) {
                        $Order['Item'] = $item['ItemID'];
                        $Order['Created'] = time();
                        $DB->insert('items_promotes', $Order);
                    }
                    $Pages->redirect('item-edit/' . $item['ItemID'] . '/promo/saved');
                }
            }
        }
        if (isset($Paths[2]) && $Paths[2] == 'saved') {
            echo Alerts::success('Your Item Is Now Being Promoted.', 'mt-3');
        }
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>Promote Your Item...</h3>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-5 col-md-3">
            <span class="font-weight-light text-muted">Item:</span>
        </div>
        <div class="col-7 col-md-9">
            <?php echo htmlentities($item['Name']); ?>
        </div>
        <div class="col-5 col-md-3">
            <span class="font-weight-light text-muted">Category:</span>
        </div>
        <div class="col-7 col-md-9">
            <?php echo htmlentities($CategoryTree[0]['Name']); ?>
        </div>
<?php if (count($CategoryTree) >= 2) { ?>
        <div class="col-5 col-md-3">
            <span class="font-weight-light text-muted">Subcategory:</span>
        </div>
        <div class="col-7 col-md-9">
            <?php echo htmlentities($CategoryTree[1]['Name']); ?>
        </div>
<?php } if (count($CategoryTree) >= 3) { ?>
        <div class="col-5 col-md-3">
            <span class="font-weight-light text-muted">Childcategory:</span>
        </div>
        <div class="col-7 col-md-9">
            <?php echo htmlentities($CategoryTree[2]['Name']); ?>
        </div>
<?php } ?>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12">
            Here you can promote your item for a fee!
            <br>
            For the period you have booked a promotion for your item (minimum one week), your ad will appear on the respective page (Marketplace Homepage, Category, Subcategory and/or Childcategory) under one of the first 15 offers (on the first page of the pagination).
            <br>
            After your promotion period has expired, your item will be listed in the order of the creation date.
            <br><br>
            <span class="text-warning">(The checkboxes of available periods are outlined black!)</span>
        </div>
    </div>
</div>

<form action="/item-edit/<?php echo $item['ItemID']; ?>/promo" method="post">
    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <h4>Marketplace Homepage</h4>
            </div>
            <div class="col-6 col-md-2">
                Fee per Week:
            </div>
            <div class="col-6 col-md-10 mb-3">
                <span>20.00 US-$</span><?php if ($User->get('Currency') != 'USD') { echo '<span class="small pl-2">(' . Currencies::exchange(20, 'USD', 'USER') . ' ' . $User->get('Currency') . ')</span>'; } ?>
            </div>
            <div class="col-md-12 overflow-auto">
                <table class="table-sm table-bordered-standard w-100">
                    <tr>
<?php
foreach ($Weeks as $Week) {
    echo '<th scope="col" class="text-center">' . $Week . '</th>' . nl;
}
?>
                    </tr>
                    <tr>
<?php
foreach ($Weeks as $WeekID => $Week) {
    if (array_key_exists('0_' . $WeekID, $MyPromoteSlots)) {
        echo '<td class="text-center" style="padding-left:30px"><input class="form-check-input" type="checkbox" checked disabled><label></label></td>' . nl;
    } else {
        echo '<td class="text-center" style="padding-left:30px"><input class="form-check-input" type="checkbox" name="layer0[]" value="' . $WeekID . '"' . ($FreePromoteSlots[0][$WeekID] ? (array_key_exists('0_' . $WeekID, $Orders) ? ' checked' : '') : ' disabled') . '><label></label></td>' . nl;
    }
}
?>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <h4>Category</h4>
            </div>
            <div class="col-6 col-md-3">
                Fee per Week:
            </div>
            <div class="col-6 col-md-9 mb-3">
                <span>15.00 US-$</span><?php if ($User->get('Currency') != 'USD') { echo '<span class="small pl-2">(' . Currencies::exchange(15, 'USD', 'USER') . ' ' . $User->get('Currency') . ')</span>'; } ?>
            </div>
            <div class="col-md-12 overflow-auto">
                <table class="table-sm table-bordered-lavender w-100">
                    <tr>
<?php
foreach ($Weeks as $Week) {
    echo '<th scope="col" class="text-center">' . $Week . '</th>' . nl;
}
?>
                    </tr>
                    <tr>
<?php
foreach ($Weeks as $WeekID => $Week) {
    if (array_key_exists('1_' . $WeekID, $MyPromoteSlots)) {
        echo '<td class="text-center" style="padding-left:30px"><input class="form-check-input" type="checkbox" checked disabled><label></label></td>' . nl;
    } else {
        echo '<td class="text-center" style="padding-left:30px"><input class="form-check-input" type="checkbox" name="layer1[]" value="' . $WeekID . '"' . ($FreePromoteSlots[1][$WeekID] ? (array_key_exists('1_' . $WeekID, $Orders) ? ' checked' : '') : ' disabled') . '><label></label></td>' . nl;
    }
}
?>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php if (count($CategoryTree) >= 2) { ?>
    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <h4>Subcategory</h4>
            </div>
            <div class="col-6 col-md-3">
                Fee per Week:
            </div>
            <div class="col-6 col-md-9 mb-3">
                <span>10.00 US-$</span><?php if ($User->get('Currency') != 'USD') { echo '<span class="small pl-2">(' . Currencies::exchange(10, 'USD', 'USER') . ' ' . $User->get('Currency') . ')</span>'; } ?>
            </div>
            <div class="col-md-12 overflow-auto">
                <table class="table-sm table-bordered-mallow w-100">
                    <tr>
<?php
foreach ($Weeks as $Week) {
    echo '<th scope="col" class="text-center">' . $Week . '</th>' . nl;
}
?>
                    </tr>
                    <tr>
<?php
foreach ($Weeks as $WeekID => $Week) {
    if (array_key_exists('2_' . $WeekID, $MyPromoteSlots)) {
        echo '<td class="text-center" style="padding-left:30px"><input class="form-check-input" type="checkbox" checked disabled><label></label></td>' . nl;
    } else {
        echo '<td class="text-center" style="padding-left:30px"><input class="form-check-input" type="checkbox" name="layer2[]" value="' . $WeekID . '"' . ($FreePromoteSlots[2][$WeekID] ? (array_key_exists('2_' . $WeekID, $Orders) ? ' checked' : '') : ' disabled') . '><label></label></td>' . nl;
    }
}
?>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php } if (count($CategoryTree) >= 3) { ?>
    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <h4>Childcategory</h4>
            </div>
            <div class="col-6 col-md-3">
                Fee per Week:
            </div>
            <div class="col-6 col-md-9 mb-3">
                <span>5.00 US-$</span><?php if ($User->get('Currency') != 'USD') { echo '<span class="small pl-2">(' . Currencies::exchange(5, 'USD', 'USER') . ' ' . $User->get('Currency') . ')</span>'; } ?>
            </div>
            <div class="col-md-12 overflow-auto">
                <table class="table-sm table-bordered-hibis w-100">
                    <tr>
<?php
foreach ($Weeks as $Week) {
    echo '<th scope="col" class="text-center">' . $Week . '</th>' . nl;
}
?>
                    </tr>
                    <tr>
<?php
foreach ($Weeks as $WeekID => $Week) {
    if (array_key_exists('3_' . $WeekID, $MyPromoteSlots)) {
        echo '<td class="text-center" style="padding-left:30px"><input class="form-check-input" type="checkbox" checked disabled><label></label></td>' . nl;
    } else {
        echo '<td class="text-center" style="padding-left:30px"><input class="form-check-input" type="checkbox" name="layer3[]" value="' . $WeekID . '"' . ($FreePromoteSlots[3][$WeekID] ? (array_key_exists('3_' . $WeekID, $Orders) ? ' checked' : '') : ' disabled') . '><label></label></td>' . nl;
    }
}
?>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php } ?>
    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-12 col-md-3">
                <div class="input-group-append">
                    <button class="btn-sm btn-secondary" type="submit" name="Action" value="Calculate">Calculate Total Price</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-md-2">
                <span class="text-muted">Total Price</span>:
            </div>
            <div class="col-6 col-md-2">
                <h5 class="text-danger font-weight-bold"><?php echo $Language->number($Total); ?> US-$</h5>
            </div>
<?php
if ($User->get('Currency') != 'USD') {
    echo '<div class="col-5 col-md-2 pt-0"><h6 class="text-success font-weight-bold">(' . Currencies::exchange($Total, 'USD', 'USER') . ' ' . $User->get('Currency') . ')</h6></div>' . nl;
}
?>
        </div>
    </div>
<?php if ($Total != 0) { ?>
    <div class="container mt-1 mb-3">
        <div class="row py-1">
            <div class="col-md-3">
                <div class="mb-2">
                    <span class="text-muted">XMR:</span>&nbsp;&nbsp;<?php echo Currencies::exchange($Total, 'USD', 'XMR') ?>
                    <br>
                    <button type="submit" class="btn-xmr btn-xmr-primary btn-xmr-block p-1 mt-2" name="Action" value="XMR"><img src="/img/xmr_white.png" alt="Logo" title="" width="20px" height="20px"/><span class="pl-2">Promote Now</span></button>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2">
                    <span class="text-muted">BTC:</span>&nbsp;&nbsp;<?php echo Currencies::exchange($Total, 'USD', 'BTC') ?>
                    <br>
                    <button type="submit" class="btn-btc btn-btc-primary btn-btc-block p-1 mt-2" name="Action" value="BTC"><img src="/img/btc_white.png" alt="Logo" title="" width="20px" height="20px"/><span class="pl-2">Promote Now</span></button>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2">
                    <span class="text-muted">LTC:</span>&nbsp;&nbsp;<?php echo Currencies::exchange($Total, 'USD', 'LTC') ?>
                    <br>
                    <button type="submit" class="btn-ltc btn-ltc-primary btn-ltc-block p-1 mt-2" name="Action" value="LTC"><img src="/img/ltc_white.png" alt="Logo" title="" width="20px" height="20px"/><span class="pl-2">Promote Now</span></button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</form>
<?php
        return;
    }
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-8 text-info">
            <h3>Here You Can Promote Your Item...</h3>
        </div>
        <div class="col-md-4">
            <a class="btn btn-success btn-block" href="/item-edit/<?php echo $ItemID; ?>/promo">Promote Item</a>
        </div>
    </div>
    <div class="row py-1 mt-3">
        <div class="col-md-12" id="separator"></div>
    </div>
</div>
<?php } ?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>Edit Your Item...</h3>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-12 col-md-3">
            <span class="text-muted">Item Nr.:</span>&nbsp;&nbsp;
            <span class="text-nowrap"><?php echo Market::ItemID($item['ItemID']); ?></span>
        </div>
    </div>
</div>

<?php
if (Forms::isPost()) {
    $errors = false;
    $NewData = [];
    for ($i = 0; $i <= 10; $i++) {
        if (isset($_FILES['Image' . $i]['error']) && $_FILES['Image' . $i]['error'] === 0) {
            list($width, $height, $type, $attr) = getimagesize($_FILES['Image' . $i]['tmp_name']);
            if ($width <= 780 && $height <= 1000) {
                $md5 = md5_file($_FILES['Image' . $i]['tmp_name']);
                if ($type == IMAGETYPE_JPEG) {
                    $type = 'image/jpeg';
                    $ext = 'jpg';
                } else if ($type == IMAGETYPE_PNG) {
                    $type = 'image/png';
                    $ext = 'png';
                } else {
                    continue;
                }
                $FileTo = FILEPATH . $md5 . '.' . $ext;
                rename($_FILES['Image' . $i]['tmp_name'], $FileTo);
                $NewData['Image' . $i] = serialize([
                    'Path'  =>  $FileTo,
                    'MD5'   =>  $md5,
                    'Size'  =>  $_FILES['Image' . $i]['size'],
                    'Type'  =>  $type,
                    'Ext'   =>  $ext
                ]);
            } else {
                $errors = true;
                echo Alerts::danger('Image Too Large! Max. 780x1000px Allowed!', 'mt-3');
            }
        }
    }

    if (!isset($_POST['Category'])) {
        echo Alerts::danger('Choose a Category!', 'mt-3');
    } else if (!$errors) {
        if (isset($_POST['Category']) && !empty($_POST['Category'])) {
            $NewData['Category'] = intval($_POST['Category']);
            $Tree = Categories::getCategoryTree(intval($_POST['Category']));
            $NewData['Cat0'] = $Tree[0]['CategoryID'] ?? null;
            $NewData['Cat1'] = $Tree[1]['CategoryID'] ?? null;
            $NewData['Cat2'] = $Tree[2]['CategoryID'] ?? null;
        }
        if (isset($_POST['Name']) && !empty($_POST['Name'])) $NewData['Name'] = $_POST['Name'];
        if (isset($_POST['Class']) && ($_POST['Class'] == 'physical' || $_POST['Class'] == 'digital')) $NewData['Class'] = $_POST['Class'];
        if ($User->hasRole('admin') || $User->hasRole('staff')) {
            if (isset($_POST['Payment']) && ($_POST['Payment'] == 'escrow' || $_POST['Payment'] == 'fe')) $NewData['Payment'] = $_POST['Payment'];
        }
        if (isset($_POST['OnlyPGP']) && $_POST['OnlyPGP'] == 'Yes') {
            $NewData['OnlyPGP'] = 1;
        } else {
            $NewData['OnlyPGP'] = 0;
        }
        if (isset($_POST['ShortDescription']) && !empty($_POST['ShortDescription'])) $NewData['ShortDescription'] = $_POST['ShortDescription'];
        if (isset($_POST['Description']) && !empty($_POST['Description'])) $NewData['Description'] = $_POST['Description'];
        if (isset($_POST['Quantity']) && $_POST['Quantity'] !== '') $NewData['Quantity'] = $_POST['Quantity'];
        if (isset($_POST['Quantity']) && ($_POST['Quantity'] === '' || preg_match('/[a-z]+/i', $_POST['Quantity']))) $NewData['Quantity'] = null;
        if (isset($_POST['Price']) && !empty($_POST['Price'])) $NewData['Price'] = guessMoney($_POST['Price']);
        if (isset($_POST['Monero']) && $_POST['Monero'] == 'Yes') {
            $NewData['Monero'] = 1;
        } else {
            $NewData['Monero'] = 0;
        }
        if (isset($_POST['Bitcoin']) && $_POST['Bitcoin'] == 'Yes') {
            $NewData['Bitcoin'] = 1;
        } else {
            $NewData['Bitcoin'] = 0;
        }
        if (isset($_POST['Litecoin']) && $_POST['Litecoin'] == 'Yes') {
            $NewData['Litecoin'] = 1;
        } else {
            $NewData['Litecoin'] = 0;
        }
        if (isset($_POST['ShipFrom']) && !empty($_POST['ShipFrom'])) $NewData['ShipFrom'] = intval($_POST['ShipFrom']);
        if (isset($_POST['ShipTo']) && !empty($_POST['ShipTo'])) $NewData['ShipTo'] = intval($_POST['ShipTo']);
        if (isset($_POST['Active']) && $_POST['Active'] == 'Yes') {
            if (!isset($_POST['Quantity']) || is_null($_POST['Quantity']) || intval($_POST['Quantity']) >= 1 || $_POST['Quantity'] == 'Unlimited') {
                $NewData['Active'] = 1;
            } else {
                $NewData['Active'] = 0;
            }
        } else {
            $NewData['Active'] = 0;
        }
        for ($i = 0; $i <= 11; $i++) {
            if (isset($_POST['Shipping' . $i]) && !empty($_POST['Shipping' . $i]) && isset($_POST['ShippingPrice' . $i]) && !empty($_POST['ShippingPrice' . $i])) $NewData['Shipping' . $i] = serialize(['ID' => $_POST['Shipping' . $i], 'Price' => guessMoney($_POST['ShippingPrice' . $i])]);
        }
        if ($User->hasRole('staff') || $User->hasRole('admin')) {
            if (isset($_POST['Blocked']) && $_POST['Blocked'] == '1') {
                $NewData['Blocked'] = 1;
            } else {
                $NewData['Blocked'] = 0;
            }
        }
        $DB->update('items', $NewData, 'ItemID = ' . $DB->int($ItemID));
        $Pages->redirect('item-edit/' . $ItemID . '/ok');
    }
} else if ($Pages->inPath('ok')) {
    echo Alerts::success('The Item Has Been Updated!', 'mt-3');
}
?>

<form action="/item-edit/<?php echo $ItemID; ?>" method="post" enctype="multipart/form-data">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-bold text-warning mb-4" style="line-height: 1.5;">
                Please note that you can publish all information in any language of your choice!<br>However, this can mean that your items will not noticed by all users!<br>We therefore urgently recommend that you publish all information in ENGLISH!
            </div>
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Choose a Category/Subcategory/Childcategory...
            </div>
            <div class="col-md-12">
                <select class="form-control" required name="Category">
<?php
foreach ($Categories as $CategoryID => $Category) {
    echo '<option value="' . $CategoryID . '"' . Forms::selectedVal($CategoryID, $item['Category']) . '>' . $Category['HTML'] . '</option>' . nl;
}
?>
                </select>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit the Item Class...
            </div>
            <div class="col-md-12">
                <span class="ml-4"><input class="form-check-input" type="radio" name="Class" id="Class1" value="physical"<?php echo Forms::checkedVal('physical', $item['Class']); ?>>
                <label class="form-check-label" for="Class1">
                    Physical
                </label></span>
                <span class="ml-5"><input class="form-check-input" type="radio" name="Class" id="Class2" value="digital"<?php echo Forms::checkedVal('digital', $item['Class']); ?>>
                <label class="form-check-label" for="Class2">
                    Digital
                </label></span>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit the Name of the Item...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Name"<?php echo Forms::valueVal($item['Name']); ?>>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit the Short Description of the Item...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="ShortDescription"<?php echo Forms::valueVal($item['ShortDescription']); ?>>
                </div>
            </div>
        </div>
    </div>

<?php
if (!empty($item['Image0'])) {
    $item['Image0'] = unserialize($item['Image0']);
?>
    <div class="col-md-12 mt-1">
        <div style="width: 100%; height: 145px;">
            <img class="p-1" src="/image/<?php echo $item['Image0']['MD5'] . '.' . $item['Image0']['Ext']; ?>" alt="item" title="" style="max-width: 190px; height: auto;"/>
        </div>
    </div>
<?php } ?>

    <div class="container mt-5">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit the Main Image of the Item...
            </div>
            <div class="form-group ml-3 mt-1">
                <input type="file" class="form-control-file mb-2" name="Image0">
<?php if (!empty($item['Image0'])) echo '<a href="/item-edit/' . $ItemID . '/delete-image/0" class="btn-sm btn-danger">Delete</a>' . nl; ?>
            </div>
            <div class="col-md-12 font-weight-light text-danger small mb-2" style="margin-top: -10px;">
                Image Type .png/.jpg/.jpeg - Size max. 780x1000px
            </div>
        </div>
    </div>

    <div class="col-md-12 mt-3">
        <div style="width: 90%; height: 145px; border: 1px solid silver; overflow: auto;">
<?php
for ($ImageNo = 1; $ImageNo <= 10; $ImageNo++) {
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

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit the Images From the Item Gallery...
            </div>
<?php
for ($ImageNo = 1; $ImageNo <= 10; $ImageNo++) {
?>
            <div class="form-group ml-3">
                <input type="file" class="form-control-file mb-2" name="Image<?php echo $ImageNo; ?>">
<?php if (!empty($item['Image' . $ImageNo])) echo '<a href="/item-edit/' . $ItemID . '/delete-image/' . $ImageNo . '" class="btn-sm btn-danger mt-2">Delete</a>' . nl; ?>
            </div>
<?php } ?>
            <div class="col-md-12 font-weight-light text-danger small mb-2" style="margin-top: -10px;">
                Image Type .png/.jpg/.jpeg - Size max. 780x1000px
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit the Stock of the Item...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Quantity" placeholder="Unlimited" value="<?php echo (is_null($item['Quantity']) ? 'Unlimited' : $item['Quantity']); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit the Item Description...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control" rows="5" name="Description"><?php echo Forms::textareaVal($item['Description']); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit Shipping To...
            </div>
            <div class="col-md-12">
                <select class="form-control" required name="ShipTo">
<?php
foreach ($ShipsTo as $STID => $ST) {
    echo '<option value="' . $STID . '"' . Forms::selectedVal($STID, $item['ShipTo']) . '>' . $ST['Name'] . '</option>' . nl;
}
?>
                </select>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted">
                Edit Payment Processing...
            </div>
            <div class="col-md-12 font-weight-light text-danger small mb-2">
                (Finalize Early (FE) - direct payment - only permitted on application and with express approval of support team!)
            </div>
            <div class="col-md-12">
                <select class="form-control" required name="Payment">
                    <option value="escrow"<?php echo Forms::selectedVal('escrow', $item['Payment']); ?>>Escrow</option>
<?php if ($User->hasRole('admin') || $User->hasRole('staff')) { ?>
                    <option value="fe"<?php echo Forms::selectedVal('fe', $item['Payment']); ?>>Finalize Early (FE)</option>
<?php } else { ?>
                    <option value="fe" disabled<?php echo Forms::selectedVal('fe', $item['Payment']); ?>>Finalize Early (FE)</option>
<?php } ?>
                </select>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit Acceptance of PGP Encrypted Notes From a Buyer...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" name="OnlyPGP" value="Yes"<?php echo Forms::checkedVal(1, $item['OnlyPGP']); ?>>
                    <span class="ml-4">Enable Only Encrypted Notes</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted">
                Shipping Costs...
            </div>
            <div class="col-md-12 font-weight-light text-danger small mb-2">
                (For digital items it&rsquo;s NOT necessary to specify shipping costs!)
            </div>
<?php
for ($ShippingNo = 0; $ShippingNo <= 11; $ShippingNo++) {
    if (!empty($item['Shipping' . $ShippingNo])) $item['Shipping' . $ShippingNo] = unserialize($item['Shipping' . $ShippingNo]);
?>
            <div class="col-7 col-md-7">
                <select class="form-control" name="Shipping<?php echo $ShippingNo; ?>">
                    <option value="">Select a Shipping Method</option>
<?php
foreach ($Shippings as $ShippingID => $Shipping) {
    echo '<option value="' . $ShippingID . '"' . Forms::selectedVal($ShippingID, isset($item['Shipping' . $ShippingNo]['ID']) ? $item['Shipping' . $ShippingNo]['ID'] : '') . '>' . $Shipping['Name'] . '</option>' . nl;
}
?>
                </select>
            </div>
            <div class="col-5 col-md-5">
                <div class="form-group">
                    <input type="text" class="form-control" name="ShippingPrice<?php echo $ShippingNo; ?>" placeholder="0.00 US-$"<?php echo Forms::valueVal(isset($item['Shipping' . $ShippingNo]['Price']) ? $Language->number($item['Shipping' . $ShippingNo]['Price']) : ''); ?>>
                </div>
            </div>
<?php } ?>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit Item Single Price (Ex. Shipping Costs)...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Price" placeholder="9999.99 US-$"<?php echo Forms::valueVal($Language->number($item['Price'])); ?>>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit Accepted Cryptocurrency Payments...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" value="Yes" name="Monero"<?php echo Forms::checkedVal(1, $item['Monero']); ?>>
                    <span class="ml-4">Monero</span>
                </div>
            </div>
            <div class="col-md-12" style="margin-top: -10px;">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" value="Yes" name="Bitcoin"<?php echo Forms::checkedVal(1, $item['Bitcoin']); ?>>
                    <span class="ml-4">Bitcoin</span>
                </div>
            </div>
            <div class="col-md-12" style="margin-top: -10px;">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" value="Yes" name="Litecoin"<?php echo Forms::checkedVal(1, $item['Litecoin']); ?>>
                    <span class="ml-4">Litecoin</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Edit Status of the Item...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" value="Yes" name="Active"<?php echo Forms::checkedVal(1, $item['Active']); ?>>
                    <span class="ml-4">Item Is Activ</span>
                </div>
            </div>
        </div>
    </div>

<?php if ($User->hasRole('staff') || $User->hasRole('admin')) { ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-bold text-danger mb-2">
                Limitations
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" name="Blocked" value="1"<?php echo Forms::checkedVal($item['Blocked'], 1); ?>>
                    <span class="ml-4 text-danger">Block Item</span>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
            </div>
        </div>
    </div>
</form>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12">
                <a href="/item-edit/<?php echo $ItemID; ?>/askdelete" type="submit" class="btn btn-danger btn-block float-right">Delete Item</a>
            </div>
        </div>
    </div>
