<?php

require_once(www_root . 'http/handler.php');
require_once(www_root . 'http/response.php');
require_once(www_root . 'script.php');

class page extends http\handler
{
    function __construct($uri, $params, $template, $script, $api)
    {
        parent::__construct($uri);

        $this->params = $params;
        $this->template = $template;
        $this->script = $script;
        $this->api = $api;
    }

    function request($request, $containers)
    {
        $response = new http\response(200, 'OK', $request->protocol);

        $params = $this->resolve($containers['_global'] + $this->params, $containers);

        if($this->script)
        {
            if(($script = $this->script->evaluate($containers + $params)) !== null)
            {
                if(isset($script['_cookies']))
                {
                    $response->cookies += $script['_cookies'];
                    unset($script['_cookies']);
                }
                if(isset($script['_redirect']))
                {
                    $response->code = 302;
                    $response->message = 'Found';
                    $response->headers['Location'] = $script['_redirect'];
                    unset($script['_redirect']);
                }

                $params += $script;
            }
        }

        if($this->template)
        {
            if(isset($containers['_global']['_collections']))
            {
                $params += $containers['_global']['_collections'];
            }
            $response->content = $this->template->render($params);
        }

        return $response;
    }

    private function resolve($params, $containers)
    {
        $result = [];

        $batch = [];

        foreach($params as $name => $param)
        {
            if(isset($param->query))
            {
                $batch[$name] = self::substitute(self::prepass($param->query, $result), $containers);
            }
            elseif(isset($param->value))
            {
                $result[$name] = self::substitute($param->value, $containers);
            }
        }

        if(!empty($batch))
        {
            $result += $this->api->batch($batch, $containers['_global']['_batch']);
        }

        foreach($params as $name => $param)
        {
            if(isset($param->value))
            {
                $result[$name] = self::prepass($result[$name], $result);
            }
        }

        return $result;
    }

    private static function prepass($string, $values)
    {
        return preg_replace_callback('/\{@(\w+)\}/', function($matches) use($values)
        {
            return isset($values[$matches[1]]) ? $values[$matches[1]] : $matches[0];
        }, $string);
    }

    private static function substitute($string, $containers)
    {
        return preg_replace_callback('/\{\$([^\.]+)\.([^\}]+)\}/', function($matches) use($containers)
        {
            $container = $matches[1];
            $offset = $matches[2];
            isset($containers[$container]) or error('missing_parameter', 'Unknown container: ' . $container);
            return readonly::create($containers[$container])[$offset];
        }, $string);
    }

    private $params;
    private $matcher;
    private $template;
    private $script;
    private $api;
}

?>