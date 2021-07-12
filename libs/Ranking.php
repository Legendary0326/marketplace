<?php
class Ranking
{
    private static $Cache = [];

    static function getVendorRank($RankID)
    {
        if (array_key_exists('getVendorRank', self::$Cache)) return self::$Cache['getVendorRank'][$RankID];
        global $DB;
        global $Language;
        $RanksDB = $DB->get('vendor_ranks');
        $Ranks = [];
        foreach ($RanksDB as $Rank) {
            $Ranks[$Rank['RankID']] = ($Rank[$Language->getLanguage()] ?? $Rank['en']);
        }
        self::$Cache['getVendorRank'] = $Ranks;
        return $Ranks[$RankID];
    }

    static function getUserRank($RankID)
    {
        if (array_key_exists('getUserRank', self::$Cache)) return self::$Cache['getUserRank'][$RankID];
        global $DB;
        global $Language;
        $RanksDB = $DB->get('user_ranks');
        $Ranks = [];
        foreach ($RanksDB as $Rank) {
            $Ranks[$Rank['RankID']] = ($Rank[$Language->getLanguage()] ?? $Rank['en']);
        }
        self::$Cache['getUserRank'] = $Ranks;
        return $Ranks[$RankID];
    }

    static function addScore($UserID, $Amount)
    {
        global $DB;
        $DB->query('UPDATE user SET Scoring = LEAST(100, GREATEST(0, Scoring + ' . $DB->float($Amount) . ')) WHERE UserID = ' . $DB->int($UserID));
        self::recalculateRanks($UserID);
    }

    static function subScore($UserID, $Amount)
    {
        global $DB;
        $DB->query('UPDATE user SET Scoring = LEAST(100, GREATEST(0, Scoring - ' . $DB->float($Amount) . ')) WHERE UserID = ' . $DB->int($UserID));
        self::recalculateRanks($UserID);
    }

    static function recalculateRanks($UserID)
    {
        global $DB;
        $UserRank = 1;
        $VendorRank = 1;
        $User = $DB->getOne('user', 'UserID = ' . $DB->int($UserID));
        if (is_array($User) && count($User) >= 1) {
            if ($User['Role'] == 'staff' || $User['Role'] == 'admin') {
                $URanks = $DB->getOne('user_ranks', '1 ORDER BY RankID DESC LIMIT 1');
                $VRanks = $DB->getOne('vendor_ranks', '1 ORDER BY RankID DESC LIMIT 1');
                $DB->update('user', ['Scoring' => 100, 'UserRank' => $URanks['RankID'], 'VendorRank' => $VRanks['RankID']], 'UserID = ' . $DB->int($UserID));
                return;
            }
            if ($User['Orders'] == 0) {
                $UserRank = 1;
            } else if ($User['Orders'] == 1) {
                $UserRank = 2;
            } else {
                $Rank = $DB->getOne('user_ranks', 'StartWith <= ' . $DB->float($User['Scoring']) . ' ORDER BY StartWith DESC LIMIT 1');
                $UserRank = $Rank['RankID'];
            }
            $Rank = $DB->getOne('vendor_ranks', 'StartWith <= ' . $DB->float($User['Scoring']) . ' ORDER BY StartWith DESC LIMIT 1');
            $VendorRank = $Rank['RankID'];
            $DB->update('user', ['UserRank' => $UserRank, 'VendorRank' => $VendorRank], 'UserID = ' . $DB->int($UserID));
        }
    }
}