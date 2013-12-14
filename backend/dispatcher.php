<?php

require_once('exception.php');

class dispatcher
{
    function insert($procedure)
    {
        $id = $procedure->id();
        !isset($this->procedures[$id]) or backend_error('bad_config', "Duplicate procedure: $id");
        $this->procedures[$id] = $procedure;
    }

    function query($name, $args)
    {
        return $this->get($name, $args)->query($args);
    }

    function get($name, $args)
    {
        $id = procedure::make_id($name, $args);
        isset($this->procedures[$id]) or backend_error('bad_query', "Unknown procedure: $id");
        return $this->procedures[$id];
    }

    private $procedures = [];
}

?>