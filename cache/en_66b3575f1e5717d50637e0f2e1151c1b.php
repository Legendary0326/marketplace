<?php /***REALFILE: D:\xampp\htdocs\marketplace/pages/index.php***/
$HTMLHead->addNav('cat_search');
if ($User->hasRole('staff') || $User->hasRole('admin')) {
if ($User->get('scoring') != 100) {
    Ranking::recalculateRanks($User->getID());
}
$Periods = [
    'today'     => 'Today',
    '30days'    => '30 Days',
    'total'     => 'Total'
];
$CashFlowPeriods = [
    '7days'     => '7 Days',
    '30days'    => '30 Days',
    'total'     => 'Total'
];
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>General Statistics...</h3>
        </div>
    </div>
</div>

<div class="container mt-0">
    <div class="row py-1">
        <div class="col-7 col-md-3">
            <span class="font-weight-light text-muted">Open Disputes:</span>
        </div>
        <div class="col-5 col-md-3">
<?php
$NumNewDisputes = $DB->fetch_assoc($DB->query('SELECT COUNT(*) AS Num FROM orders WHERE Moderator IS NULL AND (Status = ' . $DB->string('Dispute') . ' OR DisputeRequested = 1)'));
echo '<span class="text-danger">' . $NumNewDisputes['Num'] . '</span>';
?>
        </div>
    </div>
</div>

<?php if ($User->hasRole('admin')) { ?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Marketplace Profit:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left">Period</th>
                    <th scope="col" class="text-center">XMR</th>
                    <th scope="col" class="text-center">BTC</th>
                    <th scope="col" class="text-center">LTC</th>
                    <th scope="col" class="text-center">US-$</th>
                    <th scope="col" class="text-center" id="pastel-green"><?php echo $User->get('Currency'); ?></th>
                </tr>
<?php
foreach ($Periods as $PeriodID => $Period) {
    $Data = $DB->getOne('stats_marketplace_profit', 'Period = ' . $DB->string($PeriodID));
    echo '<tr>
<td class="text-muted">' . $Period . '</td>
<td class="text-right">' . $Language->number($Data['XMR'] ?? 0, 8) . '</td>
<td class="text-right">' . $Language->number($Data['BTC'] ?? 0, 8) . '</td>
<td class="text-right">' . $Language->number($Data['LTC'] ?? 0, 8) . '</td>
<td class="text-right">' . $Language->number($Data['USD'] ?? 0, 2) . '</td>
<td class="text-right">' . Currencies::exchange($Data['USD'] ?? 0, 'USD', 'USER') . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Marketplace Account Balances:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center">Currency</th>
                    <th scope="col" class="text-center">Users</th>
                    <th scope="col" class="text-center">Vendors</th>
                    <th scope="col" class="text-center">Escrow</th>
                    <th scope="col" class="text-center">Admins</th>
                    <th scope="col" class="text-center">Staff</th>
                    <th scope="col" class="text-center">Accounts Total</th>
                    <th scope="col" class="text-center">Master Wallets</th>
                </tr>
<?php
$Data = $DB->fetch_assoc($DB->query('SELECT
(SELECT SUM(XMR) FROM user WHERE Role = ' . $DB->string('user') . ') AS Users,
(SELECT SUM(XMR) FROM user WHERE Role = ' . $DB->string('vendor') . ') AS Vendors,
(SELECT XMR FROM system_accounts WHERE SAccountID = ' . $DB->string('escrow') . ') AS Escrow,
(SELECT XMR FROM system_accounts WHERE SAccountID = ' . $DB->string('fee') . ') AS Fees,
(SELECT SUM(XMR) FROM user WHERE Role = ' . $DB->string('staff') . ') AS Staff,
(SELECT SUM(XMR) FROM user) AS Accounts'));
$XMR = XMR::raw('get_balance', ['account_index' => 0]);
?>
                <tr>
                    <td class="text-center">XMR</td>
                    <td class="text-right"><?php echo $Language->number($Data['Users'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Vendors'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Escrow'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Fees'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Staff'] ?? 0, 8); ?></td>
                    <td class="text-right text-danger"><?php echo $Language->number($Data['Accounts'] ?? 0, 8); ?></td>
                    <td class="text-right text-success"><?php echo $Language->number(($XMR['balance'] ?? 0) / 1000000000000, 8); ?></td>
                </tr>
<?php
$Data = $DB->fetch_assoc($DB->query('SELECT
(SELECT SUM(BTC) FROM user WHERE Role = ' . $DB->string('user') . ') AS Users,
(SELECT SUM(BTC) FROM user WHERE Role = ' . $DB->string('vendor') . ') AS Vendors,
(SELECT BTC FROM system_accounts WHERE SAccountID = ' . $DB->string('escrow') . ') AS Escrow,
(SELECT BTC FROM system_accounts WHERE SAccountID = ' . $DB->string('fee') . ') AS Fees,
(SELECT SUM(BTC) FROM user WHERE Role = ' . $DB->string('staff') . ') AS Staff,
(SELECT SUM(BTC) FROM user) AS Accounts'));
$BTC= BTC::raw('getwalletinfo', []);
?>
                <tr>
                    <td class="text-center">BTC</td>
                    <td class="text-right"><?php echo $Language->number($Data['Users'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Vendors'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Escrow'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Fees'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Staff'] ?? 0, 8); ?></td>
                    <td class="text-right text-danger"><?php echo $Language->number($Data['Accounts'] ?? 0, 8); ?></td>
                    <td class="text-right text-success"><?php echo $Language->number($BTC['balance'] ?? 0, 8); ?></td>
                </tr>
<?php
$Data = $DB->fetch_assoc($DB->query('SELECT
(SELECT SUM(LTC) FROM user WHERE Role = ' . $DB->string('user') . ') AS Users,
(SELECT SUM(LTC) FROM user WHERE Role = ' . $DB->string('vendor') . ') AS Vendors,
(SELECT LTC FROM system_accounts WHERE SAccountID = ' . $DB->string('escrow') . ') AS Escrow,
(SELECT LTC FROM system_accounts WHERE SAccountID = ' . $DB->string('fee') . ') AS Fees,
(SELECT SUM(LTC) FROM user WHERE Role = ' . $DB->string('staff') . ') AS Staff,
(SELECT SUM(LTC) FROM user) AS Accounts'));
$LTC = LTC::raw('getwalletinfo', []);
?>
                <tr>
                    <td class="text-center">LTC</td>
                    <td class="text-right"><?php echo $Language->number($Data['Users'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Vendors'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Escrow'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Fees'] ?? 0, 8); ?></td>
                    <td class="text-right"><?php echo $Language->number($Data['Staff'] ?? 0, 8); ?></td>
                    <td class="text-right text-danger"><?php echo $Language->number($Data['Accounts'] ?? 0, 8); ?></td>
                    <td class="text-right text-success"><?php echo $Language->number($LTC['balance'] ?? 0, 8); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Marketplace Cash Flow:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left">Period</th>
                    <th scope="col" class="text-center">XMR</th>
                    <th scope="col" class="text-center">BTC</th>
                    <th scope="col" class="text-center">LTC</th>
                    <th scope="col" class="text-center">US-$</th>
                    <th scope="col" class="text-center" id="pastel-green"><?php echo $User->get('Currency'); ?></th>
                </tr>
<?php
foreach ($Periods as $PeriodID => $Period) {
    $Data = $DB->getOne('stats_marketplace_cash_flow', 'Period = ' . $DB->string($PeriodID));
    echo '<tr>
<td class="text-muted">' . $Period . '</td>
<td class="text-right">' . $Language->number($Data['XMR'] ?? 0, 8) . '</td>
<td class="text-right">' . $Language->number($Data['BTC'] ?? 0, 8) . '</td>
<td class="text-right">' . $Language->number($Data['LTC'] ?? 0, 8) . '</td>
<td class="text-right">' . $Language->number($Data['USD'] ?? 0, 2) . '</td>
<td class="text-right">' . Currencies::exchange($Data['USD'] ?? 0, 'USD', 'USER') . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>New User/Vendor:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left">Period</th>
                    <th scope="col" class="text-center">New User</th>
                    <th scope="col" class="text-center">New Vendor</th>
                    <th scope="col" class="text-center">Vendor Paid Bond</th>
                    <th scope="col" class="text-center">Vendor Invited</th>
                </tr>
<?php
foreach ($Periods as $PeriodID => $Period) {
    $Data = $DB->getOne('stats_new_user_vendor', 'Period = ' . $DB->string($PeriodID));
    echo '<tr>
<td class="text-muted">' . $Period . '</td>
<td class="text-right">' . ($Data['NewUser'] ?? 0) . '</td>
<td class="text-right">' . ($Data['NewVendor'] ?? 0) . '</td>
<td class="text-right">' . ($Data['VendorPaidBond'] ?? 0) . '</td>
<td class="text-right">' . ($Data['VendorInvited'] ?? 0) . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php } ?>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Vendor Most Items Online:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left">User</th>
                    <th scope="col" class="text-left">Number of Items</th>
                    <th scope="col" class="text-left">Vendor Status</th>
                    <th scope="col" class="text-left">Vendor Score</th>
                </tr>
<?php
    $Data = $DB->get('stats_vendor_most_items_online');
    foreach ($Data as $Dat) {
        echo '<tr>
<td class="text-left">' . ucfirst($User->getByUserID('Username', $Dat['User'])) . '</td>
<td class="text-left">' . $Dat['NumItems'] . '</td>
<td class="text-left">' . Ranking::getVendorRank($User->getByUserID('VendorRank', $Dat['User'])) . '</td>
<td class="text-left">' . $Language->number($User->getByUserID('Scoring', $Dat['User']), 1) . '%</td>
</tr>' . nl;
    }
?>
            </table>
        </div>
    </div>
</div>

<?php
    foreach ($CashFlowPeriods as $PeriodID => $Period) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Vendor Highest Cash Flow (US-$, <?php echo $Period; ?>):</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left">Vendor</th>
                    <th scope="col" class="text-left">Current Fee (%)</th>
                    <th scope="col" class="text-left">USD</th>
                    <th scope="col" class="text-center" id="pastel-green"><?php echo $User->get('Currency'); ?></th>
                </tr>
<?php
        $Data = $DB->get('stats_vendor_highest_cash_flow_' . $PeriodID);
        foreach ($Data as $Dat) {
            echo '<tr>
<td class="text-left">' . ucfirst($User->getByUserID('Username', $Dat['User'])) . '</td>
<td class="text-left">' . $Language->number($User->getByUserID('VendorFee', $Dat['User']), 1) . '%</td>
<td class="text-left">' . $Language->number($Dat['USD'], 2) . '</td>
<td class="text-right">' . Currencies::exchange($Dat['USD'] ?? 0, 'USD', 'USER') . '</td>
</tr>' . nl;
        }
?>
            </table>
        </div>
    </div>
</div>
<?php
    }
    foreach ($CashFlowPeriods as $PeriodID => $Period) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>User Highest Cash Flow (US-$, <?php echo $Period; ?>):</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-left">User</th>
                    <th scope="col" class="text-left">USD</th>
                    <th scope="col" class="text-center" id="pastel-green"><?php echo $User->get('Currency'); ?></th>
                </tr>
<?php
        $Data = $DB->get('stats_vendor_highest_cash_flow_' . $PeriodID);
        foreach ($Data as $Dat) {
            echo '<tr>
<td class="text-left">' . ucfirst($User->getByUserID('Username', $Dat['User'])) . '</td>
<td class="text-left">' . $Language->number($Dat['USD'], 2) . '</td>
<td class="text-right">' . Currencies::exchange($Dat['USD'] ?? 0, 'USD', 'USER') . '</td>
</tr>' . nl;
        }
?>
            </table>
        </div>
    </div>
</div>
<?php
    }
} else { ?>
<div class="container mt-4">
    <div class="row justify-content-md-center">
        <div class="col-md-6 text-center text-success" id="frame_standard">
            <?php echo $User->get('APP'); ?>
        </div>
        <div class="col-md-10 text-center mt-4">
            If the text above doesn&rsquo;t match with your APP, you are probably on a scam- or phishing site!
        </div>
        <div class="col-md-10 text-center mt-2 pb-4" id="hyphen">
            In this case, please log in by using a verified marketplace URL and change your password as well as your withdrawal code (WDC) immediately!
        </div>
        <div class="col-md-8 text-center mt-5 font-italic text-muted lead">
            "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum."
        </div>
        <div class="col-md-8 text-right mt-2 text-muted">
            <small>Albert Einstein</small>
        </div>
        <div class="col-md-10 mt-5">
            Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
        </div>
        <div class="col-md-10 mt-2">
            Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
        </div>
        <div class="col-md-8 text-center mt-5 font-italic text-muted lead">
            "At vero eos et accusam et justo duo dolores et ea rebum."
        </div>
        <div class="col-md-8 text-right mt-2 text-muted">
            <small>George Washington</small>
        </div>
    </div>
</div>
<?php }