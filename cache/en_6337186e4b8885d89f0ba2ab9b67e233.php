<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/litecoinwallet.php***/
$HTMLHead->addNav('cat_search');
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="/monerowallet" class="btn btn-link">Monero Wallet</a>
        </div>
        <div class="col-md-6 px-2" id="" style="display: ">
            <a class="text-white" href="/bitcoinwallet" class="btn btn-link">Bitcoin Wallet</a>
        </div>
    </div>
</div>
<?php
if ($Pages->inPath('withdrawalok')) {
    echo Alerts::success('Transfer Successful.', 'mt-3');
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
                                echo Alerts::danger('Interface Software (API) Error! Please Contact the Support!', 'mt-3');
                            }
                        } else {
                            echo Alerts::danger('Your Balance Is Not Sufficient! Please Transfer Credit to Your Account First!', 'mt-3');
                        }
                    } else {
                        echo Alerts::danger(sprintf('The Balance On Your Account Is Too Low! (Min. Balance = %.2f US-$!)', LTC_MINUSD), 'mt-3');
                    }
                } else {
                    echo Alerts::danger('The Entered Value Is Too Low!', 'mt-3');
                }
            } else {
                echo Alerts::danger('The Inserted Wallet Address Is Invalid!', 'mt-3');
            }
        } else {
            echo Alerts::danger('The Inserted WDC Is Invalid!', 'mt-3');
        }
    } else {
        echo Alerts::danger('Sorry, the CAPTCHA Is Incorrect. Please Try Again.', 'mt-3');
    }
}
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>Your Personal Litecoin Wallet...</h3>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12">
            Here you can manage your Litecoin balance as well as deposit and withdraw funds. To deposit funds, send Litecoins to the integrated address below (klick button [Create deposit address]). The balance will appear after 3 blockchain confirmations, which should complete your payment in about 10 minutes. The address will change between each deposit, so please always deposit to the latest address. You can deposit any amount you want. All marketplace internal transactions are absolutely free of fees. For deposits and withdrawals of Litecoins, you have to pay the usual fees, charged by the blockchain.
            <br><br>
            <span class="text-danger">Please note! A generated wallet address is only valid for 72 hours! An unused wallet address (without transfer) will be irrevocably deleted from the database after this time.</span>
            <br><br>
            <span class="text-danger">If you send Litecoins to an unvalid address, they might not be credited properly!</span>
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
            <a class="btn btn-primary btn-block mx-auto d-block" style="width: 33%; min-width: 250px;" href="/litecoinwallet/create">Create Deposit Address</a>
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row py-1">
        <div class="col-md-12">
            <span class="text-danger"><h4>Lorem ipsum</h4></span>
            <br>
            Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
            <br>
            Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
            <br><br>
            <span class="text-secondary"><h4>Lorem ipsum</h4></span>
            <br>
            Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
        </div>
    </div>
</div>

<div class="container mt-5">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h3>Transaction History</h3>
        </div>
        <div class="table-responsive-md" style="min-width: 100%;">
            <table class="table table-bordered-wallet-in">
                <tr>
                    <th scope="col" class="text-nowrap" style="width: 15%;">ID</th>
                    <th scope="col" class="text-nowrap" style="width: 15%;">Date</th>
                    <th scope="col" class="text-nowrap" style="width: 40%;">Wallet Address</th>
                    <th scope="col" class="text-nowrap" style="width: 15%;">Amount</th>
                    <th scope="col" class="text-nowrap" style="width: 15%;">Status</th>
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
        echo '<td class="text-nowrap">' . ($Transaction['Confirmations'] >= 2 ? 'Confirmed' : 'Pending') . '</td>' . nl;
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
            <h3>Withdrawal Your Credit</h3>
        </div>
        <div class="col-md-12">
            You can withdraw funds to an external wallet address.
            <br>
            Enter a valid Litecoin address and the amount you wish to withdraw below.
            <br><br>
            <span class="text-danger"><?php printf('Please note!<br>Amounts less than %.2f US-$ cannot be paid out for security reasons (deposit for blockchain transaction fees). Amounts that you have received from an external wallet address can be paid out at least 24 hours after they have been credited!', LTC_MINUSD); ?></span>
        </div>
    </div>
</div>

<?php
if ($User->get('BlockTransactions')) {
    echo Alerts::danger('Transactions Are Currently Not Possible!', 'mt-3');
} else {
?>
<form method="post" action="/litecoinwallet">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-4 mb-1 pt-1">
                Withdraw To This Address:
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
                Amount:
            </div>
            <div class="col-md-8">
                <input type="text" class="form-control float-left w-50" name="Amount" placeholder="0.00000000"><span class="float-left ml-2 pt-1">LTC</span>
            </div>
        </div>
    </div>

    <div class="container mt-0">
        <div class="row py-1">
            <div class="col-md-4 mb-1 pt-1">
                Enter Your Withdrawal Code (WDC):
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
                Repeat the CAPTCHA Code:
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
                <button type="submit" class="btn btn-primary btn-block" style="width: 33%; min-width: 250px;">Withdrawal Amount</button>
            </div>
        </div>
    </div>
</form>
<?php } ?>

<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h3>Withdrawal History</h3>
        </div>
        <div class="table-responsive-md" style="min-width: 100%;">
            <table class="table table-bordered-wallet-out">
                <tr>
                    <th scope="col" class="text-nowrap" style="width: 15%;">ID</th>
                    <th scope="col" class="text-nowrap" style="width: 15%;">Date</th>
                    <th scope="col" class="text-nowrap" style="width: 55%;">Wallet Address</th>
                    <th scope="col" class="text-nowrap" style="width: 15%;">Amount</th>
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