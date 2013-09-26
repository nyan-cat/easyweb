<?php

class backend_exception extends Exception
{
    function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    function type()
    {
        return $this->type;
    }

    function message()
    {
        return $this->message;
    }

    private $type;
}

function backend_error($type, $message)
{
    throw new backend_exception($type, $message);
}

?>