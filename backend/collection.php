<?php

require_once(www_root . 'error.php');

class thiscall
{
    function __construct($object, $name, $value)
    {
        $this->object = $object;
        $this->name = $name;
        $this->value = $value;
    }

    function __call($name, $args)
    {
        $args[0][$this->name] = $this->value;
        return call_user_func_array([$this->object, $name], $args);
    }

    private $object;
    private $name;
    private $value;
}

class collection implements ArrayAccess
{
    function __construct($key)
    {
        $this->key = $key;
    }

    function offsetExists($offset)
    {
        return $this->_exists($offset);
    }

    function offsetGet($offset)
    {
        return new thiscall($this, $this->key, $offset);
    }

    function offsetSet($offset, $value)
    {
        if($offset === null)
        {
            $this->_create($value);
        }
        else
        {
            $value[$this->key] = $offset;
            $this->_update($value);
        }
    }

    function offsetUnset($offset)
    {
        $this->_delete($offset);
    }

    function attach($name, $procedure)
    {
        $mangled = self::mangle($name, array_keys($procedure->params()));
        $this->procedures[$mangled] = $procedure;
    }

    function __call($name, $params)
    {
        $params = empty($params) ? [] : $params[0];
        $mangled = self::mangle($name, array_keys($params));
        isset($this->procedures[$mangled]) or error('object_not_found', 'Unknown procedure: ' . $mangled);
        return $this->procedures[$mangled]->query($params);
    }

    static function mangle($name, $args)
    {
        sort($args);
        $result = $name;
        foreach($args as $arg)
        {
            if($arg[0] != '_')
            {
                $result .= "[$arg]";
            }
        }
        return $result;
    }

    private $key;
    private $procedures = [];
}

?>