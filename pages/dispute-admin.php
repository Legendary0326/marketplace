<?php
if (!$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <h3><lang>New Disputes</lang>...</h3>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center text-nowrap"><lang>Date</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Buyer</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Vendor</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Item</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Price</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Edit</lang></th>
                </tr>
<?php
$SQL = 'SELECT OrderID, StatusChanged, Buyer, (SELECT Username FROM user WHERE UserID = Buyer) AS BuyerName, Vendor, (SELECT Username FROM user WHERE UserID = Vendor) AS VendorName, Name, PayAmount, PayWith FROM orders WHERE ';
$SQL .= 'Moderator IS NULL AND (Status = ' . $DB->string('Dispute') . ' OR DisputeRequested = 1) ORDER BY Created';
$Disputes = $DB->query($SQL);
foreach ($Disputes as $Dispute) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Dispute['StatusChanged']) . '</td>
<td class="text-left"><a href="profile/' . $Dispute['Buyer'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Dispute['BuyerName'])) . '</span></a></td>
<td class="text-left"><a href="profile/' . $Dispute['Vendor'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Dispute['VendorName'])) . '</span></a></td>
<td class="text-left text-nowrap">' . htmlentities($Dispute['Name']) . '</td>
<td class="text-right">' . $Language->number($Dispute['PayAmount'], 8) . ' ' . $Dispute['PayWith'] . '</td>' . nl;
    echo '<td class="text-center"><a class="btn-sm btn-primary" href="/order-view/' . $Dispute['OrderID'] . '" role="button"><lang>Edit</lang></a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <h3><lang>My Disputes In Process</lang>...</h3>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center text-nowrap"><lang>Date</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Buyer</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Vendor</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Item</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Price</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Edit</lang></th>
                </tr>
<?php
$SQL = 'SELECT OrderID, StatusChanged, Buyer, (SELECT Username FROM user WHERE UserID = Buyer) AS BuyerName, Vendor, (SELECT Username FROM user WHERE UserID = Vendor) AS VendorName, Name, PayAmount, PayWith, DisputeLastRead, (SELECT MAX(DisputeID) FROM orders_disputes WHERE orders_disputes.OrderID = orders.OrderID) AS DisputeLastMessage FROM orders WHERE ';
$SQL .= 'Moderator = ' . $DB->int($User->getID()) . ' AND Status = ' . $DB->string('Dispute');
$SQL .= ' ORDER BY Created';
$Disputes = $DB->query($SQL);
foreach ($Disputes as $Dispute) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Dispute['StatusChanged']) . '</td>
<td class="text-left"><a href="profile/' . $Dispute['Buyer'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Dispute['BuyerName'])) . '</span></a></td>
<td class="text-left"><a href="profile/' . $Dispute['Vendor'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Dispute['VendorName'])) . '</span></a></td>
<td class="text-left text-nowrap">' . htmlentities($Dispute['Name']) . '</td>
<td class="text-right">' . $Language->number($Dispute['PayAmount'], 8) . ' ' . $Dispute['PayWith'] . '</td>' . nl;
    echo '<td class="text-center"><a class="btn-sm ' . ($Dispute['DisputeLastRead'] != $Dispute['DisputeLastMessage'] ? 'btn-warning' : 'btn-primary') . '" href="/order-view/' . $Dispute['OrderID'] . '" role="button"><lang>Edit</lang></a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>

<?php if ($User->hasRole('admin')) { ?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <h3><lang>Other Disputes In Process</lang>...</h3>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center text-nowrap"><lang>Date</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Buyer</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Vendor</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Moderator</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Item</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Price</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Edit</lang></th>
                </tr>
<?php
$SQL = 'SELECT OrderID, StatusChanged, Buyer, (SELECT Username FROM user WHERE UserID = Buyer) AS BuyerName, Vendor, (SELECT Username FROM user WHERE UserID = Vendor) AS VendorName, Moderator, (SELECT Username FROM user WHERE UserID = Moderator) AS ModeratorName, Name, PayAmount, PayWith FROM orders WHERE ';
$SQL .= 'Moderator IS NOT NULL AND Moderator != ' . $DB->int($User->getID()) . ' AND Status = ' . $DB->string('Dispute');
$SQL .= ' ORDER BY Created';
$Disputes = $DB->query($SQL);
foreach ($Disputes as $Dispute) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Dispute['StatusChanged']) . '</td>
<td class="text-left"><a href="profile/' . $Dispute['Buyer'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Dispute['BuyerName'])) . '</span></a></td>
<td class="text-left"><a href="profile/' . $Dispute['Vendor'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Dispute['VendorName'])) . '</span></a></td>
<td class="text-left"><a href="profile/' . $Dispute['Moderator'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Dispute['ModeratorName'])) . '</span></a></td>
<td class="text-left text-nowrap">' . htmlentities($Dispute['Name']) . '</td>
<td class="text-right">' . $Language->number($Dispute['PayAmount'], 8) . ' ' . $Dispute['PayWith'] . '</td>' . nl;
    echo '<td class="text-center"><a class="btn-sm btn-primary" href="/order-view/' . $Dispute['OrderID'] . '" role="button"><lang>Edit</lang></a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php } ?>