<?php

namespace xml;

require_once('node.php');

class nodelist implements \Countable, \Iterator
{
    function __construct($nodelist)
    {
        $this->nodelist = $nodelist;
    }

    function count()
    {
        return $this->nodelist->length;
    }

    function current()
    {
        return new node($this->nodelist->item($this->position));
    }

    function key()
    {
        return $this->position;
    }

    function next()
    {
        ++$this->position;
    }

    function rewind()
    {
        $this->position = 0;
    }

    function valid()
    {
        return $this->position < $this->nodelist->length;
    }

    function __tostring()
    {
        $result = '';

        for($n = 0; $n < $this->nodelist->length; ++$n)
        {
            $result .= $this->nodelist->item($n)->nodeValue;
        }

        return $result;
    }

    function native()
    {
        return $this->nodelist;
    }

    private $position = 0;
    private $nodelist;
}

?>