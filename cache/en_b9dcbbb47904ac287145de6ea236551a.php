<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/help.php***/
$Paths = $Pages->getPath();
$Lang = $Language->getLanguage();
if (file_exists(PAGEPATH . 'help.' . $Lang . '.php')) {
    include PAGEPATH . 'help.' . $Lang . '.php';
} else if (file_exists(PAGEPATH . 'help.en.php')) {
    include PAGEPATH . 'help.en.php';
} else {
    die('Error: No file found.');
}