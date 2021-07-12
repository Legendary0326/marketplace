<?php /***REALFILE: D:\xampp\htdocs\marketplace/nav/cat_search.php***/
$VendorShop = (int) (isset(($Pages->getPath())[0]) ? ($Pages->getPath())[0] : 0);
if (empty($VendorShop)) {
    $VendorShop = false;
} else {
    $VendorShop = $User->getByUserID('UserID', $VendorShop);
    if (empty($VendorShop) || $User->getByUserID('Role', $VendorShop) != 'vendor') {
        $VendorShop = false;
    }
}
$Market = new Market($VendorShop);
?><style>
#catShowHide_down + label::before { content: "All Categories"; }
#catShowHide_down:checked + label::before { content: "All Categories"; }
#searchShowHide_down + label::before { content: "Search Options ▼"; }
#searchShowHide_down:checked + label::before { content: "Search Options ▲"; }
</style>


<form action="/marketplace<?php if ($VendorShop !== false) echo '/' . $VendorShop; ?>?search" method="post">
    <div class="container">
        <div class="row">
            <div class="col-md-5 mt-1 pt-1 ml-1 mr-4">
                <input type="checkbox" id="catShowHide_down">
                <label for="catShowHide_down"><span style="margin-left: 25px; color: #007bff;">(<?php echo Categories::numAllCategories(); ?>)</span></label>
                <aside id="cat_down">
<?php
$Categories = Categories::getCategories();
echo '<div><a style="color: #495057; margin-left: -1.8%;" href="/marketplace?setCat=ALL"><span>All Categories</span><span style="margin-left: 25px; color: #007bff;">(' . Categories::numAllCategories() . ')</span></a></div><br>' . nl;
foreach ($Categories as $CategoryID => $Category) {
    echo '<div';
    if ($Category['Layer'] == 1) {
        echo ' class="ml-2"';
    } else if ($Category['Layer'] == 2) {
        echo ' class="ml-4"';
    }
    echo '><a style="color: #495057;" href="/marketplace?setCat=' . $CategoryID . '">' . htmlentities($Category['Name']) . '<span style="margin-left: 25px; color: #';
    if ($Category['Layer'] == 1) {
        echo 'ff4000';
    } else if ($Category['Layer'] == 2) {
        echo 'ffbf00';
    } else {
        echo 'bf0041';
    }
    echo ';">(' . $Category['Num'] . ')</span></a></div><br>' . nl;
}
?>
                </aside>
            </div>
            <div class="col-md-6 p-1">
                <div class="input-group" style="margin-bottom: -20px;">
                    <input type="text" class="form-control" placeholder="Searchterm" name="searchterm" value="<?php echo $Market->get('searchterm'); ?>">
                    <div class="input-group-append">
                        <button class="btn-sm btn-secondary ml-2" type="submit">Search</button>
                    </div>
                </div>
                <br>
                <input type="checkbox" id="searchShowHide_down">
                <label for="searchShowHide_down"></label>
                <aside id="search_down" style="margin-top: -20px; line-height: 80%;">
                    <div class="row" style="cursor: auto;">
                        <br>
                        <br>
                        <div>Category:</div>
                        <br>
                        <div class="col-md-6 p-1 pr-3">
                            <select class="form-control" name="category">
                            <option value="">All Categories</option>
<?php
foreach ($Categories as $CategoryID => $Category) {
    echo '<option value="' . $CategoryID . '"' . Forms::selectedVal($Market->get('category'), $CategoryID) . '>' . $Category['HTML'] . '</option>' . nl;
}
?>
                            </select>
                        </div>
                        <br>
                        <div>Price Range:</div>
                        <br>
                        <br>
                        <div class="form-group">
                            <span class="float-left pt-2">From&nbsp;</span><input type="text" class="form-control w-25 float-left" placeholder="0.00" name="priceFrom" value="<?php echo (empty($Market->get('priceFrom')) ? '' : $Language->number($Market->get('priceFrom'), 2)); ?>"><span class="float-left pt-2">&nbsp;Till&nbsp; </span><input type="text" class="form-control w-25 float-left" placeholder="9999.99" name="priceTill" value="<?php echo (empty($Market->get('priceTill')) ? '' : $Language->number($Market->get('priceTill'), 2)); ?>"><span class="float-left pt-2">&nbsp;<?php echo $User->get('Currency'); ?></span>
                        </div>
                        <br>
                        <br>
                        <br>
                        <br>
                        Minimum Vendor Status:
                        <br>
                        <br>
                        <div>
                            <select class="form-control" name="vendorRank">
<?php
$Ranks = Status::getVendorRanks();
foreach ($Ranks as $RankID => $Rank) {
    echo '<option value="' . $RankID . '"' . Forms::selectedVal($Market->get('vendorRank'), $RankID) . '>' . $Rank['Name'] . '</option>' . nl;
}
?>
                            </select>
                        </div>
                        <br>
                        Country of Dispatch:
                        <br>
                        <br>
                        <div>
                        <select class="form-control" name="shipFrom">
                            <option value="">All Countries</option>
<?php
$Countries = $Language->getCountries();
foreach ($Countries as $CountryID => $Country) {
    echo '<option value="' . $CountryID . '"' . Forms::selectedVal($Market->get('shipFrom'), $CountryID) . '>' . $Country . '</option>' . nl;
}
?>
                        </select>
                        </div>
                        <br>
                        Ships To:
                        <br>
                        <br>
                        <div>
                        <select class="form-control" name="shipTo">
<?php
$ShipsTo = Market::getShipsTo();
foreach ($ShipsTo as $ShipsToID => $Area) {
    echo '<option value="' . $ShipsToID . '"' . Forms::selectedVal($Market->get('shipTo'), $ShipsToID) . '>' . $Area['Name'] . '</option>' . nl;
}
?>
                        </select>
                        </div>
                        <br>
                        Order By:
                        <br>
                        <br>
                        <div>
                            <select class="form-control" name="orderBy">
                                <option value="priceASC"<?php echo Forms::selectedVal($Market->get('orderBy'), 'priceASC'); ?>>Price Asc</option>
                                <option value="priceDESC"<?php echo Forms::selectedVal($Market->get('orderBy'), 'priceDESC'); ?>>Price Desc</option>
                                <option value="dateASC"<?php echo Forms::selectedVal($Market->get('orderBy'), 'dateASC'); ?>>Date Asc</option>
                                <option value="dateDESC"<?php echo Forms::selectedVal($Market->get('orderBy'), 'dateDESC'); ?>>Date Desc</option>
                            </select>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</form>