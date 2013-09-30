<?php

require_once('exception.php');

class dispatcher
{
    function insert($mangled, $procedure)
    {
        !isset($this->procedures[$mangled]) or backend_error('bad_config', "Duplicate procedure: $mangled");
        $this->procedures[$mangled] = $procedure;
    }

    function query($name, $args)
    {
        return $this->get($name, $args)->query($args);
    }

    function get($name, $args)
    {
        $mangled = procedure::mangle($name, $args);
        isset($this->procedures[$mangled]) or backend_error('bad_query', "Unknown procedure: $mangled");
        return $this->procedures[$mangled];
    }

    private $procedures = [];
}

?>