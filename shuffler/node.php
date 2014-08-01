<?php

namespace shuffler;

class node
{
    function push($node)
    {
        $this->children[] = $node;
    }

    function get()
    {
        $result = '';
        foreach($this->children as $node)
        {
            $result .= $node->get();
        }
        return $result;
    }

    private $children = [];
}

?>