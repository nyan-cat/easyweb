<?php

require_once(www_root . 'error.php');

class sql
{
    function __construct($driver, $server, $username, $password, $database, $charset)
    {
        $this->driver = self::$drivers[$driver];
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->charset = $charset;
    }

    function begin()
    {
        $this->get()->setAttribute(PDO::ATTR_AUTOCOMMIT, false) or $this->error('SQL autocommit disabling failed');
        $this->get()->beginTransaction() or $this->error('SQL begin transaction failed');
    }

    function commit()
    {
        $this->get()->commit() or $this->error('SQL transaction commit failed');
        $this->get()->setAttribute(PDO::ATTR_AUTOCOMMIT, true) or $this->error('SQL autocommit enabling failed');
    }

    function rollback()
    {
        $this->get()->rollBack() or $this->error('SQL transaction rollback failed');
        $this->get()->setAttribute(PDO::ATTR_AUTOCOMMIT, true) or $this->error('SQL autocommit enabling failed');
    }

    function query($query)
    {
        if($result = $this->get()->query($query))
        {
            return $result->fetchAll(PDO::FETCH_OBJ);
        }
        else
        {
            $this->error('SQL query failed');
        }
    }

    function quote($value)
    {
        return $this->get()->quote($value);
    }

    static function drivers()
    {
        return self::$drivers;
    }

    private function get()
    {
        if(!$this->pdo)
        {
            $this->pdo = new PDO("{$this->driver}:host={$this->server};dbname={$this->database};charset={$this->charset}", $this->username, $this->password, [PDO::ATTR_PERSISTENT => false, PDO::ATTR_EMULATE_PREPARES => false]);
        }
        return $this->pdo;
    }

    private function error($message)
    {
        $message .= ': ' . $this->get()->errorInfo()[2];
        $this->pdo = null;
        error('database_error', $message);
    }

    private static $drivers =
    [
        'cubrid'     => 'cubrid',
        'dblib'      => 'dblib',
        'firebird'   => 'firebird',
        'ibm'        => 'ibm',
        'informix'   => 'informix',
        'mysql'      => 'mysql',
        'oracle'     => 'oci',
        'odbc'       => 'odbc',
        'postgresql' => 'pgsql',
        'sqlite'     => 'sqlite',
        'mssql'      => 'sqlsrv',
        '4d'         => '4d'
    ];

    private $driver;
    private $server;
    private $username;
    private $password;
    private $database;
    private $charset;
    private $pdo = null;
}

?>