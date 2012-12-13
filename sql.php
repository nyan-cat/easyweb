<?php

class sql
{
    function __construct($driver, $server, $username, $password, $database, $charset)
    {
        $this->pdo = new PDO("$driver:host=$server;dbname=$database;charset=$charset", $username, $password, array
        (
            PDO::ATTR_PERSISTENT => true
        ));
    }

    function begin()
    {
        $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false) or $this->push('SQL autocommit disabling failed: ' . $this->error());
        if($this->ready())
        {
            $this->pdo->beginTransaction() or $this->push('SQL begin transaction failed: ' . $this->error());
            if(!$this->ready())
            {
                $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true) or $this->push('SQL autocommit enabling failed: ' . $this->error());
            }
        }
        $this->flush();
        ++$this->depth;
    }

    function commit()
    {
        $this->pdo->commit() or $this->push('SQL transaction commit failed: ' . $this->error());
        $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true) or $this->push('SQL autocommit enabling failed: ' . $this->error());
        $this->flush();
        --$this->depth;
    }

    function rollback()
    {
        $this->pdo->rollBack() or $this->push('SQL transaction rollback failed: ' . $this->error());
        $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true) or $this->push('SQL autocommit enabling failed: ' . $this->error());
        $this->flush();
        --$this->depth;
    }

    function query($query)
    {
        if($result = $this->pdo->query($query))
        {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        else
        {
            $error = 'SQL query failed: ' . $this->error();
            if($this->depth)
            {
                $this->rollback();
            }
            $this->push($error);
            $this->flush();
        }
    }

    function quote($value)
    {
        return $this->pdo->quote($value);
    }

    private function push($error)
    {
        $this->stack[] = $error;
    }

    private function ready()
    {
        return empty($this->stack);
    }

    private function flush()
    {
        if(!$this->ready())
        {
            $message = implode(', ', $this->stack);
            $this->stack = array();
            runtime_error($message);
        }
    }

    private function error()
    {
        $info = $this->pdo->errorInfo();
        return $info[2];
    }

    private $pdo;
    private $depth = 0;
    private $stack = array();
}

?>