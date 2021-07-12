<?php /***REALFILE: /var/www/vhosts/market1304.de/httpdocs/pages/image.php***/
$Paths = $Pages->getPath();
if (isset($Paths[0]) && !empty($Paths[0]) && preg_match('/^[0-9a-f]{32}\.(png|jpg)$/', $Paths[0], $erg)) {
    if (file_exists(FILEPATH . $Paths[0])) {
        if ($erg[1] == 'png') {
            header('Content-Type: image/png');
        } else if ($erg[1] == 'jpg') {
            header('Content-Type: image/jpeg');
        }
        readfile(FILEPATH . $Paths[0]);
    }
    exit;
} else {
    echo 'Permission denied!';
}