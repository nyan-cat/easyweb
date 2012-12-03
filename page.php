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
    function __construct($url = null, $template = null, $action = null, $permission = null, $code = '200', $message = 'OK')
    {
        $this->template = $template;
        $this->action = $action;
        $this->permission = $permission;
        $this->code = $code;
        $this->message = $message;

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

    function template()
    {
        return $this->template;
    }

    function permission()
    {
        return $this->permission;
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

    private $url;
    private $params = array();
    private $template;
    private $action;
    private $permission;
    private $code;
    private $message;
}

?>