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
        preg_match('/\A(\w+)\[([\w,]*)\]\Z/', $expression, $matches) or backend_error('bad_group', "Incorrect group expression: $expression");
        $name = $matches[1];
        $params = explode(',', $matches[2]);
        $id = group::make_id($name, $params);
        isset($this->groups[$id]) or backend_error('bad_group', "Unknown group: $id");

        $matched = [];

        foreach($params as $param)
        {
            isset($args[$param]) or backend_error('bad_group', "Not enough arguments to evaluate group $id");
            $matched[$param] = $args[$param];
        }

        return $this->groups[$id]->evaluate($matched);
    }

    private $groups;
}

?>