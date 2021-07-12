<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/terms.php***/
if (!$User->hasRole('vendor')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
$CountOrders = Market::countMyVendorOrders();
$CountDisputes = Market::countMyVendorDisputes();
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md px-2" id="borderPageHeaderFirstLineFirstRow">
            <a class="text-white" href="/vendorshop" class="btn btn-link">Statistics</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderFirstLineSecondRow">
            <a class="text-white" href="/item-create" class="btn btn-link">Upload Item</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineFirstRow">
            <a class="text-white" href="/item-all" class="btn btn-link">My Items</a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineSecondRow">
            <a class="text-white" href="/vendororders" class="btn btn-link">Orders<?php if ($CountOrders >= 1) { echo '<span class="badge badge-order badge-pill ml-1">' . $CountOrders . '</span>'; } ?></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderThirdLine">
            <a class="text-white" href="/dispute-vendor" class="btn btn-link">Disputes<?php if ($CountDisputes >= 1) { echo '<span class="badge badge-dispute badge-pill ml-1">' . $CountDisputes . '</span>'; } ?></a>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3>Your Terms & Conditions / Refund Policy...</h3>
        </div>
    </div>
</div>

<?php
if (Forms::isPost()) {
    if (!Forms::validateString('VendorTerms', ['min' => 50])) {
        echo Alerts::danger('The Text Is Too Short! Min. 50 Characters!', 'mt-3');
    } else if (!Forms::validateString('VendorRefunds', ['min' => 50])) {
        echo Alerts::danger('The Text Is Too Short! Min. 50 Characters!', 'mt-3');
    } else {
        $User->set('VendorTerms', trim($_POST['VendorTerms']));
        $User->set('VendorRefunds', trim($_POST['VendorRefunds']));
        $Pages->redirect('terms/ok');
    }
} else if ($Pages->inPath('ok')) {
    echo Alerts::success('Saved!', 'mt-3');
}
?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12">
            Please upload your general terms and conditions and your refund policy to your Vendor-Shop.
            <br>
            This information will be added to every item you create and applies to you and your customers as the basis for every order.
            <br>
            Please include in your general terms and conditions and in your refund policy all aspects that are essential for a smooth and unproblematic flow of an order- or a refund process.
            <br>
            Meaningful and unambiguous formulations of your general terms and conditions/your refund policy are an essential basis for the decision of the moderator in the case of a dispute!
            <br><br>
            <span class="font-weight-bold text-warning" style="line-height: 1.5;">Please note that you can publish all information in any language of your choice!<br>However, this can mean that your items will not noticed by all users!<br>We therefore urgently recommend that you publish all information in ENGLISH!</span>
        </div>
    </div>
</div>

<form action="/terms" method="post">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted">
                Enter Your Terms & Conditions...
            </div>
            <div class="col-md-12 font-weight-light text-info mb-3" style="line-height: 1;">
                <small>(The text of your Terms & Conditions and your Refund Policy must contain at least 50 characters each!)</small>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control overflow-auto" rows="5" name="VendorTerms"><?php echo htmlentities($User->get('VendorTerms')); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted">
                Enter Your Refund Policy...
            </div>
            <div class="col-md-12 font-weight-light text-info mb-3" style="line-height: 1;">
                <small>(The text of your Terms & Conditions and your Refund Policy must contain at least 50 characters each!)</small>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="form-control overflow-auto" rows="5" name="VendorRefunds"><?php echo htmlentities($User->get('VendorRefunds')); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-block">Update Terms</button>
            </div>
        </div>
    </div>
</form>