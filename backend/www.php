<?php

require_once(www_root . 'xml.php');
require_once(www_root . 'backend/method.php');
require_once(www_root . 'backend/sql.php');
require_once(www_root . 'backend/sql_procedure.php');
require_once(www_root . 'backend/dispatcher.php');

class www
{
    function __construct()
    {
        $this->dispatcher = new dispatcher();
        include('www_load.php');
    }

    function call($url, $args)
    {
        $query = parse_url($url);
        $path = $query['path'];
        isset($this->methods[$path]) or backend_error('bad_request', "Method not found: $path");
        return $this->methods[$path]->call(array_merge($args, isset($query['query']) ? $query['query'] : []));
    }

    private $methods = [];
    private $dispatcher;
}

?>