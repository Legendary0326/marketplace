<?php
$HTMLHead->addNav('cat_search');
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="/monerowallet" class="btn btn-link"><lang>Monero Wallet</lang></a>
        </div>
        <div class="col-md-6 px-2" id="" style="display: ">
            <a class="text-white" href="/bitcoinwallet" class="btn btn-link"><lang>Bitcoin Wallet</lang></a>
        </div>
    </div>
</div>
<?php
if ($Pages->inPath('withdrawalok')) {
    echo Alerts::success('<lang>Transfer Successful.</lang>', 'mt-3');
} else if (Forms::isPost() && isset($_POST['Address']) && isset($_POST['Amount']) && isset($_POST['WDC']) && Captcha::issetCaptcha() && !$User->get('BlockTransactions')) {
    if (Captcha::verify()) {
        if ($User->get('WC') == $_POST['WDC']) {
            if (LTC::checkAddress($_POST['Address'])) {
                $UserAmount = guessMoney($_POST['Amount']);
                if ($UserAmount > 0) {
                    if ($User->hasRole('admin')) {
                        $SysAcc = $DB->getOne('system_accounts', 'SAccountID = ' . $DB->string('fee'));
                        $Amount = $SysAcc['LTC'];
                    } else {
                        $Amount = $User->get('LTC');
                        $SQLResult = $DB->query('SELECT SUM(Amount) AS AmountSum FROM crypto_transactions WHERE User = ' . $DB->int($User->getID()) . ' AND Currency = ' . $DB->string('LTC') . ' AND Received >= ' . $DB->int(time() - 86400));
                        if (!empty($SQLResult)) {
                            $AmountResult = $DB->fetch_assoc($SQLResult);
                            if (isset($AmountResult['AmountSum'])) {
                                $Amount = $Amount - (float) $AmountResult['AmountSum'];
                            }
                        }
                    }
                    $USD = Currencies::exchange($Amount - $UserAmount, 'LTC', 'USD', false);
                    if ($USD >= LTC_MINUSD) {
                        if ($Amount >= $UserAmount) {
                            if (LTC::sendMoney($_POST['Address'], $UserAmount)) {
                                $NewData = [
                                    'User'          =>  $User->getID(),
                                    'Currency'      =>  'LTC',
                                    'Date'          =>  time(),
                                    'Address'       =>  $_POST['Address'],
                                    'Amount'        =>  $UserAmount
                                ];
                                $DB->insert('crypto_withdrawals', $NewData);
                                if ($User->hasRole('admin')) {
                                    $DB->query('UPDATE system_accounts SET LTC = LTC - ' . $DB->float($UserAmount) . ' WHERE SAccountID = ' . $DB->string('fee'));
                                } else {
                                    $DB->query('UPDATE user SET LTC = LTC - ' . $DB->float($UserAmount) . ' WHERE UserID = ' . $DB->int($User->getID()));
                                }
                                $Pages->redirect('bitcoinwallet/withdrawalok');
                            } else {
                                echo Alerts::danger('<lang>Interface Software (API) Error! Please Contact the Support!</lang>', 'mt-3');
                            }
                        } else {
                            echo Alerts::danger('<lang>Your Balance Is Not Sufficient! Please Transfer Credit to Your Account First!</lang>', 'mt-3');
                        }
                    } else {
                        echo Alerts::danger(sprintf('<lang>The Balance On Your Account Is Too Low! (Min. Balance = %.2f US-$!)</lang>', LTC_MINUSD), 'mt-3');
                    }
                } else {
                    echo Alerts::danger('<lang>The Entered Value Is Too Low!</lang>', 'mt-3');
                }
            } else {
                echo Alerts::danger('<lang>The Inserted Wallet Address Is Invalid!</lang>', 'mt-3');
            }
        } else {
            echo Alerts::danger('<lang>The Inserted WDC Is Invalid!</lang>', 'mt-3');
        }
    } else {
        echo Alerts::danger('<lang>Sorry, the CAPTCHA Is Incorrect. Please Try Again.</lang>', 'mt-3');
    }
}
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><lang>Your Personal Litecoin Wallet</lang>...</h3>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12">
            <lang>Here you can manage your Litecoin balance as well as deposit and withdraw funds. To deposit funds, send Litecoins to the integrated address below (klick button [Create deposit address]). The balance will appear after 3 blockchain confirmations, which should complete your payment in about 10 minutes. The address will change between each deposit, so please always deposit to the latest address. You can deposit any amount you want. All marketplace internal transactions are absolutely free of fees. For deposits and withdrawals of Litecoins, you have to pay the usual fees, charged by the blockchain.</lang>
            <br><br>
            <span class="text-danger"><lang>Please note! A generated wallet address is only valid for 72 hours! An unused wallet address (without transfer) will be irrevocably deleted from the database after this time.</lang></span>
            <br><br>
            <span class="text-danger"><lang>If you send Litecoins to an unvalid address, they might not be credited properly!</lang></span>
        </div>
    </div>
</div>

<?php
if ($Pages->inPath('create')) {
    $Address = LTC::addAddress();
?>
<div class="container mt-3">
    <div class="row">
        <div class="col-md-12">
            <img class="mx-auto d-block" src="<?php
echo 'data:image/svg+xml;base64,' . base64_encode(QRcode::svg('litecoin:' . $Address, md5($Address), false, QR_ECLEVEL_L, false, false, 0));
?>" alt="" title="" style="max-width: 20%; min-width: 150px; max-height: auto; min-height: auto;"/>
        </div>
        <br><br>
        <div class="col-md-12 text-center text-break mt-3" id="frame_standard">
            <?php echo $Address; ?>
        </div>
    </div>
</div>
<?php
}
?>
<div class="container mt-3">
    <div class="row">
        <div class="col-md-12">
            <a class="btn btn-primary btn-block mx-auto d-block" style="width: 33%; min-width: 250px;" href="/litecoinwallet/create"><lang>Create Deposit Address</lang></a>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row py-1">
        <div class="col-md-12">
            <span class="text-danger"><h4><lang>Lorem ipsum</lang></h4></span>
            <br>
            <lang>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</lang>
            <br>
            <lang>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</lang>
            <br><br>
            <span class="text-secondary"><h4><lang>Lorem ipsum</lang></h4></span>
            <br>
            <lang>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</lang>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h3><lang>Transaction History</lang></h3>
        </div>
        <div class="table-responsive-md" style="min-width: 100%;">
            <table class="table table-bordered-wallet-in">
                <tr>
                    <th scope="col" class="text-nowrap" style="width: 15%;"><lang>ID</lang></th>
                    <th scope="col" class="text-nowrap" style="width: 15%;"><lang>Date</lang></th>
                    <th scope="col" class="text-nowrap" style="width: 40%;"><lang>Wallet Address</lang></th>
                    <th scope="col" class="text-nowrap" style="width: 15%;"><lang>Amount</lang></th>
                    <th scope="col" class="text-nowrap" style="width: 15%;"><lang>Status</lang></th>
                </tr>
<?php
$Transactions = LTC::getLastReceived();
if (count($Transactions) >= 1) {
    foreach ($Transactions as $Transaction) {
        echo '<tr>' . nl;
        echo '<td class="text-nowrap">' . $Transaction['TransactionID'] . '</td>' . nl;
        echo '<td class="text-nowrap">' . $Language->date($Transaction['Received']) . '</td>' . nl;
        echo '<td>' . $Transaction['Address'] . '</td>' . nl;
        echo '<td class="text-nowrap">' . $Language->number($Transaction['Amount'], 8) . '</td>' . nl;
        echo '<td class="text-nowrap">' . ($Transaction['Confirmations'] >= 2 ? '<lang>Confirmed</lang>' : '<lang>Pending</lang>') . '</td>' . nl;
        echo '</tr>' . nl;
    }
}
?>
            </table>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h3><lang>Withdrawal Your Credit</lang></h3>
        </div>
        <div class="col-md-12">
            <lang>You can withdraw funds to an external wallet address.</lang>
            <br>
            <lang>Enter a valid Litecoin address and the amount you wish to withdraw below.</lang>
            <br><br>
            <span class="text-danger"><?php printf('<lang>Please note!<br>Amounts less than %.2f US-$ cannot be paid out for security reasons (deposit for blockchain transaction fees). Amounts that you have received from an external wallet address can be paid out at least 24 hours after they have been credited!</lang>', LTC_MINUSD); ?></span>
        </div>
    </div>
</div>

<?php
if ($User->get('BlockTransactions')) {
    echo Alerts::danger('<lang>Transactions Are Currently Not Possible!</lang>', 'mt-3');
} else {
?>
<form method="post" action="/litecoinwallet">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-4 mb-1 pt-1">
                <lang>Withdraw To This Address</lang>:
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <input type="text" class="form-control" name="Address">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-0">
        <div class="row py-1">
            <div class="col-md-4 mb-1 pt-1">
                <lang>Amount</lang>:
            </div>
            <div class="col-md-8">
                <input type="text" class="form-control float-left w-50" name="Amount" placeholder="0.00000000"><span class="float-left ml-2 pt-1">LTC</span>
            </div>
        </div>
    </div>

    <div class="container mt-0">
        <div class="row py-1">
            <div class="col-md-4 mb-1 pt-1">
                <lang>Enter Your Withdrawal Code (WDC)</lang>:
            </div>
            <div class="col-md-8">
                <input type="password" class="form-control float-left w-50" name="WDC">
            </div>
        </div>
    </div>
<?php if (Captcha::showCaptcha()) { ?>
    <div class="container mt-0">
        <div class="row py-1">
            <div class="col-md-8 offset-md-4 pt-1">
                <div class="form-group">
                    <img src="<?php echo Captcha::get(); ?>" alt="" title="CAPTCHA" />
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-0">
        <div class="row py-1">
            <div class="col-md-4 mb-1 pt-1">
                <lang>Repeat the CAPTCHA Code</lang>:
            </div>
            <div class="col-md-8">
                <input type="text" class="form-control float-left w-50" name="Captcha"<?php devCodeCAPTCHA(); ?>>
            </div>
        </div>
    </div>
<?php } ?>
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-12 mb-1 pt-1">
                <button type="submit" class="btn btn-primary btn-block" style="width: 33%; min-width: 250px;"><lang>Withdrawal Amount</lang></button>
            </div>
        </div>
    </div>
</form>
<?php } ?>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h3><lang>Withdrawal History</lang></h3>
        </div>
        <div class="table-responsive-md" style="min-width: 100%;">
            <table class="table table-bordered-wallet-out">
                <tr>
                    <th scope="col" class="text-nowrap" style="width: 15%;"><lang>ID</lang></th>
                    <th scope="col" class="text-nowrap" style="width: 15%;"><lang>Date</lang></th>
                    <th scope="col" class="text-nowrap" style="width: 55%;"><lang>Wallet Address</lang></th>
                    <th scope="col" class="text-nowrap" style="width: 15%;"><lang>Amount</lang></th>
                </tr>
<?php
$Withdrawals = LTC::getLastWithdrawals();
if (count($Withdrawals) >= 1) {
    foreach ($Withdrawals as $Withdrawal) {
        echo '<tr>' . nl;
        echo '<td class="text-nowrap">' . $Withdrawal['WithdrawalID'] . '</td>' . nl;
        echo '<td class="text-nowrap">' . $Language->date($Withdrawal['Date']) . '</td>' . nl;
        echo '<td class="text-nowrap">' . $Withdrawal['Address'] . '</td>' . nl;
        echo '<td class="text-nowrap">' . $Language->number($Withdrawal['Amount'], 8) . '</td>' . nl;
        echo '</tr>' . nl;
    }
}
?>
            </table>
        </div>
    </div>
</div>