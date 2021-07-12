<style>
#menuShowHide_down + label:before {content: "<lang>Info</lang> ▼";}
#menuShowHide_down:checked + label:before {content: "<lang>Info</lang> ▲";}
@media screen and (max-width: 2800px) {
#menuShowHideAdmin + label:before {content:"<lang>Open Admin Menu</lang>";}
#menuShowHideAdmin:checked + label:before {content:"<lang>Close Admin Menu</lang>";}
#menuShowHide + label:before {content:"<lang>Show Menu</lang>";}
#menuShowHide:checked + label:before {content:"<lang>Hide Menu</lang>";}
}
</style>

<?php
$CountUnreadMessages = Messages::countUnreadMessages();
$ls = $Language->getLanguages();
?><div class="container-fluid" style="background-color:#378BE5;">
    <div class="row border-bottom border-white">
        <div class="col-md-12">
            <span><a href="<?php echo MAINURI; ?>"><img class="my-2" style="min-width: 160px;" src="/img/logo_white.png" alt="Logo" title="" width="40%" height=""/></a>
                <span class="dropdown dropbtnMenu float-right">
                    <span class="dropdown-toggle" style="color: #FFFFFF;">
                        <button class="btn btn-link text-white" id="top-cust-lang">
<?php echo $ls[$Language->getLanguage()]; ?> <span class="caret ml-n3"></span>
                        </button>
                    </span>
                    <span class="dropdown-content">
<?php
foreach ($ls as $ID => $Name) {
    echo '<a href="?setLanguage=' . $ID . '">' . $Name . '</a>' . nl;
}
?>
                    </span>
                </span>
            </span>
        </div>
    </div>
    <div class="row py-1 border-bottom border-white" id="exchange">
        <div class="col-md-auto text-white border-right border-white text-nowrap">
            <span class="font-weight-light"><lang>Exchange Rates</lang></span>
        </div>
        <div class="col-md-auto text-white border-right border-white text-nowrap">
            <span class="font-weight-light">XMR = </span><?php echo Currencies::exchange(1, 'XMR', 'USER'); ?> <span class="font-weight-light"><?php echo $User->getCurrency(); ?></span>
        </div>
        <div class="col-md-auto text-white border-right border-white text-nowrap">
            <span class="font-weight-light">BTC = </span><?php echo Currencies::exchange(1, 'BTC', 'USER'); ?> <span class="font-weight-light"><?php echo $User->getCurrency(); ?></span>
        </div>
        <div class="col-md-auto text-white text-nowrap">
            <span class="font-weight-light">LTC = </span><?php echo Currencies::exchange(1, 'LTC', 'USER'); ?> <span class="font-weight-light"><?php echo $User->getCurrency(); ?></span>
        </div>
    </div>
    <div class="row py-1 border-bottom border-white" id="balance">
        <div class="col-md-auto text-white text-nowrap" id="borderHead">
            <span class="font-weight-light"><lang>Balance Wallets</lang></span>
        </div>
<?php
if ($User->hasRole('admin')) {
    $SysAcc = $DB->getOne('system_accounts', 'SAccountID = ' . $DB->string('fee'));
?>
        <div class="col-md-auto text-white text-nowrap" id="borderHead">
            <?php echo $Language->number($SysAcc['XMR'], 8) ?> <span class="font-weight-light">XMR</span>
        </div>
        <div class="col-md-auto text-white text-nowrap" id="borderHead">
            <?php echo $Language->number($SysAcc['BTC'], 8) ?> <span class="font-weight-light">BTC</span>
        </div>
        <div class="col-md-auto text-white text-nowrap">
            <?php echo $Language->number($SysAcc['LTC'], 8) ?> <span class="font-weight-light">LTC</span>
        </div>
<?php } else { ?>
        <div class="col-md-auto text-white text-nowrap" id="borderHead">
            <?php echo $Language->number($User->get('XMR'), 8) ?> <span class="font-weight-light">XMR</span>
        </div>
        <div class="col-md-auto text-white text-nowrap" id="borderHead">
            <?php echo $Language->number($User->get('BTC'), 8) ?> <span class="font-weight-light">BTC</span>
        </div>
        <div class="col-md-auto text-white text-nowrap">
            <?php echo $Language->number($User->get('LTC'), 8) ?> <span class="font-weight-light">LTC</span>
        </div>
<?php } ?>
    </div>

    <input type="checkbox" id="menuShowHide">
    <label for="menuShowHide"></label>
    <div class="row py-1" id="mainMenu">
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/marketplace" class="btn btn-link"><lang>Marketplace</lang></a>
        </div>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white text-decoration-white" href="/account" class="btn btn-link"><lang>Account</lang></a>
        </div>
<?php if ($User->hasRole('vendor')) { ?>
        <div class="col-xs-auto px-2" id="borderHead" style="display: ">
            <a class="text-white" href="/vendorshop" class="btn btn-link"><lang>Vendor-Shop</lang></a>
        </div>
<?php } ?>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/monerowallet" class="btn btn-link"><lang>Wallets</lang></a>
        </div>
<?php if ($User->hasRole('admin')) { ?>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/moneromaster" class="btn btn-link"><lang>Master Wallets</lang></a>
        </div>
<?php }
if ($User->hasRole('user') || $User->hasRole('vendor')) { ?>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/userorders" class="btn btn-link"><lang>Orders</lang></a>
        </div>
<?php }?>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/message-in" class="btn btn-link"><lang>Messages</lang><?php if ($CountUnreadMessages >= 1) { echo '<span class="badge badge-light badge-pill ml-1">' . $CountUnreadMessages . '</span>'; } ?></a>
        </div>
<?php if ($User->hasRole('user') || $User->hasRole('vendor')) { ?>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/help" class="btn btn-link"><lang>Help</lang></a>
        </div>
<?php }?>
        <div class="col-xs-auto px-2">
            <a class="text-white" href="/logout" class="btn btn-link"><lang>Logout</lang></a>
        </div>
    </div>

<?php if ($User->hasRole('user') || $User->hasRole('vendor')) { ?>
<!-- info-elements -->
<input type="checkbox" id="menuShowHide_down">
<label for="menuShowHide_down"></label>
<div id="down" style="max-width: 400px; padding-top: 10px; padding-bottom: 10px; padding-left: 80px;">
    <div class="row" style="cursor: auto;">
        <div style="width: 55%; max-width: 250px; float: left; margin-left: -60px; margin-top: 15px;">
            <div>
                <img class="rounded-circle border border-white mt-2 mb-2" src="<?php echo $User->getAvatar(); ?>" alt="avatar" title="" style="width: 50%; max-width: 60px; height: 50%; max-height: 60px;"/>
            </div>
            <br>
            <div style="width: 60%; color: white; line-height: 1.5;">
                <div class="font-weight-light text-nowrap mt-2"><lang>Logged in As</lang>:</div>&nbsp;
                <div>
<?php
echo $User->getUsername();
?>
                </div>
            </div>
        </div>
        <div class="text-white" style="width: 60%; float: left; padding-left: 20px; line-height: 1.2;">
<?php if ($User->hasRole('user')) { ?>
            <div class="mt-2"><lang>Status</lang>:</div>
            <div class="font-weight-light text-nowrap">
<?php echo Ranking::getUserRank($User->get('UserRank')); ?>
            </div>
            <br>
<?php } else if ($User->hasRole('vendor')) { ?>
            <div class="mt-2"><lang>Status</lang>:</div>
            <div class="font-weight-light text-nowrap">
<?php echo Ranking::getVendorRank($User->get('VendorRank')); ?>
            </div>
            <br>
<?php } ?>
            <div class="mt-2"><lang>Scoring</lang>:</div>
            <div class="font-weight-light text-nowrap">
<?php
echo $Language->number($User->get('Scoring'), 2);
?>%</div>
            <br>
            <div class="mt-2 text-nowrap"><lang>First Login</lang>:</div>
            <div class="font-weight-light text-nowrap">
<?php
echo date('Y-m-d', $User->get('Registered'));
?>
            </div>
            <br>
            <div class="mt-2 text-nowrap"><lang>Last Login</lang>:</div>
            <div class="font-weight-light text-nowrap">
<?php
echo date('Y-m-d', $User->getLastLogin('Registered'));
?>
            </div>
            <br>
            <div class="mt-2"><lang>Sales</lang>:<span class="font-weight-light">
<?php
echo $User->get('Sales');
?>
            </span></div>
            <br>
            <div class="mt-2"><lang>Purchases</lang>:<span class="font-weight-light">
<?php
echo $User->get('Orders');
?>
            </span></div>
        </div>
    </div>
</div>
<?php
} else if ($User->hasRole('staff') || $User->hasRole('admin')) { ?>
    <input type="checkbox" id="menuShowHideAdmin">
    <label for="menuShowHideAdmin"></label>
    <div class="row mb-4" id="mainMenuAdmin">
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/item-overview" class="btn btn-link"><lang>Items</lang></a>
        </div>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/order-overview" class="btn btn-link"><lang>Orders</lang></a>
        </div>
<?php if ($User->hasRole('admin')) { ?>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/categories" class="btn btn-link"><lang>Categories</lang></a>
        </div>
<?php } ?>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/messages-support" class="btn btn-link"><lang>Messages</lang><?php
$NumNewSupportMessages = $DB->fetch_assoc($DB->query('SELECT COUNT(*) AS Num FROM messages WHERE Moderator IS NULL AND Recipient = 0 AND ModeratorDeleted = 0'));
if ($NumNewSupportMessages['Num'] >= 1) {
    echo '<span class="badge badge-light badge-pill ml-1">' . $NumNewSupportMessages['Num'] . '</span>';
}
?></a>
        </div>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/account-user" class="btn btn-link"><lang>User Accounts</lang></a>
        </div>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/account-vendor" class="btn btn-link"><lang>Vendor Accounts</lang></a>
        </div>
<?php if ($User->hasRole('admin')) { ?>
        <div class="col-xs-auto px-2" id="borderHead">
            <a class="text-white" href="/account-staff" class="btn btn-link"><lang>Staff Accounts</lang></a>
        </div>
<?php } ?>
        <div class="col-xs-auto px-2">
            <a class="text-white" href="/dispute-admin" class="btn btn-link"><lang>Disputes</lang><?php
$NumNewDisputes = $DB->fetch_assoc($DB->query('SELECT COUNT(*) AS Num FROM orders WHERE Moderator IS NULL AND (Status = ' . $DB->string('Dispute') . ' OR DisputeRequested = 1)'));
if ($NumNewDisputes['Num'] >= 1) {
    echo '<span class="badge badge-danger badge-pill ml-1">' . $NumNewDisputes['Num'] . '</span>';
}
?></a>
        </div>
    </div>
<?php }?>
</div>
