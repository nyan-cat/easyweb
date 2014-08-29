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

        $params = $this->resolve($this->params, $containers);

        if($this->script)
        {
            if(($script = $this->script->evaluate($params)) !== null)
            {
                $params = array_merge($params, $script);
            }
        }

        if($this->template)
        {
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
                $batch[$name] = self::substitute($param->query, $containers);
            }
            elseif(isset($param->value))
            {
                $result[$name] = self::substitute($param->value, $containers);
            }
        }

        if(!empty($batch))
        {
            $result = array_merge($result, $this->api->batch($batch));
        }

        return $result;
    }

    private static function substitute($string, $containers)
    {
        return preg_replace_callback('/\{\$([^\.]+)\.([^\}]+)\}/', function($matches) use($containers)
        {
            $container = $matches[1];
            $value = $matches[2];
            isset($containers[$container]) or error('missing_parameter', 'Unknown container: ' . $container);
            isset($containers[$container]->$value) or error('missing_parameter', 'Unknown container value: ' . $container . '[' . $value . ']');
            return $containers[$container]->$value;
        }, $string);
    }

    private $params;
    private $matcher;
    private $template;
    private $script;
    private $api;
}

?>