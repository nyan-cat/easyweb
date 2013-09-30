<?php

require_once('datatype.php');

class procedure
{
    function __construct($params, $required = true)
    {
        $this->params = $params;
        $this->required = $required;
    }

    function query($args)
    {
        $this->validate($args);
        return $this->query_direct($args);
    }

    static function mangle($name, $params)
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
    protected $required;
}

?>