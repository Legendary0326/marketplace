<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/profile.php***/
$UserID = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
if (!$User->exists($UserID)) $Pages->redirect('');
$UserRole = $User->getByUserID('Role', $UserID);
if ($UserRole == 'user' && $User->hasRole('user') && $UserID != $User->getID()) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
$CLs = $Language->getCommunicateLanguages();
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
<?php
if ($UserRole == 'user') {
    echo '<h3>User Profile...</h3>' . nl;
} else if ($UserRole == 'vendor') {
    echo '<h3>Vendor Profile...</h3>' . nl;
} else if ($UserRole == 'staff') {
    echo '<h3>Staff Profile...</h3>' . nl;
} else if ($UserRole == 'admin') {
    echo '<h3>Admin Profile...</h3>' . nl;
}
?>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-6 col-md-3">
            <div style="width: 100%; height: auto;">
                <img class="p-1 ml-3" src="<?php echo $User->getAvatar($UserID); ?>" alt="item" title="" style="max-width: 150px; height: auto;"/>
            </div>
        </div>
    </div>
</div>

<div class="container mt-2">
    <div class="row">
            <div class="col-auto col-md-12">
                <div style="line-height: 200%;">
                    <span class="text-info font-weight-bold text-nowrap"><h4><?php echo htmlentities(ucwords($User->getByUserID('Username', $UserID))); ?></h4></span>
<?php if ($UserRole == 'vendor') { ?>
                    <a class="btn-sm btn-primary" href="/marketplace/<?php echo $UserID; ?>" role="button">Visit Vendor-Shop</a>
                    <br>
<?php } ?>
                    <a class="btn-sm btn-secondary" href="/message-new/<?php echo $UserID; ?>" role="button">Send Message</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Status:</span>
        </div>
        <div class="col-auto col-md-8">
<?php if ($UserRole == 'vendor') { ?>
            <span class="text-nowrap"><?php echo Ranking::getVendorRank($User->getByUserID('VendorRank', $UserID)); ?></span>
<?php } else { ?>
            <span class="text-nowrap"><?php echo Ranking::getUserRank($User->getByUserID('UserRank', $UserID)); ?></span>
<?php } ?>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Scoring:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php echo $Language->number($User->getByUserID('Scoring', $UserID)); ?>%
        </div>
<?php if ($UserRole != 'staff' && $UserRole != 'admin') { ?>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">User Since:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo $Language->date($User->getByUserID('Registered', $UserID)); ?></span>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Last Login:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo $Language->date($User->getLastLogin('Registered', $UserID)); ?></span>
        </div>
<?php } ?>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted text-nowrap">I Can Communicate In:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php
$Communicate = explode(',', $User->getByUserID('Communicate', $UserID));
foreach ($Communicate as $i => $C) {
    if ($i >= 1) echo ' / ';
    echo $CLs[$C];
}
?>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">My ICQ:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo htmlentities($User->getByUserID('ICQ', $UserID)); ?></span>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">My AOL:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo htmlentities($User->getByUserID('AOL', $UserID)); ?></span>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">My Jabber:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo htmlentities($User->getByUserID('Jabber', $UserID)); ?></span>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">My Email:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo htmlentities($User->getByUserID('EMail', $UserID)); ?></span>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">My PGP Public Key:</span>
        </div>
        <div class="col-auto col-md-8">
            <textarea class="form-control overflow-auto mt-3 mb-3" style="min-width: 350px;" rows="5"><?php echo htmlentities($User->getByUserID('PGP', $UserID)); ?></textarea>
        </div>
<?php if ($UserRole == 'vendor') { ?>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">My Terms & Conditions:</span>
        </div>
        <div class="col-auto col-md-8">
            <textarea class="form-control overflow-auto mt-3 mb-3" style="min-width: 350px;" rows="5"><?php echo htmlentities($User->getByUserID('VendorTerms', $UserID)); ?></textarea>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">My Refund Policy:</span>
        </div>
        <div class="col-auto col-md-8">
            <textarea class="form-control overflow-auto mt-3 mb-3" style="min-width: 350px;" rows="5"><?php echo htmlentities($User->getByUserID('VendorRefunds', $UserID)); ?></textarea>
        </div>
<?php
}
if ($UserRole != 'staff' && $UserRole != 'admin') {
?>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Total Orders:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo htmlentities($User->getByUserID('Orders', $UserID)); ?></span>
        </div>
<?php if ($UserRole == 'vendor') { ?>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Total Sales:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo htmlentities($User->getByUserID('Sales', $UserID)); ?></span>
        </div>
<?php } ?>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Disputes:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo htmlentities($User->getByUserID('Disputes', $UserID)); ?></span>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Feedback Rate:</span>
        </div>
        <div class="col-auto col-md-8">
            <span class="text-nowrap"><?php echo $Language->number($User->getByUserID('FeedbackRate', $UserID)); ?>%</span>
        </div>
<?php } ?>
    </div>
</div>
<?php
if ($UserRole == 'staff' || $UserRole == 'admin') return;
if ($UserRole == 'vendor') {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Review Overview:</h5>
        </div>
        <div class="table-responsive-md ml-3">
            <table class="table table-bordered-standard">
                <tr>
                    <th scope="col" style="background: none; border-top: 1px solid transparent; border-left: 1px solid transparent;"></th>
                    <th scope="col">30 Days</th>
                    <th scope="col">6 Months</th>
                    <th scope="col">Total Period</th>
                </tr>
<?php $ReviewOverview = Market::getReviewOverviewVendor($UserID); ?>
                <tr>
                    <td class="text-success">Positive</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Positive'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Positive'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Positive'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td class="text-primary">Neutral</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Neutral'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Neutral'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Neutral'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td class="text-danger">Negative</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Negative'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Negative'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Negative'] ?? 0; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php $ReviewCategories = Market::getReviewCategoriesVendor($UserID); ?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Reviews Categories:</h5>
        </div>
        <div class="col-8 col-md-3">
            <span class="font-weight-light text-muted">Service:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php echo $ReviewCategories['Service'] ?? ''; ?>
        </div>
        <div class="col-8 col-md-3">
            <span class="font-weight-light text-muted">Communication:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php echo $ReviewCategories['Communication'] ?? ''; ?>
        </div>
        <div class="col-8 col-md-3">
            <span class="font-weight-light text-muted">Price Performance Ratio:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php echo $ReviewCategories['Ratio'] ?? ''; ?>
        </div>
    </div>
</div>

<?php
$LastReviews = Market::getLastReviewsVendor($UserID, true);
if (isset($LastReviews['Positive']) && count($LastReviews['Positive']) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Positive Reviews:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-positive w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Buyer</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
<?php
foreach (($LastReviews['Positive'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php
}
if (isset($LastReviews['Neutral']) && count($LastReviews['Neutral']) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Neutral Reviews:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-neutral w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Buyer</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
<?php
foreach (($LastReviews['Neutral'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php
}
if (isset($LastReviews['Negative']) && count($LastReviews['Negative']) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Negative Reviews:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-negative w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Buyer</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
<?php
foreach (($LastReviews['Negative'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php
}
if (isset($LastReviews['Left']) && count($LastReviews['Left']) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Left Reviews:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-dark-gray w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Buyer</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
<?php
foreach (($LastReviews['Left'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php
}
} else {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Review Overview:</h5>
        </div>
        <div class="table-responsive-md ml-3">
            <table class="table table-bordered-standard">
                <tr>
                    <th scope="col" style="background: none; border-top: 1px solid transparent; border-left: 1px solid transparent;"></th>
                    <th scope="col">30 Days</th>
                    <th scope="col">6 Months</th>
                    <th scope="col">Total Period</th>
                </tr>
<?php $ReviewOverview = Market::getReviewOverviewUser($UserID); ?>
                <tr>
                    <td class="text-success">Positive</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Positive'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Positive'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Positive'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td class="text-primary">Neutral</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Neutral'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Neutral'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Neutral'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td class="text-danger">Negative</td>
                    <td class="text-center"><?php echo $ReviewOverview['30Days']['Negative'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['6Months']['Negative'] ?? 0; ?></td>
                    <td class="text-center"><?php echo $ReviewOverview['Total']['Negative'] ?? 0; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php $ReviewCategories = Market::getReviewCategoriesUser($UserID); ?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Reviews Categories:</h5>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Trust:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php echo $ReviewCategories['Confidentiality'] ?? ''; ?>
        </div>
        <div class="col-6 col-md-3">
            <span class="font-weight-light text-muted">Communication:</span>
        </div>
        <div class="col-auto col-md-8">
            <?php echo $ReviewCategories['Communication'] ?? ''; ?>
        </div>
    </div>
</div>
<?php
$LastReviews = Market::getLastReviewsUser($UserID, true);
if (isset($LastReviews['Positive']) && count($LastReviews['Positive']) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Positive Reviews:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-positive w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Vendor</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
<?php
foreach (($LastReviews['Positive'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php
}
if (isset($LastReviews['Neutral']) && count($LastReviews['Neutral']) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Neutral Reviews:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-neutral w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Vendor</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
<?php
foreach (($LastReviews['Neutral'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php
}
if (isset($LastReviews['Negative']) && count($LastReviews['Negative']) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Negative Reviews:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-negative w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Vendor</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
<?php
foreach (($LastReviews['Negative'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-left text-nowrap w-25">' . $Review['Itemname'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php
}
if (isset($LastReviews['Left']) && count($LastReviews['Left']) >= 1) {
?>
<div class="container mt-4">
    <div class="row py-1">
        <div class="col-md-12 font-weight-light text-muted mb-2">
            <h5>Left Reviews:</h5>
        </div>
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-dark-gray w-100">
                <tr>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Rating</th>
                    <th scope="col" class="text-center">Vendor</th>
                    <th scope="col" class="text-left pl-4">Feedback</th>
                </tr>
<?php
foreach (($LastReviews['Left'] ?? []) as $Review) {
    echo '<tr>
<td class="text-center text-nowrap">' . $Review['Created'] . '</td>
<td class="text-center text-nowrap">' . $Review['Stars'] . '</td>
<td class="left text-nowrap">' . $Review['Name'] . '</td>
<td class="left text-nowrap">' . $Review['Feedback'] . '</td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
<?php
}
}