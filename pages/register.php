<?php
$currencies = Currencies::getCurrencies();
$Pages->setLayout('small');

if (Forms::isPost()) {
    $errors = [];
    if (!Forms::validateUsername('Username')) {
        $errors['Username'] = true;
    } else {
        if ($DB->exists('user', 'Username = ' . $DB->string($_POST['Username']))) {
            $errors['Username'] = true;
        }
    }
    if (!Forms::validatePassword('Password', 'PasswordRepeat')) {
        $errors['Password'] = true;
    }
    if (!Forms::validateList('Currency', array_keys($currencies))) {
        $errors['Currency'] = true;
    }
    if (!Forms::validateString('WC', ['min' => 4, 'max' => 100])) {
        $errors['WC'] = true;
    }
    if (!Forms::validateString('APP', ['min' => 4, 'max' => 100])) {
        $errors['APP'] = true;
    }
    if (!Captcha::verify()) {
        $errors['Captcha'] = true;
    }
    if (count($errors) == 0) {
        $MPP = Password::generate();
        $Data = [
            'Username'      =>  $_POST['Username'],
            'Password'      =>  Password::hash($_POST['Password']),
            'Registered'    =>  time(),
            'Currency'      =>  $_POST['Currency'],
            'WC'            =>  $_POST['WC'],
            'APP'           =>  $_POST['APP'],
            'MPP'           =>  Password::hash($MPP),
        ];
        $DB->insert('user', $Data);
        //Send welcome mail
        $UserID = $DB->lastInsertId();
        $LangInfo = $DB->getOne('languages', 'LangCode = ' . $DB->string($Language->getLanguage()));
        if (!is_null($LangInfo['WelcomeMailSubject']) && !is_null($LangInfo['WelcomeMailMessage'])) {
            $NewMessage = [];
            $NewMessage['Sender'] = 0;
            $NewMessage['Recipient'] = $UserID;
            $NewMessage['Created'] = time();
            $NewMessage['Subject'] = trim($LangInfo['WelcomeMailSubject']);
            $NewMessage['Message'] = trim($LangInfo['WelcomeMailMessage']);
            $DB->insert('messages', $NewMessage);
        }
        $HTMLHead->setNav('top_logoonly');
?>

<h3 class="mt-2 mb-4 text-info"><lang>Your MPP</lang></h3>
<p><lang>Please take note of the security code (MPP) below. It will be required if you lose your password and if you have to recover it. This page will be displayed only ONCE and will not be accessible again.</lang></p>
<div class="text-center" style="border: 1.5px solid #378BE5; margin-top: 8px; margin-bottom: 5px; padding: 3px;"><span id="mpp"><?php echo $MPP; ?></span></div><br>
<p><lang>After you&rsquo;ve noted the above MPP somewhere safe, please click the [Go to Login] botton.</lang></p>
<p><lang>Remember that the login page will never ask for your MPP or your WDC!</lang></p>
<p class="text-danger"><lang>Be careful!</lang></p>
<p><lang>If you lose your password and you cannot remember your MPP, your account cannot be reset and this will result in loss of all your coins!</lang></p>
<p><lang>YES, I&rsquo;ve read the safety instructions and I saved my personal MPP!</lang></p>
<div class="mt-3"><a href="/login" class="btn btn-primary btn-block"><lang>Go To Login</lang></a></div>
<?php
        return; //Print page, we don't need the formular
    }
}
?>

<h1 class="text-info"><lang>Create a New Account</lang></h1>
<form action="/register" method="post">
    <div class="form-group">
        <label for="Username"><lang>Username</lang></label>
        <input type="text" class="form-control<?php if (isset($errors['Username'])) echo ' is-invalid'; ?>" id="Username" name="Username" aria-describedby="UsernameInfo"<?php echo Forms::value('Username'); ?>>
        <small id="UsernameInfo" class="form-text text-muted"><?php printf('<lang>Please use atleast %1$d characters. Only letters and numbers are allowed.</lang>', 8); ?></small>
    </div>
    <div class="form-group">
        <label for="Password"><lang>Password</lang></label>
        <input type="password" class="form-control<?php if (isset($errors['Password'])) echo ' is-invalid'; ?>" id="Password" name="Password" aria-describedby="PasswordInfo">
        <small id="PasswordInfo" class="form-text text-muted"><?php printf('<lang>Please use atleast %1$d characters. Use atleast one letter, number and special character.</lang>', 8); ?></small>
    </div>
    <div class="form-group">
        <label for="PasswordRepeat"><lang>Repeat Password</lang></label>
        <input type="password" class="form-control<?php if (isset($errors['Password'])) echo ' is-invalid'; ?>" id="PasswordRepeat" name="PasswordRepeat" aria-describedby="PasswordRepeatInfo">
        <small id="PasswordRepeatInfo" class="form-text text-muted"><lang>Please Reapeat the Password.</lang></small>
    </div>
    <div class="form-group">
        <label for="Currency"><lang>Choose Your Preferred Currency</lang></label>
        <select class="form-control<?php if (isset($errors['Currency'])) echo ' is-invalid'; ?>" id="Currency" name="Currency" aria-describedby="CurrencyInfo">
<?php
foreach ($currencies as $currency) {
    echo '<option value="' . $currency['Code'] . '"' . Forms::selected('Currency', $currency['Code']) . '>' . $currency['Code'] . ' (' . $currency['Name'] . ')</option>' . nl;
}
?>
        </select>
        <small id="CurrencyInfo" class="form-text text-muted"><lang>You can also change your preferred currency in your acoount settings.</lang></small>
    </div>
    <div class="form-group">
        <label for="WC"><lang>Withdrawal Code (WDC)</lang></label>
        <small class="form-text text-muted mt-n2 mb-2"><lang>You need this PIN if you want to transfer your crypto balance</lang>...</small>
        <input type="text" class="form-control<?php if (isset($errors['WC'])) echo ' is-invalid'; ?>" id="WC" name="WC" aria-describedby="APPInfo"<?php echo Forms::value('WC'); ?>>
        <small id="APPInfo" class="form-text text-muted"><?php printf('<lang>Please use atleast %1$d characters.</lang>', 4); ?></small>
    </div>
    <div class="form-group">
        <label for="APP"><lang>Anti-Phishing Phrase (APP)</lang></label>
        <small class="form-text text-muted mt-n2 mb-2"><lang>Please enter a welcome text (a few words or a sentence) of your choice</lang>...</small>
        <input type="text" class="form-control<?php if (isset($errors['APP'])) echo ' is-invalid'; ?>" id="APP" name="APP" aria-describedby="APPInfo"<?php echo Forms::value('APP'); ?>>
        <small id="APPInfo" class="form-text text-muted"><lang>If your APP is either missing or incorrect, you may be the victim of a phishing attack.</lang></small>
    </div>
    <div class="form-group">
        <img src="<?php echo Captcha::get(); ?>" alt="" title="Captcha" />
    </div>
    <div class="form-group">
        <label for="Captcha">CAPTCHA</label>
        <input type="text" class="form-control<?php if (isset($errors['Captcha'])) echo ' is-invalid'; ?>" id="Captcha" name="Captcha" autocomplete="off"<?php devCodeCAPTCHA(); ?>>
    </div>
    <button type="submit" class="btn btn-primary btn-block"><lang>Register</lang></button>
</form>
<p class="pt-3 text-center"><lang>Forgot Your Password?</lang> <a href="/resetpassword"><lang>Click Here To Reset</lang></a></p>
