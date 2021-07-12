<?php
class Pagination
{
    private $Data = ['Page' => 1, 'Pages' => 1];
    private $HTML;
    private $Pagination;

    function __construct($Pages = 1, $Page = 1)
    {
        $this->Data['Page'] = (int) $Page;
        $this->Data['Pages'] = (int) $Pages;
    }

    function get($Key)
    {
        if (isset($this->Data[$Key])) {
            return $this->Data[$Key];
        }
        return false;
    }

    function set($Key, $Val)
    {
        $this->HTML = null;
        $this->Pagination = [];
        $this->Data[$Key] = $Val;
        return true;
    }

    function getHTML()
    {
        $HTML = &$this->HTML;
        if (!empty($HTML)) return $HTML;
        $this->calculatePagination();
        if (!isset($this->Pagination) || count($this->Pagination) == 0) {
            $HTML .= $this->getPageLink(1, 'font-weight-bold');
            return $HTML;
        }
        foreach ($this->Pagination as $Page) {
            if (is_int($Page)) {
                $HTML .= $this->getPageLink($Page, ($Page == $this->Data['Page'] ? 'font-weight-bold' : ''));
            } else if ($Page == '...') {
                $HTML .= $this->getSeparator();
            }
        }
        return $HTML;
    }

    private function calculatePagination()
    {
        $Page = $this->Data['Page'];
        $Pages = $this->Data['Pages'];
        $Pagination = &$this->Pagination;
        $LastSeparator = 0;
        for ($x = 1; $x <= $Pages; $x++) {
            if ($Pages <= 5) {
                $Pagination[] = $x;
                continue;
            }
            if ($x == 1 || $x == 2 || $x == ($Page - 1) || $x == $Page || $x == ($Page + 1) || $x == ($Pages - 1) || $x == $Pages) {
                $Pagination[] = $x;
            } else {
                if ($LastSeparator != $x - 1) {
                    $Pagination[] = '...';
                }
                $LastSeparator = $x;
            }
        }
    }

    private function getPageLink($Page, $Class = null)
    {
		return '<span class="pagination_box"><a href="?page=' . $Page . '"' . (empty($Class) ? '' : ' class="' .$Class. '"') . '>' . $Page . '</a></span>' . nl;
    }

    private function getSeparator()
    {
        return '...' . nl;
    }
}