<?php

namespace json;

class flat implements \ArrayAccess, \Iterator
{
    function __construct($flat = [])
    {
        $this->flat = $flat;
    }

    function offsetSet($offset, $value)
    {
        $this->flat[$offset] = $value;
    }

    function offsetExists($offset)
    {
        return isset($this->flat[$offset]);
    }

    function offsetUnset($offset)
    {
        unset($this->flat[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->flat[$offset];
    }

    function rewind()
    {
        reset($this->flat);
    }

    function current()
    {
        return current($this->flat);
    }

    function key()
    {
        return key($this->flat);
    }

    function next()
    {
        next($this->flat);
    }

    function valid()
    {
        return key($this->flat) !== null;
    }

    function update(flat $flat)
    {
        foreach($flat as $name => $property)
        {
            $this->flat[$name] = $property;
        }
    }

    function render()
    {
        $result = (object) [];

        foreach($this->flat as $path => $property)
        {
            self::set($result, explode('.', $path), $property);
        }

        return $result;
    }

    static function inplace($flat)
    {
        $result = (object) [];

        foreach($flat as $path => $property)
        {
            self::set($result, explode('.', $path), $property);
        }

        return $result;
    }

    static function denormalize($object)
    {
        $flat = new flat();
        $flat->unwind($object);
        return $flat;
    }

    private function unwind($object, $path = null)
    {
        foreach($object as $name => $property)
        {
            $current = $path === null ? $name : "$path.$name";

            if(is_object($property) or is_array($property))
            {
                $this->unwind($property, $current);
            }
            else
            {
                $this->flat[$current] = $property;
            }
        }
    }

    private static function set(&$object, $members, $property)
    {
        $member = array_shift($members);

        if(preg_match('/([^\[]+)\[\d+\]\Z/', $member, $matches))
        {
            $member = $matches[1];

            if(!isset($object->$member))
            {
                $object->$member = [];
            }

            $object->{$member}[] = $property;
        }
        else
        {
            if(!empty($members))
            {
                if(!isset($object->$member))
                {
                    $object->$member = (object) [];
                }
                self::set($object->$member, $members, $property);
            }
            else
            {
                $object->$member = $property;
            }
        }
    }

    private $flat = [];
}

?>