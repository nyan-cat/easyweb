<?php

require_once('exception.php');

class dispatcher
{
    function insert($mangled, $procedure)
    {
        !isset($this->procedures[$mangled]) or backend_error('bad_config', "Duplicate procedure: $mangled");
        $this->procedures[$mangled] = $procedure;
    }

    function query($name, $params)
    {
        return $this->get($name, $params)->query($params);
    }

    function evaluate($name, $params)
    {
        $fetch = function($entity) use(&$fetch)
        {
            if(is_array($entity))
            {
                (count($entity) == 1 and isset($entity[0])) or backend_error('bad_query', 'Query result is not evaluateable');
                return $fetch($entity[0]);
            }
            else
            {
                return $entity;
            }
        };

        return $fetch($this->query($name, $params));
    }

    function get($name, $params)
    {
        $mangled = procedure::mangle($name, $params);
        isset($this->procedures[$mangled]) or backend_error('bad_query', "Unknown procedure: $mangled");
        return $this->procedures[$mangled];
    }

    private $procedures = [];
}

?>