<?php

namespace shuffler;

class branch
{
    function __construct($generator)
    {
        $this->generator = $generator;
    }

    function push($node)
    {
        $this->branches[] = $node;
    }

    function get()
    {
        return $this->branches[$this->generator->irand(count($this->branches))]->get();
    }

    private $generator;
    private $branches = [];
}

?>