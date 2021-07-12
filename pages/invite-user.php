<?php
if (!$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');

if (Forms::isPost() && isset($_POST['cmd']) && $_POST['cmd'] == 'generatecode') {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_?!';
    if (!isset($chars)) return false;
    $Code = '';
    for($i = 0; $i < 32; $i++) {
        $Code .= $chars[rand(0, strlen($chars)-1)];
    }
    $newData = [
        'Code'      =>  $Code,
        'Created'   =>  time()
    ];
    $DB->insert('invite_codes', $newData);
} else if (isset($_GET['del'])) {
    $DB->delete('invite_codes', 'CodeID = ' . $DB->int($_GET['del']));
    $Pages->redirect('invite-user');
}
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <span><h3><lang>Overview Of All Invitation Codes</lang>...</h3></span>
        </div>
    </div>
</div>


<div class="container mt-3">
    <div class="row py-1">
        <div class="col-auto col-md-8 font-weight-light text-muted mb-2">
            <span><h5><lang>Generate an Invitation Code for a Vendor Account</lang>:</h5></span>
        </div>
    </div>
</div>

<form method="post" action="/invite-user">
    <input type="hidden" name="cmd" value="generatecode">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-5 col-md-2">
                <button class="btn-sm btn-primary" type="submit"><lang>Generate Code</lang></button>
            </div>
            <div class="col-7 col-md-4">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" value="<?php if (isset($Code)) { echo htmlentities($Code); } ?>">
                </div>
            </div>
        </div>
    </div>
</form>

<form method="post" action="/invite-user">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-8 col-md-4">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" placeholder="<lang>Searchterm</lang>" name="Searchterm"<?php echo Forms::value('Searchterm'); ?>>
                </div>
            </div>
            <div class="col-auto col-md-8">
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
                    <th scope="col" class="text-center text-nowrap"><lang>Date</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Invitation Code</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Used By</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Date</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Delete Code</lang></th>
                </tr>
<?php
$SQL = 'SELECT CodeID, Code, Created, (SELECT Username FROM user WHERE UserID = UsedBy) AS Username, UsedBy, UsedAt FROM invite_codes';
if (Forms::isPost() && isset($_POST['Searchterm']) && !empty($_POST['Searchterm'])) {
    $SQL .= ' WHERE Code LIKE ' . $DB->string('%' . trim($_POST['Searchterm']) . '%');
}
$SQL .= ' ORDER BY Created';
$Codes = $DB->query($SQL);
foreach ($Codes as $Code) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Code['Created']) . '</td>
<td class="text-left text-nowrap">' . htmlentities($Code['Code']) . '</td>' . nl;
    if (is_null($Code['UsedAt'])) {
        echo '<td class="text-center">&nbsp;</td>
<td class="text-center">&nbsp;</td>
<td class="text-center"><a class="btn-sm btn-danger" href="/invite-user?del=' . $Code['CodeID'] . '" role="button"><lang>Delete</lang></a></td>' . nl;
    } else {
        echo '<td class="text-center text-nowrap"><a href="/account/' . $Code['UsedBy'] . '">' . htmlentities(ucfirst($Code['Username'])) . '</a></td>
<td class="text-center text-nowrap">' . $Language->date($Code['Created']) . '</td>
<td class="text-center">&nbsp;</td>' . nl;
    }
    echo '</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
