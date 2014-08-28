<?php

class readonly
{
    function __construct($object)
    {
        $this->object = $object;
    }

    function __get($name)
    {
        isset($object->$name) or die('Parameter not set: ' . $name);
        return $object->$name;
    }

    private $object;
}

?>