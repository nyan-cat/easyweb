<?php

class access
{
    function __construct()
    {
    }

    function evaluate($name, $args)
    {
        isset($this->groups[$name]) or backend_error('bad_group', "Unknown group: $name");
        $group = $this->groups[$name];
    }

    private $groups;
}

?>