<?php
class Pages
{
    private $path;
    private $error_path;
    private $uri_page;
    private $uri_realpath;
    private $uri_path;
    private $uri_query;
    private $suppress_html = false;
    private $layout = '';

    function __construct()
    {
        $this->path = MAINPATH . 'pages/';
        $this->error_path = MAINPATH . 'error_pages/';

        $uri = explode('?', $_SERVER['REQUEST_URI'], 2);
        $this->uri_query = (isset($uri[1]) ? $uri[1] : '');
        $this->uri_realpath = $uri[0];
        $uri2 = explode('/', preg_replace('/^\//', '', $uri[0]));
        if (preg_match('/^[a-z0-9_\-]*$/', $uri2[0])) {
            $this->uri_page = array_shift($uri2);
            $this->uri_path = $uri2;
        } else {
            $this->error(404);
        }
    }

    public function error($code)
    {
        if (is_int($code) && file_exists($this->error_path . $code . '.php')) {
            include($this->error_path . $code . '.php');
            exit;
        } else {
            trigger_error('Unexpected error!');
        }
    }

    public function getPage()
    {
        return $this->uri_page;
    }

    public function getPath()
    {
        return $this->uri_path;
    }

    public function getQuery()
    {
        return $this->uri_query;
    }

    public function inPath($path)
    {
        if (array_search($path, $this->uri_path) === false) {
            return false;
        }
        return true;
    }

    public function redirect($uri)
    {
        ob_end_clean();
        header('Location: /' . $uri, 307);
        exit;
    }

    public function load($page = null)
    {
        if (!isset($page)) $page = $this->uri_page;
        if (file_exists($this->path . $page . '.php')) {
            global $Pages;
            global $Language;
            global $DB;
            global $HTMLHead;
            global $HTMLFoot;
            global $User;

            ob_start();
            include $Language->translateFile($this->path . $page . '.php');
            $Content = ob_get_clean();
            if (!$this->suppress_html) {
                $HTMLHead->print();
                echo $Content;
                $HTMLFoot->print();
            }
        } else {
            $this->error(404);
        }
    }

    public function suppressHTML()
    {
        $this->suppress_html = true;
    }

    public function setLayout($Layout)
    {
        $this->layout = $Layout;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function reload()
    {
        $this->redirect(substr($this->uri_realpath, 1));
    }
}

$Pages = new Pages;