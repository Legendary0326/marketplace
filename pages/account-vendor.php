<?php
if (!$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <span><h3><lang>Overview Of All Vendor Accounts</lang>...</h3></span>
        </div>
    </div>
</div>

<form action="/account-vendor" method="post">
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
                    <th scope="col" class="text-center text-nowrap"><lang>Username</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>First Login</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Last Login</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Status</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Scoring</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Cash Flow (US-$)</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Fee</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Edit</lang></th>
                </tr>
<?php
$SQL = 'SELECT UserID, Username, Registered, (SELECT MAX(ActiveSince) FROM user_sessions WHERE UserID = User) AS LastLogin, (SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM vendor_ranks WHERE RankID = VendorRank) AS VendorRank, Scoring, CashFlow, VendorFee FROM user WHERE ';
$SQL .= 'Role = ' . $DB->string('vendor');
if (Forms::isPost() && isset($_POST['Searchterm']) && !empty($_POST['Searchterm'])) {
    $Searchterm = trim($_POST['Searchterm']);
    $Where = [];
    if (preg_match('/^[a-z0-9]{4,32}$/i', $Searchterm)) {
        $Where[] = 'Username LIKE ' . $DB->string('%' . $Searchterm . '%');
    }
    if (preg_match('/^\d\d\\d\d-\d\d\-\d\d$/i', $Searchterm)) {
        $Where[] = 'FROM_UNIXTIME(Registered, \'%Y-%m-%d\') = ' . $DB->string($Searchterm);
        $Where[] = 'FROM_UNIXTIME((SELECT MAX(ActiveSince) FROM user_sessions WHERE UserID = User), \'%Y-%m-%d\') = ' . $DB->string($Searchterm);
    }
    if (preg_match('/^[0-9\.,]+$/i', $Searchterm)) {
        $Where[] = 'Scoring = ' . $DB->float(guessMoney($Searchterm));
        $Where[] = 'CashFlow = ' . $DB->float(guessMoney($Searchterm));
        $Where[] = 'VendorFee = ' . $DB->float(guessMoney($Searchterm));
    }
    $Where[] = '(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM vendor_ranks WHERE RankID = VendorRank) LIKE ' . $DB->string($Searchterm);
    $SQL .= ' AND (' . implode(' OR ', $Where) . ')';
}
$SQL .= ' ORDER BY Registered';
$Users = $DB->query($SQL);
foreach ($Users as $UserList) {
    echo '<tr>
<td class="text-left text-nowrap">' . htmlentities(ucfirst($UserList['Username'])) . '</td>
<td class="text-center text-nowrap">' . $Language->date($UserList['Registered']) . '</td>
<td class="text-center text-nowrap">' . $Language->date($UserList['LastLogin']) . '</td>
<td class="text-left text-nowrap">' . $UserList['VendorRank'] . '</td>
<td class="text-center text-nowrap">' . $Language->number($UserList['Scoring'], 1) . '%</td>
<td class="text-right">' . $Language->number($UserList['CashFlow'], 2) . '</td>
<td class="text-right text-nowrap">' . $Language->number($UserList['VendorFee'], 1) . '%</td>
<td class="text-center"><a class="btn-sm btn-primary" href="/account-support/' . $UserList['UserID'] . '" role="button"><lang>Edit</lang></a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
