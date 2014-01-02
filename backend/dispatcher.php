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

    function parse_query($expression, $args)
    {
        preg_match('/\A(\w+)\(([\w:, ]*)\)\Z/', preg_replace('/\s+/', '', $expression), $matches) or backend_error('bad_query', "Incorrect query expression: $expression");
        $name = $matches[1];
        $bindings = explode(',', $matches[2]);
        $params = [];
        foreach($bindings as &$binding)
        {
            $trimmed = trim($binding);

            if(preg_match('/(\w+) *: *(\w+)/', $trimmed, $matches))
            {
                $binding = ['param' => $matches[1], 'arg' => $matches[2]];
            }
            else
            {
                $binding = ['param' => $trimmed, 'arg' => $trimmed];
            }
            $params[] = $binding['param'];
        }

        $matched = [];

        foreach($bindings as $bound)
        {
            isset($args[$bound['arg']]) or backend_error('bad_query', "Not enough arguments to query procedure $id");
            $matched[$bound['param']] = $args[$bound['arg']];
        }

        return $this->query($name, $matched);
    }

    function get($name, $args)
    {
        $id = procedure::make_id($name, $args);
        isset($this->procedures[$id]) or backend_error('bad_query', "Unknown procedure: $id");
        return $this->procedures[$id];
    }

    function __call($name, $args)
    {
        return $this->query($name, $args[0]);
    }

    private $procedures = [];
}

?>