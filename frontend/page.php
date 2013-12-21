<?php

class page
{
    function __construct($url, $params, $require, $script, $templates, $data, $scripts, $cache, $template, $engine, $api, $locale)
    {
        $escaped = str_replace(['/', '.'], ['\/', '\.'], $url);
        $this->regex = '/\A' . preg_replace('/\{\$\w+\}/', '(.+)', $escaped) . '\Z/';
        preg_match_all('/\{\$(\w+)\}/', $url, $matches);

        if(isset($matches[1]))
        {
            $this->args = $matches[1];
        }

        $this->params = $params;
        $this->require = $require;
        $this->script = strlen($script) ? $script : null;
        $this->templates = $templates;
        $this->data = $data;
        $this->scripts = $scripts;
        $this->cache = $cache;
        $this->template = $template;
        $this->engine = $engine;
        $this->api = $api;
        $this->locale = $locale;
    }

    function match($url, &$params)
    {
        if(preg_match($this->regex, $url, $matches))
        {
            $params = [];

            foreach($this->args as $n => $arg)
            {
                $params[$arg] = $matches[$n + 1];
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    function request($params, $global, $get, $post, $cookies)
    {
        $values = [];
        $batch = [];

        foreach(array_merge($global, $this->params) as $name => $param)
        {
            $value = self::substitute($param->value, $params);

            switch($param->type)
            {
            case 'value':
                $values[$name] = $value;
                break;

            case 'json':
                $values[$name] = json::decode(fs::checked_read($value));
                break;

            case 'xml':
                $values[$name] = 'TODO: Add XML support here';
                break;

            case 'query':
                $batch[$name] = $value;
                break;

            case 'get':
                isset($get[$value]) or isset($param->default) or runtime_error('GET parameter not found: ' . $name);
                $values[$name] = isset($get[$value]) ? $get[$value] : $param->default;
                break;

            case 'post':
                isset($post[$value]) or isset($param->default) or runtime_error('POST parameter not found: ' . $name);
                $values[$name] = isset($post[$value]) ? $post[$value] : $param->default;
                break;

            case 'cookie':
                isset($cookies[$value]) or isset($param->default) or runtime_error('Cookie parameter not found: ' . $name);
                $values[$name] = isset($cookies[$value]) ? $cookies[$value] : $param->default;
                break;
            }
        }

        foreach($batch as $name => &$value)
        {
            $value = preg_replace_callback
            (
                '/\{@(\w+)\}/',
                function($matches) use($values)
                {
                    return isset($values[$matches[1]]) ? $values[$matches[1]] : $matches[0];
                },
                $value
            );
        }

        $response = [];

        $params = array_merge($values, empty($batch) ? [] : $this->api->batch($batch));

        if($this->script)
        {
            $prototype = '$' . implode(',$', array_keys($params));
            $script = '';
            foreach($this->require as $require)
            {
                $script .= 'require_once(\'' . $this->scripts . $require . '\');';
            }
            $script .= 'return function(' . (empty($params) ? '' : $prototype) . ") { {$this->script} };";
            $closure = eval($script);

            $args = array_values($params);

            if($result = call_user_func_array($closure->bindTo($this->api), $args))
            {
                $params = array_merge($params, $result);
            }

            foreach(['cookies', 'redirect', 'headers'] as $builtin)
            {
                $mangled = '_' . $builtin;

                if(isset($params[$mangled]))
                {
                    $response[$builtin] = $params[$mangled];
                    unset($params[$mangled]);
                }
            }
        }

        if($this->template)
        {
            switch($this->engine)
            {
            case 'twig':
                $loader = new Twig_Loader_Filesystem($this->templates);
                $options = [];
                if($this->cache)
                {
                    $options['cache'] = $this->cache;
                }
                $twig = new Twig_Environment($loader, $options);
                $twig->getExtension('core')->setNumberFormat(0, '.', ' ');

                $closure = function ($filename)
                {
                    return json_decode(file_get_contents($this->data . $filename));
                };            
                $function = new Twig_SimpleFunction('json', $closure->bindTo($this, $this));
                $twig->addFunction($function);

                $closure = function ($alias)
                {
                    return $this->locale->get($alias);
                };
                $function = new Twig_SimpleFilter('local', $closure->bindTo($this, $this));
                $twig->addFilter($function);

                $closure = function ($number)
                {
                    return ceil($number);
                };
                $filter = new Twig_SimpleFilter('ceil', $closure);
                $twig->addFilter($filter);

                $template = $twig->loadTemplate($this->template);
                $response['content'] = $template->render($params);
                break;

            case 'smarty':
                $smarty = new Smarty();
                $smarty->setTemplateDir($this->templates);
                if($this->cache)
                {
                    $smarty->setCompileDir($this->cache)
                           ->setCacheDir($this->cache);
                }
                $smarty->assign($params);
                $response['content'] = @$smarty->fetch($this->template);
                break;
            }
        }

        return (object) $response;
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
    private $require;
    private $script;
    private $templates;
    private $data;
    private $scripts;
    private $cache;
    private $template;
    private $engine;
    private $api;
    private $locale;
}

?>