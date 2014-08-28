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
                $batch[$name] = $param->query;
            }
            elseif(isset($param->value))
            {
                $result[$name] = preg_replace_callback('/\{\$([^\.]+)\.([^\}]+)\}/', function($matches) use($containers)
                {
                    $container = $matches[1];
                    $value = $matches[2];
                    isset($containers[$container]) or runtime_error('Unknown container: ' . $container);
                    isset($containers[$container][$value]) or runtime_error('Unknown container value: ' . $container . '[' . $value . ']');
                    return $containers[$container][$value];
                }, $param->value);
            }
        }

        if(!empty($batch))
        {
            foreach($batch as $name => &$query)
            {
                $query = preg_replace_callback('/\{@(\w+)\}/', function($matches) use($result)
                {
                    return isset($result[$matches[1]]) ? $result[$matches[1]] : $matches[0];
                }, $query);
            }

            $result = array_merge($result, $this->api->batch($batch));
        }

        return $result;
    }

    private $params;
    private $matcher;
    private $template;
    private $script;
    private $api;
}

?>