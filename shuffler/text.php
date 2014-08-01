<?php

namespace shuffler;

class text
{
    function __construct($text)
    {
        $this->text = $text;
    }

    function get()
    {
        return $this->text;
    }

    private $text;
}

?>