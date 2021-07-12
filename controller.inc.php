<?php
session_start();
if (!defined('MAINPATH')) define('MAINPATH', __DIR__ . '/');
if (!defined('TMPPATH')) define('TMPPATH', __DIR__ . '/tmp/');
if (!defined('FILEPATH')) define('FILEPATH', __DIR__ . '/files/');
if (!defined('PAGEPATH')) define('PAGEPATH', __DIR__ . '/pages/');
if (defined('DEVELOPMENT') && DEVELOPMENT === true) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once 'config.inc.php';
require_once 'error.inc.php';
require_once 'libcontroller.inc.php';
require_once 'pagecontroller.inc.php';
$Libs->load([
    'MiscFunctions',
    'MySQLiExt',
    'HTMLHead',
    'HTMLFoot',
    'User',
    'Language',
    'Currencies',
    'Forms',
    'Password',
    'Captcha',
    'Alerts',
    'Categories',
    'Status',
    'Market',
    'Messages',
    'gpg',
    'Pagination',
    'BTC',
    'LTC',
    'XMR',
    'AccountSupport',
    'Ranking'
]);

require_once __DIR__ . '/libs/qrcode/qrlib.php';

$DB = new MySQLiExt(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
$DB->logErrors();
$HTMLHead = new HTMLHead();
$HTMLFoot = new HTMLFoot();
$User = new User();
$Language = new Language();

if (isset($_GET['setLanguage']) && preg_match('/^[a-z]{2}$/', $_GET['setLanguage'])) {
    $Language->setLanguage($_GET['setLanguage']);
    $Pages->reload();
}

if ($User->check_login()) {
    if ($User->check_2fa()) {
        $HTMLHead->setNav('top_customer');
        if ($Pages->getPage() == 'logout') {
            $User->logout();
            $Pages->redirect('login/logoutsuccess');
        }
        if ($Pages->getPage() == 'login') $Pages->redirect('');
        if ($Pages->getPage() == '2fa') $Pages->redirect('');
        if ($Pages->getPage() == '') {
            $Pages->load('index');
        } else {
            $Pages->load();
        }
    } else {
        $HTMLHead->setNav('top_2fa');
        if ($Pages->getPage() == 'logout') {
            $User->logout();
            $Pages->redirect('login/logoutsuccess');
        }
        if ($Pages->getPage() != '2fa') {
            $Pages->redirect('2fa');
        }
        $Pages->load('2fa');
    }
} else {
    //Not logged in
    switch ($Pages->getPage()) {
        case 'register':
        case 'login':
        case 'resetpassword':
            $Pages->load();
            break;
        default:
            $Pages->redirect('login');
    }
}
