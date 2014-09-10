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
        $_count = isset($request->get->_count) ? $request->get->_count : 10;
        $_offset = isset($request->get->_offset) ? $request->get->_offset : isset($request->get->_page) ? ($request->get->_page - 1) * $_count : 0;
        return $this->script->evaluate($containers + ['_offset' => $_offset, '_count' => $_count]);
    }

    private $params;
    private $script;
}

?>