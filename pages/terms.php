<?php
if (!$User->hasRole('vendor')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
$CountOrders = Market::countMyVendorOrders();
$CountDisputes = Market::countMyVendorDisputes();
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md px-2" id="borderPageHeaderFirstLineFirstRow">
            <a class="text-white" href="/vendorshop" class="btn btn-link"><lang>Statistics</lang></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderFirstLineSecondRow">
            <a class="text-white" href="/item-create" class="btn btn-link"><lang>Upload Item</lang></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineFirstRow">
            <a class="text-white" href="/item-all" class="btn btn-link"><lang>My Items</lang></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderSecondLineSecondRow">
            <a class="text-white" href="/vendororders" class="btn btn-link"><lang>Orders</lang><?php if ($CountOrders >= 1) { echo '<span class="badge badge-order badge-pill ml-1">' . $CountOrders . '</span>'; } ?></a>
        </div>
        <div class="col-md px-2" id="borderPageHeaderThirdLine">
            <a class="text-white" href="/dispute-vendor" class="btn btn-link"><lang>Disputes</lang><?php if ($CountDisputes >= 1) { echo '<span class="badge badge-dispute badge-pill ml-1">' . $CountDisputes . '</span>'; } ?></a>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12 text-info">
            <h3><lang>Your Terms & Conditions / Refund Policy</lang>...</h3>
        </div>
    </div>
</div>

<?php
if (Forms::isPost()) {
    if (!Forms::validateString('VendorTerms', ['min' => 50])) {
        echo Alerts::danger('<lang>The Text Is Too Short! Min. 50 Characters!</lang>', 'mt-3');
    } else if (!Forms::validateString('VendorRefunds', ['min' => 50])) {
        echo Alerts::danger('<lang>The Text Is Too Short! Min. 50 Characters!</lang>', 'mt-3');
    } else {
        $User->set('VendorTerms', trim($_POST['VendorTerms']));
        $User->set('VendorRefunds', trim($_POST['VendorRefunds']));
        $Pages->redirect('terms/ok');
    }
} else if ($Pages->inPath('ok')) {
    echo Alerts::success('<lang>Saved!</lang>', 'mt-3');
}
?>

<div class="container mt-3">
    <div class="row py-1">
        <div class="col-md-12">
            <lang>Please upload your general terms and conditions and your refund policy to your Vendor-Shop.</lang>
            <br>
            <lang>This information will be added to every item you create and applies to you and your customers as the basis for every order.</lang>
            <br>
            <lang>Please include in your general terms and conditions and in your refund policy all aspects that are essential for a smooth and unproblematic flow of an order- or a refund process.</lang>
            <br>
            <lang>Meaningful and unambiguous formulations of your general terms and conditions/your refund policy are an essential basis for the decision of the moderator in the case of a dispute!</lang>
            <br><br>
            <span class="font-weight-bold text-warning" style="line-height: 1.5;"><lang>Please note that you can publish all information in any language of your choice!<br>However, this can mean that your items will not noticed by all users!<br>We therefore urgently recommend that you publish all information in ENGLISH!</lang></span>
        </div>
    </div>
</div>

<form action="/terms" method="post">
    <div class="container mt-3">
        <div class="row py-1">
            <div class="col-md-12 font-weight-light text-muted">
                <lang>Enter Your Terms & Conditions</lang>...
            </div>
            <div class="col-md-12 font-weight-light text-info mb-3" style="line-height: 1;">
                <small>(<lang>The text of your Terms & Conditions and your Refund Policy must contain at least 50 characters each!</lang>)</small>
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
                <lang>Enter Your Refund Policy</lang>...
            </div>
            <div class="col-md-12 font-weight-light text-info mb-3" style="line-height: 1;">
                <small>(<lang>The text of your Terms & Conditions and your Refund Policy must contain at least 50 characters each!</lang>)</small>
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
                <button type="submit" class="btn btn-primary btn-block"><lang>Update Terms</lang></button>
            </div>
        </div>
    </div>
</form>