<?php

class page
{
    function __construct($url, $params, $script, $templates, $data, $cache, $template, $engine, $api)
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
        $this->templates = $templates;
        $this->data = $data;
        $this->cache = $cache;
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
        $values = [];
        $batch = [];

        foreach($this->params as $name => $param)
        {
            $value = self::substitute($param->value, $params);

            switch($param->type)
            {
            case 'value':
                $values[$name] = $value;
                break;

            case 'json':
                $values[$name] = json_decode(file_get_contents($value));
                break;

            case 'xml':
                $values[$name] = 'TODO: Add XML support here';
                break;

            case 'get':
                $batch[$name] = $value;
                break;
            }
        }

        $params = array_merge($values, empty($batch) ? [] : $this->api->batch($batch));

        if($this->script)
        {
            $prototype = '$' . implode(',$', array_keys($params));
            $script = '';
            $script .= 'return function(' . (empty($params) ? '' : $prototype) . ") { {$this->script} };";
            $closure = eval($script);
            if($result = call_user_func_array($closure->bindTo($this->api), array_values($params)))
            {
                $params = array_merge($params, $result);
            }
        }

        switch($this->engine)
        {
        case 'twig':
            $loader = new Twig_Loader_Filesystem($this->templates);
            $closure = function ($filename)
            {
                return json_decode(file_get_contents($this->data . $filename));
            };
            $json = new Twig_SimpleFunction('json', $closure->bindTo($this, $this));
            $twig = new Twig_Environment($loader, ['cache' => $this->cache]);
            $twig->addFunction($json);
            $template = $twig->loadTemplate($this->template);
            return $template->render($params);

        case 'smarty':
            $smarty = new Smarty();
            $smarty->setTemplateDir($this->templates)
                   ->setCompileDir($this->cache)
                   ->setCacheDir($this->cache);
            $smarty->assign($params);
            return @$smarty->fetch($this->template);
        }
    }

    static function substitute($value, $params)
    {
        return preg_replace_callback
        (
            '/\{\$(\w+)\}/',
            function($matches) use($params)
            {
                return $params[$matches[1]];
            },
            $value
        );
    }

    private $regex;
    private $args = [];
    private $params;
    private $script;
    private $templates;
    private $data;
    private $cache;
    private $template;
    private $engine;
    private $api;
}

?>