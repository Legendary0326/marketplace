<?php
class HTMLFoot
{
    private $path;
    private $foot = 'normal';

    function __construct()
    {
        $this->path = MAINPATH . 'foot/';
    }

    public function print()
    {
        $this->printLayout();
        $this->printFoot();
        echo '</div>
</body>
</html>';
    }

    private function printFoot()
    {
        global $DB;
        global $User;
        global $Language;
        if (file_exists($this->path . $this->foot . '.php')) {
            include $Language->translateFile($this->path . $this->foot . '.php');
        } else {
            trigger_error('Foot not found!');
        }
    }

    private function printLayout()
    {
        global $Pages;
        switch ($Pages->getLayout()) {
            case 'small':
                echo '</div>' . nl . '</div>' . nl;
                break;
            default:
                echo '';
        }
    }
}