<?php
class Categories
{
    private static $cache = [];

    public static function getCategories()
    {
        global $DB;
        if (isset(self::$cache['getCategories'])) return self::$cache['getCategories'];
        self::$cache['getCategories'] = [];
        self::getCategoriesHelper();
        return self::$cache['getCategories'];
    }

    private static function getCategoriesHelper($start = null, $layer = 0)
    {
        global $DB;
        global $Language;
        $categories = $DB->fetch_all($DB->query('SELECT CategoryID, NumAll AS Num, IFNULL(' . $DB->field($Language->getLanguage()) . ', en) AS Name FROM categories WHERE Parent ' . (is_null($start) ? 'IS NULL' : '= ' . $DB->int($start)) .' ORDER BY Name'), MYSQLI_ASSOC);
        if (count($categories) >= 1) {
            foreach ($categories as $category) {
                self::$cache['getCategories'][$category['CategoryID']] = ['Name' => $category['Name'], 'HTML' => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $layer) . ($layer >= 1 ? '- ' : '') . $category['Name'], 'Layer' => $layer, 'Num' => $category['Num']];
                self::getCategoriesHelper($category['CategoryID'], $layer + 1);
            }
        }
    }

    public static function getCategoryTree($CategoryID)
    {
        $Tree = [];
        self::getCategoryTreeHelper($CategoryID, $Tree);
        return $Tree;
    }

    private static function getCategoryTreeHelper($CategoryID, &$Tree)
    {
        $Category = self::getCategory($CategoryID);
        if (isset($Category['Parent']) && !is_null($Category['Parent'])) {
            self::getCategoryTreeHelper($Category['Parent'], $Tree);
        }
        $Tree[] = $Category;
    }

    public static function getCategory($CategoryID)
    {
        global $DB;
        global $Language;
        return $DB->fetch_assoc($DB->query('SELECT CategoryID, Parent, NumAll AS Num, IFNULL(' . $DB->field($Language->getLanguage()) . ', en) AS Name FROM categories WHERE CategoryID = ' . $DB->int($CategoryID)));
    }

    public static function numAllCategories()
    {
        global $DB;
        $Result = $DB->query('SELECT SUM(NumAll) AS NumAll FROM categories WHERE Parent IS NULL');
        if (is_null($Result)) return 0;
        $NumAll = $DB->fetch_assoc($Result);
        return $NumAll['NumAll'];
    }
}