<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/message.php***/
$MessageID = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
if (empty($MessageID)) {
    echo Alerts::danger('No message Selected!', 'mt-3');
    return;
}
if (!Messages::exists($MessageID)) {
    echo Alerts::danger('No message Selected!', 'mt-3');
    return;
}
$Msg = Messages::getMessage($MessageID);

$IAmRecipient = $Msg['Recipient'] == $User->getID() ? true : false;
$IAmSender = $Msg['Sender'] == $User->getID() ? true : false;
$WithSupport = ($Msg['Recipient'] == 0 || $Msg['Sender'] == 0) ? true: false;
$CaptchaError = false;
$MessageError = false;
$IsReply = $Pages->inPath('reply');

if ($Pages->inPath('delete')) {
    Messages::delete($MessageID);
    if ($IAmRecipient) {
        $Pages->redirect('message-in');
    } else {
        $Pages->redirect('message-out');
    }
} else if ($IsReply && !$WithSupport) {
    if (Forms::isPost()) {
        if (!Captcha::verify()) {
            $CaptchaError = true;
        } else if (!isset($_POST['Message']) || empty($_POST['Message'])) {
            $MessageError = true;
        } else {
            $NewMessage = [
                'Sender'        =>  $User->getID(),
                'Recipient'     =>  ($Msg['Sender'] == $User->getID() ? $Msg['Recipient'] : $Msg['Sender']),
                'Moderator'     =>  $Msg['Moderator'],
                'Created'       =>  time(),
                'InReplyTo'     =>  (is_null($Msg['InReplyTo']) ? $Msg['MessageID'] : $Msg['InReplyTo']),
                'Subject'       =>  'Re: ' . $Msg['Subject'],
                'Message'       =>  trim($_POST['Message'])
            ];
            $DB->insert('messages', $NewMessage);
            $Pages->redirect('message-out/sent');
        }
    }
}
?><!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="/message-in" class="btn btn-link">Inbox</a>
        </div>
        <div class="col-md-6 px-2">
            <a class="text-white" href="/message-out" class="btn btn-link">Outbox</a>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-6 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr style="border: 1px solid transparent;">
<?php if (!$WithSupport) echo '<th class="text-left" style="background: transparent; border-left: 2px solid #378BE5;"><a class="" href="/message/' . $MessageID . '/reply"><img src="/img/reply_blue.png" width="15"><span class="pl-2" style="color: #378BE5;">Reply</span></a></th>' . nl; ?>
                    <th class="text-left" style="background: transparent; border-left: 2px solid #378BE5;"><a class="" href="/message/<?php echo $MessageID . '/delete'; ?>"><img src="/img/delete_blue.png" width="15"><span class="pl-2" style="color: #378BE5;">Delete message</span></a></th>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><?php echo ($IsReply ? 'Create a New Message' : 'Message'); ?>...</h3>
        </div>
    </div>
</div>
<?php
if ($CaptchaError) echo Alerts::danger('Sorry, the captcha is incorrect. Please try again.');
if ($MessageError) echo Alerts::danger('Message empty');
if ($IsReply) {
?>
<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            Message To
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <input type="text" class="form-control" value="<?php
    $To = [];
    if ($Msg['Sender'] != $User->getID() && $Msg['Sender'] != 0) $To[] = htmlentities(ucfirst($Msg['SenderName']));
    if ($Msg['Recipient'] != $User->getID() && $Msg['Recipient'] != 0) $To[] = htmlentities(ucfirst($Msg['RecipientName']));
    echo implode(', ', $To);
?>" disabled>
            </div>
        </div>
    </div>
</div>
<form action="/message/<?php echo $MessageID; ?>/reply" method="post">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Message
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control overflow-auto" rows="5" name="Message" placeholder="Enter your message"></textarea>
                </div>
            </div>
        </div>
    </div>
<?php if (Captcha::showCaptcha()) { ?>
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 mb-3 form-group">
            <img src="<?php echo Captcha::get(); ?>" alt="" title="Captcha" />
        </div>
        <div class="col-md-3 mb-3 form-group">
            <label for="Captcha">CAPTCHA</label>
            <input type="text" class="form-control" id="Captcha" name="Captcha" autocomplete="off"<?php devCodeCAPTCHA(); ?>>
        </div>
    </div>
<?php } ?>
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-12 col-md-6 mb-3">
                <button type="submit" class="btn btn-secondary btn-block float-left">Send Message</button>
            </div>
        </div>
    </div>
</form>
<?php
        printHistoryMessage($Msg);
    } else {
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-5 col-md-4">
            <span class="font-weight-light text-muted"><?php echo ($IAmRecipient ? 'Received from:' : 'Send to:'); ?></span>
        </div>
        <div class="col-auto col-md-8">
<?php
        if ($Msg['Sender'] == 0 || $Msg['Recipient'] == 0) {
            echo 'Support' . nl;
        } else {
            echo '<a href="/profile/' . ($IAmRecipient ? $Msg['Sender'] : $Msg['Recipient']) . '" class="text-primary">' . htmlentities(ucfirst($IAmRecipient ? $Msg['SenderName'] : $Msg['RecipientName'])) . '</a>' . nl;
        }
?>
        </div>
        <div class="col-5 col-md-4">
            <span class="font-weight-light text-muted">Subject:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo htmlentities($Msg['Subject']); ?></span>
        </div>
        <div class="col-5 col-md-4">
            <span class="font-weight-light text-muted">Date:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo $Language->date($Msg['Created']); ?></span>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            Message
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <textarea class="form-control overflow-auto" rows="5" readonly><?php echo htmlentities($Msg['Message']); ?></textarea>
            </div>
        </div>
    </div>
</div>
<?php
}
foreach ($Msg['History'] as $HMsg) {
    printHistoryMessage($HMsg);
}

function printHistoryMessage($Msg)
{
    global $Language;
    global $User;
    echo '<div class="container mt-2">
    <div class="row py-1">
        <div class="col">
            <span class="text-nowrap">' . ($Msg['Sender'] == $User->getID() ? 'You' : ($Msg['Sender'] == 0 ? 'Support' : '<a href="/profile/' . $Msg['Sender'] . '">' . htmlentities(ucfirst($Msg['SenderName'])) . '</a>')) . ' wrote at ' . $Language->date($Msg['Created']) . ':</span>
        </div>
    </div>
    <div class="row py-1">
        <div class="col">
            <div class="form-group">
                <textarea class="form-control overflow-auto" rows="5" readonly>' . htmlentities($Msg['Message']) . '</textarea>
            </div>
        </div>
    </div>
</div>' . nl;
}