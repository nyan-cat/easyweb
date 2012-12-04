<?php

require_once('filesystem.php');

class node implements ArrayAccess
{
    function __construct($node)
    {
        $this->node = $node;
    }

    function offsetExists($offset)
    {
        if($offset[0] == '@')
        {
            return $this->node->attributes->getNamedItem(substr($offset, 1)) !== null;
        }
        else
        {
            return $this->child($offset) !== null;
        }
    }

    function offsetGet($offset)
    {
        if($offset[0] == '@')
        {
            $attribute = $this->node->attributes->getNamedItem(substr($offset, 1)) or runtime_error('Attribute not found: ' . $offset);
            return $attribute->nodeValue;
        }
        else
        {
            $node = $this->child($offset) or runtime_error('Child node not found: ' . $offset);
            return $node->nodeValue;
        }
    }

    function offsetSet($offset, $value)
    {
        runtime_error('Call to private method');
    }

    function offsetUnset($offset)
    {
        runtime_error('Call to private method');
    }

    function name()
    {
        return $this->node->nodeName;
    }

    function path()
    {
        return $this->node->getNodePath();
    }

    function value()
    {
        return $this->node->nodeValue;
    }

    function checked_value()
    {
        $value = $this->value();
        if($value !== null)
        {
            return $value;
        }
        else
        {
            runtime_error('XML node value is null');
        }
    }

    function attribute($name, $default = null)
    {
        $attribute = $this->node->attributes->getNamedItem($name);
        return $attribute ? $attribute->nodeValue : $default;
    }

    function get()
    {
        return $this->node;
    }

    private function child($name)
    {
        foreach($this->node->childNodes as $node)
        {
            if($node->nodeName == $name)
            {
                return $node;
            }
        }
        return null;
    }

    private $node;
}

class nodeset implements Iterator
{
    function __construct($nodeset)
    {
        foreach($nodeset as $node)
        {
            $this->array[] = new node($node);
        }
    }

    function first()
    {
        return isset($this->array[0]) ? $this->array[0] : null;
    }

    function checked_first()
    {
        $node = $this->first();
        if($node !== null)
        {
            return $node;
        }
        else
        {
            runtime_error('XML node set is null');
        }
    }

    function rewind()
    {
        $this->position = 0;
    }

    function current()
    {
        return $this->array[$this->position];
    }

    function key()
    {
        return $this->position;
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        return isset($this->array[$this->position]);
    }

    function get()
    {
        return $this->nodeset;
    }

    private $position = 0;
    private $nodeset;
    private $array = array();
}

class xml
{
    function __construct($xml = null)
    {
        $this->xml = $xml ? $xml : new DOMDocument();
    }

    function load($filename)
    {
        $this->xml->load(fs::normalize($filename));
        $this->xml->xinclude();
    }

    function query($expression, $root = null)
    {
        $xpath = new DOMXPath($this->xml);
        return $root ? new nodeset($xpath->query($expression, $root->get())) : new nodeset($xpath->query($expression));
    }

    function query_assoc($expression, $root, $key, $value)
    {
        $result = array();
        foreach($this->query($expression, $root) as $node)
        {
            $result[$node[$key]] = $node[$value];
        }
        return $result;
    }

    function text()
    {
        return $this->xml->saveXML();
    }

    private $xml;
}

?>