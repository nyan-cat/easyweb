<?php

class mysql_datasource
{
    function __construct($server, $username, $password, $database, $charset)
    {
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
    }

    function __destruct()
    {
        if($this->mysqli)
        {
            $this->mysqli->close();
        }
    }

    function get()
    {
        if(!$this->mysqli)
        {
            $this->mysqli = new mysqli('p:' . $this->server, $this->username, $this->password, $this->database);
            !$this->mysqli->connect_error or runtime_error("Can't connect to MySQL database: $database");
            $this->mysqli->set_charset($this->charset) or runtime_error("Can't set charset for database $database");
        }
        return $this->mysqli;
    }

    private $mysqli = null;

    private $server;
    private $username;
    private $password;
    private $database;
    private $charset;
}

?>