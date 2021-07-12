<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/item-create.php***/
if (!$User->hasRole('vendor')) $Pages->redirect('');
$currencies = Currencies::getCurrencies();
$HTMLHead->addNav('cat_search');
$ShipsTo = Market::getShipsTo();
$Shippings = Market::getShippings();
$Categories = Categories::getCategories();
$CountOrders = Market::countMyVendorOrders();
$CountDisputes = Market::countMyVendorDisputes();
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

<?php
if (empty($User->get('VendorTerms')) || empty($User->get('VendorRefunds'))) {
    echo Alerts::danger('Please Upload Your Terms & Conditions and/or Refund Policy First!', 'mt-3');
    return;
} else if ($User->get('2FA') == 0) {
    echo Alerts::danger('Please Activate 2FA First!', 'mt-3');
    return;
}
?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>Create a New Item...</h3>
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
        echo Alerts::danger('Please Choose a Category!', 'mt-3');
    } else if (!$errors) {
        $NewData['Vendor'] = $User->getID();
        $NewData['Created'] = time();
        if (isset($_POST['Category']) && !empty($_POST['Category'])) {
            $NewData['Category'] = intval($_POST['Category']);
            $Tree = Categories::getCategoryTree(intval($_POST['Category']));
            $NewData['Cat0'] = $Tree[0]['CategoryID'] ?? null;
            $NewData['Cat1'] = $Tree[1]['CategoryID'] ?? null;
            $NewData['Cat2'] = $Tree[2]['CategoryID'] ?? null;
        }
        if (isset($_POST['Name']) && !empty($_POST['Name'])) $NewData['Name'] = $_POST['Name'];
        if (isset($_POST['Class']) && ($_POST['Class'] == 'physical' || $_POST['Class'] == 'digital')) $NewData['Class'] = $_POST['Class'];
        if (isset($_POST['Payment']) && ($_POST['Payment'] == 'escrow' || $_POST['Payment'] == 'fe')) $NewData['Payment'] = 'escrow';
        if (isset($_POST['OnlyPGP']) && $_POST['OnlyPGP'] == 'Yes') $NewData['OnlyPGP'] = 1;
        if (isset($_POST['ShortDescription']) && !empty($_POST['ShortDescription'])) $NewData['ShortDescription'] = $_POST['ShortDescription'];
        if (isset($_POST['Description']) && !empty($_POST['Description'])) $NewData['Description'] = $_POST['Description'];
        if (isset($_POST['Quantity']) && $_POST['Quantity'] !== '') $NewData['Quantity'] = $_POST['Quantity'];
        if (isset($_POST['Price']) && !empty($_POST['Price'])) $NewData['Price'] = guessMoney($_POST['Price']);
        if (isset($_POST['Monero']) && $_POST['Monero'] == 'Yes') $NewData['Monero'] = 1;
        if (isset($_POST['Bitcoin']) && $_POST['Bitcoin'] == 'Yes') $NewData['Bitcoin'] = 1;
        if (isset($_POST['Litecoin']) && $_POST['Litecoin'] == 'Yes') $NewData['Litecoin'] = 1;
        if (isset($_POST['ShipFrom']) && !empty($_POST['ShipFrom'])) $NewData['ShipFrom'] = intval($_POST['ShipFrom']);
        if (isset($_POST['ShipTo']) && !empty($_POST['ShipTo'])) $NewData['ShipTo'] = intval($_POST['ShipTo']);
        for ($i = 0; $i <= 11; $i++) {
            if (isset($_POST['Shipping' . $i]) && !empty($_POST['Shipping' . $i]) && isset($_POST['ShippingPrice' . $i]) && !empty($_POST['ShippingPrice' . $i])) $NewData['Shipping' . $i] = serialize(['ID' => $_POST['Shipping' . $i], 'Price' => guessMoney($_POST['ShippingPrice' . $i])]);
        }
        $NewData['Active'] = 1;
        $DB->insert('items', $NewData);
        $Pages->redirect('item-all/ok');
    }
}
?>

<form action="/item-create" method="post" enctype="multipart/form-data">
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
                    <option value="" disabled selected hidden>Categories</option>
<?php
foreach ($Categories as $CategoryID => $Category) {
    echo '<option value="' . $CategoryID . '">' . $Category['HTML'] . '</option>' . nl;
}
?>
                </select>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Choose a Item Class...
            </div>
            <div class="col-md-12">
                <span class="ml-4"><input class="form-check-input" type="radio" name="Class" id="Class1" value="physical" checked>
                <label class="form-check-label" for="Class1">
                    Physical
                </label></span>
                <span class="ml-5"><input class="form-check-input" type="radio" name="Class" id="Class2" value="digital">
                <label class="form-check-label" for="Class2">
                    Digital
                </label></span>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter a Name for the Item...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Name">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter a Short Description for Your Item...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="ShortDescription">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Upload a Main Image for the Item...
            </div>
                <div class="form-group ml-3">
                    <input type="file" class="form-control-file" name="Image0">
                </div>
            <div class="col-md-12 font-weight-light text-danger small mb-2" style="margin-top: -10px;">
                Image Type .png/.jpg/.jpeg - Size max. 780x1000px
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Upload More Images for Your Item Gallery...
            </div>
<?php
for ($ImageNo = 1; $ImageNo <= 10; $ImageNo++) {
?>
            <div class="form-group ml-3">
                <input type="file" class="form-control-file" name="Image<?php echo $ImageNo; ?>">
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
                Items in Stock...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Quantity" placeholder="Unlimited">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Item Description...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control" rows="5" name="Description"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Shipping To...
            </div>
            <div class="col-md-12">
                <select class="form-control" required name="ShipTo">
<?php
foreach ($ShipsTo as $STID => $ST) {
    echo '<option value="' . $STID . '">' . $ST['Name'] . '</option>' . nl;
}
?>
                </select>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted">
                Payment Processing...
            </div>
            <div class="col-md-12 font-weight-light text-danger small mb-2">
                (Finalize Early (FE) - direct payment - only permitted on application and with express approval of support team!)
            </div>
            <div class="col-md-12">
                <select class="form-control" required name="Payment">
                    <option value="escrow">Escrow</option>
                    <option value="fe" disabled>Finalize Early (FE)</option>
                </select>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                I Will Only Accept PGP Encrypted Notes From a Buyer...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" name="OnlyPGP" value="Yes">
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
?>
            <div class="col-7 col-md-7">
                <select class="form-control" name="Shipping<?php echo $ShippingNo; ?>">
                    <option value="">Select a Shipping Method</option>
<?php
foreach ($Shippings as $ShippingID => $Shipping) {
    echo '<option value="' . $ShippingID . '">' . $Shipping['Name'] . '</option>' . nl;
}
?>
                </select>
            </div>
            <div class="col-5 col-md-5">
                <div class="form-group">
                    <input type="text" class="form-control" name="ShippingPrice<?php echo $ShippingNo; ?>" placeholder="0.00 US-$">
                </div>
            </div>
<?php } ?>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Item Single Price (Ex. Shipping Costs)
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Price" placeholder="9999.99 US-$">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                For Payments Accepted Cryptocurrencies...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" value="Yes" name="Monero">
                    <span class="ml-4">Monero</span>
                </div>
            </div>
            <div class="col-md-12" style="margin-top: -10px;">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" value="Yes" name="Bitcoin">
                    <span class="ml-4">Bitcoin</span>
                </div>
            </div>
            <div class="col-md-12" style="margin-top: -10px;">
                <div class="form-group">
                    <input class="form-check-input ml-1" type="checkbox" value="Yes" name="Litecoin">
                    <span class="ml-4">Litecoin</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 text-info">
                <h3>Promote Your Item...</h3>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12 text-info">
                After you&rsquo;ve created your item, you can promote your ad by placing it among the first 15 articles on a category page.
                <br>
                To do this, go to the &raquo;My Items&laquo; page in your Vendor-Shop and then click on [Edit] in the line for the item you want to promote. At the top of the &raquo;Edit your Item&laquo; page you will find a button with the option [Promote Item].
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-block">Create Item</button>
            </div>
        </div>
    </div>
</form>