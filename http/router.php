<?php

namespace http;

require_once(www_root . 'readonly.php');
require_once(www_root . 'http/handler.php');

class router
{
    function attach($method, $handler)
    {
        $this->handlers[strtoupper($method)][] = $handler;
    }

    function request($request, $global = [])
    {
        foreach($this->handlers[$request->method] as $handler)
        {
            if($handler->match($request, $matches))
            {
                return $handler->request($request, array_merge($global, $matches,
                [
                    'get'     => new \readonly($request->get),
                    'post'    => new \readonly($request->post),
                    'cookies' => new \readonly($request->cookies)
                ]));
            }
        }

        return null;
    }

    private $handlers =
    [
        'OPTIONS' => [],
        'GET'     => [],
        'HEAD'    => [],
        'POST'    => [],
        'PUT'     => [],
        'DELETE'  => [],
        'TRACE'   => []
    ];
}

?>