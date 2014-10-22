<?php

namespace json;

require_once('json.php');

class document implements \ArrayAccess, \Iterator
{
    function __construct($object = [])
    {
        $this->object = $object;
        $this->denormalize_object($this->object);
    }

    function offsetSet($offset, $value)
    {
        $this->denormalized[$offset] = $value;
    }

    function offsetExists($offset)
    {
        return isset($this->denormalized[$offset]);
    }

    function offsetUnset($offset)
    {
    }

    function offsetGet($offset)
    {
        return $this->denormalized[$offset];
    }

    function rewind()
    {
        reset($this->object);
    }

    function current()
    {
        return current($this->object);
    }

    function key()
    {
        return key($this->object);
    }

    function next()
    {
        next($this->object);
    }

    function valid()
    {
        return key($this->object) !== null;
    }

    static function update($a, $b)
    {
        if(!($a instanceof document))
        {
            $a = new document(decode(encode($a), true));
        }
        if(!($b instanceof document))
        {
            $b = new document(decode(encode($b), true));
        }
        return new document(join($a->object, $b->object));
    }

    static function parse($json)
    {
        return new document(decode($json, true));
    }

    private function denormalize_object(&$object, $path = null)
    {
        foreach($object as $name => &$property)
        {
            $this->denormalize($property, $path === null ? $name : "$path.$name");
        }
    }

    private function denormalize_array(&$array, $path)
    {
        foreach($array as $n => &$property)
        {
            $this->denormalize($property, $path . "[$n]");
        }
    }

    private function denormalize(&$property, $path)
    {
        $this->denormalized[$path] = &$property;
        if(is_array($property))
        {
            if($property !== array_values($property))
            {
                $this->denormalize_object($property, $path);
            }
            else
            {
                $this->denormalize_array($property, $path);
            }
        }
    }

    private $object;
    private $denormalized = [];
}

?>