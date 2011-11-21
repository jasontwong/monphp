<?php

class MonDB
{
    // {{{ properties
    private $conn;
    private $db;
    // }}}
    // {{{ function __construct ($set = 'default')
    function __construct ($set = 'default', $db_file = '')
    {
        include $db_file === ''
            ? DIR_SYS.'/config.database.php'
            : $db_file;
        $this->conn = new Mongo($_db_conn[$set]['server'], $_db_conn[$set]['options']);
        $this->db = $this->conn->selectDB($_db_conn[$set]['options']['db']);
    }
    // }}}
    // {{{ public function __call ($name, $arguments)
    public function __call ($name, $arguments)
    {
        if (method_exists($this->db, $name))
        {
            return call_user_func_array(array($this->db, $name), $arguments);
        }
        throw new MonDBException('That method does not exist');
    }
    // }}}
    // {{{ public static function __callStatic ($name, $arguments)
    public static function __callStatic ($name, $arguments)
    {
        $mondb = new MonDB();
        $db = $mondb->db;
        if (method_exists($db, $name))
        {
            return call_user_func_array(array($db, $name), $arguments);
        }
        throw new MonDBException('That method does not exist');
    }
    // }}}
    // {{{ public function __get ($name)
    public function __get ($name)
    {
        return $this->db->$name;
    }
    // }}}
}

class MonDBException extends Exception
{
}

?>
