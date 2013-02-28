<?php

require_once('validate.php');

function preg_escape($pattern)
{
    $pattern = str_replace('/', '\/', $pattern);
    $pattern = str_replace('-', '\-', $pattern);
    $pattern = str_replace('?', '\?', $pattern);
    return $pattern;
}

class page
{
    function __construct($name, $url, $template, $action, $permission, $code, $message, $content_type)
    {
        $this->name = $name;
        $this->template = $template;
        $this->action = $action;
        $this->permission = $permission;
        $this->code = $code;
        $this->message = $message;
        $this->content_type = $content_type;

        if($url)
        {
            if(preg_match_all('/\(([\w:]+) +\-> +([\w:]+)\)/', $url, $match))
            {
                $patterns = $match[2];
                $this->params = $match[1];

                $url = preg_replace('/(\([\w:]+ +\-> +[\w:]+\))/i', '%', $url);
                $url = preg_escape($url);

                foreach($patterns as $pattern)
                {
                    $url = preg_replace('/%/', '(' . validate::get($pattern) . ')', $url, 1);
                }

                $this->url = '/\A' . $url . '\Z/';
            }
            else
            {
                $url = preg_escape($url);
                $this->url = '/\A' . $url . '\Z/';
            }
        }
        else
        {
            $this->data['template'] = null;
        }
    }

    function name()
    {
        return $this->name;
    }

    function template()
    {
        return $this->template;
    }

    function action()
    {
        return $this->action;
    }

    function permission()
    {
        return $this->permission;
    }

    function code()
    {
        return $this->code;
    }

    function message()
    {
        return $this->message;
    }

    function content_type()
    {
        return $this->content_type;
    }

    function match($url, &$args)
    {
        if($this->url && preg_match($this->url, $url, $match))
        {
            $args = array();
            for($n = 0; $n < count($this->params); ++$n)
            {
                $args[$this->params[$n]] = $match[$n + 1];
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    private $name;
    private $url;
    private $params = array();
    private $template;
    private $action;
    private $permission;
    private $code;
    private $message;
    private $content_type;
}

?>