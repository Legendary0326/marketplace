<?php
$HTMLHead->addNav('cat_search');
if (Forms::isPost()) {
    if (isset($_POST['Messages']) && count($_POST['Messages']) >= 1) {
        foreach ($_POST['Messages'] as $Message) {
            Messages::delete($Message);
        }
    }
    $Pages->reload();
}
?>
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-12 p-auto col-md-12">
            <a class="text-white" href="/message-out" class="btn btn-link"><lang>Outbox</lang></a>
        </div>
    </div>
</div>

<form action="/message-in" method="post">
    <div class="container mt-4">
        <div class="row py-1">
            <div class="col-md-6 overflow-auto">
                <table class="table-sm table-bordered-standard w-100">
                    <tr style="border: 1px solid transparent;">
                        <th class="text-left text-nowrap" style="background: transparent; border-left: 2px solid #378BE5;"><a href="/message-new"><img src="/img/mail_new_blue.png" width="15"><span class="pl-2" style="color: #378BE5;"><lang>Create New Message</lang></span></a></th>
                        <th class="text-left text-nowrap" style="background: transparent; border-left: 2px solid #378BE5;"><button type="submit" style="background: transparent; border: transparent;"><img src="/img/delete_blue.png" width="15"><span class="pl-2" style="color: #378BE5;"><lang>Delete Message</lang></span></button></th>
                    </tr>
                </table>
            </div>
            <div class="col-md-12 overflow-auto">
                <table class="table-sm table-bordered-standard w-100">
                    <tr>
                        <th class="text-center" style="min-width: 40px;"><img src="/img/mail_un_white.png" width="15"></th>
                        <th class="text-center" style="min-width: 40px;"><a href="?markall"><img src="/img/checkbox_checked_white.png" width="15"></a></th>
                        <th class="text-center text-nowrap" style="min-width: 200px;"><lang>Message From</lang></th>
                        <th class="text-center" style="min-width: 400px;"><lang>Subject</lang></th>
                        <th class="text-center" style="min-width: 100px;"><lang>Date</lang></th>
                    </tr>
<?php
$Messages = Messages::getMessagesIn();
foreach ($Messages as $Message) {
    $Unread = is_null($Message['Readed']);
    echo '<tr>';
    echo '<td class="text-center"><img src="/img/' . ($Unread ? 'mail_un' : 'mail_re') . '.png" width="15"></td>';
    echo '<td class="text-center "><input class="form-check-input ml-n2 mt-n2" type="checkbox" value="' . $Message['MessageID'] . '" name="Messages[]"' . (isset($_GET['markall']) ? ' checked' : '') . '></td>';
    if ($Message['Sender'] == 0) {
        echo '<td class="text-center text-nowrap">Support</td>';
    } else {
        echo '<td class="text-center text-nowrap"><a href="/profile/' . $Message['Sender'] . '" class="text-primary">' . htmlentities(ucfirst($Message['SenderName'])) . '</a></td>';
    }
    echo '<td class="text-left text-nowrap"><a href="/message/' . $Message['MessageID'] . '" class="text-dark' . ($Unread ? ' font-weight-bold' : '') . '">' . htmlentities($Message['Subject']) . '</a></td>';
    echo '<td class="text-left text-nowrap">' . $Language->date($Message['Created']) . '</td>';
    echo '</tr>' . nl;
}
?>
                </table>
            </div>
        </div>
    </div>
</form>
