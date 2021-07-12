<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/2fa.php***/
$Pages->setLayout('small');
if (isset($_POST['answer'])) $User->login_2fa($_POST['answer']);
$code = gpg::newCode();
if ($code === false) {
    Alerts::danger('Sorry, There Was an Error. Please Try Again!', 'mt-3');
}
?>

<h1 class="text-info">2FA-Verification</h1>
<form action="" method="post">
    <div class="form-group">
        <label class="text-muted mt-2">Encrypted Verification Code:</label>
    </div>
    <div class="form-group">
        <div class="text-primary p-2" style="white-space: pre;"><?php echo $code; ?></div>
    </div>
    <div class="form-group mb-4">
        <label class="text-muted font-weight-bold mt-3">Please Enter Your Decrypted Verification Code</label>
        <input type="text" class="form-control" id="" name="answer" autocomplete="off"<?php devCodeGPG(); ?>>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Login</button>
</form>

<div class="form-group mt-5">
    <div class="col-md-12">
        <img class="mx-auto d-block" src="<?php
echo 'data:image/svg+xml;base64,' . base64_encode(QRcode::svg($code, md5($code), false, QR_ECLEVEL_L, false, false, 0));
?>" alt="" title="" style="max-width: 15%; min-width: 200px; max-height: auto; min-height: auto;"/>
    </div>
</div>