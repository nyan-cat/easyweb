<?php

class postgre_datasource
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
        if($this->pg)
        {
            pg_close($this->pg);
        }
    }

    function get()
    {
        if(!$this->pg)
        {
            $this->pg = pg_connect('host=' . $this->server . ' dbname=' . $this->database . ' user=' . $this->user . ' password=' . $this->password) or runtime_error("Can't connect to Postgre database: $database");
        }
        return $this->pg;
    }

    private $pg = null;

    private $server;
    private $username;
    private $password;
    private $database;
    private $charset;
}

?>