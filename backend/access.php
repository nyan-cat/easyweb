<?php

class access
{
    function insert($group)
    {
        $id = $group->id();
        !isset($this->groups[$id]) or backend_error('bad_config', "Duplicate group: $id");
        $this->groups[$id] = $group;
    }

    function get($name, $params)
    {
        $id = group::make_id($name, $params);
        isset($this->groups[$id]) or backend_error('bad_group', "Unknown group: $id");
        return $this->groups[$id];
    }

    function query($name, $args)
    {
        return $this->get($name, array_keys($args))->query($args);
    }

    function parse_query($expression, $args)
    {
        preg_match('/\A(\w+)\[([\w:, ]*)\]\Z/', preg_replace('/\s+/', '', $expression), $matches) or backend_error('bad_group', "Incorrect group expression: $expression");
        $name = $matches[1];
        $bindings = explode(',', $matches[2]);
        $params = [];
        foreach($bindings as &$binding)
        {
            $binding = trim($binding);

            if(preg_match('/(\w+) *: *(\w+)/', $binding, $matches))
            {
                $binding = ['param' => $matches[1], 'arg' => $matches[2]];
            }
            else
            {
                $binding = ['param' => $binding, 'arg' => $binding];
            }
            $params[] = $binding['param'];
        }

        $group = $this->get($name, $params);

        $matched = [];

        foreach($bindings as $binding)
        {
            isset($args[$binding['arg']]) or backend_error('bad_group', "Not enough arguments to query group $id");
            $matched[$binding['param']] = $args[$binding['arg']];
        }

        return $group->query($matched);
    }

    private $groups;
}

?>