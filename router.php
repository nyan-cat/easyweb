<?php

require_once('exception.php');

class router
{
    function __construct($access)
    {
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
                if($page->permission())
                {
                    if($this->access->query($page->permission(), $args))
                    {
                        return $page;
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

    private $access;
    private $pages = array();
}

?>