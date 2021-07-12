<?php
if (!$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
$Paths = $Pages->getPath();
if ($User->hasRole('admin')) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-6 overflow-auto">
        <table class="table-sm table-bordered-standard w-100">
                <tr style="border: 1px solid transparent;">
                    <th class="text-left" style="background: transparent; border-left: 2px solid #378BE5;"><a class="" href="/message-new-admin"><img src="/img/mail_new_blue.png" width="15"><span class="pl-2" style="color: #378BE5;"><lang>Create a New Message</lang></span></a></th>
                </tr>
        </table>
        </div>
    </div>
</div>
<?php
}
if (isset($Paths[0]) && $Paths[0] == 'sent') {
    echo Alerts::success('<lang>Message Has Been Sent!</lang>', 'mt-3');
}
?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <h3><lang>New Messages</lang>...</h3>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100 mb-4">
                <tr>
                    <th scope="col" class="text-center text-nowrap"><lang>Date</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Request Number</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>From</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Subject</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Edit</lang></th>
                </tr>
<?php
$SQL = 'SELECT MessageID, Created, Sender, (SELECT Username FROM user WHERE UserID = Sender) AS SenderName, Subject FROM messages WHERE Recipient = 0 AND Moderator IS NULL AND ModeratorDeleted = 0 ORDER BY Created';
$Messages = $DB->query($SQL);
foreach ($Messages as $Message) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Message['Created']) . '</td>
<td class="text-center">#' . $Message['MessageID'] . '</td>
<td class="text-left"><a href="/profile/' . $Message['Sender'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Message['SenderName'])) . '</span></a></td>
<td class="text-left text-nowrap">' . htmlentities(substr($Message['Subject'], 0, 25)) . '</td>
<td class="text-center"><a class="btn-sm btn-primary" href="/message-support/' . $Message['MessageID'] . '" role="button"><lang>Open</lang></a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row py-1">
        <div class="col-12 col-md-12" id="separator"></div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <h3><lang>Archived Requests</lang>...</h3>
        </div>
    </div>
</div>

<form action="/messages-support" method="post">
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
                    <th scope="col" class="text-center text-nowrap"><lang>Date</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Request Number</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>From</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Moderator</lang></th>
                    <th scope="col" class="text-center text-nowrap"><lang>Read</lang></th>
                </tr>
<?php
$SQL = 'SELECT MessageID, Created, Sender, (SELECT Username FROM user WHERE UserID = Sender) AS SenderName, Moderator, (SELECT Username FROM user WHERE UserID = Moderator) AS ModeratorName FROM messages WHERE Recipient = 0 AND Moderator IS NOT NULL AND Created >= UNIX_TIMESTAMP() - 7776000';
if (Forms::isPost() && isset($_POST['Searchterm']) && !empty($_POST['Searchterm'])) {
    $Searchterm = trim($_POST['Searchterm']);
    $Where = [];
    if (preg_match('/^\d\d\\d\d-\d\d\-\d\d$/i', $Searchterm)) {
        $Where[] = 'FROM_UNIXTIME(Created, \'%Y-%m-%d\') = ' . $DB->string($Searchterm);
    }
    if (preg_match('/^\d+$/i', $Searchterm)) {
        $Where[] = 'MessageID = ' . $DB->int($Searchterm);
    }
    if (preg_match('/^[a-z0-9]{4,32}$/i', $Searchterm)) {
        $Where[] = '(SELECT Username FROM user WHERE Sender = UserID) LIKE ' . $DB->string('%' . $Searchterm . '%');
        $Where[] = '(SELECT Username FROM user WHERE Moderator = UserID) LIKE ' . $DB->string('%' . $Searchterm . '%');
    }
    $SQL .= ' AND (' . implode(' OR ', $Where) . ')';
}
$SQL .= ' ORDER BY Created';
$Messages = $DB->query($SQL);
foreach ($Messages as $Message) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Language->date($Message['Created']) . '</td>
<td class="text-center">#' . $Message['MessageID'] . '</td>
<td class="text-left"><a href="/profile/' . $Message['Sender'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Message['SenderName'])) . '</span></a></td>
<td class="text-left"><a href="/profile/' . $Message['Moderator'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($Message['ModeratorName'])) . '</span></a></td>
<td class="text-center"><a class="btn-sm btn-light" href="/message-support/' . $Message['MessageID'] . '" role="button"><lang>Read</lang></a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>