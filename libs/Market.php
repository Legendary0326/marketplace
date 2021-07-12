<?php
class Market
{
    private $Data;
    private $VendorShop;
    private static $Cache = [];

    function __construct($VendorShop = false)
    {
        $this->VendorShop = $VendorShop;
        if (!isset($_SESSION['Market'])) {
            $_SESSION['Market'] = [];
        }
        $this->Data = &$_SESSION['Market'];
        if (!isset($this->Data['page']) || $this->Data['page'] < 1) $this->set('page', 1);
        $this->set('perpage', 15);
        if ($this->get('Layer') === false) $this->set('Layer', 0);
        if ($this->get('searchSQL') === false) {
            $this->set('Layer', 0);
            $this->set('searchSQL', '(SELECT VendorRank FROM user WHERE user.UserID = items.Vendor) >= 1 AND Active = 1 AND Blocked = 0 AND (SELECT BlockTransactions FROM user WHERE UserID = Vendor) = 0 AND (SELECT OnHoliday FROM user WHERE UserID = Vendor) = 0 ORDER BY IsPromoted DESC, Price ASC, ItemID DESC');
        }
    }

    function setSearch($Options)
    {
        global $DB;
        $where = [];
        if (isset($Options['category']) && !empty($Options['category'])) {
            $this->set('category', (int) $Options['category']);
            $Tree = Categories::getCategoryTree($Options['category']);
            $Layer = count($Tree);
            $this->set('Layer', $Layer);
            $where[] = '(Cat' . ($Layer - 1) . ' = ' . $DB->int($Options['category']) . ' OR (SELECT 1 FROM items_promotes WHERE Item = ItemID AND Week = ' . $DB->int(date('Y') . str_pad(date('W'), 2, '0', STR_PAD_LEFT)) . ' AND Layer = ' . $DB->int($this->get('Layer')) . ' AND items_promotes.Category = ' . $DB->int($Options['category']) . ') = 1)';
        } else {
            $this->set('category', null);
            $this->set('Layer', 0);
        }
        if (isset($Options['searchterm']) && !empty($Options['searchterm'])) {
            $st = preg_replace('/%/', '', mb_substr(trim($Options['searchterm']), 0, 49));
            $this->set('searchterm', $st);
            $Names = '(Name LIKE ' . $DB->string('%' . $st . '%');
            if (!preg_match('/\s/', $st)) {
                $Names .= ' OR (SELECT Username FROM user WHERE UserID = Vendor) LIKE ' . $DB->string('%' . $st . '%');
            }
            $Names .= ' OR Description LIKE ' . $DB->string('%' . $st . '%') . ')';
            $where[] = $Names;
        } else {
            $this->set('searchterm', null);
        }
        if (isset($Options['priceFrom']) && !empty($Options['priceFrom']) && $Options['priceFrom'] > 0) {
            $this->set('priceFrom', guessMoney($Options['priceFrom']));
            $where[] = 'Price >= ' . $DB->float(guessMoney($Options['priceFrom']));
        } else {
            $this->set('priceFrom', null);
        }
        if (isset($Options['priceTill']) && !empty($Options['priceTill']) && $Options['priceTill'] > 0) {
            $this->set('priceTill', guessMoney($Options['priceTill']));
            $where[] = 'Price <= ' . $DB->float(guessMoney($Options['priceTill']));
        } else {
            $this->set('priceTill', null);
        }
        if (isset($Options['vendorRank']) && !empty($Options['vendorRank'])) {
            $this->set('vendorRank', (int) $Options['vendorRank']);
            $where[] = '(SELECT VendorRank FROM user WHERE user.UserID = items.Vendor) >= ' . $DB->int($Options['vendorRank']);
        } else {
            $this->set('vendorRank', null);
        }
        if (isset($Options['shipFrom']) && !empty($Options['shipFrom'])) {
            $this->set('shipFrom', (int) $Options['shipFrom']);
            $where[] = '(SELECT Location FROM user WHERE user.UserID = items.Vendor) = ' . $DB->int($Options['shipFrom']);
        } else {
            $this->set('shipFrom', null);
        }
        if (isset($Options['shipTo']) && !empty($Options['shipTo'])) {
            $this->set('shipTo', (int) $Options['shipTo']);
            $ShipsTo = $DB->getOne('items_shipsto', 'ShipsToID = ' . $DB->int($Options['shipTo']));
            if (isset($ShipsTo['Type'])) {
                if ($ShipsTo['Type'] == 'country') {
                    $where[] = 'ShipTo = ' . $DB->int($Options['shipTo']);
                } else if ($ShipsTo['Type'] == 'area') {
                    $regions = $DB->get('items_shipsto_relations', 'RegionIn = ' . $DB->int($Options['shipTo']));
                    if (count($regions) >= 1) {
                        $o = [];
                        foreach ($regions as $region) {
                            $o[] = 'ShipTo = ' . $DB->int($region['Region']);
                        }
                        $where[] = '(' . implode(' OR ', $o) . ')';
                    }
                }
            }
        } else {
            $this->set('shipTo', null);
        }
        $SQL = '';
        $where[] = 'Active = 1';
        $where[] = 'Blocked = 0';
        $where[] = '(SELECT BlockTransactions FROM user WHERE UserID = Vendor) = 0';
        $where[] = '(SELECT OnHoliday FROM user WHERE UserID = Vendor) = 0';
        if (count($where) >= 1) {
            $SQL = implode(' AND ', $where);
        }
        if (isset($Options['orderBy']) && array_search($Options['orderBy'], ['priceASC', 'priceDESC', 'dateASC', 'dateDESC']) !== false) {
            $this->set('orderBy', $Options['orderBy']);
            switch ($Options['orderBy']) {
                case 'priceDESC':
                    $SQL .= ' ORDER BY IsPromoted DESC, Created DESC, ItemID DESC';
                    break;
                case 'dateASC':
                    $SQL .= ' ORDER BY IsPromoted DESC, Created ASC, ItemID DESC';
                    break;
                case 'dateDESC':
                    $SQL .= ' ORDER BY IsPromoted DESC, Price DESC, ItemID DESC';
                    break;
                case 'priceASC':
                default:
                    $SQL .= ' ORDER BY IsPromoted DESC, Price ASC, ItemID DESC';
            }
        } else {
            $this->set('orderBy', null);
        }
        $this->set('page', 1);
        $this->set('searchSQL', $SQL);
    }

    function setPage($Page = 1)
    {
        $this->set('page', max(intval($Page) , 1));
        return true;
    }

    function getPageData()
    {
        global $DB;
        global $Language;
        global $Pages;

        $SQL = 'SELECT SQL_CALC_FOUND_ROWS
*,
(SELECT Username FROM user WHERE UserID = Vendor) AS VendorName,
(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM countries WHERE CountryID = (SELECT Location FROM user WHERE UserID = Vendor)) AS ShipFromName,
(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM items_shipsto WHERE ShipsToID = ShipTo) AS ShipToName,
(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM vendor_ranks WHERE RankID = (SELECT VendorRank FROM user WHERE UserID = Vendor)) AS VendorRankName,
(SELECT 1 FROM items_promotes WHERE Item = ItemID AND Week = ' . $DB->int(date('Y') . str_pad(date('W'), 2, '0', STR_PAD_LEFT)) . ' AND Layer = ' . $DB->int($this->get('Layer')) . ') AS IsPromoted,
(SELECT (SUM(IF(BuyerReview IS NULL, 0, 1)) / COUNT(*)) * 100 FROM orders WHERE Item = ItemID AND Status = ' . $DB->string('Finalized') . ') AS ItemReview
FROM items WHERE ' . ($this->VendorShop === false ? '' : 'Vendor = ' . $DB->int($this->VendorShop) . ' AND ') . $this->get('searchSQL') . '
LIMIT ' . $DB->int(($this->get('page') - 1) * $this->get('perpage')) . ', ' . $DB->int($this->get('perpage'));
        //echo $SQL;//exit;

        $result = $DB->query($SQL);
        $numItems = $DB->foundRows();
        $items = $DB->fetch_all($result, MYSQLI_ASSOC);
        $NumPages = ceil($numItems / $this->get('perpage'));
        if (count($items) == 0 && $numItems >= 1 && $this->get('page') >= 2) {
            $this->set('page', 1);
            $Pages->redirect($Pages->getPage());
        }
        if ($this->get('page') >= $NumPages) $this->set('page', $NumPages);
        $this->set('pages', $NumPages);

        return [
            'NumPages' => $NumPages,
            'Page' => $this->get('page'),
            'NumItems' => $numItems,
            'Items' => $items,
            'Pagination' => new Pagination($NumPages, $this->get('page'))
        ];
    }

    function get($Key)
    {
        if (isset($this->Data[$Key])) {
            return $this->Data[$Key];
        }
        return false;
    }

    function set($Key, $Val)
    {
        global $DB;
        $this->Data[$Key] = $Val;
        return true;
    }

    static function getShipsTo()
    {
        global $DB;
        global $Language;
        $countries = $DB->fetch_all($DB->query('SELECT ShipsToID, IFNULL(' . $DB->field($Language->getLanguage()) . ', en) AS Name, Type FROM items_shipsto ORDER BY Sort, Name'), MYSQLI_ASSOC);
        $all = [];
        if (is_array($countries) && count($countries) >= 1) {
            foreach ($countries as $country) {
                $all[$country['ShipsToID']] = ['Type' => $country['Type'], 'Name' => $country['Name']];
            }
            return $all;
        }
    }

    static function getShippings()
    {
        global $DB;
        global $Language;
        $countries = $DB->fetch_all($DB->query('SELECT ShippingsID, IFNULL(' . $DB->field($Language->getLanguage()) . ', en) AS Name FROM items_shippings ORDER BY Sort, Name'), MYSQLI_ASSOC);
        $all = [];
        $Types = ['standard', 'traceable', 'express'];
        if (is_array($countries) && count($countries) >= 1) {
            foreach ($countries as $country) {
                foreach ($Types as $Type) {
                    $all[$country['ShippingsID'] . '_' . $Type] = ['ID' => $country['ShippingsID'], 'Type' => $Type, 'Name' => $country['Name'] . ' (' . $Language->translate($Type) . ')'];
                }
            }
            return $all;
        }
    }

    static function getItemData($ItemID, $OnlySelfItems = false)
    {
        if (!is_int($ItemID)) return false;
        global $DB;
        global $Language;
        $SQL = 'SELECT *,
(SELECT Username FROM user WHERE UserID = Vendor) AS VendorName,
(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM countries WHERE CountryID = (SELECT Location FROM user WHERE UserID = Vendor)) AS ShipFromName,
(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM items_shipsto WHERE ShipsToID = ShipTo) AS ShipToName,
(SELECT IFNULL(' . $DB->field($Language->getLanguage()) . ', en) FROM vendor_ranks WHERE RankID = (SELECT VendorRank FROM user WHERE UserID = Vendor)) AS VendorRankName,
(SELECT VendorTerms FROM user WHERE UserID = Vendor) AS VendorTerms,
(SELECT VendorRefunds FROM user WHERE UserID = Vendor) AS VendorRefunds,
(SELECT AVG(Rating) FROM items_reviews WHERE SenderType = ' . $DB->string('Buyer') . ' AND Item = ItemID) As Rating
FROM items WHERE ItemID = ' . $DB->int($ItemID);
        if ($OnlySelfItems) {
            global $User;
            if (!$User->hasRole('staff') && !$User->hasRole('admin')) {
                $SQL .= ' AND Vendor = ' . $DB->int($User->getID());
            }
        } else {
            $SQL .= ' AND Active = 1 AND Blocked = 0';
        }
        //echo $SQL;
        $ItemData = $DB->fetch_assoc($DB->query($SQL));
        if (is_null($ItemData)) return false;
        $Shipping = [];
        for ($ShippingNo = 0; $ShippingNo <= 11; $ShippingNo++) {
            if (!empty($ItemData['Shipping' . $ShippingNo])) {
                $Shipping[] = unserialize($ItemData['Shipping' . $ShippingNo]);
            }
        }
        $ItemData['Shipping'] = $Shipping;
        return $ItemData;
    }

    static function ItemID($ItemID, $Count = 5)
    {
        return '# ' . str_pad($ItemID, $Count, '0', STR_PAD_LEFT);
    }

    static function OrderID($OrderID, $Count = 11)
    {
        return '# ' . str_pad($OrderID, $Count, '0', STR_PAD_LEFT);
    }

    static function getMyOrders($Mode = 'current')
    {
        global $DB;
        global $User;
        if ($Mode == 'current') {
            return $DB->fetch_all($DB->query('SELECT *, (SELECT Name FROM items WHERE ItemID = Item) AS Name FROM orders WHERE (Status = ' . $DB->string('NotYetConfirmed') . ' OR Status = ' . $DB->string('Confirmed') . ' OR Status = ' . $DB->string('Shipped') . ' OR (Status = ' . $DB->string('Finalized') . ' AND BuyerReview IS NULL)) AND Buyer = ' . $DB->int($User->getID())), MYSQLI_ASSOC);
        } else if ($Mode == 'archived') {
            return $DB->fetch_all($DB->query('SELECT *, (SELECT Name FROM items WHERE ItemID = Item) AS Name FROM orders WHERE (Status = ' . $DB->string('Canceled') . ' OR (Status = ' . $DB->string('Finalized') . ' AND BuyerReview IS NOT NULL)) AND Buyer = ' . $DB->int($User->getID())), MYSQLI_ASSOC);
        } else if ($Mode == 'disputes') {
            return $DB->fetch_all($DB->query('SELECT *, (SELECT Name FROM items WHERE ItemID = Item) AS Name FROM orders WHERE Status = ' . $DB->string('Dispute') . ' AND Buyer = ' . $DB->int($User->getID())), MYSQLI_ASSOC);
        }
    }

    static function getMyVendorOrders($Mode = 'current')
    {
        global $DB;
        global $User;
        if ($Mode == 'current') {
            return $DB->fetch_all($DB->query('SELECT *, (SELECT Name FROM items WHERE ItemID = Item) AS Name FROM orders WHERE (Status = ' . $DB->string('NotYetConfirmed') . ' OR Status = ' . $DB->string('Confirmed') . ' OR Status = ' . $DB->string('Shipped') . ' OR (Status = ' . $DB->string('Finalized') . ' AND VendorReview IS NULL)) AND Vendor = ' . $DB->int($User->getID())), MYSQLI_ASSOC);
        } else if ($Mode == 'archived') {
            return $DB->fetch_all($DB->query('SELECT *, (SELECT Name FROM items WHERE ItemID = Item) AS Name FROM orders WHERE (Status = ' . $DB->string('Canceled') . ' OR (Status = ' . $DB->string('Finalized') . ' AND VendorReview IS NOT NULL)) AND Vendor = ' . $DB->int($User->getID())), MYSQLI_ASSOC);
        } else if ($Mode == 'disputes') {
            return $DB->fetch_all($DB->query('SELECT *, (SELECT Name FROM items WHERE ItemID = Item) AS Name FROM orders WHERE Status = ' . $DB->string('Dispute') . ' AND Vendor = ' . $DB->int($User->getID())), MYSQLI_ASSOC);
        }
    }

    static function countMyVendorOrders()
    {
        global $DB;
        global $User;
        $Data = $DB->fetch_assoc($DB->query('SELECT COUNT(*) AS Num FROM orders WHERE (Status = ' . $DB->string('NotYetConfirmed') . ' OR Status = ' . $DB->string('Confirmed') . ' OR Status = ' . $DB->string('Shipped') . ') AND Vendor = ' . $DB->int($User->getID())));
        if (isset($Data['Num'])) return $Data['Num'];
        return false;
    }

    static function countMyVendorDisputes()
    {
        global $DB;
        global $User;
        $Data = $DB->fetch_assoc($DB->query('SELECT COUNT(*) AS Num FROM orders WHERE Status = ' . $DB->string('Dispute') . ' AND Vendor = ' . $DB->int($User->getID())));
        if (isset($Data['Num'])) return $Data['Num'];
        return false;
    }

    static function getButton($Status)
    {
        global $Language;
        if ($Status == 'NotYetConfirmed') {
            return '<span class="deco-btn deco-btn-secondary">&#9900;<span class="pl-2 text-nowrap">' . $Language->translate('Not Yet Confirmed') . '</lang></span></span>';
        } else if ($Status == 'Confirmed') {
            return '<span class="deco-btn deco-btn-primary">&#10003;<span class="pl-2 text-nowrap">' . $Language->translate('Confirmed') . '</lang></span></span>';
        } else if ($Status == 'Shipped') {
            return '<span class="deco-btn deco-btn-success">&#8614;<span class="pl-2 text-nowrap">' . $Language->translate('Shipped') . '</lang></span></span>';
        } else if ($Status == 'Canceled') {
            return '<span class="deco-btn deco-btn-danger">X<span class="pl-2 text-nowrap">' . $Language->translate('Canceled') . '</lang></span></span>';
        } else if ($Status == 'Finalized') {
            return '<span class="deco-btn deco-btn-dark">&#10149;<span class="pl-2 text-nowrap">' . $Language->translate('Finalized') . '</lang></span></span>';
        } else if ($Status == 'Dispute') {
            return '<span class="deco-btn deco-btn-warning">&#8800;<span class="pl-2 text-nowrap">' . $Language->translate('Dispute') . '</lang></span></span>';
        }
    }

    static function getStatusText($Status)
    {
        global $Language;
        if ($Status == 'NotYetConfirmed') {
            return '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-12"><span class="text-danger"><h4><lang>' . $Language->translate('The vendor will now check your order!') . '</lang></h4></span></div></div></div>' . nl;
        } else if ($Status == 'Confirmed') {
            return '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-12"><span class="text-success"><h4>' . $Language->translate('The vendor has confirmed your order! A physical item will be shipped within 72 hours, a digital item will be delivered to you within 24 hours!') . '</h4></span></div></div></div>' . nl;
        } else if ($Status == 'Shipped') {
            return '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-12"><span class="text-success"><h4>' . $Language->translate('The vendor has shipped or delivered your item!') . '</h4></span></div></div></div>' . nl;
        } else if ($Status == 'Canceled') {
            return '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-12"><span class="text-danger"><h4>' . $Language->translate('The vendor canceled your order!') . '</h4></span></div></div></div>' . nl . '<div class="container mt-0"><div class="row py-1"><div class="col-12 col-md-12"><span class="small">' . $Language->translate('The vendor can have different reasons for canceling your order. It is possible that the item you have ordered is currently not available or the vendor is rejecting deliveries to the address/country you provided! Please contact the vendor if you have further questions!') . '</span></div></div></div>' . nl;
        } else if ($Status == 'Finalized') {
            return '<div class="container mt-3"><div class="row py-1"><div class="col-12 col-md-12"><span class="text-success"><h4>' . $Language->translate('Thank you for your purchase!') . '</h4></span></div></div></div>' . nl;
        } else if ($Status == 'Dispute') {
            return '' . nl;
        }
    }

    static function getOrderData($OrderID)
    {
        if (!is_int($OrderID)) return false;
        global $DB;
        global $User;
        $SQL = 'SELECT * FROM orders WHERE OrderID = ' . $DB->int($OrderID);
        if (!$User->hasRole('staff') && !$User->hasRole('admin')) {
            $SQL .= ' AND (Buyer = ' . $DB->int($User->getID()) . ' OR Vendor = ' . $DB->int($User->getID()) . ')';
        }
        return $DB->fetch_assoc($DB->query($SQL));
    }

    static function getReviewData($ReviewID)
    {
        if (!is_int($ReviewID)) return false;
        global $DB;
        return self::processReviewData($DB->fetch_assoc($DB->query('SELECT *, (SELECT Username FROM user WHERE UserID = Sender) AS Name FROM items_reviews WHERE ReviewID = ' . $DB->int($ReviewID))));
    }

    static function transfer($Amount, $Currency, $Sender, $Recipient)
    {
        global $DB;
        $NewTransaction = [];
        $NewTransaction['Date'] = time();
        $NewTransaction['Sender'] = $Sender;
        $NewTransaction['Recipient'] = $Recipient;
        $NewTransaction['Currency'] = $Currency;
        $NewTransaction['Amount'] = $Amount;
        $NewTransaction['USD'] = Currencies::exchange($Amount, $Currency, 'USD', false);
        if (preg_match('/^\d+$/', $Sender)) {
            $DB->query('UPDATE user SET ' . $DB->field($Currency) . ' = ' . $DB->field($Currency) . ' - ' . $DB->float($Amount) . ' WHERE UserID = ' . $DB->int($Sender));
        } else {
            $DB->query('UPDATE system_accounts SET ' . $DB->field($Currency) . ' = ' . $DB->field($Currency) . ' - ' . $DB->float($Amount) . ' WHERE SAccountID = ' . $DB->string($Sender));
        }
        if (preg_match('/^\d+$/', $Recipient)) {
            $DB->query('UPDATE user SET ' . $DB->field($Currency) . ' = ' . $DB->field($Currency) . ' + ' . $DB->float($Amount) . ' WHERE UserID = ' . $DB->int($Recipient));
        } else {
            $DB->query('UPDATE system_accounts SET ' . $DB->field($Currency) . ' = ' . $DB->field($Currency) . ' + ' . $DB->float($Amount) . ' WHERE SAccountID = ' . $DB->string($Recipient));
        }
        $DB->insert('transactions', $NewTransaction);
        return $DB->lastInsertId();
    }

    static function getDisputes($OrderID)
    {
        global $DB;
        return $DB->fetch_all($DB->query('SELECT *, (SELECT Username FROM user WHERE UserID = User) AS Name FROM orders_disputes WHERE OrderID = ' . $DB->int($OrderID) . ' ORDER BY Created DESC'), MYSQLI_ASSOC);
    }

    static function getReviewOverviewVendor($VendorID)
    {
        global $DB;
        $return = [];
        $Total = $DB->fetch_all($DB->query('SELECT RatingType, COUNT(*) AS Num FROM items_reviews WHERE SenderType = ' . $DB->string('Buyer') . ' AND Recipient = ' . $DB->int($VendorID) . ' GROUP BY RatingType'), MYSQLI_ASSOC);
        foreach ($Total as $Type) {
            $return['Total'][$Type['RatingType']] = $Type['Num'];
        }
        $Months = $DB->fetch_all($DB->query('SELECT RatingType, COUNT(*) AS Num FROM items_reviews WHERE SenderType = ' . $DB->string('Buyer') . ' AND Recipient = ' . $DB->int($VendorID) . ' AND Created >= ' . $DB->int(time() - 15552000) . ' GROUP BY RatingType'), MYSQLI_ASSOC);
        foreach ($Months as $Type) {
            $return['6Months'][$Type['RatingType']] = $Type['Num'];
        }
        $Days = $DB->fetch_all($DB->query('SELECT RatingType, COUNT(*) AS Num FROM items_reviews WHERE SenderType = ' . $DB->string('Buyer') . ' AND Recipient = ' . $DB->int($VendorID) . ' AND Created >= ' . $DB->int(time() - 2592000) . ' GROUP BY RatingType'), MYSQLI_ASSOC);
        foreach ($Days as $Type) {
            $return['30Days'][$Type['RatingType']] = $Type['Num'];
        }
        return $return;
    }

    static function getReviewOverviewUser($UserID)
    {
        global $DB;
        $return = [];
        $Total = $DB->fetch_all($DB->query('SELECT RatingType, COUNT(*) AS Num FROM items_reviews WHERE SenderType = ' . $DB->string('Vendor') . ' AND Recipient = ' . $DB->int($UserID) . ' GROUP BY RatingType'), MYSQLI_ASSOC);
        foreach ($Total as $Type) {
            $return['Total'][$Type['RatingType']] = $Type['Num'];
        }
        $Months = $DB->fetch_all($DB->query('SELECT RatingType, COUNT(*) AS Num FROM items_reviews WHERE SenderType = ' . $DB->string('Vendor') . ' AND Recipient = ' . $DB->int($UserID) . ' AND Created >= ' . $DB->int(time() - 15552000) . ' GROUP BY RatingType'), MYSQLI_ASSOC);
        foreach ($Months as $Type) {
            $return['6Months'][$Type['RatingType']] = $Type['Num'];
        }
        $Days = $DB->fetch_all($DB->query('SELECT RatingType, COUNT(*) AS Num FROM items_reviews WHERE SenderType = ' . $DB->string('Vendor') . ' AND Recipient = ' . $DB->int($UserID) . ' AND Created >= ' . $DB->int(time() - 2592000) . ' GROUP BY RatingType'), MYSQLI_ASSOC);
        foreach ($Days as $Type) {
            $return['30Days'][$Type['RatingType']] = $Type['Num'];
        }
        return $return;
    }

    static function getLastReviewsVendor($VendorID, $AddLeft = false)
    {
        global $DB;
        $return = [];
        $Positive = $DB->fetch_all($DB->query('SELECT ReviewID, items_reviews.Created AS Created, items.Name AS Itemname, Rating, (SELECT Username FROM user WHERE UserID = Sender) AS Name, Feedback FROM items_reviews LEFT JOIN items ON Item = ItemID WHERE SenderType = ' . $DB->string('Buyer') . ' AND RatingType = ' . $DB->string('Positive') . ' AND Recipient = ' . $DB->int($VendorID) . ' AND items_reviews.Created >= ' . $DB->int(time() - 2592000) . ' ORDER BY items_reviews.Created DESC LIMIT 10'), MYSQLI_ASSOC);
        foreach ($Positive as $Review) {
            $return['Positive'][] = self::processReviewData($Review);
        }
        $Neutral = $DB->fetch_all($DB->query('SELECT ReviewID, items_reviews.Created AS Created, items.Name AS Itemname, Rating, (SELECT Username FROM user WHERE UserID = Sender) AS Name, Feedback FROM items_reviews LEFT JOIN items ON Item = ItemID WHERE SenderType = ' . $DB->string('Buyer') . ' AND RatingType = ' . $DB->string('Neutral') . ' AND Recipient = ' . $DB->int($VendorID) . ' AND items_reviews.Created >= ' . $DB->int(time() - 2592000) . ' ORDER BY items_reviews.Created DESC LIMIT 10'), MYSQLI_ASSOC);
        foreach ($Neutral as $Review) {
            $return['Neutral'][] = self::processReviewData($Review);
        }
        $Negative = $DB->fetch_all($DB->query('SELECT ReviewID, items_reviews.Created AS Created, items.Name AS Itemname, Rating, (SELECT Username FROM user WHERE UserID = Sender) AS Name, Feedback FROM items_reviews LEFT JOIN items ON Item = ItemID WHERE SenderType = ' . $DB->string('Buyer') . ' AND RatingType = ' . $DB->string('Negative') . ' AND Recipient = ' . $DB->int($VendorID) . ' AND items_reviews.Created >= ' . $DB->int(time() - 2592000) . ' ORDER BY items_reviews.Created DESC LIMIT 10'), MYSQLI_ASSOC);
        foreach ($Negative as $Review) {
            $return['Negative'][] = self::processReviewData($Review);
        }
        if ($AddLeft) {
            $Left = $DB->fetch_all($DB->query('SELECT ReviewID, items_reviews.Created AS Created, items.Name AS Itemname, Rating, (SELECT Username FROM user WHERE UserID = Sender) AS Name, Feedback FROM items_reviews LEFT JOIN items ON Item = ItemID WHERE SenderType = ' . $DB->string('Vendor') . ' AND Sender = ' . $DB->int($VendorID) . ' AND items_reviews.Created >= ' . $DB->int(time() - 2592000) . ' ORDER BY items_reviews.Created DESC LIMIT 10'), MYSQLI_ASSOC);
            foreach ($Left as $Review) {
                $return['Left'][] = self::processReviewData($Review);
            }
        }
        return $return;
    }

    static function getLastReviewsUser($UserID, $AddLeft = false)
    {
        global $DB;
        $return = [];
        $Positive = $DB->fetch_all($DB->query('SELECT ReviewID, items_reviews.Created AS Created, items.Name AS Itemname, Rating, (SELECT Username FROM user WHERE UserID = Sender) AS Name, Feedback FROM items_reviews LEFT JOIN items ON Item = ItemID WHERE SenderType = ' . $DB->string('Vendor') . ' AND RatingType = ' . $DB->string('Positive') . ' AND Recipient = ' . $DB->int($UserID) . ' AND items_reviews.Created >= ' . $DB->int(time() - 2592000) . ' ORDER BY items_reviews.Created DESC LIMIT 10'), MYSQLI_ASSOC);
        foreach ($Positive as $Review) {
            $return['Positive'][] = self::processReviewData($Review);
        }
        $Neutral = $DB->fetch_all($DB->query('SELECT ReviewID, items_reviews.Created AS Created, items.Name AS Itemname, Rating, (SELECT Username FROM user WHERE UserID = Sender) AS Name, Feedback FROM items_reviews LEFT JOIN items ON Item = ItemID WHERE SenderType = ' . $DB->string('Vendor') . ' AND RatingType = ' . $DB->string('Neutral') . ' AND Recipient = ' . $DB->int($UserID) . ' AND items_reviews.Created >= ' . $DB->int(time() - 2592000) . ' ORDER BY items_reviews.Created DESC LIMIT 10'), MYSQLI_ASSOC);
        foreach ($Neutral as $Review) {
            $return['Neutral'][] = self::processReviewData($Review);
        }
        $Negative = $DB->fetch_all($DB->query('SELECT ReviewID, items_reviews.Created AS Created, items.Name AS Itemname, Rating, (SELECT Username FROM user WHERE UserID = Sender) AS Name, Feedback FROM items_reviews LEFT JOIN items ON Item = ItemID WHERE SenderType = ' . $DB->string('Vendor') . ' AND RatingType = ' . $DB->string('Negative') . ' AND Recipient = ' . $DB->int($UserID) . ' AND items_reviews.Created >= ' . $DB->int(time() - 2592000) . ' ORDER BY items_reviews.Created DESC LIMIT 10'), MYSQLI_ASSOC);
        foreach ($Negative as $Review) {
            $return['Negative'][] = self::processReviewData($Review);
        }
        if ($AddLeft) {
            $Left = $DB->fetch_all($DB->query('SELECT ReviewID, items_reviews.Created AS Created, items.Name AS Itemname, Rating, (SELECT Username FROM user WHERE UserID = Sender) AS Name, Feedback FROM items_reviews LEFT JOIN items ON Item = ItemID WHERE SenderType = ' . $DB->string('Buyer') . ' AND Sender = ' . $DB->int($UserID) . ' AND items_reviews.Created >= ' . $DB->int(time() - 2592000) . ' ORDER BY items_reviews.Created DESC LIMIT 10'), MYSQLI_ASSOC);
            foreach ($Left as $Review) {
                $return['Left'][] = self::processReviewData($Review);
            }
        }
        return $return;
    }

    static function getReviewCategoriesVendor($VendorID)
    {
        global $DB;
        $return = $DB->fetch_assoc($DB->query('SELECT AVG(VendorService) AS Service, AVG(VendorCommunication) AS Communication, AVG(VendorRatio) AS Ratio FROM `items_reviews` WHERE SenderType = ' . $DB->string('Buyer') . ' AND Recipient = ' . $DB->int($VendorID)));
        $return['Service'] = self::processStars($return['Service'] ?? 0);
        $return['Communication'] = self::processStars($return['Communication'] ?? 0);
        $return['Ratio'] = self::processStars($return['Ratio'] ?? 0);
        return $return;
    }

    static function getReviewCategoriesUser($UserID)
    {
        global $DB;
        $return = $DB->fetch_assoc($DB->query('SELECT AVG(BuyerConfidentiality) AS Confidentiality, AVG(BuyerCommunication) AS Communication FROM `items_reviews` WHERE SenderType = ' . $DB->string('Vendor') . ' AND Recipient = ' . $DB->int($UserID)));
        $return['Confidentiality'] = self::processStars($return['Confidentiality'] ?? 0);
        $return['Communication'] = self::processStars($return['Communication'] ?? 0);
        return $return;
    }

    static function processReviewData($Data)
    {
        global $Language;
        $return = [];
        if (!is_array($Data)) return false;
        if (isset($Data['ReviewID'])) $return['ReviewID'] = $Data['ReviewID'];
        if (isset($Data['Created'])) $return['Created'] = $Language->date($Data['Created']);
        if (isset($Data['Itemname'])) $return['Itemname'] = htmlentities(strlen($Data['Itemname']) >= 21 ? substr($Data['Itemname'], 0, 20) . ' ...' : $Data['Itemname']);
        if (isset($Data['Rating'])) $return['Stars'] = self::processStars($Data['Rating']);
        if (isset($Data['Name'])) $return['Name'] = strtoupper(substr($Data['Name'], 0, 1)) . '***' . substr($Data['Name'], strlen($Data['Name']) - 1, 1);
        if (isset($Data['Feedback'])) $return['Feedback'] = htmlentities($Data['Feedback']);
        return $return;
    }

    static function processStars($Rating)
    {
        $Stars = round($Rating, 0);
        $return = '<span style="color:#FFE700">' . str_repeat('★', $Stars) . '</span>';
        if (5 - $Stars >= 1) {
            $return .= '<span style="color:#C0C0C0">' . str_repeat('★', 5 - $Stars) . '</span>';
        }
        return $return;
    }

    static function getNextWeeks()
    {
        if (array_key_exists('getNextWeeks', self::$Cache)) return self::$Cache['getNextWeeks'];
        $date = new DateTime;
        $dateM = new DateTime('December 28th');
        $Week = $date->format('W');
        $Year = $date->format('Y');
        $WeekMax = $dateM->format('W');
        $Weeks = [];
        $Weeks[$Year . str_pad($Week, 2, '0',STR_PAD_LEFT)] = $Week . '/' . $Year;
        for ($i = 1; $i <= 11; $i++) {
            $Week++;
            if ($Week > $WeekMax) {
                $Week = 1;
                $Year++;
            }
            $Weeks[$Year . str_pad($Week, 2, '0',STR_PAD_LEFT)] = $Week . '/' . $Year;
        }
        self::$Cache['getNextWeeks'] = $Weeks;
        return $Weeks;
    }

    static function getWeeksPeriod()
    {
        $Weeks = self::getNextWeeks();
        $Weeks = array_keys($Weeks);
        return ['from' => $Weeks[0], 'till' => array_pop($Weeks)];
    }

    static function getFreePromoteSlots()
    {
        global $DB;
        $Weeks = self::getNextWeeks();
        $Slots = [];
        foreach ($Weeks as $Week => $WeekName) {
            $Slots[0][$Week] = true;
            $Slots[1][$Week] = true;
            $Slots[2][$Week] = true;
            $Slots[3][$Week] = true;
            $Data = $DB->fetch_all($DB->query('SELECT Layer, COUNT(*) AS Num FROM items_promotes WHERE Week = ' . $DB->int($Week) . ' GROUP BY Layer'), MYSQLI_ASSOC);
            foreach ($Data as $Layer) {
                if ($Layer['Num'] >= 15) $Slots[$Layer['Layer']][$Week] = false;
            }
        }
        return $Slots;
    }

    static function getMyPromoteSlots($ItemID)
    {
        global $DB;
        global $User;
        $WeeksPeriod = self::getWeeksPeriod();
        $Slots = [];
        $Data = $DB->fetch_all($DB->query('SELECT * FROM items_promotes WHERE Item = ' . $DB->int($ItemID) . ' AND (SELECT Vendor FROM items WHERE ItemID = Item) = ' . $DB->int($User->getID()) . ' AND Week >= ' . $DB->int($WeeksPeriod['from']) . ' AND Week <= ' . $DB->int($WeeksPeriod['till'])), MYSQLI_ASSOC);
        foreach ($Data as $Slot) {
            $Slots[$Slot['Layer'] . '_' . $Slot['Week']] = true;
        }
        return $Slots;
    }
}