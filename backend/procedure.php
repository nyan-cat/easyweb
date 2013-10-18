<?php

require_once('datatype.php');

class procedure
{
    function __construct($params, $id, $required)
    {
        $this->params = $params;
        $this->id = $id;
        $this->required = $required;
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
        return $name . '[' . implode(',', array_keys($params)) . ']';
    }

    private function validate($args)
    {
        foreach($args as $name => $value)
        {
            datatype::assert($this->params[$name], $value);
        }
    }

    private $params;
    protected $id;
    protected $required;
}

?>