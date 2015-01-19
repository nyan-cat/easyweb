<?php

namespace http;

require_once(www_root . 'readonly.php');
require_once(www_root . 'http/handler.php');

class router
{
    function __construct($access = null)
    {
        $this->access = $access;
    }

    function attach($method, $handler)
    {
        $this->handlers[strtoupper($method)][] = $handler;
    }

    function request($request, $global = [])
    {
        foreach($this->handlers[$request->method] as $handler)
        {
            if($handler->match($request, $matches, $this->access, $global))
            {
                $collections =
                [
                    'get'     => new \readonly($request->get),
                    'post'    => new \readonly($request->post),
                    'cookies' => new \readonly($request->cookies),
                    'files'   => new \readonly($request->files),
                    '_global' => $global
                ];

                if(isset($global['_collections']))
                {
                    foreach($global['_collections'] as $name => $collection)
                    {
                        $collections[$name] = new \readonly($collection);
                    }
                }

                return $handler->request($request, $matches + $collections);
            }
        }

        error('not_found', 'Resource and method not found: ' . $request->method . ' ' . $request->uri);
    }

    private $access;

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