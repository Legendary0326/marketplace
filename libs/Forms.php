<?php
class Forms
{
    static function textarea($name)
    {
        if (isset($_POST[$name]) && !empty($_POST[$name])) return htmlentities(self::deletenl($_POST[$name]));
    }

    static function value($name)
    {
        if (isset($_POST[$name]) && !empty($_POST[$name])) return ' value="' . htmlentities(self::deletenl($_POST[$name])) . '"';
    }

    static function deletenl($string)
    {
        return preg_replace('/(\r|\n)/s', '', $string);
    }

    static function selected($name, $id)
    {
        if (isset($_POST[$name]) && !empty($_POST[$name]) && $_POST[$name] == $id) return ' selected="selected"';
    }

    static function checked($name, $id)
    {
        if (isset($_POST[$name]) && !empty($_POST[$name]) && $_POST[$name] == $id) return ' checked="checked"';
    }

    static function selectedVal($value, $id)
    {
        if (isset($value) && !empty($value) && $value == $id) return ' selected="selected"';
    }

    static function checkedVal($value, $id)
    {
        if (isset($value)) {
            if (is_array($value)) {
                $result = array_search($id, $value);
                if ($result !== false) {
                    return ' checked="checked"';
                }
            } else if (!empty($value) && $value == $id) {
                return ' checked="checked"';
            }
        }
    }

    static function valueVal($string)
    {
        return ' value="' . htmlentities(self::deletenl($string)) . '"';
    }

    static function textareaVal($string)
    {
        return htmlentities($string);
    }

    static function isPost()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'POST' ? true : false);
    }

    static function validateUsername($name)
    {
        if (!isset($_POST[$name])) return false;
        if (empty($_POST[$name])) return false;
        $_POST[$name] = strtolower($_POST[$name]);
        if (!preg_match('/^[a-z0-9]{4,32}$/', $_POST[$name])) return false;
        return true;
    }

    static function validatePassword($name, $name2)
    {
        if (!isset($_POST[$name])) return false;
        if (!isset($_POST[$name2])) return false;
        if ($_POST[$name] !== $_POST[$name2]) return false;
        if (!Password::checkComplexity($_POST[$name])) return false;
        return true;
    }

    static function validateList($name, $list)
    {
        if (!isset($_POST[$name])) return false;
        if (empty($_POST[$name])) return false;
        $d = array_search($_POST[$name], $list);
        if (array_search($_POST[$name], $list) === false) return false;
        return true;
    }

    static function validateString($name, $options = [])
    {
        foreach ($options as $option => $value) {
            if ($option == 'min') {
                if (!isset($_POST[$name]) || strlen($_POST[$name]) < $value) return false;
            } else if ($option == 'max') {
                if (isset($_POST[$name]) && strlen($_POST[$name]) > $value) return false;
            }
        }
        return true;
    }
}