<?php

namespace xml;

class node implements \ArrayAccess
{
    function __construct($node)
    {
        $this->node = $node;
        $this->xpath = new \DOMXPath($this->node->ownerDocument);
    }

    function offsetExists($offset)
    {
        return $this->xpath->query($offset, $this->node)->length != 0;
    }

    function offsetGet($offset)
    {
        return (string) $this->query($offset);
    }

    function offsetSet($offset, $mixed)
    {
        $result = $this->xpath->query($offset, $this->node);
        if($result->length == 1)
        {
            $result->item(0)->nodeValue = $value;
        }
    }

    function offsetUnset($offset)
    {
        // TODO: Exception?
    }

    function query($expression)
    {
        return new nodelist($this->xpath->query($expression, $this->node));
    }

    function uri()
    {
        return $this->node->namespaceURI;
    }

    function xmlns()
    {
        return $this->node->prefix;
    }

    function name()
    {
        return $this->node->nodeName;
    }

    function path()
    {
        return $this->node->getNodePath();
    }

    function value($value = null)
    {
        if(is_null($value))
        {
            return $this->node->nodeValue;
        }
        else
        {
            $this->node->nodeValue = $value;
        }
    }

    function attributes()
    {
        foreach($this->node->attributes as $name => $node)
        {
            yield $name => $node->nodeValue;
        }
    }

    function attribute($name, $default = null)
    {
        $attribute = $this->node->attributes->getNamedItem($name);
        return $attribute ? $attribute->nodeValue : $default;
    }

    function parent()
    {
        return new node($this->node->parentNode);
    }

    function children()
    {
        return new nodelist($this->node->childNodes);
    }

    function append($node)
    {
        $this->node->appendChild($node->native());
    }

    function insert($new, $node)
    {
        $this->node->insertBefore($new->native(), $node->native());
    }

    function replace($old, $new)
    {
        $this->node->replaceChild($new->native(), $old->native());
    }

    function remove($child)
    {
        $this->node->removeChild($child->native());
    }

    function __tostring()
    {
        return $this->value();
    }

    function native()
    {
        return $this->node;
    }

    private $node;
    private $xpath;
}

?>