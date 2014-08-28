<?php

require_once(www_root . 'backend/collection.php');

class dispatcher
{
    function attach($name, $colletion)
    {
        $this->collections[$name] = $colletion;
    }

    function __get($name)
    {
        isset($this->collections[$name]) or (debug_print_backtrace() and die('Unknown collection: ' . $name));
        return $this->collections[$name];
    }

    function __call($name, $args)
    {
        $params = empty($args) ? [] : $args[0];

        if(isset($this->collections[$name]))
        {
            return $this->collections[$name]->_query($params);
        }
        else
        {
            return $this->_global->$name($params);
        }
    }

    private $collections = [];
}

?>