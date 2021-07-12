<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/marketplace.php***/
$VendorShop = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
if (empty($VendorShop)) {
    $VendorShop = false;
} else {
    $VendorShop = $User->getByUserID('UserID', $VendorShop);
    if (empty($VendorShop) || $User->getByUserID('Role', $VendorShop) != 'vendor') {
        $VendorShop = false;
    }
}
$HTMLHead->addNav('cat_search');
$Market = new Market($VendorShop);
if (isset($_GET['search'])) {
    $Market->setSearch($_POST);
    $Pages->redirect('marketplace' . ($VendorShop === false ? '' : '/' . $VendorShop));
} else if (isset($_GET['reset'])) {
    $Market->set('searchSQL', false);
    $Pages->redirect('marketplace' . ($VendorShop === false ? '' : '/' . $VendorShop));
} else if (isset($_GET['page'])) {
    $Market->setPage($_GET['page']);
} else if (isset($_GET['setCat']) && intval($_GET['setCat']) == $_GET['setCat']) {
    $Market->setSearch(['category' => intval($_GET['setCat']), 'orderBy' => 'priceASC']);
    $Pages->redirect('marketplace');
}

$Data = $Market->getPageData();

if ($Data['NumItems'] == 0) {
    echo Alerts::info('No Results Found!', 'mt-3');
    return false;
}

if ($Market->get('category')) {
    $Tree = Categories::getCategoryTree($Market->get('category'));
    if (!empty($Tree)) {
    $CategoriesNum = count($Tree);
        echo '<div class="container mt-3">' . nl . '<div class="row py-1">' . nl . '<div class="col-md-12 text-secondary" style="font-size: 175%;">';
    foreach ($Tree as $i => $Category) {
        echo htmlentities($Category['Name']) . ($i < $CategoriesNum - 1 ? '&nbsp;/&nbsp;' : '') . nl;
    }
    echo '</div>' . nl . '</div>' . nl . '</div>';
    }
}

if ($VendorShop !== false) {
    echo '<div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 text-info">
                <h3>' . htmlentities(ucwords($User->getByUserID('Username', $VendorShop))) . '&nbsp;Vendor-Shop:</h3>
            </div>
        </div>
    </div>' . nl;
}

echo '<div class="container text-center mt-3 mb-3">' . nl; 
echo $Data['Pagination']->getHTML();
echo '</div>' . nl;

echo '<div class="container">' . nl;
foreach ($Data['Items'] as $itemID => $item) {
    if (($itemID) % 3 == 0) {
        echo '<div class="row">' . nl;
    }
    echo '<div class="col-md-auto border shadow-sm p-2 m-1" id="item_box">
<div class="row">
<div class="col-md-12">
<span class="text-muted">Item Nr.:</span>&nbsp;&nbsp;
# ' . str_pad($item['ItemID'], 5, '0', STR_PAD_LEFT) . '
<br>
<a href="/item-details/' . $item['ItemID'] . '" class="font-weight-bold text-info">' . htmlentities($item['Name']) . '</a>
<br>
<span class="text-muted">Vendor:</span>&nbsp;&nbsp;
<a href="/profile/' . $item['Vendor'] . '" class="text-nowrap text-info">' . htmlentities($item['VendorName']) . '</a>
<br>
<span class="text-muted">Status:</span>&nbsp;&nbsp;
' . $item['VendorRankName'] . '
<br>
<span class="text-muted">Item Review:</span>&nbsp;&nbsp;
' . $Language->number($item['ItemReview'], 1) . '%
<br>
<span class="text-muted">Quantity Left:</span>&nbsp;&nbsp;
' . (is_null($item['Quantity']) ? $Language->translate('unlimited') : $item['Quantity']) . '
<br>
<span class="text-muted">Country of Dispatch:</span>&nbsp;&nbsp;
' . htmlentities($item['ShipFromName']) . '
<br>
<span class="text-muted">Ships To:</span>&nbsp;&nbsp;
' . htmlentities($item['ShipToName']) . '
<br>
<span class="text-muted">Item Class:</span>&nbsp;&nbsp;
' . $Language->translate($item['Class']) . '
<br>
<span class="text-muted">Price (Ex. Shipping):</span>&nbsp;&nbsp;
<span class="text-danger">' . $Language->number($item['Price'], 2) . ' US-$</span>
<br><br>' . nl;
    if (!empty($item['Image0'])) {
        $Pic = unserialize($item['Image0']);
        echo '<img class="mb-2" src="/image/' . $Pic['MD5'] . '.' . $Pic['Ext'] . '" alt="item" title="" style="width: 40%; max-width: ; height: auto; max-width: ;"/>' . nl;
    } else {
        echo '<img class="mb-2" src="/img/no_pic.png" alt="item" title="" style="width: 40%; max-width: ; height: auto; max-width: ;"/>' . nl;
    }
    echo '<a class="btn-sm btn-success text-nowrap ml-4" href="/item-details/' . $item['ItemID'] . '" role="button">Show Details</a>
</div>
</div>
</div>' . nl;
    if (($itemID) % 3 == 2) {
        echo '</div>' . nl; //row
    }
}
if (($itemID) % 3 != 2) {
    echo '</div>' . nl; //row
}
echo '</div>' . nl; //container
echo '<div class="container text-center mt-3 mb-3">' . nl; 
echo $Data['Pagination']->getHTML();
echo '</div>' . nl;
