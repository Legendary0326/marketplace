<?php
class Status
{
    static function getVendorRanks()
    {
        global $DB;
        global $Language;
        $ranks = $DB->fetch_all($DB->query('SELECT RankID, StartWith, IFNULL(' . $DB->field($Language->getLanguage()) . ', en) AS Name FROM vendor_ranks ORDER BY StartWith, Name'), MYSQLI_ASSOC);
        $all = [];
        foreach ($ranks as $rank) {
            $all[$rank['RankID']] = ['Name' => $rank['Name'], 'StartWith' => $rank['StartWith']];
        }
        return $all;
    }

    static function getUserRanks()
    {
        global $DB;
        global $Language;
        $ranks = $DB->fetch_all($DB->query('SELECT RankID, StartWith, PurchasesNeeded, IFNULL(' . $DB->field($Language->getLanguage()) . ', en) AS Name FROM user_ranks ORDER BY PurchasesNeeded, StartWith, Name'), MYSQLI_ASSOC);
        $all = [];
        foreach ($ranks as $rank) {
            $all[$rank['RankID']] = ['Name' => $rank['Name'], 'PurchasesNeeded' => $rank['PurchasesNeeded'], 'StartWith' => $rank['StartWith']];
        }
        return $all;
    }
}