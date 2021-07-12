<?php
$HTMLHead->addNav('cat_search');

if ($User->get('BlockTransactions')) {
    echo Alerts::danger('<lang>Transactions Are Currently Not Possible!</lang>', 'mt-3');
    return;
}
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="/account" class="btn btn-link"><lang>Account Settings</lang></a>
        </div>
        <div class="col-md-6 px-2" id="" style="display: ">
            <a class="text-white" href="/changepassword" class="btn btn-link"><lang>Change Passwords</lang></a>
        </div>
    </div>
</div>

<?php
if ($Pages->inPath('welcome')) {
    echo Alerts::success('<lang>Your Vendor Account Is Now Activated.</lang>', 'mt-3');
    return;
} else if (!$User->hasRole('user')) {
    echo Alerts::success('<lang>You Are Already Registered as a Vendor!</lang>', 'mt-3');
    return;
}

$Bond = 500;
$BondXMR = Currencies::exchange($Bond, 'USD', 'XMR');
$BondBTC = Currencies::exchange($Bond, 'USD', 'BTC');
$BondLTC = Currencies::exchange($Bond, 'USD', 'LTC');

if (Forms::isPost()) {
    if ($User->get('2FA') != 1 || empty($User->get('PGP'))) {
        $Pages->redirect('vendorform/2famandatory');
    } else if (empty($User->get('Location'))) {
        $Pages->redirect('vendorform/locationmandatory');
    } else if (!isset($_POST['yesRead'])) {
        $Pages->redirect('vendorform/noread');
    } else if (isset($_POST['XMR']) && isset($_POST['BTC']) && isset($_POST['LTC'])) {
        $Pages->redirect('vendorform/browserproblem');
    } else if (isset($_POST['XMR'])) {
        if ($User->get('XMR') >= $BondXMR) {
            Market::transfer($BondXMR, 'XMR', $User->getID(), 'fee');
            $User->set('role', 'vendor');
            $User->set('VendorSince', time());
            Ranking::addScore($User->getID(), 10);
            $Pages->redirect('vendorform/welcome');
        } else {
            $Pages->redirect('vendorform/lowbalance');
        }
    } else if (isset($_POST['BTC'])) {
            Market::transfer($BondBTC, 'BTC', $User->getID(), 'fee');
        if ($User->get('BTC') >= $BondBTC) {
            $User->set('role', 'vendor');
            $User->set('VendorSince', time());
            Ranking::addScore($User->getID(), 10);
            $Pages->redirect('vendorform/welcome');
        } else {
            $Pages->redirect('vendorform/lowbalance');
        }
    } else if (isset($_POST['LTC'])) {
        if ($User->get('LTC') >= $BondLTC) {
            Market::transfer($BondLTC, 'LTC', $User->getID(), 'fee');
            $User->set('role', 'vendor');
            $User->set('VendorSince', time());
            Ranking::addScore($User->getID(), 10);
            $Pages->redirect('vendorform/welcome');
        } else {
            $Pages->redirect('vendorform/lowbalance');
        }
    } else if (isset($_POST['register'])) {
        if (Forms::validateString('inviteCode', ['min' => 32, 'max' => 32])) {
            $Code = $DB->getOne('invite_codes', 'Code = ' . $DB->string($_POST['inviteCode']));
            if (array_key_exists('UsedAt', $Code) && is_null($Code['UsedAt'])) {
                $DB->update('invite_codes', ['UsedBy' => $User->getID(), 'UsedAt' => time()], 'CodeID = ' . $DB->int($Code['CodeID']));
                $User->set('VendorInvited', 1);
                $User->set('role', 'vendor');
                $User->set('VendorSince', time());
                $Pages->redirect('vendorform/welcome');
            } else {
                $Pages->redirect('vendorform/errorCode');
            }
        } else {
            $Pages->redirect('vendorform/errorCode');
        }
    } else {
        $Pages->redirect('vendorform/usebutton');
    }
}

if ($Pages->inPath('noread')) { echo Alerts::danger('<lang>Please Confirm That You Accept the Marketplace Rules!</lang>', 'mt-3'); }
if ($Pages->inPath('browserproblem')) { echo Alerts::danger('<lang>Your Browser Does Not Meet the Minimum Requirements! Please Make an Update!</lang>', 'mt-3'); }
if ($Pages->inPath('usebutton')) { echo Alerts::danger('<lang>You Have To Pay the Vendor Bond First! Please Click on [Pay Bond]!</lang>', 'mt-3'); }
if ($Pages->inPath('errorCode')) { echo Alerts::danger('<lang>Your Invitation Code Is Invalid!</lang>', 'mt-3'); }
if ($Pages->inPath('lowbalance')) { echo Alerts::danger('<lang>Your Account Balance Is Not Sufficient!</lang>', 'mt-3'); }
if ($Pages->inPath('2famandatory')) { echo Alerts::danger('<lang>2FA Is Mandatory for Your Account!</lang>', 'mt-3'); }
if ($Pages->inPath('locationmandatory')) { echo Alerts::danger('<lang>Your Location (Country of Dispatch) Is Mandatory for Your Account!</lang>', 'mt-3'); }
?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><lang>How I Can Become a Vendor</lang>...</h3>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12">
            <lang>Here you can activate your vendor account. Take time to read the marketplace rules below, check the box and click one of the buttons [Pay Bond]. After that, you will be able to create your Vendor-Shop, list your items and start selling. Be careful and observe the rules - breaching them may result to your account suspension!</lang>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12">
            <ol>
                <li><lang>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.<br>At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</lang></li>
                <li>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</li>
                <li>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</li>
                <li>At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</li>
                <li>Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet:<br>
                    - dolor sit amet<br>
                    - consetetur sadipscing elitr<br>
                    - sed diam nonumy<br>
                    - eirmod tempor<br>
                    - invidunt ut labore et dolore magna</li>
                <li>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</li>
                <li>At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</li>
            </ol>
        </div>
    </div>
</div>

<form action="" method="post">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="yesRead"><input class="form-check-input ml-1 mt-2 font-weight-bold" type="checkbox" value="1" id="yesRead" name="yesRead"><span class="ml-4 text-danger"><lang>I have read and accepted the marketplace rules mentioned above.</lang></span></label>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1 mb-3">
        <div class="row py-1">
            <div class="col-md-3 pt-3">
                <div class="text-success">
                    <h2><?php echo $Language->number($Bond, 2); ?> US-$</h2>
                    <h6 class="text-secondary">(<?php echo Currencies::exchange($Bond, 'USD', 'USER') . ' ' . $User->getCurrency(); ?>)</h6> 
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2" style="line-height: 200%;">
                    <span class="text-muted">XMR:</span>&nbsp;&nbsp;<?php echo $BondXMR; ?>
                    <br>
                    <button type="submit" class="btn-xmr btn-xmr-primary btn-xmr-block pl-2" name="XMR"><img class="" src="/img/xmr_white.png" alt="Logo" title="" width="20px" height="20px"/>&nbsp;<lang>Pay Bond</lang></button>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2" style="line-height: 200%;">
                    <span class="text-muted">BTC:</span>&nbsp;&nbsp;<?php echo $BondBTC; ?>
                    <br>
                    <button type="submit" class="btn-btc btn-btc-primary btn-btc-block pl-2" name="BTC"><img class="" src="/img/btc_white.png" alt="Logo" title="" width="20px" height="20px"/>&nbsp;<lang>Pay Bond</lang></button>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-2" style="line-height: 200%;">
                    <span class="text-muted">LTC:</span>&nbsp;&nbsp;<?php echo $BondLTC; ?>
                    <br>
                    <button type="submit" class="btn-ltc btn-ltc-primary btn-ltc-block pl-2" name="LTC"><img class="" src="/img/ltc_white.png" alt="Logo" title="" width="20px" height="20px"/>&nbsp;<lang>Pay Bond</lang></button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 text-danger">
                <lang>Attention!<br>You can only activate your vendor account if you have previously:<br>- stored your location (country of dispatch);<br>- uploaded your PGP public key and<br>- activated the 2FA authentication<br>in your</lang>&nbsp;<a class="text-info" href="/account" class="btn btn-link"><span class="text-nowrap"><lang>Account Settings</lang></span></a>
            </div>
        </div>
    </div>
    
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted mb-2">
                <lang>I have an invitation code. Please register me as a vendor for free</lang>...
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="<lang>Enter Invitation Code</lang>" name="inviteCode">
                </div>
            </div>
            <div class="col-md-12 pb-4">
                <button type="submit" class="btn btn-primary btn-block" name="register"><lang>Register</lang></button>
            </div>
        </div>
    </div>
</form>