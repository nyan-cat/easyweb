<?php

class matcher
{
    function __construct($uri)
    {
        $this->uri = $uri;
    }

    function match($uri, &$matches)
    {
        $matches =
        [
            'uri' =>
            [
                'page' => '10'
            ]
        ];
        return true;
    }

    private $uri;
}

?>