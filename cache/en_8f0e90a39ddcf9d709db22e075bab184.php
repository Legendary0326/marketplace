<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/item-overview.php***/
if (!$User->hasRole('staff') && !$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
$Categories = Categories::getCategories();
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-6 col-md-8 text-info mb-2">
            <h3>Overview Of All Items...</h3>
        </div>
    </div>
</div>

<form action="/item-overview" method="post">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-4">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" placeholder="Searchterm" name="Searchterm"<?php echo Forms::value('Searchterm'); ?>>
                </div>
            </div>
            <div class="col-md-8">
                <button class="btn-sm btn-secondary" type="submit">Search</button>
            </div>
        </div>
    </div>
</form>

<div class="container mt-2">
    <div class="row py-1">
        <div class="col-md-12 overflow-auto">
            <table class="table-sm table-bordered-standard w-100">
                <tr>
                    <th scope="col" class="text-center">Item Nr.</th>
                    <th scope="col" class="text-center">Vendor</th>
                    <th scope="col" class="text-center">Item</th>
                    <th scope="col" class="text-center">Category</th>
                    <th scope="col" class="text-center">Stock</th>
                    <th scope="col" class="text-center">Status</th>
                    <th scope="col" class="text-center">Edit</th>
                </tr>
<?php
$SQL = 'SELECT ItemID, Vendor, (SELECT Username FROM user WHERE Vendor = UserID) AS VendorName, Name, Category, Quantity, Active FROM items';
if (Forms::isPost() && isset($_POST['Searchterm']) && !empty($_POST['Searchterm'])) {
    $Searchterm = trim($_POST['Searchterm']);
    $Where = ['Name LIKE ' . $DB->string('%' . $Searchterm . '%')];
    $Where[] = '(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM categories WHERE Cat0 = CategoryID) LIKE ' . $DB->string('%' . $Searchterm . '%');
    $Where[] = '(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM categories WHERE Cat1 = CategoryID) LIKE ' . $DB->string('%' . $Searchterm . '%');
    $Where[] = '(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM categories WHERE Cat2 = CategoryID) LIKE ' . $DB->string('%' . $Searchterm . '%');
    if (preg_match('/^[a-z0-9]{4,32}$/i', $Searchterm)) {
        $Where[] = '(SELECT Username FROM user WHERE Vendor = UserID) LIKE ' . $DB->string('%' . $Searchterm . '%');
    }
    if (preg_match('/^\d+$/i', $Searchterm)) {
        $Where[] = 'ItemID = ' . $DB->int($Searchterm);
    }
    if (preg_match('/^\d+$/i', $Searchterm)) {
        $Where[] = 'Quantity = ' . $DB->int($Searchterm);
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Unlimited'))) {
        $Where[] = 'Quantity IS NULL';
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Active'))) {
        $Where[] = 'Active = 1';
    }
    if (strtolower($Searchterm) == strtolower($Language->translate('Inactive'))) {
        $Where[] = 'Active = 0';
    }
    $SQL .= ' WHERE ' . implode(' OR ', $Where);
}
$SQL .= ' ORDER BY Created';
$Items = $DB->query($SQL);
foreach ($Items as $Item) {
    echo '<tr>' . nl;
    echo '<td class="text-center text-nowrap">' . Market::ItemID($Item['ItemID']) . '</td>' . nl;
    echo '<td class="text-center text-nowrap"><a class="text-primary" href="/account/' . $Item['Vendor'] . '" class="btn btn-link">' . htmlentities(ucfirst($Item['VendorName'])) . '</a></td>' . nl;
    echo '<td class="text-center text-nowrap">' . htmlentities(substr($Item['Name'], 0, 25)) . '</td>' . nl;
    $CategoryTree = Categories::getCategoryTree($Item['Category']);
    echo '<td class="text-center text-nowrap">';
    foreach ($CategoryTree as $i => $Category) {
        if ($i != 0) echo ' / ';
        echo $Category['Name'];
    }
    echo '</td>' . nl;
    echo '<td class="text-center">' . ($Item['Quantity'] ?? 'Unlimited') . '</td>' . nl;
    echo '<td class="text-center">' . ($Item['Active'] ? 'Active' : 'Inactive') . '</td>' . nl;
    echo '<td class="text-center"><a class="btn-sm btn-primary" href="/item-edit/' . $Item['ItemID'] . '" role="button">Edit</a></td>' . nl;
    echo '</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
