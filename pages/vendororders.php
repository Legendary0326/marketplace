<?php
if (!$User->hasRole('vendor')) $Pages->redirect('');
$Paths = $Pages->getPath();
$ShowArchived = false;
if (isset($Paths[0]) && $Paths[0] == 'archived') {
    $ShowArchived = true;
}
$HTMLHead->addNav('cat_search');
$CountDisputes = Market::countMyVendorDisputes();
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md px-2" id="borderPageHeaderFirstLineFirstRow">
            <a class="text-white" href="/vendorshop" class="btn btn-link"><lang>Statistics</lang></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderFirstLineSecondRow">
            <a class="text-white" href="/terms" class="btn btn-link"><lang>Terms & Conditions</lang></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineFirstRow">
            <a class="text-white" href="/item-create" class="btn btn-link"><lang>Upload Item</lang></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineSecondRow">
            <a class="text-white" href="/item-all" class="btn btn-link"><lang>My Items</lang></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderThirdLine">
            <a class="text-white" href="/dispute-vendor" class="btn btn-link"><lang>Disputes</lang><?php if ($CountDisputes >= 1) { echo '<span class="badge badge-dispute badge-pill ml-1">' . $CountDisputes . '</span>'; } ?></a>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
<?php if ($ShowArchived) { ?>
            <h3><lang>Archived Orders</lang>...</h3>
<?php } else { ?>
            <h3><lang>Current Orders</lang>...</h3>
<?php } ?>
        </div>
        <div class="col-6 col-md-4 p-1" id="subMenu" id="borderPageHeaderFirstLineFirstRow" style="max-height: 35px;">
<?php if ($ShowArchived) { ?>
            <a class="text-white" href="/vendororders" class="btn btn-link"><lang>Current Orders</lang></a>
<?php } else { ?>
            <a class="text-white" href="/vendororders/archived" class="btn btn-link"><lang>Archived Orders</lang></a>
<?php } ?>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center"><lang>Date</lang></th>
                    <th scope="col" class="text-center"><lang>Item</lang></th>
                    <th scope="col" class="text-center"><lang>Price</lang></th>
                    <th scope="col" class="text-center"><lang>Status</lang></th>
                    <th scope="col" class="text-center"><lang>Edit</lang></th>
                </tr>
<?php
$Orders = Market::getMyVendorOrders($ShowArchived ? 'archived' : 'current');
foreach ($Orders as $Order) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Order['Created']) . '</td>
<td class="text-center text-nowrap">' . substr($Order['Name'], 0, 25) . '</td>
<td class="text-center text-nowrap">' . $Language->number($Order['PayAmount'], 8) . ' ' . $Order['PayWith'] . '</td>
<td class="text-center">' . Market::getButton($Order['Status']) . '</td>
<td class="text-center"><a class="btn-sm btn-primary" href="/order-view/' . $Order['OrderID'] . '" role="button"><lang>View</lang></a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>