<?php
if (!$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
?>

<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="moneromaster" class="btn btn-link"><lang>Monero Masterwallet</lang></a>
        </div>
        <div class="col-md-6 px-2" id="" style="display: ">
            <a class="text-white" href="litecoinmaster" class="btn btn-link"><lang>Litecoin Masterwallet</lang></a>
        </div>
    </div>
</div>

<?php
$getwalletinfo = BTC::raw('getwalletinfo', []);
?>

<div class="container mt-4">
    <div class="row py-1 mt-3">
        <div class="col-md-12 text-info">
                <h3><lang>Bitcoin Masterwallet</lang>...</h3>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Name</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $getwalletinfo['walletname']; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Balance</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $getwalletinfo['balance']; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Unconfirmed Balance</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $getwalletinfo['unconfirmed_balance']; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Immature Balance</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $getwalletinfo['immature_balance']; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Number Transactions</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $getwalletinfo['txcount']; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Set Fee</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $getwalletinfo['paytxfee']; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Version</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $getwalletinfo['walletversion']; ?>
        </div>
    </div>
</div>