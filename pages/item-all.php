<?php
if (!$User->hasRole('vendor')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
$CountOrders = Market::countMyVendorOrders();
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
            <a class="text-white" href="/vendororders" class="btn btn-link"><lang>Orders</lang><?php if ($CountOrders >= 1) { echo '<span class="badge badge-order badge-pill ml-1">' . $CountOrders . '</span>'; } ?></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderThirdLine">
            <a class="text-white" href="/dispute-vendor" class="btn btn-link"><lang>Disputes</lang><?php if ($CountDisputes >= 1) { echo '<span class="badge badge-dispute badge-pill ml-1">' . $CountDisputes . '</span>'; } ?></a>
        </div>
    </div>
</div>

<?php
if ($Pages->inPath('ok')) {
    echo Alerts::success('<lang>Your Item Has Been Created Successfully!</lang>', 'mt-3');
} else if ($Pages->inPath('okdelete')) {
    echo Alerts::success('<lang>Your Item Has Been Deleted Successfully!</lang>', 'mt-3');
}
?>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 text-info mb-2">
            <h3><lang>Items Overview</lang>...</h3>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center"><lang>Item Nr.</lang></th>
                    <th scope="col" class="text-center"><lang>Item</lang></th>
                    <th scope="col" class="text-center"><lang>Category</lang></th>
                    <th scope="col" class="text-center"><lang>Stock</lang></th>
                    <th scope="col" class="text-center"><lang>Status</lang></th>
                    <th scope="col" class="text-center"><lang>Edit</lang></th>
                </tr>
<?php
$MyItems = $DB->fetch_all($DB->query('SELECT * FROM items WHERE Vendor = ' . $DB->int($User->getID())), MYSQLI_ASSOC);
if (count($MyItems) >= 1) {
    foreach ($MyItems as $MyItem) {
        echo '<tr>' . nl;
        echo '<td class="text-center text-nowrap">' . Market::ItemID($MyItem['ItemID'], 8) . '</td>' . nl;
        echo '<td class="text-center text-nowrap">' . htmlentities($MyItem['Name']) . '</td>' . nl;
        $CategoryTree = Categories::getCategoryTree($MyItem['Category']);
        echo '<td class="text-center text-nowrap">';
        foreach ($CategoryTree as $i => $Category) {
            if ($i != 0) echo ' / ';
            echo $Category['Name'];
        }
        echo '</td>' . nl;
        echo '<td class="text-center">' . (is_null($MyItem['Quantity']) ? '<lang>Unlimited</lang>' : $MyItem['Quantity']) . '</td>';
        echo '<td class="text-center">' . ($MyItem['Blocked'] ? '<lang>Blocked</lang>' : ($MyItem['Active'] ? '<lang>Active</lang>' : '<lang>Inactive</lang>')) . '</td>';
        echo '<td class="text-center"><a class="btn-sm btn-primary" href="/item-edit/' . $MyItem['ItemID'] . '" role="button"><lang>Edit</lang></a></td>' . nl;
        echo '</tr>' . nl;
    }
}
?>
            </table>
        </div>
    </div>
</div>
