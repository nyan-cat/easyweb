<?php

class page
{
    function __construct($url, $params, $script, $template, $engine, $api)
    {
        $escaped = str_replace(['/', '.'], ['\/', '\.'], $url);
        $this->regex = '/\A' . preg_replace('/\{\$\w+\}/', '(.+)', $escaped) . '\Z/';
        preg_match_all('/\{\$(\w+)\}/', $url, $matches);

        if(isset($matches[1]))
        {
            $this->args = $matches[1];
        }

        $this->params = $params;
        $this->script = $script;
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

        switch($this->engine)
        {
        case 'twig':
            $loader = new Twig_Loader_Filesystem('/var/www/html/vzagule.com/tpl');
            $twig = new Twig_Environment($loader/*, ['cache' => '/path/to/compilation_cache']*/);
            $template = $twig->loadTemplate($this->template);
            return $template->render($this->api->batch($batch));
        }
    }

    private $regex;
    private $args = [];
    private $params;
    private $script;
    private $template;
    private $engine;
    private $api;
}

?>