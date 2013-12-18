<?php

require_once('datatype.php');

class procedure
{
    function __construct($params, $id, $required, $result)
    {
        $this->params = $params;
        $this->id = $id;
        $this->required = $required;
        $this->result = $result;
    }

    function query($args)
    {
        $this->validate($args);
        return $this->query_direct($args);
    }

    function params()
    {
        return $this->params;
    }

    function id()
    {
        return $this->id;
    }

    static function make_id($name, $params)
    {
        $id = $name;

        foreach($params as $name => $value)
        {
            if($name[0] != '_')
            {
                $id .= "[$name]";
            }
        }

        return $id;
    }

    private function validate($args)
    {
        foreach($args as $name => $value)
        {
            if($name[0] != '_')
            {
                datatype::assert($this->params[$name], $value);
            }
        }
    }

    private $params;
    protected $id;
    protected $required;
    protected $result;
}

?>