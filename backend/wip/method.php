<?php

require_once(www_root . 'http/handler.php');
require_once(www_root . 'script.php');

class method extends http\handler
{
    function __construct($uri, $script)
    {
        parent::__construct($uri);
        
        $this->script = $script;
    }

    function request($request, $containers)
    {
        return $this->script->evaluate($containers);
    }

    private $params;
    private $script;
}

?>