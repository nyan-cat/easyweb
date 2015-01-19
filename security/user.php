<?php

namespace security;

class user
{
    function __construct($id, $groups = [])
    {
        $this->id = (string) $id;
        $this->groups = $groups;
    }

    var $id;
    var $groups;
};

?>