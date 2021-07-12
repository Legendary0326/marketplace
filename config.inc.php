<?php
define('MAINURI',               'http://localhost:5000/');
define('USE_SSL',               false);
define('DB_NAME',               'admin_marketplace');
define('DB_USER',               'marketplace');
define('DB_PASS',               '');
define('DB_HOST',               '127.0.0.1');
define('DB_PORT',               '3306');
define('DB_CHAR',               'utf8');
define('USER_COOKIE',           'marketplace');
define('SECRET_COOKIE_KEY',     'EeY-B[hFgcRBf1SRd6cQPk5HV%Uzcw6}'); //Must be 32 characters
define('DISABLEADMINCAPTCHA',   true); //Disable Captchas for staff and admin members
define('DEVELOPMENT',           true); //Should be always false in live mode!!!
define('REGDISABLED',           false);
define('WATCHLANGUAGE',         true); //Should be disabled in productive
define('MAINTENANCE',           false);
define('COIN_IO_KEY',           'E6330ED8-EA81-4894-8E9D-D524A48ADE93');
define('XMR_MINUSD',            3);
define('BTC_MINUSD',            5);
define('LTC_MINUSD',            3);
define('SESSION_TIMEOUT',       3000);

define('XMR_IP',                '85.214.77.194');
define('XMR_PORT',              '18085');
define('XMR_USER',              'anon_xmr');
define('XMR_PASS',              'SDvC13.04.');
define('XMR_WUSER',             'anon_xmr');
define('XMR_WPASS',             'SDvC13.04.');
define('XMR_LABEL',             'sswelcome');

define('BTC_IP',                '85.214.77.194');
define('BTC_PORT',              '8332');
define('BTC_USER',              'anon_btc');
define('BTC_PASS',              'SDvC13.04.');
define('BTC_ACCOUNT',           'btcaccount@protonmail.com');

define('LTC_IP',                '85.214.77.194');
define('LTC_PORT',              '9332');
define('LTC_USER',              'anon_ltc');
define('LTC_PASS',              'SDvC13.04.');
define('LTC_ACCOUNT',           'btcaccount@protonmail.com');
