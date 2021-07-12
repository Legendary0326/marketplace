<?php
if (!$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$Paths = $Pages->getPath();
$ShowArchived = false;
if (isset($Paths[0]) && $Paths[0] == 'archived') {
    $ShowArchived = true;
}
$HTMLHead->addNav('cat_search');
?>
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-12 col-md-12 px-2">
<?php if ($ShowArchived) { ?>
            <a class="text-white" href="/order-overview" class="btn btn-link"><lang>Current Orders</lang></a>
<?php } else { ?>
            <a class="text-white" href="/order-overview/archived" class="btn btn-link"><lang>Archived Orders</lang></a>
<?php } ?>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <h3><lang>Overview Of All Orders</lang>...</h3>
        </div>
    </div>
</div>


<form action="/order-overview<?php if ($ShowArchived) { echo '/archived'; } ?>" method="post">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-4">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" placeholder="<lang>Searchterm</lang>" name="Searchterm"<?php echo Forms::value('Searchterm'); ?>>
                </div>
            </div>
            <div class="col-md-8">
                <button class="btn-sm btn-secondary" type="submit"><lang>Search</lang></button>
            </div>
        </div>
    </div>
</form>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center"><lang>Date</lang></th>
                    <th scope="col" class="text-center"><lang>Vendor</lang></th>
                    <th scope="col" class="text-center"><lang>Buyer</lang></th>
                    <th scope="col" class="text-center"><lang>Item</lang></th>
                    <th scope="col" class="text-center"><lang>Price</lang></th>
                    <th scope="col" class="text-center"><lang>Status</lang></th>
                    <th scope="col" class="text-center"><lang>Edit</lang></th>
                </tr>
<?php
$SQL = 'SELECT OrderID, Created, Vendor, (SELECT Username FROM user WHERE Vendor = UserID) AS VendorName, Buyer, (SELECT Username FROM user WHERE Buyer = UserID) AS BuyerName, Name, PayWith, PayAmount, Status FROM orders WHERE ';
if ($ShowArchived) {
    $SQL .= '(Status = ' . $DB->string('Canceled') . ' OR (Status = ' . $DB->string('Finalized') . ' AND VendorReview IS NOT NULL))';
} else {
    $SQL .= '(Status = ' . $DB->string('NotYetConfirmed') . ' OR Status = ' . $DB->string('Confirmed') . ' OR Status = ' . $DB->string('Shipped') . ' OR (Status = ' . $DB->string('Finalized') . ' AND VendorReview IS NULL))';
}
if (Forms::isPost() && isset($_POST['Searchterm']) && !empty($_POST['Searchterm'])) {
    $Searchterm = trim($_POST['Searchterm']);
    $Where = ['Name LIKE ' . $DB->string('%' . $Searchterm . '%')];
    if (preg_match('/^\d\d\\d\d-\d\d\-\d\d$/i', $Searchterm)) {
        $Where[] = 'FROM_UNIXTIME(Created, \'%Y-%m-%d\') = ' . $DB->string($Searchterm);
    }
    if (preg_match('/^[a-z0-9]{4,32}$/i', $Searchterm)) {
        $Where[] = '(SELECT Username FROM user WHERE Vendor = UserID) LIKE ' . $DB->string('%' . $Searchterm . '%');
        $Where[] = '(SELECT Username FROM user WHERE Buyer = UserID) LIKE ' . $DB->string('%' . $Searchterm . '%');
    }
    if (preg_match('/^[0-9\.,]+$/i', $Searchterm)) {
        $Where[] = 'PayAmount = ' . $DB->float(guessMoney($Searchterm));
    }
    if (strtolower($Searchterm) == 'xmr') {
        $Where[] = 'PayWith = ' . $DB->string('XMR');
    }
    if (strtolower($Searchterm) == 'btc') {
        $Where[] = 'PayWith = ' . $DB->string('BTC');
    }
    if (strtolower($Searchterm) == 'ltc') {
        $Where[] = 'PayWith = ' . $DB->string('LTC');
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Not Yet Confirmed'))) {
        $Where[] = 'Status = ' . $DB->string('NotYetConfirmed');
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Confirmed'))) {
        $Where[] = 'Status = ' . $DB->string('Confirmed');
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Shipped'))) {
        $Where[] = 'Status = ' . $DB->string('Shipped');
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Canceled'))) {
        $Where[] = 'Status = ' . $DB->string('Canceled');
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Finalized'))) {
        $Where[] = 'Status = ' . $DB->string('Finalized');
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Dispute'))) {
        $Where[] = 'Status = ' . $DB->string('Dispute');
    }
    $SQL .= ' AND (' . implode(' OR ', $Where) . ')';
}
$SQL .= ' ORDER BY Created';
$Orders = $DB->query($SQL);
foreach ($Orders as $Order) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Order['Created']) . '</td>
<td class="text-center text-nowrap"><a class="text-primary" href="/account/' . $Order['Vendor'] . '" class="btn btn-link">' . htmlentities(ucfirst($Order['VendorName'])) . '</a></td>
<td class="text-center text-nowrap"><a class="text-primary" href="/account/' . $Order['Buyer'] . '" class="btn btn-link">' . htmlentities(ucfirst($Order['BuyerName'])) . '</a></td>
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
