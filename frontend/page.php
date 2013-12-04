<?php

class page
{
    function __construct($url, $params, $script, $folder, $template, $engine, $api)
    {
        $escaped = str_replace(['/', '.'], ['\/', '\.'], $url);
        $this->regex = '/\A' . preg_replace('/\{\$\w+\}/', '(.+)', $escaped) . '\Z/';
        preg_match_all('/\{\$(\w+)\}/', $url, $matches);

        if(isset($matches[1]))
        {
            $this->args = $matches[1];
        }

        $this->params = $params;
        $this->script = strlen($script) ? $script : null;
        $this->folder = $folder;
        $this->template = $template;
        $this->engine = $engine;
        $this->api = $api;
    }

    function match($url, &$params)
    {
        if(preg_match($this->regex, $url, $matches))
        {
            $params = [];

            foreach($this->args as $n => $arg)
            {
                $params[$arg] = $matches[1][$n];
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    function request($params)
    {
        $batch = [];

        foreach($this->params as $name => $url)
        {
            $batch[$name] = preg_replace_callback
            (
                '/\{\$(\w+)\}/',
                function($matches) use($params)
                {
                    return $params[$matches[1]];
                },
                $url
            );
        }

        $batch = $this->api->batch($batch);

        if($this->script)
        {
            $params = array_merge($params, $batch);
            $prototype = '$' . implode(',$', array_keys($params));
            $script = '';
            $script .= 'return function(' . (empty($params) ? '' : $prototype) . ") { {$this->script} };";
            $closure = eval($script);
            if($result = call_user_func_array($closure->bindTo($this->api), array_values($params)))
            {
                $batch = array_merge($batch, $result);
            }
        }

        switch($this->engine)
        {
        case 'twig':
            $loader = new Twig_Loader_Filesystem($this->folder);
            $twig = new Twig_Environment($loader/*, ['cache' => '/path/to/compilation_cache']*/);
            $template = $twig->loadTemplate($this->template);
            return $template->render($batch);
        }
    }

    private $regex;
    private $args = [];
    private $params;
    private $script;
    private $folder;
    private $template;
    private $engine;
    private $api;
}

?>