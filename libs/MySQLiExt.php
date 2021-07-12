<?php
class MySQLiExt extends mysqli
{
    private $lastQuery;
    private $logErrors = false;
    private $host;
    private $user;
    private $pass;
    private $db;
    private $charset;

    function __construct($host, $user, $pass, $db, $port, $charset = 'utf8')
    {
        $this->host = (empty($host) ? ini_get("mysqli.default_host") : $host);
        $this->user = (empty($user) ? ini_get("mysqli.default_user") : $user);
        $this->pass = (empty($pass) ? ini_get("mysqli.default_pw") : $pass);
        $this->db = $db;
        $this->port = (empty($port) ? ini_get("mysqli.default_port") : $port);
        $this->charset = $charset;
        try {
            parent::connect($host, $user, $pass, $db, $port);
            parent::set_charset($charset);
            $con = parent::ping();
        } catch(Exception $e) {
            trigger_error($e->getMessage());
        }
        if (!$con) trigger_error('Unable to connect to Database-Server!');
    }

    function logErrors($log = true)
    {
        $this->logErrors = (bool) $log;
    }

    function get($table, $where = '', $extra = '')
    {
        $result = $this->query($this->lastQuery = 'SELECT * FROM ' . $this->field($table) . ($where ? ' WHERE ' . $where : '') . ($extra ? ' ' . $extra : ''));
        if ($result === false) return false;
        return $this->fetch_all($result, MYSQLI_ASSOC);
    }

    function getOne($table, $where = '')
    {
        $result = $this->query($this->lastQuery = 'SELECT * FROM ' . $this->field($table) . ($where ? ' WHERE ' . $where : ''));
        if ($result === false) return false;
        return $this->fetch_assoc($result);
    }

    function exists($table, $where = '')
    {
        $result = $this->query($this->lastQuery = 'SELECT * FROM ' . $this->field($table) . ($where ? ' WHERE ' . $where : ''));
        if ($result === false) return false;
        return $result->num_rows;
    }

    function delete($table, $where = '')
    {
        return $this->query($this->lastQuery = 'DELETE FROM ' . $this->field($table) . ($where ? ' WHERE ' . $where : ''));
    }

    function insert($table, $data, $where = '')
    {
        if (!is_array($data) || empty($data)) return false;
        $keys = array_keys($data);
        $values = array_values($data);
        foreach ($keys as &$key) {
            $key = $this->field($key);
        }
        foreach ($values as &$value) {
            if (is_null($value)) {
                $value = 'NULL';
            } else if (is_int($value)) {
                $value = $this->int($value);
            } else if (is_bool($value)) {
                $value = $this->bool($value);
            } else {
                $value = $this->string($value);
            }
        }
        if ($where) {
            return $this->query($this->lastQuery = 'INSERT INTO ' . $this->field($table) . ' (' . implode(', ', $keys) . ') SELECT ' . implode(', ', $values) . ' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ' . $this->field($table) . ' WHERE ' . $where . ') LIMIT 1');
        } else {
            return $this->query($this->lastQuery = 'INSERT INTO ' . $this->field($table) . ' (' . implode(', ', $keys) . ') VALUES(' . implode(', ', $values) . ')');
        }
    }

    function lastInsertId()
    {
        $result = $this->query($this->lastQuery = 'SELECT LAST_INSERT_ID() AS ID');
        if ($result === false) return false;
        $erg = $this->fetch_assoc($result);
        return $erg['ID'];
    }

    function update($table, $data, $where = '')
    {
        if (!is_array($data) || empty($data)) return false;
        $fields = [];
        foreach ($data as $key => $value) {
            $field = $this->field($key) . ' = ';
            if (is_null($value)) {
                $field .= 'NULL';
            } else if (is_int($value)) {
                $field .= $this->int($value);
            } else if (is_bool($value)) {
                $value = $this->bool($value);
            } else {
                $field .= $this->string($value);
            }
            $fields[] = $field;
        }
        return $this->query($this->lastQuery = 'UPDATE ' . $this->field($table) . ' SET ' . implode(', ', $fields) . ($where ? ' WHERE ' . $where : ''));
    }

    function field($string)
    {
        $data = explode('.', parent::escape_string($string));
        return '`' . implode('`.`', $data) . '`';
    }

    function table($string)
    {
        return '`' . parent::escape_string($string) . '`';
    }

    function int($int)
    {
        if (is_null($int)) return 'NULL';
        return intval($int);
    }

    function float($float)
    {
        if (is_null($float)) return 'NULL';
        return floatval($float);
    }

    function string($string)
    {
        if (is_null($string)) return 'NULL';
        return '\'' . parent::escape_string($string) . '\'';
    }

    function bool($bool)
    {
        if (is_null($bool)) return 'NULL';
        return ($bool ? 1 : 0);
    }

    function now()
    {
        return date('Y.m.d H:i:s');
    }

    function lastQuery()
    {
        return $this->lastQuery;
    }

    function foundRows()
    {
        $result = $this->query('SELECT FOUND_ROWS()');
        if ($result === false) return false;
        $assoc = $this->fetch_assoc($result);
        return $assoc['FOUND_ROWS()'];
    }

    function query($query, $resultmode = NULL)
    {
        $this->lastQuery = $query;
        $result = parent::query($query, $resultmode);
        if ($this->logErrors) {
            if ($this->errno) {
                @file_put_contents(MAINPATH . 'logs/MySQLi_Errors.log', date('d.m.Y H:i:s') . ': ' . $this->error . "\r\n", FILE_APPEND);
            } else {
                //@file_put_contents(MAINPATH . 'logs/MySQLi_Query.log', date('d.m.Y H:i:s') . ': ' . $this->lastQuery . "\r\n", FILE_APPEND);
            }
        }
        return $result;
    }

    function fetch_all($result, $options = MYSQLI_NUM)
    {
        $finfo = $result->fetch_fields();
        $array = $result->fetch_all($options);
        if (is_array($array) && count($array) >= 1) {
            foreach ($array as &$array2) {
                $ID = 0;
                foreach ($array2 as &$value) {
                    if (!is_null($value)) $value = $this->castType($value, $finfo[$ID]->type);
                    $ID++;
                }
            }
        }
        return $array;
    }

    function fetch_assoc($result)
    {
        $finfo = $result->fetch_fields();
        $array = $result->fetch_assoc();
        if (is_array($array) && count($array) >= 1) {
            $ID = 0;
            foreach ($array as &$value) {
                if (!is_null($value)) $value = $this->castType($value, $finfo[$ID]->type);
                $ID++;
            }
        }
        return $array;
    }

    function castType($data, $fieldtype)
    {
        switch ($fieldtype)
        {
            case MYSQLI_TYPE_DECIMAL:
            case MYSQLI_TYPE_NEWDECIMAL:
            case MYSQLI_TYPE_FLOAT:
            case MYSQLI_TYPE_DOUBLE:
                return (float) $data;

            case MYSQLI_TYPE_BIT:
            case MYSQLI_TYPE_TINY:
            case MYSQLI_TYPE_SHORT:
            case MYSQLI_TYPE_LONG:
            case MYSQLI_TYPE_LONGLONG:
            case MYSQLI_TYPE_INT24:
            case MYSQLI_TYPE_YEAR:
            case MYSQLI_TYPE_ENUM:
                return (int) $data;

            case MYSQLI_TYPE_TIMESTAMP:
            case MYSQLI_TYPE_DATE:
            case MYSQLI_TYPE_TIME:
            case MYSQLI_TYPE_DATETIME:
            case MYSQLI_TYPE_NEWDATE:
            case MYSQLI_TYPE_INTERVAL:
            case MYSQLI_TYPE_SET:
            case MYSQLI_TYPE_VAR_STRING:
            case MYSQLI_TYPE_STRING:
            case MYSQLI_TYPE_CHAR:
            case MYSQLI_TYPE_GEOMETRY:
            case MYSQLI_TYPE_TINY_BLOB:
            case MYSQLI_TYPE_MEDIUM_BLOB:
            case MYSQLI_TYPE_LONG_BLOB:
            case MYSQLI_TYPE_BLOB:
            default:
                return (string) $data;
        }
    }
}
