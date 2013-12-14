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
        if($name[0] != '@')
        {
            return $this->get($name, $args)->query($args);
        }
        else
        {
            return $this->evaluate(substr($name, 1), $args);
        }
    }

    function evaluate($name, $args)
    {
        return $this->get($name, $args)->evaluate($args);
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