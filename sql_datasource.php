<?php

require_once('sql.php');

class sql_datasource
{
    function __construct($driver, $server, $username, $password, $database, $charset)
    {
        $this->driver = $driver;
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
    }

    function get()
    {
        if(!$this->sql)
        {
            $this->sql = new sql($this->driver, $this->server, $this->username, $this->password, $this->database, $this->charset);
        }
        return $this->sql;
    }

    private $sql = null;

    private $driver;
    private $server;
    private $username;
    private $password;
    private $database;
    private $charset;
}

?>