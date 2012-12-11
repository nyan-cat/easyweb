<?php

require_once('exception.php');

class router
{
    function __construct($vars, $access)
    {
        $this->vars = $vars;
        $this->access = $access;
    }

    function insert($name, $page)
    {
        !isset($this->pages[$name]) or runtime_error('Duplicate page: ' . $name);
        $this->pages[$name] = $page;
    }

    function get($name)
    {
        isset($this->pages[$name]) or runtime_error('Page not found: ' . $name);
        return $this->pages[$name];
    }

    function match($url, &$args)
    {
        foreach($this->pages as $page)
        {
            if($page->match($url, $args))
            {
                $this->vars->push($args);
                if($page->permission())
                {
                    if($this->access->query($page->permission()))
                    {
                        return $page;
                    }
                    else
                    {
                        $this->vars->pop();
                    }
                }
                else
                {
                    return $page;
                }
            }
        }
        return null;
    }

    private $vars;
    private $access;
    private $pages = array();
}

?>