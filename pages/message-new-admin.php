<?php
if (!$User->hasRole('admin')) $Pages->redirect('');
if ($Pages->inPath('captchaerror')) {
    echo Alerts::danger('<lang>Sorry, the CAPTCHA Is Incorrect. Please Try Again.</lang>');
}
$CaptchaError = false;
$SubjectError = false;
$MessageError = false;
$RecipientError = false;
if (Forms::isPost()) {
    if (!Captcha::verify()) {
        $CaptchaError = true;
    } else if (!isset($_POST['Subject']) || empty($_POST['Subject'])) {
        $SubjectError = true;
    } else if (!isset($_POST['Message']) || empty($_POST['Message'])) {
        $MessageError = true;
    } else {
        if (isset($_POST['Recipient']) && !empty($_POST['Recipient'])) {
            $User = $DB->getOne('user', 'Username LIKE ' . $DB->string($_POST['Recipient']));
            if (!isset($User['UserID'])) {
                $RecipientError = true;
            } else {
                $NewMessage = [];
                $NewMessage['Sender'] = 0;
                $NewMessage['Recipient'] = $User['UserID'];
                $NewMessage['Created'] = time();
                $NewMessage['Subject'] = trim($_POST['Subject']);
                $NewMessage['Message'] = trim($_POST['Message']);
                $DB->insert('messages', $NewMessage);
                $Pages->redirect('messages-support/sent');
            }
        } else if (isset($_POST['Recipients'])) {
            $Users = [];
            if ($_POST['Recipients'] === 'Users') {
                $Users = $DB->get('user', 'Role = ' . $DB->string('user'));
            } else if ($_POST['Recipients'] === 'Vendors') {
                $Users = $DB->get('user', 'Role = ' . $DB->string('vendor'));
            } else if ($_POST['Recipients'] === 'Staffs') {
                $Users = $DB->get('user', 'Role = ' . $DB->string('staff'));
            }
            if (is_array($Users) && count($Users) >= 1) {
                $NewMessage = [];
                $NewMessage['Sender'] = 0;
                $NewMessage['Created'] = time();
                $NewMessage['Subject'] = trim($_POST['Subject']);
                $NewMessage['Message'] = trim($_POST['Message']);
                foreach ($Users as $User) {
                    $NewMessage['Recipient'] = $User['UserID'];
                    $DB->insert('messages', $NewMessage);
                }
                $Pages->redirect('messages-support/sent');
            }
        }
    }
}
?>
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
if ($RecipientError) echo Alerts::danger('<lang>Cannot find recipient!</lang>');
?>
<form method="post" action="/message-new-admin">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Message To</lang>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Recipient">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <div class="row py-1">
            <div class="col-md-12">
                <span class="ml-4"><input class="form-check-input" type="radio" name="Recipients" id="AllUsers" value="Users">
                <label class="form-check-label" for="AllUsers">
                    <lang>To All Users</lang>
                </label></span>
                <span class="ml-5"><input class="form-check-input" type="radio" name="Recipients" id="AllVendors" value="Vendors">
                <label class="form-check-label" for="AllVendors">
                    <lang>To All Vendors</lang>
                </label></span>
                <span class="ml-5"><input class="form-check-input" type="radio" name="Recipients" id="AllStaffs" value="Staffs">
                <label class="form-check-label" for="AllStaffs">
                    <lang>To All Staff Members</lang>
                </label></span>
            </div>
        </div>
    </div>

    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Message From</lang>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" value="Support" disabled>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Subject</lang>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="Subject">
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
                    <textarea class="form-control overflow-auto" rows="5" placeholder="Enter your message" name="Message"></textarea>
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