<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'qWuW41s3hxsZGN3Fvwu0xY3BQ8E22kTG') die('Permission denied');

if (!defined('nl')) define('nl', "\r\n");
require_once '../config.inc.php';
require_once '../libs/MySQLiExt.php';
$DB = new MySQLiExt(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
$DB->logErrors();

if (isset($_GET['cron']) && preg_match('/^[a-zA-Z0-9_\-]+$/', $_GET['cron'])) {
    if (file_exists($_GET['cron'] . '.php')) {
        define('CRON_INCLUDED', true);
        require $_GET['cron'] . '.php';
    }
}
