<?php
class HTMLHead
{
    private $path;
    private $nav = 'top_guest';
    private $additionalNavs = [];

    function __construct()
    {
        $this->path = MAINPATH . 'nav/';
    }

    public function print()
    {
        global $Language;
        echo '<!DOCTYPE html>
<html>
<head>
<title>' . $Language->translate('MAIN_NAME') . '</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="keywords" content="' . $Language->translate('head.keywords') . '">
<meta name="description" content="' . $Language->translate('head.description') . '">
<meta property="og:title" content="' . $Language->translate('head.og:title') . '" />
<meta property="og:url" content="' . $Language->translate('head.og:url') . '" />
<meta property="og:image" content="' . $Language->translate('head.og:image') . '" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<link rel="stylesheet" type="text/css" href="' . MAINURI . 'css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="' . MAINURI . 'css/custom.css">
<link rel="stylesheet" type="text/css" href="' . MAINURI . 'css/custom2.css">
<link rel="shortcut icon" type="image/x-icon" href="' . MAINURI . 'favicon.ico">
</head>
<body>
<div class="container-xl">' . nl;
        $this->printNav($this->nav);
        if (count($this->additionalNavs) >= 1) {
            foreach ($this->additionalNavs as $nav) {
                $this->printNav($nav);
            }
        }
        $this->printLayout();
    }

    private function printNav($nav)
    {
        global $DB;
        global $User;
        global $Language;
        global $Pages;
        if (file_exists($this->path . $nav . '.php')) {
            include $Language->translateFile($this->path . $nav . '.php');
        } else {
            trigger_error('Nav not found!');
        }
    }

    public function addNav($nav)
    {
        $this->additionalNavs[] = $nav;
    }

    private function printLayout()
    {
        global $Pages;
        switch ($Pages->getLayout()) {
            case 'small':
                echo '<div class="row justify-content-md-center">' . nl . '<div class="col-md-6">' . nl;
                break;
            default:
                echo '';
        }
    }

    public function setNav($nav)
    {
        $this->nav = $nav;
    }
}
