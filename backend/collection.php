<?php

require_once(www_root . 'error.php');

class bind
{
    function __construct($collection, $key)
    {
        $this->collection = $collection;
        $this->key = $key;
    }

    function __call($name, $params)
    {
        return $this->collection->call($name, $this->key, empty($params) ? [] : $params[0]);
    }

    private $collection;
    private $key;
}

class collection implements ArrayAccess
{
    function __construct($key)
    {
        $this->key = $key;
    }

    function offsetExists($offset)
    {
        $this->key !== null or error('missing_parameter', 'Collection has no key');
        return $this->exists($offset);
    }

    function offsetGet($offset)
    {
        $this->key !== null or error('missing_parameter', 'Collection has no key');
        return new bind($this, $offset);
    }

    function offsetSet($offset, $value)
    {
        if($offset === null)
        {
            $this->create($value);
        }
        else
        {
            $this[$offset]->update($value);
        }
    }

    function offsetUnset($offset)
    {
        $this[$offset]->delete();
    }

    function attach($name, $procedure, $static = false)
    {
        $mangled = $this->mangle($name, array_keys($procedure->params()));
        if($static)
        {
            $this->static[$mangled] = $procedure;
        }
        else
        {
            $this->members[$mangled] = $procedure;
        }
    }

    function call($name, $key, $params)
    {
        $mangled = $this->mangle($name, array_keys($params));
        isset($this->members[$mangled]) or error('object_not_found', 'Unknown member procedure: ' . $mangled);
        return $this->members[$mangled]->query([$this->key => $key] + $params);
    }

    function __call($name, $params)
    {
        $params = empty($params) ? [] : $params[0];
        $mangled = $this->mangle($name, array_keys($params));
        isset($this->static[$mangled]) or error('object_not_found', 'Unknown static procedure: ' . $mangled);
        return $this->static[$mangled]->query($params);
    }

    private function mangle($name, $args)
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
    private $members = [];
    private $static = [];
}

?>