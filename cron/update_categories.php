<?php
if (!defined('CRON_INCLUDED')) die('Permission denied');

$DB->query('UPDATE categories SET NumCat = (SELECT COUNT(*) FROM items WHERE categories.CategoryID = items.Category AND Active = 1 AND Blocked = 0 AND (SELECT BlockTransactions FROM user WHERE UserID = Vendor) = 0 AND (SELECT OnHoliday FROM user WHERE UserID = Vendor) = 0)');
numCategories();

function numCategories($start = null)
{
    global $DB;
    $Categories = $DB->fetch_all($DB->query('SELECT CategoryID, NumCat, NumAll FROM categories WHERE Parent ' . (is_null($start) ? 'IS NULL' : '= ' . $DB->int($start))), MYSQLI_ASSOC);
    $Count = 0;
    if (count($Categories) >= 1) {
        foreach ($Categories as $Category) {
            $Sub = numCategories($Category['CategoryID']);
            $Count += $Category['NumCat'] + $Sub;
            $DB->update('categories', ['NumAll' => $Category['NumCat'] + $Sub], 'CategoryID = ' . $DB->int($Category['CategoryID']));
        }
    }
    return $Count;
}
