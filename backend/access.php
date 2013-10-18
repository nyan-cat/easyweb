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
        $id = $expression;
        isset($this->groups[$id]) or backend_error('bad_group', "Unknown group: $id");
        return $this->groups[$id]->evaluate($args);
    }

    private $groups;
}

?>