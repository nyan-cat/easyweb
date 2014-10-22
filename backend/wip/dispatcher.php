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
        isset($this->collections[$name]) or error('object_not_found', 'Unknown collection: ' . $name);
        return $this->collections[$name];
    }

    function __call($name, $args)
    {
        $params = empty($args) ? [] : $args[0];

        if(isset($this->collections[$name]))
        {
            return $this->collections[$name]->query($params);
        }
        else
        {
            return $this->_global->$name($params);
        }
    }

    private $collections = [];
}

?>