<?php

namespace http;

require_once(www_root . 'http/request.php');

function preg_escape($pattern)
{
    return str_replace(['/', '-'], ['\/', '\-'], $pattern);
}

class handler
{
    function __construct($uri, $access = [])
    {
        $this->access = $access;

        $this->patterns = (object) [];

        $pattern = (object)
        [
            'regex' => '/\A' . preg_replace('/\{\$[^\}]+\}/', '(.+)', preg_escape($uri)) . '\Z/'
        ];

        if(preg_match_all('/\{\$([^\}]+)\}/', $uri, $matches) and isset($matches[1]))
        {
            $pattern->params = [];

            foreach($matches[1] as $param)
            {
                $pattern->params[] = (object)
                [
                    'name' => $param
                ];
            }
        }

        $this->patterns->uri = $pattern;
    }

    function match($request, &$params, $access = null, $global = null)
    {
        $uri = $this->patterns->uri;

        if(preg_match($uri->regex, $request->uri, $matches))
        {
            foreach($this->access as $query)
            {
                // $access($global, $query) or error('bad_credentials', 'Access denied for ' . $query);
            }

            $params = [];

            $object = (object) [];

            if(isset($uri->params))
            {
                foreach($uri->params as $n => $param)
                {
                    $name = $param->name;
                    $object->$name = $matches[$n + 1];
                }
            }

            $params['uri'] = new \readonly($object);

            return true;
        }
        else
        {
            return false;
        }
    }

    private $access = [];
    private $patterns;
}

?>