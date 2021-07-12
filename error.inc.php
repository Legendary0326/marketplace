<?php
function exceptionHandler($ex)
{
    ob_clean();
    $code = md5(serialize($GLOBALS));
    echo '<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<meta name=viewport content="initial-scale=1, minimum-scale=1, width=device-width">
<title>Error</title>
<style>.main_message{font-weight:bold;font-size:2rem;}</style>
</head>
<body>
<span class="main_message">Sorry, there was an error.  &#x1F622;</span><p>Please contact us with the following code: ' . $code . '</p>' . nl;
    if (defined('DEVELOPMENT') && DEVELOPMENT === true) {
        echo '<pre>';
        echo '<b>Message:</b> ' . $ex->getMessage() . "\r\n\r\n";
        $trace = $ex->getTrace();
        echo "<b>Stacktrace:</b>\r\n";
        var_dump($trace);
        echo "\r\n<b>GLOBALS:</b>\r\n";
        var_dump($GLOBALS);
        echo "</pre>\r\n";
    }
    echo "</body>
</html>";

    $errlog = 'Message: ' . $ex->getMessage() . "\r\n\r\n";
    $trace = $ex->getTrace();
    $errlog .= "Stacktrace:\r\n";
    ob_flush();
    ob_start();
    var_dump($trace);
    $errlog .= ob_get_clean();
    $errlog .= "\r\n<b>GLOBALS:</b>\r\n";
    ob_flush();
    ob_start();
    var_dump($GLOBALS);
    $errlog .= ob_get_clean();
    @file_put_contents('logs/exception_' . date('ymd_His') . '_' . $code . '.log', $errlog);

    exit;
}

function errorHandler($errno, $errstr, $errfile, $errline)
{
    ob_clean();
    $code = md5(serialize($GLOBALS));
    global $Language_REALFILE;
    echo '<!DOCTYPE html>
<html>
<head>
<meta charset=utf-8>
<meta name=viewport content="initial-scale=1, minimum-scale=1, width=device-width">
<title>Error</title>
<style>.main_message{font-weight:bold;font-size:2rem;}</style>
</head>
<body>
<span class="main_message">Sorry, there was an error.  &#x1F622;</span><p>Please contact us with the following code: ' . $code . '</p>' . nl;
    if (defined('DEVELOPMENT') && DEVELOPMENT === true) {
        echo '<pre>';
        echo '<b>ErrNo:</b> ' . $errno . "\r\n";
        echo '<b>ErrStr:</b> ' . $errstr . "\r\n";
        echo '<b>ErrFile:</b> ' . $errfile . "\r\n";
        if (isset($Language_REALFILE)) {
            echo '<b>REALFILE:</b> ' . $Language_REALFILE . "\r\n";
        }
        echo '<b>ErrLine:</b> ' . $errline . "\r\n\r\n";
        echo "<b>GLOBALS:</b>\r\n";
        var_dump($GLOBALS);
        echo "</pre>\r\n";
    }
    echo "</body>
</html>";

    $errlog = 'ErrNo: ' . $errno . "\r\n";
    $errlog .= 'ErrStr: ' . $errstr . "\r\n";
    $errlog .= 'ErrFile: ' . $errfile . "\r\n";
        if (isset($Language_REALFILE)) {
            $errlog .= '<b>REALFILE:</b> ' . $Language_REALFILE . "\r\n";
        }
    $errlog .= 'ErrLine: ' . $errline . "\r\n\r\n";
    $errlog .= "GLOBALS:\r\n";
    ob_flush();
    ob_start();
    var_dump($GLOBALS);
    $errlog .= ob_get_clean();
    @file_put_contents('logs/error_' . date('ymd_His') . '_' . $code . '.log', $errlog);

    exit;
}

set_exception_handler('exceptionHandler');
set_error_handler('errorHandler', E_ALL);
ob_start();
