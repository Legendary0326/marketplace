<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/dispute-vendor.php***/
if (!$User->hasRole('vendor') && !$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
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
            <a class="text-white" href="/item-create" class="btn btn-link">Upload Item</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineSecondRow">
            <a class="text-white" href="/item-all" class="btn btn-link">My Items</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderThirdLine">
            <a class="text-white" href="/vendororders" class="btn btn-link">Orders<?php if ($CountOrders >= 1) { echo '<span class="badge badge-order badge-pill ml-1">' . $CountOrders . '</span>'; } ?></a>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 text-info mb-2">
            <h3>Disputes...</h3>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Price</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col" class="text-center">Edit</th>
                </tr>
<?php
$Orders = Market::getMyVendorOrders('disputes');
foreach ($Orders as $Order) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Order['Created']) . '</td>
<td class="text-center text-nowrap">' . substr($Order['Name'], 0, 25) . '</td>
<td class="text-center text-nowrap">' . $Language->number($Order['PayAmount'], 8) . ' ' . $Order['PayWith'] . '</td>
<td class="text-center">' . Market::getButton($Order['Status']) . '</td>
<td class="text-center"><a class="btn-sm btn-primary" href="/order-view/' . $Order['OrderID'] . '" role="button">View</a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>