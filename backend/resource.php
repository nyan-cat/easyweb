<?php

class resource
{
    function __construct($uri, $methods)
    {
        $this->uri = $uri;
        $this->methods = $methods;
    }

    function __invoke($request)
    {
        foreach($this->methods[$request->type] as $method)
        {
            if($method->match($request))
            {
                return $method($request);
            }
        }
    }

    private $uri;
    private $methods;
}

?>