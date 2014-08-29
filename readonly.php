<?php

require_once(www_root . 'error.php');

class readonly
{
    function __construct($object)
    {
        $this->object = $object;
    }

    function __get($name)
    {
        isset($this->object->$name) or error('missing_parameter', 'Parameter not set: ' . $name);
        return $this->object->$name;
    }

    function __isset($name)
    {
        return isset($this->object->$name);
    }

    private $object;
}

?>