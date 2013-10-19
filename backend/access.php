<?php

class access
{
    function insert($group)
    {
        $id = $group->id();
        !isset($this->groups[$id]) or backend_error('bad_config', "Duplicate group: $id");
        $this->groups[$id] = $group;
    }

    function parse_evaluate($expression, $args)
    {
        preg_match('/\A(\w+)\[([\w:,]*)\]\Z/', preg_replace('/\s+/', '', $expression), $matches) or backend_error('bad_group', "Incorrect group expression: $expression");
        $name = $matches[1];
        $bindings = explode(',', $matches[2]);
        $params = [];
        foreach($bindings as &$binding)
        {
            if(preg_match('/(\w+):(\w+)/', $binding, $matches))
            {
                $binding = ['param' => $matches[1], 'arg' => $matches[2]];
            }
            else
            {
                $binding = ['param' => $binding, 'arg' => $binding];
            }
            $params[] = $binding['param'];
        }

        $id = group::make_id($name, $params);
        isset($this->groups[$id]) or backend_error('bad_group', "Unknown group: $id");

        $matched = [];

        foreach($bindings as $binding)
        {
            isset($args[$binding['arg']]) or backend_error('bad_group', "Not enough arguments to evaluate group $id");
            $matched[$binding['param']] = $args[$binding['arg']];
        }

        return $this->groups[$id]->evaluate($matched);
    }

    private $groups;
}

?>