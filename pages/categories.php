<?php
if (!$User->hasRole('admin')) $Pages->redirect('');
$HTMLHead->addNav('cat_search');
$Paths = $Pages->getPath();
$CatID = $Paths[0] ?? false;
$CatID = intval($CatID);
if ($CatID == 0) $CatID = false;
$AllCategories = Categories::getCategories();
$Layer = 0;

if (Forms::isPost() && isset($_POST['New'])) {
    $DB->insert('categories', ['Parent' => ($CatID === false ? null : $CatID), 'en' => trim($_POST['New'])]);
    $Pages->redirect('categories/' . $DB->lastInsertId() . '/created');
} else if ($Paths[1] ?? false === 'delete') {
    $Delete = intval($Paths[2] ?? 0);
    if ($Delete >= 1) {
        $DB->delete('categories', 'CategoryID = ' . $Delete);
    }
    $Pages->redirect('categories/' . $CatID);
}
?>
<div class="container mt-2">
    <div class="row py-1">
        <div class="col-12 col-md-12 text-info mb-2">
            <h3><lang>Categories Edit</lang>...</h3>
        </div>
    </div>
</div>
<?php
if ($CatID !== false) {
    $Category = $DB->getOne('categories', 'CategoryID = ' . $DB->int($CatID));
    if (count($Category) >= 1) {
        $Layer = $AllCategories[$CatID]['Layer'] + 1;
        if (Forms::isPost() && isset($_POST['Edit'])) {
            $DB->update('categories', ['en' => trim($_POST['Edit'])], 'CategoryID = ' . $DB->int($CatID));
            $Pages->redirect('categories/' . $Category['CategoryID'] . '/ok');
        } else if (isset($Paths[1]) && $Paths[1] == 'ok') {
            echo Alerts::success('<lang>Category Successfully Edited.</lang>', 'mt-3');
        }
?>
<div class="container mt-2">
    <div class="row py-1">
        <div class="col-auto col-md-8 font-weight-light text-muted mb-2">
<?php
if ($Layer == 2) {
    echo '<h5><lang>Edit the Subcategory</lang>:</h5>' . nl;
} else if ($Layer == 3) {
    echo '<h5><lang>Edit the Childcategory</lang>:</h5>' . nl;
} else {
    echo '<h5><lang>Edit the Category</lang>:</h5>' . nl;
}
?>
        </div>
    </div>
</div>

<form action="/categories/<?php echo $Category['CategoryID']; ?>" method="post">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-md-4">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="Edit" value="<?php echo htmlentities($Category['en']); ?>">
                </div>
            </div>
            <div class="col-md-8">
                <button class="btn-sm btn-primary ml-1" type="submit"><lang>Change</lang></button>
            </div>
        </div>
    </div>
</form>
<?php
    }
}

if ($CatID === false) {
    $Categories = $DB->get('categories', 'Parent IS NULL');
} else {
    $Categories = $DB->get('categories', 'Parent = ' . $DB->int($CatID));
}
if ($Layer >= 3) return;
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-auto col-md-8 font-weight-light text-muted mb-2">
<?php
if ($Layer == 1) {
    echo '<span><h5><lang>Create a New Subcategory</lang>:</h5></span>' . nl;
} else if ($Layer == 2) {
    echo '<span><h5><lang>Create a New Childcategory</lang>:</h5></span>' . nl;
} else {
    echo '<span><h5><lang>Create a New Category</lang>:</h5></span>' . nl;
}
?>
        </div>
    </div>
</div>

<form action="/categories/<?php echo $CatID; ?>" method="post">
    <div class="container mt-2">
        <div class="row py-1">
            <div class="col-7 col-md-4">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="New">
                </div>
            </div>
            <div class="col-5 col-md-2">
                <button class="btn-sm btn-primary" type="submit"><lang>Create</lang></button>
            </div>
        </div>
    </div>
</form>
<?php
if (count($Categories) == 0) return;
?>
<div class="container mt-3">
    <div class="row py-1">
        <div class="col-12 col-md-12">
            <div class="input-group mb-2">
                <span class="text-warning"><lang>Attention! If you delete a category/subcategory, the associated subcategories/childcategories will also be deleted!</lang></span>
            </div>
        </div>
    </div>
</div>
<div class="container mt-2">
    <div class="row py-1">
        <div class="col-sm-12 col-md-8 overflow-auto">
            <table class="table-sm table-bordered-standard w-75">
                <tr>
                    <th scope="col" class="text-left w-75"><lang>Category</lang></th>
                    <th scope="col" class="text-center w-25"><lang>Edit</lang></th>
                </tr>
<?php
foreach ($Categories as $Category) {
    echo '<tr>
<td class="text-left text-nowrap">' . htmlentities($Category['en']) . '</td>
<td class="text-center"><a class="btn-sm btn-primary" href="/categories/' . $Category['CategoryID'] . '" role="button"><lang>Edit</lang></a> <a class="btn-sm btn-danger" href="/categories/' . $CatID . '/delete/' . $Category['CategoryID'] . '" role="button"><lang>Delete</lang></a></td>
</tr>' . nl;
}
?>
            </table>
        </div>
    </div>
</div>
