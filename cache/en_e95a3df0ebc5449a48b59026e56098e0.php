<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/order-view.php***/
$OrderID = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
if (empty($OrderID)) {
    echo Alerts::danger('No Order Selected!', 'mt-3');
    return;
}
$item = Market::getOrderData($OrderID);
if (empty($item)) {
    echo Alerts::danger('No Order Selected!', 'mt-3');
    return;
}
$CaptchaError = false;
$DoReview = false;
$IamVendor = false;
if ($item['Vendor'] == $User->getID()) $IamVendor = true;

$Subtotal = $item['Price'] * $item['Quantity'];
$Total = $Subtotal + $item['ShippingPrice'];
$FEE = $User->getByUserID('VendorFee', $item['Vendor']);
$Fees = round($Total / 100 * $FEE, 2);
$Payout = $Total - $Fees;
$PayoutCrypto = round($item['PayAmount'] / 100 * (100 - $FEE), 8);
$FeeCrypto = $item['PayAmount'] - $PayoutCrypto;

$Paths = $Pages->getPath();
if ($User->hasRole('staff') || $User->hasRole('admin')) {
    if ($User->getID() == $item['Moderator']) {
        $DisputeLastMessage = $DB->fetch_assoc($DB->query('SELECT MAX(DisputeID) AS DisputeLastMessage FROM orders_disputes WHERE OrderID = ' . $DB->int($item['OrderID'])));
        if (isset($DisputeLastMessage['DisputeLastMessage'])) {
            $DB->update('orders', ['DisputeLastRead' => $DisputeLastMessage['DisputeLastMessage']], 'OrderID = ' . $DB->int($item['OrderID']));
        }
    }
    if ($item['Status'] == 'Dispute' || $item['DisputeRequested']) {
        if (isset($Paths[1])) {
            if ($Paths[1] == 'cancel') {
                $DB->update('orders', ['Status' => 'Canceled', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($item['OrderID']));
                Market::transfer($item['PayAmount'], $item['PayWith'], 'escrow', $item['Buyer']); //Send money back
                $DB->query('UPDATE items SET Quantity = Quantity + ' . $DB->int($item['Quantity']) . ' WHERE Quantity IS NOT NULL AND ItemID = ' . $DB->int($item['Item']));
                if ($Total >= 100) {
                    Ranking::addScore($item['Vendor'], 1.5);
                } else if ($Total >= 50) {
                    Ranking::addScore($item['Vendor'], 1);
                } else {
                    Ranking::addScore($item['Vendor'], 0.5);
                }
                Ranking::subScore($item['Buyer'], 5);
                $Pages->redirect('order-view/' . $item['OrderID']);
            } else if ($Paths[1] == 'finalize') {
                $DB->update('orders', ['Status' => 'Finalized', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($item['OrderID']));
                Market::transfer($FeeCrypto, $item['PayWith'], 'escrow', 'fee'); //Transfer fees to fee account
                Market::transfer($PayoutCrypto, $item['PayWith'], 'escrow', $item['Vendor']); //Pay vendor
                $DB->query('UPDATE user SET Orders = Orders + 1 WHERE UserID = ' . $DB->int($item['Buyer']));
                $DB->query('UPDATE user SET Sales = Sales + 1 WHERE UserID = ' . $DB->int($item['Vendor']));
                $DB->query('UPDATE items SET Sales = Sales + 1 WHERE ItemID = ' . $DB->int($item['Item']));
                if ($Total >= 100) {
                    Ranking::addScore($item['Buyer'], 1.5);
                } else if ($Total >= 50) {
                    Ranking::addScore($item['Buyer'], 1);
                } else {
                    Ranking::addScore($item['Buyer'], 0.5);
                }
                Ranking::subScore($item['Vendor'], 5);
                $Pages->redirect('order-view/' . $item['OrderID']);
            } else if ($Paths[1] == 'split' && Forms::isPost() && isset($_POST['Vendor']) && isset($_POST['Buyer'])) {
                $VendorP = guessMoney($_POST['Vendor']);
                $BuyerP = guessMoney($_POST['Buyer']);
                if ($VendorP + $BuyerP != 100) {
                    echo Alerts::danger('Split is not 100%', 'mt-3');
                } else if ($VendorP == 0 || $BuyerP == 0) {
                    echo Alerts::danger('0% is not allowed', 'mt-3');
                } else {
                    //Recalculate fee and amount
                    $BuyerAmount = round(($item['PayAmount'] / 100) * $BuyerP, 8);
                    $PayAmount = round($item['PayAmount'] - $BuyerAmount, 8);
                    $PayoutCrypto = round($PayAmount / 100 * (100 - $FEE), 8);
                    $FeeCrypto = $PayAmount - $PayoutCrypto;
                    //Now finalize order and pay both parties
                    $DB->update('orders', ['Status' => 'Finalized', 'StatusChanged' => time(), 'DisputeSplitMultiplier' => round($VendorP / 100, 2)], 'OrderID = ' . $DB->int($item['OrderID']));
                    Market::transfer($FeeCrypto, $item['PayWith'], 'escrow', 'fee'); //Transfer fees to fee account
                    Market::transfer($PayoutCrypto, $item['PayWith'], 'escrow', $item['Vendor']); //Pay vendor
                    Market::transfer($BuyerAmount, $item['PayWith'], 'escrow', $item['Buyer']); //Send money back
                    $DB->query('UPDATE user SET Orders = Orders + 1 WHERE UserID = ' . $DB->int($item['Buyer']));
                    $DB->query('UPDATE user SET Sales = Sales + 1 WHERE UserID = ' . $DB->int($item['Vendor']));
                    $DB->query('UPDATE items SET Sales = Sales + 1 WHERE ItemID = ' . $DB->int($item['Item']));
                    $Pages->redirect('order-view/' . $item['OrderID']);
                }
            }
        } else if (Forms::isPost() && isset($_POST['Message'])) {
            if (Captcha::verify()) {
                $NewMessage = [];
                $NewMessage['OrderID'] = $item['OrderID'];
                $NewMessage['Created'] = time();
                $NewMessage['User'] = $User->getID();
                $NewMessage['Message'] = trim($_POST['Message']);
                $DB->insert('orders_disputes', $NewMessage);
                if ($item['DisputeRequested']) {
                    $DB->query('UPDATE user SET Disputes = Disputes + 1 WHERE UserID = ' . $DB->int($item['Vendor']));
                    $DB->update('orders', ['Moderator' => $User->getID(), 'Status' => 'Dispute', 'StatusChanged' => time(), 'DisputeRequested' => 0, 'DisputeOpened' => 1], 'OrderID = ' . $DB->int($item['OrderID']));
                } else {
                    $DB->update('orders', ['Moderator' => $User->getID()], 'OrderID = ' . $DB->int($item['OrderID']));
                }
                $Pages->redirect('order-view/' . $item['OrderID']);
            } else {
                echo Alerts::danger('captcha wrong', 'mt-3');
            }
        }
    }
} else if ($IamVendor) {
    if ($item['Status'] == 'Finalized' && is_null($item['VendorReview'])) {
        $DoReview = true;
        if (Forms::isPost() && is_null($item['VendorReview']) && isset($_POST['Feedback']) && !empty($_POST['Feedback'])) {
            $NewData = [];
            $NewData['Item'] = $item['Item'];
            $NewData['Created'] = time();
            $NewData['Sender'] = $item['Vendor'];
            $NewData['SenderType'] = 'Vendor';
            $NewData['Recipient'] = $item['Buyer'];
            $NewData['Feedback'] = trim($_POST['Feedback']);
            $NewData['BuyerConfidentiality'] = max(min(intval($_POST['BuyerConfidentiality'] ?? 1), 5), 1);
            $NewData['BuyerCommunication'] = max(min(intval($_POST['BuyerCommunication'] ?? 1), 5), 1);
            $NewData['Rating'] = round(($NewData['BuyerConfidentiality'] + $NewData['BuyerCommunication']) / 2, 1);
            Ranking::subScore($item['Vendor'], 0.5);
            if ($NewData['Rating'] <= 1.4) {
                $NewData['RatingType'] = 'Negative';
                Ranking::subScore($item['Buyer'], 1);
            } else if ($NewData['Rating'] <= 3.4) {
                $NewData['RatingType'] = 'Neutral';
                Ranking::addScore($item['Buyer'], 0.5);
            } else {
                $NewData['RatingType'] = 'Positive';
                Ranking::addScore($item['Buyer'], 1);
            }
            $DB->insert('items_reviews', $NewData);
            $DB->update('orders', ['VendorReview' => $DB->lastInsertId()], 'OrderID = ' . $DB->int($item['OrderID']));
            $Pages->redirect('order-view/' . $item['OrderID']);
        }
    } else if (isset($Paths[1])) {
        if ($item['Status'] == 'NotYetConfirmed' && $Paths[1] == 'confirm') {
            $DB->update('orders', ['Status' => 'Confirmed', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($item['OrderID']));
            $DB->query('UPDATE items SET Quantity = Quantity - ' . $DB->int($item['Quantity']) . ', Active = IF(Quantity - ' . $DB->int($item['Quantity']) . ' <= 0, 0, Active) WHERE Quantity IS NOT NULL AND ItemID = ' . $DB->int($item['Item']));
            $Pages->redirect('order-view/' . $item['OrderID']);
        } else if ($item['Status'] == 'NotYetConfirmed' && $Paths[1] == 'cancel') {
            $DB->update('orders', ['Status' => 'Canceled', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($item['OrderID']));
            Market::transfer($item['PayAmount'], $item['PayWith'], 'escrow', $item['Buyer']); //Send money back
            $DB->query('UPDATE items SET Quantity = Quantity + ' . $DB->int($item['Quantity']) . ' WHERE Quantity IS NOT NULL AND ItemID = ' . $DB->int($item['Item']));
            $Pages->redirect('order-view/' . $item['OrderID']);
        } else if ($item['Status'] == 'Confirmed' && $Paths[1] == 'cancel') {
            $DB->update('orders', ['Status' => 'Canceled', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($item['OrderID']));
            Market::transfer($item['PayAmount'], $item['PayWith'], 'escrow', $item['Buyer']); //Send money back
            $DB->query('UPDATE items SET Quantity = Quantity + ' . $DB->int($item['Quantity']) . ' WHERE Quantity IS NOT NULL AND ItemID = ' . $DB->int($item['Item']));
            $Pages->redirect('order-view/' . $item['OrderID']);
        } else if ($item['Status'] == 'Confirmed' && $Paths[1] == 'shipped') {
            if ($item['Payment'] == 'fe') {
                $DB->update('orders', ['Status' => 'Finalized', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($item['OrderID']));
                Market::transfer($FeeCrypto, $item['PayWith'], 'escrow', 'fee'); //Transfer fees to fee account
                Market::transfer($PayoutCrypto, $item['PayWith'], 'escrow', $item['Vendor']); //Pay vendor
                $DB->query('UPDATE user SET Orders = Orders + 1 WHERE UserID = ' . $DB->int($item['Buyer']));
                $DB->query('UPDATE user SET Sales = Sales + 1 WHERE UserID = ' . $DB->int($item['Vendor']));
                $DB->query('UPDATE items SET Sales = Sales + 1 WHERE ItemID = ' . $DB->int($item['Item']));
                if ($Total >= 100) {
                    Ranking::addScore($item['Vendor'], 1.5);
                    Ranking::addScore($item['Buyer'], 1.5);
                } else if ($Total >= 50) {
                    Ranking::addScore($item['Vendor'], 1);
                    Ranking::addScore($item['Buyer'], 1);
                } else {
                    Ranking::addScore($item['Vendor'], 0.5);
                    Ranking::addScore($item['Buyer'], 0.5);
                }
                $Pages->redirect('order-view/' . $item['OrderID']);
            } else {
                $DB->update('orders', ['Status' => 'Shipped', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($item['OrderID']));
                $Pages->redirect('order-view/' . $item['OrderID']);
            }
        }
    } else if ($item['Status'] == 'Dispute' && Forms::isPost() && isset($_POST['Message'])) {
        if (Captcha::verify()) {
            $NewMessage = [];
            $NewMessage['OrderID'] = $item['OrderID'];
            $NewMessage['Created'] = time();
            $NewMessage['User'] = $User->getID();
            $NewMessage['Message'] = trim($_POST['Message']);
            $DB->insert('orders_disputes', $NewMessage);
            $Pages->redirect('order-view/' . $item['OrderID']);
        } else {
            echo Alerts::danger('captcha wrong', 'mt-3');
        }
    }
} else {
    if ($item['Status'] == 'Finalized' && is_null($item['BuyerReview'])) {
        $DoReview = true;
        if (Forms::isPost() && is_null($item['BuyerReview']) && isset($_POST['Feedback']) && !empty($_POST['Feedback'])) {
            $NewData = [];
            $NewData['Item'] = $item['Item'];
            $NewData['Created'] = time();
            $NewData['Sender'] = $item['Buyer'];
            $NewData['SenderType'] = 'Buyer';
            $NewData['Recipient'] = $item['Vendor'];
            $NewData['Feedback'] = trim($_POST['Feedback']);
            $NewData['VendorService'] = max(min(intval($_POST['VendorService'] ?? 1), 5), 1);
            $NewData['VendorCommunication'] = max(min(intval($_POST['VendorCommunication'] ?? 1), 5), 1);;
            $NewData['VendorRatio'] = max(min(intval($_POST['VendorRatio'] ?? 1), 5), 1);;
            $NewData['Rating'] = round(($NewData['VendorService'] + $NewData['VendorCommunication'] + $NewData['VendorRatio']) / 3, 1);
            Ranking::subScore($item['Buyer'], 0.5);
            if ($NewData['Rating'] <= 1.4) {
                $NewData['RatingType'] = 'Negative';
                Ranking::subScore($item['Vendor'], 1);
            } else if ($NewData['Rating'] <= 3.4) {
                $NewData['RatingType'] = 'Neutral';
                Ranking::addScore($item['Vendor'], 0.5);
            } else {
                $NewData['RatingType'] = 'Positive';
                Ranking::addScore($item['Vendor'], 1);
            }
            $DB->insert('items_reviews', $NewData);
            $DB->update('orders', ['BuyerReview' => $DB->lastInsertId()], 'OrderID = ' . $DB->int($item['OrderID']));
            $Pages->redirect('order-view/' . $item['OrderID']);
        }
    } else if (isset($Paths[1])) {
        if (($item['Status'] == 'Shipped' || $item['Status'] == 'Dispute') && $Paths[1] == 'finalize') {
            echo '<div class="row justify-contend-md-center"><div class="col-10 offset-1 col-md-6 offset-md-3 border p-2 mt-5 mb-5"><div class="text-muted text-center">Are You Sure You Want To Finalize Your Order?<br>After Your Confirmation, the Purchase Price Will Be Irrevocably Credited To the Vendor!</div><div class="mt-3"><a class="btn btn-sm btn-success w-25 float-left ml-3" href="/order-view/' . $item['OrderID'] . '/finalize-do">YES</a><a class="btn btn-sm btn-danger w-25 float-right mr-3" href="/order-view/' . $item['OrderID'] . '">NO</a></div></div></div>' . nl;
            return;
        } else if ($item['Status'] == 'Shipped' && $Paths[1] == 'dispute' && $item['Payment'] != 'fe') {
            echo '<div class="row justify-contend-md-center"><div class="col-10 offset-1 col-md-6 offset-md-3 border p-2 mt-5 mb-5"><div class="text-muted text-center">Are You Sure You Want To Start a Dispute?</div><div class="mt-3"><a class="btn btn-sm btn-success w-25 float-left ml-3" href="/order-view/' . $item['OrderID'] . '/dispute-do">YES</a><a class="btn btn-sm btn-danger w-25 float-right mr-3" href="/order-view/' . $item['OrderID'] . '">NO</a></div></div></div>' . nl;
            return;
        } else if (($item['Status'] == 'Shipped' || $item['Status'] == 'Dispute') && $Paths[1] == 'finalize-do') {
            $DB->update('orders', ['Status' => 'Finalized', 'StatusChanged' => time()], 'OrderID = ' . $DB->int($item['OrderID']));
            if ($item['Payment'] != 'fe') {
                Market::transfer($FeeCrypto, $item['PayWith'], 'escrow', 'fee'); //Transfer fees to fee account
                Market::transfer($PayoutCrypto, $item['PayWith'], 'escrow', $item['Vendor']); //Pay vendor
                $DB->query('UPDATE user SET Orders = Orders + 1 WHERE UserID = ' . $DB->int($item['Buyer']));
                $DB->query('UPDATE user SET Sales = Sales + 1 WHERE UserID = ' . $DB->int($item['Vendor']));
                $DB->query('UPDATE items SET Sales = Sales + 1 WHERE ItemID = ' . $DB->int($item['Item']));
                if ($Total >= 100) {
                    Ranking::addScore($item['Vendor'], 1.5);
                    Ranking::addScore($item['Buyer'], 1.5);
                } else if ($Total >= 50) {
                    Ranking::addScore($item['Vendor'], 1);
                    Ranking::addScore($item['Buyer'], 1);
                } else {
                    Ranking::addScore($item['Vendor'], 0.5);
                    Ranking::addScore($item['Buyer'], 0.5);
                }
            }
            $Pages->redirect('order-view/' . $item['OrderID']);
        } else if ($item['Status'] == 'Shipped' && $Paths[1] == 'dispute-do' && $item['Payment'] != 'fe') {
            $DB->update('orders', ['DisputeRequested' => 1], 'OrderID = ' . $DB->int($item['OrderID']));
            $Pages->redirect('order-view/' . $item['OrderID']);
        }
    } else if (($item['Status'] == 'Dispute' || $item['DisputeRequested']) && Forms::isPost() && isset($_POST['Message'])) {
        if (Captcha::verify()) {
            $NewMessage = [];
            $NewMessage['OrderID'] = $item['OrderID'];
            $NewMessage['Created'] = time();
            $NewMessage['User'] = $User->getID();
            $NewMessage['Message'] = trim($_POST['Message']);
            $DB->insert('orders_disputes', $NewMessage);
            $Pages->redirect('order-view/' . $item['OrderID']);
        } else {
            echo Alerts::danger('captcha wrong', 'mt-3');
        }
    }
}

$PayWith = strtolower($item['PayWith']);
$Shippings = Market::getShippings();

//Order-Nr
echo '<div class="container mt-3"><div class="row py-1"><div class="col-md-12 text-info"><h3>Order-Nr.:&nbsp;' . Market::OrderID($OrderID) . '</h3></div></div></div>' . nl;

if ($item['Status'] == 'Shipped') {
    //Auto-Finalization
    echo '<div class="container mt-2"><div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-danger">Auto-Finalization:</span></div><div class="col-auto col-md-8"><span class="text-danger text-nowrap">';
    if ($item['Class'] == 'physical') {
        $TimeLeft = $item['StatusChanged'] + 1209600 - time();
    } else if ($item['Class'] == 'digital') {
        $TimeLeft = $item['StatusChanged'] + 259200 - time();
    }
    if ($TimeLeft <= 0) {
        echo 'Process Pending';
    } else {
        $TLDays = floor($TimeLeft / 86400);
        $TimeLeft -= $TLDays * 86400;
        $TLHours = floor($TimeLeft / 3600);
        $TimeLeft -= $TLHours * 3600;
        $TLMinutes = floor($TimeLeft / 60);
        $TimeLeft -= $TLMinutes * 60;
        printf('%02dd %02dh %02dmin' , $TLDays, $TLHours, $TLMinutes);
    }
    echo '</span></div></div></div>' . nl;
}

if ($DoReview) {
    if ($IamVendor) {
        echo '<div class="container mt-2"><div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-success"><h4>The order is finalized! Please leave a review for the buyer!</h4></span></div></div></div>' . nl;
    } else {
        echo '<div class="container mt-2"><div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-success"><h4>Your order is finalized! Please leave a review for the vendor!</h4></span></div></div></div>' . nl;
    }
}

if ($item['Status'] == 'Dispute' || (!$IamVendor && $item['DisputeRequested'])) {
    echo '<div class="container mt-2"><div class="row py-1"><div class="col-auto col-md-8"><span class="text-danger"><h4>Open dispute!</h4></span></div></div></div>' . nl;
}

echo '<div class="container mt-0"><div class="row py-1">' . nl;

//Item
echo '<div class="col-md-12"><span class="font-weight-light text-muted">Item:</span>&nbsp;&nbsp;' . htmlentities($item['Name']) . '</div>' . nl;

//Category
echo '<div class="col-md-12"><span class="font-weight-light text-muted">Category:</span>&nbsp;&nbsp;';
$Tree = Categories::getCategoryTree($item['Category']);
$CategoriesNum = count($Tree);
foreach ($Tree as $i => $Category) {
    echo htmlentities($Category['Name']) . ($i < $CategoriesNum - 1 ? '&nbsp;/&nbsp;' : '') . nl;
}
echo '</div>' . nl;

echo '</div></div>' . nl;
echo '<div class="container mt-3"><div class="row py-1">' . nl;

//Vendor
echo '<div class="col-8 col-md-4"><span class="font-weight-light text-muted">Vendor:</span></div><div class="col-auto col-md-8"><a href="/profile/' . $item['Vendor'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($User->getByUserID('Username', $item['Vendor']))) . '</span></a></div>' . nl;
if ($IamVendor || $User->hasRole('staff') || $User->hasRole('admin')) {
    //Buyer
    echo '<div class="col-8 col-md-4"><span class="font-weight-light text-muted">Buyer:</span></div><div class="col-auto col-md-8"><a href="/profile/' . $item['Buyer'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($User->getByUserID('Username', $item['Buyer']))) . '</span></a></div>' . nl;
}

if (($User->hasRole('staff') || $User->hasRole('admin')) && !is_null($item['Moderator'])) {
    echo '<div class="col-8 col-md-4"><span class="font-weight-light text-muted">Moderator:</span></div><div class="col-auto col-md-8"><a href="/profile/' . $item['Moderator'] . '" class="text-primary"><span class="text-nowrap">' . htmlentities(ucfirst($User->getByUserID('Username', $item['Moderator']))) . '</span></a></div>' . nl;
}

//Item-Nr
echo '<div class="col-8 col-md-4"><span class="font-weight-light text-muted">Item Nr.:</span></div><div class="col-auto col-md-8"><span class="text-nowrap">' . Market::ItemID($item['Item']) . '</span></div>' . nl;

//Item Class
echo '<div class="col-8 col-md-4"><span class="font-weight-light text-muted">Item Class:</span></div><div class="col-auto col-md-8">' . $Language->translate($item['Class']) . '</div>' . nl;

//Payment Processing
echo '<div class="col-8 col-md-4"><span class="font-weight-light text-muted">Payment Processing:</span></div><div class="col-auto col-md-8">' . $Language->translate($item['Payment']) . '</div>' . nl;

echo '</div></div>' . nl;

if ($DoReview) {
    echo '<form action="/order-view/' . $item['OrderID'] . '" method="post">';

    echo '<div class="container mt-2"><div class="row py-1">' . nl;
    if ($IamVendor) {
        echo '<div class="col-10 col-md-4"><span class="font-weight-light text-muted">Buyer&rsquo;s Confidentiality:</span></div><div class="col-auto col-md-8">' . stars('BuyerConfidentiality') . '</div>' . nl;
        echo '<div class="col-10 col-md-4"><span class="font-weight-light text-muted">Buyer&rsquo;s Communication:</span></div><div class="col-auto col-md-8">' . stars('BuyerCommunication') . '</div> ' . nl;
    } else {
        echo '<div class="col-10 col-md-4"><span class="font-weight-light text-muted">Vendor&rsquo;s Service:</span></div><div class="col-auto col-md-8">' . stars('VendorService') . '</div>' . nl;
        echo '<div class="col-10 col-md-4"><span class="font-weight-light text-muted">Vendor&rsquo;s Communication:</span></div><div class="col-auto col-md-8">' . stars('VendorCommunication') . '</div> ' . nl;
        echo '<div class="col-10 col-md-4"><span class="font-weight-light text-muted">Price Performance Ratio:</span></div><div class="col-auto col-md-8">' . stars('VendorRatio') . '</div>' . nl;
    }
    echo '</div></div>' . nl;

    //Textarea
    echo '<div class="container mt-3"><div class="row py-1"><div class="col-md-12 font-weight-light text-muted mb-2">Please Leave a Feedback...</div><div class="col-md-12"><div class="form-group"><textarea class="form-control" rows="5" name="Feedback"></textarea></div></div></div></div>' . nl;

    //Button
    echo '<div class="container mt-1"><div class="row py-1"><div class="col-md-12"><button type="submit" class="btn btn-primary btn-block">Submit Review</button></div></div></div>' . nl;

    echo '</form>';
    return;
}

echo '<div class="container mt-3">' . nl;

//Date
echo '<div class="row py-1"><div class="col-7 col-md-4"><span class="font-weight-light text-muted text-nowrap">Date:</span></div><div class="col-auto text-nowrap">' . $Language->date($item['Created']) . '</div></div>' . nl;

//Quantity
echo '<div class="row py-1"><div class="col-7 col-md-4"><span class="font-weight-light text-muted text-nowrap">Quantity:</span></div><div class="col-auto">' . $item['Quantity'] . '</div></div>' . nl;

//Single Price
echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted text-nowrap">Singel Price:</span></div><div class="col-auto text-nowrap">';
echo $Language->number($item['Price']) . ' US-$&nbsp;';
if ($User->getCurrency() != 'USD') echo '<span style="font-size: 80%;">(' . Currencies::exchange($item['Price'], 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>';
echo '</div></div>' . nl;

//Price Subtotal
echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted text-nowrap">Price Subtotal:</span></div><div class="col-auto text-nowrap">';
echo $Language->number($Subtotal) . ' US-$&nbsp;';
if ($User->getCurrency() != 'USD') echo '<span style="font-size: 80%;">(' . Currencies::exchange($Subtotal, 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>';
echo '</div></div>' . nl;

//Shipping Method
echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted text-nowrap">Shipping Method:</span></div><div class="col-auto">';
echo (isset($Shippings[$item['Shipping'] . '_' . $item['ShippingType']]) ? htmlentities($Shippings[$item['Shipping'] . '_' . $item['ShippingType']]['Name']) : '');
echo '</div></div>' . nl;

//Shipping Costs
echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted text-nowrap">Shipping Costs:</span></div><div class="col-auto text-nowrap">';
echo $Language->number($item['ShippingPrice']) . ' US-$&nbsp;';
if ($User->getCurrency() != 'USD') echo '<span style="font-size: 80%;">(' . Currencies::exchange($item['ShippingPrice'], 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>' . nl;
echo '</div></div>' . nl;

if ($IamVendor) {
    //Price Total
    echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="text-muted text-nowrap font-weight-bold">Price Total:</span></div><div class="col-auto text-nowrap">';
    echo '<span class="font-weight-bold">' . $Language->number($Total) . ' US-$</span>&nbsp;';
    if ($User->getCurrency() != 'USD') echo '<span class="font-weight-bold" style="font-size: 80%;">(' . Currencies::exchange($Total, 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>';
    echo '</div></div>' . nl;

    //Commission Fee
    echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="text-muted text-nowrap">Commission Fee</span><span class="text-muted text-nowrap pl-2">(Currently</span><span class="text-muted"> ' . $FEE . '%)</span></div><div class="col-auto text-nowrap">';
    echo '<span class="font-weight-bold text-danger">- ' . $Language->number($Fees) . ' US-$</span>&nbsp;';
    if ($User->getCurrency() != 'USD') echo '<span class="text-danger font-weight-bold" style="font-size: 80%;">(- ' . Currencies::exchange($Fees, 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>';
    echo '</div></div>' . nl;

    //Payout
    echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="text-success text-nowrap font-weight-bold">Payout:</span></div><div class="col-auto text-nowrap">';
    echo '<span class="text-success font-weight-bold" style="font-size: 120%;">' . $Language->number($Payout) . ' US-$</span>&nbsp;';
    if ($User->getCurrency() != 'USD') echo '<span id="pastel-green" class="font-weight-bold" style="font-size: 100%;">(' . Currencies::exchange($Payout, 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>';
    echo '</div></div>' . nl;

    //Selected Cryptocurrency
    echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted">Selected Cryptocurrency:</span></div><div class="col-auto col-md-4 align-text-bottom"><img class="mb-1" src="/img/' . $PayWith . '_color.png" alt="Logo" title="" width="16px" height="16px"/><span class="pl-1" id="color-' . $PayWith . '">';
    if ($item['PayWith'] == 'XMR') {
        echo 'Monero';
    } else if ($item['PayWith'] == 'BTC') {
        echo 'Bitcoin';
    } else if ($item['PayWith'] == 'LTC') {
        echo 'Litecoin';
    }
    echo '</span></div></div>' . nl;

    //Payout
    echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted text-nowrap">Payout:</span></div><div class="col-auto text-nowrap"><span class="font-weight-bold" id="color-' . $PayWith . '" style="font-size: 120%;">' . $Language->number($PayoutCrypto, 8) . '</span>&nbsp;&nbsp;<span id="color-' . $PayWith . '">' . $item['PayWith'] . '</span></div></div>' . nl;

    echo '</div>' . nl;

    //Order Status
    echo '<div class="container mt-0"><div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted">Order Status:</span></div><div class="col-auto col-md-4">' . Market::getButton($item['Status']) . '</div></div></div>' . nl;

    if ($item['Status'] == 'NotYetConfirmed' || $item['Status'] == 'Confirmed') {
        //Buyer's Note
        echo '<div class="container mt-3"><div class="row py-1"><div class="col-5 col-md-4"><span class="font-weight-light text-muted text-nowrap">Buyer’s Note:</span></div><div class="col-md-12 mt-4"><textarea class="form-control" rows="5">' . htmlentities($item['Note']) . '</textarea></div></div></div>' . nl;
    }
    if ($item['Status'] == 'NotYetConfirmed') {
        //Buttons
        echo '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-6 mb-3"><a href="/order-view/' . $item['OrderID'] . '/confirm" type="submit" class="btn btn-success btn-block float-left">Confirm Order</a></div><div class="col-12 col-md-6"><a href="/order-view/' . $item['OrderID'] . '/cancel" type="submit" class="btn btn-danger btn-block float-right">Cancel Order</a></div></div></div>' . nl;
    }
    if ($item['Status'] == 'Confirmed') {
        //Buttons
        echo '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-6 mb-3"><a href="/order-view/' . $item['OrderID'] . '/shipped" type="submit" class="btn btn-success btn-block float-left">Shipped Order</a></div><div class="col-12 col-md-6"><a href="/order-view/' . $item['OrderID'] . '/cancel" type="submit" class="btn btn-danger btn-block float-right">Cancel Order</a></div></div></div>' . nl;
    }
} else {
    //Price Total
    echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="text-muted text-nowrap font-weight-bold">Price Total:</span></div><div class="col-auto text-nowrap">';
    echo '<span class="text-danger font-weight-bold" style="font-size: 120%;">' . $Language->number($Total) . ' US-$</span>&nbsp;';
    if ($User->getCurrency() != 'USD') echo '<span class="text-success font-weight-bold" style="font-size: 100%;">(' . Currencies::exchange($Total, 'USD', 'USER') . ' ' . $User->getCurrency() . ')</span>';
    echo '</div></div>' . nl;

    //Selected Cryptocurrency
    echo '<div class="row py-1"><div class="col-10 col-md-4"><span class="font-weight-light text-muted">Selected Cryptocurrency:</span></div><div class="col-auto align-text-bottom"><img class="mb-1" src="/img/' . $PayWith . '_color.png" alt="Logo" title="" width="16px" height="16px"/><span class="pl-1" id="color-' . $PayWith . '">';
    if ($item['PayWith'] == 'XMR') {
        echo 'Monero';
    } else if ($item['PayWith'] == 'BTC') {
        echo 'Bitcoin';
    } else if ($item['PayWith'] == 'LTC') {
        echo 'Litecoin';
    }
    echo '</span></div></div>' . nl;

    //Payable Amount
    echo '<div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted text-nowrap">Payable Amount:</span></div><div class="col-auto text-nowrap"><span class="font-weight-bold" id="color-' . $PayWith . '" style="font-size: 120%;">' . Currencies::exchange($Total, 'USD', $item['PayWith']) . '</span>&nbsp;&nbsp;<span id="color-' . $PayWith . '">' . $item['PayWith'] . '</span></div></div>' . nl;

    echo '</div>' . nl;

    //Order Status
    echo '<div class="container mt-0"><div class="row py-1"><div class="col-8 col-md-4"><span class="font-weight-light text-muted text-nowrap">Order Status:</span></div><div class="col-auto col-md-4">' . Market::getButton($item['Status']) . '</div></div></div>' . nl;

    echo Market::getStatusText($item['Status']);

    if ($item['Status'] == 'Shipped' && !$item['DisputeRequested']) {
        //Buttons
        echo '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-6 mb-3"><a href="/order-view/' . $item['OrderID'] . '/finalize" type="submit" class="btn btn-success btn-block float-left">Finalize Order</a></div>';
        if ($item['Payment'] != 'fe') echo '<div class="col-12 col-md-6"><a href="/order-view/' . $item['OrderID'] . '/dispute" type="submit" class="btn btn-danger btn-block float-right">Start Dispute</a></div>';
        echo '</div></div>' . nl;
    }
}

if ($item['Status'] == 'Dispute' || (!$IamVendor && $item['DisputeRequested'])) {
    $Messages = Market::getDisputes($item['OrderID']);
    foreach ($Messages as $Message) {
        echo '<div class="container mt-3"><div class="row py-1"><div class="col-md-12 mb-2"><span class="font-weight-light text-muted">Message from:</span><a href="/profile/' . $Message['User'] . '" class="text-primary pl-2"><span class="text-nowrap">' . htmlentities(ucfirst($Message['Name'])) . '</span></a><span class="font-weight-light text-muted pl-2">send on:</span><span class="pl-2">' . $Language->date($Message['Created']) . '</span></div><div class="col-md-12"><div class="form-group"><textarea class="form-control overflow-auto" rows="5">' . htmlentities($Message['Message']) . '</textarea></div></div></div></div>' . nl;
    }

    echo '<form action="/order-view/' . $item['OrderID'] . '" method="post">' . nl;

    //Textarea
    echo '<div class="container mt-3"><div class="row py-1"><div class="col-md-12 font-weight-light text-muted mb-2">Add a new message to the dispute:</div><div class="col-md-12"><div class="form-group"><textarea class="form-control" rows="3" placeholder="Enter your message" name="Message"></textarea></div></div></div></div>' . nl;

if (Captcha::showCaptcha()) {
    //Captcha
    echo '<div class="container mt-3"><div class="row py-1"><div class="col-md-12 mb-3 form-group"><img src="' . Captcha::get() . '" alt="" title="Captcha" /></div><div class="col-8 col-md-4 mb-3 form-group"><input type="text" class="form-control" id="Captcha" name="Captcha" autocomplete="off" placeholder="Repeat the CAPTCHA Code"';
    devCodeCAPTCHA();
    echo '></div></div></div>' . nl;
}

    //Buttons
    echo '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-6 mb-3"><button type="submit" class="btn btn-secondary btn-block float-left">Send Message</button></div>';
    if (!$User->hasRole('staff') && !$User->hasRole('admin') && !$IamVendor) echo '<div class="col-12 col-md-6"><a href="/order-view/' . $item['OrderID'] . '/finalize" type="button" class="btn btn-success btn-block float-right">Finalize order</a></div>';
    echo '</div></div>' . nl;

    echo '</form>' . nl;

    if ($User->hasRole('staff') || $User->hasRole('admin')) {
        echo '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-6"><a href="/order-view/' . $item['OrderID'] . '/finalize" class="btn btn-success btn-block float-left">Finalize Order</a></div></div></div>' . nl;

        echo '<form action="/order-view/' . $item['OrderID'] . '/split" method="post">';
        echo '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-6"><span class="float-left pt-2">Vendor</span><input type="text" class="form-control float-left ml-2" style="width: 10%;" name="Vendor"><span class="float-left pt-2 ml-2">%</span><span class="float-left pt-2 ml-5">Buyer</span><input type="text" class="form-control float-left ml-2" style="width: 10%;" name="Buyer"><span class="float-left pt-2 ml-2">%</span></div></div></div>';
        echo '<div class="container"><div class="row py-1"><div class="col-12 col-md-6"><button type="submit" class="btn btn-warning btn-block float-left">Split Amount</a></div></div></div>';
        echo '</form>' . nl;

        echo '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-6"><a href="/order-view/' . $item['OrderID'] . '/cancel" class="btn btn-danger btn-block float-left">Cancel Order</a></div></div></div>' . nl;
    }
}

if (!is_null($item['BuyerReview']) && !$DoReview) {
    $Data = Market::getReviewData($item['BuyerReview']);
    echo '<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Buyer&rsquo;s Review:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
        <table class="table-sm table-bordered-secondary w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Buyer</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
                <tr>
                    <td class="text-center text-nowrap">' . $Data['Created'] . '</td>
                    <td class="text-center">' . $Data['Stars'] . '</td>
                    <td class="left">' . $Data['Name'] . '</td>
                    <td class="left text-nowrap">' . $Data['Feedback'] . '</td>
                </tr>
        </table>
        </div>
    </div>
</div>' . nl;
}

if (!is_null($item['VendorReview']) && !$DoReview) {
    $Data = Market::getReviewData($item['VendorReview']);
    echo '<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Vendor&rsquo;s Review:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
        <table class="table-sm table-bordered-dark-gray w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Vendor</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
                <tr>
                    <td class="text-center text-nowrap">' . $Data['Created'] . '</td>
                    <td class="text-center">' . $Data['Stars'] . '</td>
                    <td class="left">' . $Data['Name'] . '</td>
                    <td class="left text-nowrap">' . $Data['Feedback'] . '</td>
                </tr>
        </table>
        </div>
    </div>
</div>' . nl;
}

function stars($Name)
{
    $stars = '<div class="ratingButtons">';
    for ($s = 5; $s >= 1; $s--) {
        $stars .= '<input type="radio" id="' . $Name . $s . '" name="' . $Name . '" value="' . $s . '"' . ($s == 1 ? ' checked' : '') . '><label for="' . $Name . $s . '">★</label>';
    }
    $stars .= '</div>';
    return $stars;
}