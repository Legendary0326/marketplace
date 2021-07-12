<?php
if (!isset(($Pages->getPath())[0])) {
    $UserID = 0;
} else {
    $UserID = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
    if (!$User->exists($UserID)) {
        echo Alerts::danger('<lang>Receiver Does Not Exist!</lang>', 'mt-3');
        return;
    }
    if ($UserID == $User->getID()) {
        echo Alerts::danger('<lang>Sender and Receiver Are the Same!</lang>', 'mt-3');
        return;
    }
}
$CaptchaError = false;
$SubjectError = false;
$MessageError = false;
if (Forms::isPost()) {
    if (!Captcha::verify()) {
        $CaptchaError = true;
    } else if (!isset($_POST['Subject']) || empty($_POST['Subject'])) {
        $SubjectError = true;
    } else if (!isset($_POST['Message']) || empty($_POST['Message'])) {
        $MessageError = true;
    } else {
        $NewMessage = [];
        $NewMessage['Sender'] = $User->getID();
        $NewMessage['Recipient'] = $UserID;
        $NewMessage['Created'] = time();
        $NewMessage['Subject'] = trim($_POST['Subject']);
        $NewMessage['Message'] = trim($_POST['Message']);
        $DB->insert('messages', $NewMessage);
        $Pages->redirect('message-out/sent');
    }
}
?>
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="message-in" class="btn btn-link"><lang>Inbox</lang></a>
        </div>
        <div class="col-md-6 px-2">
            <a class="text-white" href="message-out" class="btn btn-link"><lang>Outbox</lang></a>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><lang>Create a New Message</lang>...</h3>
        </div>
    </div>
</div>
<?php
if ($CaptchaError) echo Alerts::danger('<lang>Sorry, the CAPTCHA Is Incorrect. Please Try Again.</lang>');
if ($SubjectError) echo Alerts::danger('<lang>Subject Is Empty!</lang>');
if ($MessageError) echo Alerts::danger('<lang>Message Is Empty!</lang>');
?>
<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <lang>Message To</lang>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <input type="text" class="form-control" value="<?php
if ($UserID == 0) {
    echo 'Support';
} else {
    echo htmlentities(ucfirst($User->getByUserID('Username', $UserID)));
}
?>" disabled>
            </div>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12">
            <span class="text-danger"><lang>To send a message to a user or vendor, please use the [Send message] button in the respective user/vendor profile!</lang></span>
        </div>
    </div>
</div>

<form action="/message-new<?php echo ($UserID == 0 ? '' : '/' . $UserID); ?>" method="post">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Subject</lang>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Subject"<?php echo Forms::value('Subject'); ?>>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Message</lang>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control overflow-auto" rows="5" name="Message"><?php echo Forms::textarea('Message'); ?></textarea>
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
                <button type="submit" class="btn btn-secondary btn-block float-left"><lang>Send Message</lang></button>
            </div>
        </div>
    </div>
</form>