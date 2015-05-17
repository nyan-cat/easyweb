<?php

require_once(www_root . 'error.php');

class readonly implements ArrayAccess, Iterator
{
    function __construct($object)
    {
        $this->object = $object;
    }

    function __get($name)
    {
        isset($this->object->$name) or error('missing_parameter', 'Parameter not set: ' . $name);
        return $this->object->$name;
    }

    function __isset($name)
    {
        return isset($this->object->$name);
    }

    function offsetSet($offset, $value)
    {
        error('not_implemented', 'Readonly object is immutable');
    }

    function offsetExists($offset)
    {
        error('not_implemented', 'Implementation is postponed');
    }

    function offsetUnset($offset)
    {
        error('not_implemented', 'Readonly object is immutable');
    }

    function offsetGet($offset)
    {
        $members = explode('.', $offset);

        $current = null;

        foreach($members as $member)
        {
            if(!$current)
            {
                $current = $this->object->$member;
            }
            else
            {
                if(preg_match('/\A(\w+)\[(\d+)\]\Z/', $member, $array))
                {
                    $current = $current->$array[1];
                    $current = $current[(int) $array[2]];
                }
                else
                {
                    $current = $current->$member;
                }
            }
        }

        return $current;
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

    function get()
    {
        return $this->object;
    }

    function optional($name)
    {
        return isset($this->object->$name) ? $this->object->$name : '';
    }

    static function create($object)
    {
        return new readonly($object);
    }

    private $object;
}

?>