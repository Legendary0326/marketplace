<?php
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
            <a class="text-white" href="/account" class="btn btn-link"><lang>Account Settings</lang></a>
        </div>
        <div class="col-md-6 px-2 text-nowrap" id="" style="display: ">
            <a class="text-white" href="/vendorform" class="btn btn-link"><lang>Become a Vendor</lang></a>
        </div>
<?php } else { ?>
        <div class="col-md-12 mx-auto text-nowrap">
            <a class="text-white" href="/account" class="btn btn-link"><lang>Account Settings</lang></a>
        </div>
<?php } ?>
    </div>
</div>

<?php
if ($Pages->inPath('errorPass1')) { echo Alerts::danger('<lang>Your Current Password Is Incorrect!</lang>', 'mt-3'); }
if ($Pages->inPath('errorPass2')) { echo Alerts::danger('<lang>Your New Password Does Not Meet the Minimum Requirements!</lang>', 'mt-3'); }
if ($Pages->inPath('savedWC')) { echo Alerts::success('<lang>Your New WDC Saved Successfully.</lang>', 'mt-3'); }
if ($Pages->inPath('errorWC1')) { echo Alerts::danger('<lang>Your Current WDC Is Incorrect!</lang>', 'mt-3'); }
if ($Pages->inPath('errorWC2')) { echo Alerts::danger('<lang>Your New WDC Does Not Meet the Minimum Requirements!</lang>', 'mt-3'); }
?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><lang>To Change Your Password</lang>...</h3>
        </div>
    </div>
</div>

<form action="?password" method="post">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Enter Your Current Password</lang>...
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
                <lang>Enter Your New Password</lang>...<br>
                <small id="PasswordInfo" class="form-text text-muted"><?php printf('(<lang>Please use atleast %1$d characters. Use atleast one letter, number and special character.</lang>)', 8); ?></small>
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
                <lang>Re-Enter Your New Password</lang>...
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
                <lang>After changing your password, you will be automatically logged out. Please log in again with your new password!</lang>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 pb-4" id="separator">
                <button type="submit" class="btn btn-primary btn-block"><lang>Update Password</lang></button>
            </div>
        </div>
    </div>
</form>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><lang>To Change Your WDC</lang>...</h3>
        </div>
    </div>
</div>

<form action="?wdc" method="post">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>Enter Your Current WDC</lang>...
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
                <lang>Enter Your New WDC</lang>...<br>
                <small id="APPInfo" class="form-text text-muted"><?php printf('(<lang>Please use atleast %1$d characters.</lang>)', 4); ?></small>
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
                <button type="submit" class="btn btn-primary btn-block"><lang>Update WDC</lang></button>
            </div>
        </div>
    </div>
</form>