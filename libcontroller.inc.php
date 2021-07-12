<?php
class Libs
{
    private $path;
    private $libs_loaded = [];

    function __construct()
    {
        $this->path = __DIR__ . '/libs/';
    }

    function load($load)
    {
        if (!isset($load)) return false;
        if (is_array($load)) {
            foreach ($load as $l) {
                $this->load($l);
            }
        } else if (is_string($load) && preg_match('/^[a-zA-Z0-9]{1,128}$/', $load)) {
            if (isset($this->libs_loaded[$load])) {
                return true;
            } else if (file_exists($this->path . $load . '.php')) {
                @require_once $this->path . $load . '.php';
                $this->libs_loaded[$load] = true;
            }
        }
    }
}

$Libs = new Libs;