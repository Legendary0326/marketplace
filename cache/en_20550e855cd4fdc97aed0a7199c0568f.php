<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/changepassword.php***/
$HTMLHead->addNav('cat_search');

if (Forms::isPost()) {
    if (isset($_GET['password'])) {
        if (isset($_POST['curPassword']) && Password::verify($_POST['curPassword'], $User->get('Password'))) {
            if (Forms::validatePassword('newPassword1', 'newPassword2')) {
                $User->set('Password', Password::hash($_POST['newPassword1']));
                $Pages->redirect('login');
            } else {
                $Pages->redirect('changepassword/errorPass2');
            }
        } else {
            $Pages->redirect('changepassword/errorPass1');
        }
    } else if (isset($_GET['wdc'])) {
        if (isset($_POST['curWDC']) && $_POST['curWDC'] == $User->get('WC')) {
            if (Forms::validateString('newWDC', ['min' => 4, 'max' => 100])) {
                $User->set('WC', $_POST['newWDC']);
                $Pages->redirect('changepassword/savedWC');
            } else {
                $Pages->redirect('changepassword/errorWC2');
            }
        } else {
            $Pages->redirect('changepassword/errorWC1');
        }
    }
}
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
<?php if ($User->hasRole('user')) { ?>
        <div class="col-md-6 px-2 text-nowrap" id="borderPageHeader">
            <a class="text-white" href="/account" class="btn btn-link">Account Settings</a>
        </div>
        <div class="col-md-6 px-2 text-nowrap" id="" style="display: ">
            <a class="text-white" href="/vendorform" class="btn btn-link">Become a Vendor</a>
        </div>
<?php } else { ?>
        <div class="col-md-12 mx-auto text-nowrap">
            <a class="text-white" href="/account" class="btn btn-link">Account Settings</a>
        </div>
<?php } ?>
    </div>
</div>

<?php
if ($Pages->inPath('errorPass1')) { echo Alerts::danger('Your Current Password Is Incorrect!', 'mt-3'); }
if ($Pages->inPath('errorPass2')) { echo Alerts::danger('Your New Password Does Not Meet the Minimum Requirements!', 'mt-3'); }
if ($Pages->inPath('savedWC')) { echo Alerts::success('Your New WDC Saved Successfully.', 'mt-3'); }
if ($Pages->inPath('errorWC1')) { echo Alerts::danger('Your Current WDC Is Incorrect!', 'mt-3'); }
if ($Pages->inPath('errorWC2')) { echo Alerts::danger('Your New WDC Does Not Meet the Minimum Requirements!', 'mt-3'); }
?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>To Change Your Password...</h3>
        </div>
    </div>
</div>

<form action="?password" method="post">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your Current Password...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="" name="curPassword">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your New Password...<br>
                <small id="PasswordInfo" class="form-text text-muted"><?php printf('(Please use atleast %1$d characters. Use atleast one letter, number and special character.)', 8); ?></small>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="" name="newPassword1">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Re-Enter Your New Password...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="" name="newPassword2">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 text-warning mb-2">
                After changing your password, you will be automatically logged out. Please log in again with your new password!
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 pb-4" id="separator">
                <button type="submit" class="btn btn-primary btn-block">Update Password</button>
            </div>
        </div>
    </div>
</form>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>To Change Your WDC...</h3>
        </div>
    </div>
</div>

<form action="?wdc" method="post">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your Current WDC...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="" name="curWDC">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                Enter Your New WDC...<br>
                <small id="APPInfo" class="form-text text-muted"><?php printf('(Please use atleast %1$d characters.)', 4); ?></small>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="" name="newWDC">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-block">Update WDC</button>
            </div>
        </div>
    </div>
</form>