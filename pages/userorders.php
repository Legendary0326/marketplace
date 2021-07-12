<?php
$HTMLHead->addNav('cat_search');
$Paths = $Pages->getPath();
$Mode = 'current';
if (isset($Paths[0])) {
    if ($Paths[0] == 'archived') {
        $Mode = 'archived';
    } else if ($Paths[0] == 'disputes') {
        $Mode = 'disputes';
    }
}

if ($Mode == 'current') { ?>
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="/userorders/archived" class="btn btn-link"><lang>Archived Orders</lang></a>
        </div>
        <div class="col-md-6 px-2">
            <a class="text-white" href="/userorders/disputes" class="btn btn-link"><lang>Disputes</lang></a>
        </div>
    </div>
</div>
<?php } else if ($Mode == 'archived') { ?>
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="/userorders" class="btn btn-link"><lang>Current Orders</lang></a>
        </div>
        <div class="col-md-6 px-2">
            <a class="text-white" href="/userorders/disputes" class="btn btn-link"><lang>Disputes</lang></a>
        </div>
    </div>
</div>
<?php } else if ($Mode == 'disputes') { ?>
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="/userorders" class="btn btn-link"><lang>Current Orders</lang></a>
        </div>
        <div class="col-md-6 px-2">
            <a class="text-white" href="/userorders/archived" class="btn btn-link"><lang>Archived Orders</lang></a>
        </div>
    </div>
</div>
<?php } ?>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 text-info mb-2">
<?php if ($Mode == 'current') { ?>
            <h3><lang>Current Orders</lang>...</h3>
<?php } else if ($Mode == 'archived') { ?>
            <h3><lang>Archived Orders</lang>...</h3>
<?php } else if ($Mode == 'disputes') { ?>
            <h3><lang>Disput Orders</lang>...</h3>
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
$Orders = Market::getMyOrders($Mode);
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