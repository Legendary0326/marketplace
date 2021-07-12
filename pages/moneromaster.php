<?php
if (!$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
?>
<!-- subMenu on subpage -->
<div class="container mt-4">
    <div class="row py-1" id="subMenu">
        <div class="col-md-6 px-2" id="borderPageHeader">
            <a class="text-white" href="bitcoinmaster" class="btn btn-link"><lang>Bitcoin Masterwallet</lang></a>
        </div>
        <div class="col-md-6 px-2" id="" style="display: ">
            <a class="text-white" href="litecoinmaster" class="btn btn-link"><lang>Litecoin Masterwallet</lang></a>
        </div>
    </div>
</div>

<?php
$get_balance = XMR::raw('get_balance', ['account_index' => 0]);
?>

<div class="container mt-4">
    <div class="row py-1 mt-3">
        <div class="col-md-12 text-info">
               <h3><lang>Monero Masterwallet</lang>...</h3>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Name</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo XMR_LABEL; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Address</lang>:
        </div>
        <div class="col-auto col-md-4" style="max-width: 60%; word-wrap: break-word;">
            <?php echo $get_balance['per_subaddress']['0']['address']; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Balance</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $get_balance['balance']; ?>
        </div>
    </div>

    <div class="row py-1">
        <div class="col-6 col-md-4 text-muted text-nowrap light">
            <lang>Wallet Unlocked Balance</lang>:
        </div>
        <div class="col-auto col-md-4 text-nowrap">
            <?php echo $get_balance['unlocked_balance']; ?>
        </div>
    </div>
</div>