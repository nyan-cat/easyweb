<?php

class pdo_datasource
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
        if(!$this->pdo)
        {
            $this->pdo = new PDO($this->driver . ':host=' . $this->server . ';dbname=' . $this->database . ';charset=' . $this->charset, $this->username, $this->password);
        }
        return $this->pdo;
    }

    private $pdo = null;

    private $driver;
    private $server;
    private $username;
    private $password;
    private $database;
    private $charset;
}

?>