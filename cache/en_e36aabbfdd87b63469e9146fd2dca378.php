<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/login.php***/
$Pages->setLayout('small');

if (Forms::isPost() && isset($_POST['Username']) && isset($_POST['Password'])) {
    if (!isset($_POST['RememberMe'])) $_POST['RememberMe'] = false;
    if (!Captcha::verify()) $Pages->redirect('login/captchaerror');
    $LoginSuccess = $User->login($_POST['Username'], $_POST['Password'], (bool) $_POST['RememberMe']);
    if (!$LoginSuccess && $User->getReason() == 5) $Pages->redirect('login/locked');
    $Pages->redirect('login/error');
}
?>
<h1 class="text-info">Login</h1>
<?php
if ($Pages->inPath('captchaerror')) echo Alerts::danger('Sorry, the CAPTCHA Is Incorrect. Please Try Again.', 'mt-3');
if ($Pages->inPath('error')) echo Alerts::danger('Sorry, Your Entries Are Incorrect. Please Try Again.', 'mt-3');
if ($Pages->inPath('locked')) echo Alerts::danger('Sorry, has been permanently banned.', 'mt-3');
if ($Pages->inPath('error2fa')) echo Alerts::danger('Sorry, the Entered Code Is Incorrect. Please Try Again.', 'mt-3');
if ($Pages->inPath('enabled2FA')) echo Alerts::success('2FA Is Enable.', 'mt-3');
?>
<form action="/login" method="post">
    <div class="form-group">
        <label for="Username">Username</label>
        <input type="text" class="form-control" id="Username" name="Username">
    </div>
    <div class="form-group">
        <label for="Password">Password</label>
        <input type="password" class="form-control" id="Password" name="Password">
    </div>
    <div class="form-group">
        <img src="<?php echo Captcha::get(); ?>" alt="" title="Captcha" />
    </div>
    <div class="form-group">
        <label for="Captcha">CAPTCHA</label>
        <input type="text" class="form-control" id="Captcha" name="Captcha" autocomplete="off"<?php devCodeCAPTCHA(); ?>>
    </div>
    <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="RememberMe" name="RememberMe" value="1">
        <label class="form-check-label" for="RememberMe">Remember Me</label>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Login</button>
</form>
<p class="pt-3 text-center">Forgot Your Password? <a href="/resetpassword">Click Here To Reset</a></p>
