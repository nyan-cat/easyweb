<?php

require_once('filesystem.php');

class node implements ArrayAccess
{
    const xpath_mask = '/[^@^\w^\-]/';

    function __construct($node)
    {
        $this->node = $node;
    }

    function offsetExists($offset)
    {
        if(preg_match(self::xpath_mask, $offset))
        {
            return $this->xpath()->query($offset)->length;
        }
        else if($offset[0] == '@')
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
        if(preg_match(self::xpath_mask, $offset))
        {
            return $this->xpath()->evaluate($offset);
        }
        else if($offset[0] == '@')
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
        if(preg_match(self::xpath_mask, $offset))
        {
            $list = $this->xpath()->query($offset);
            if($list->length == 1)
            {
                $list->item(0)->nodeValue = $value;
            }
            else
            {
                runtime_error('Node array set for node set is not supported');
            }
        }
        else if($offset[0] == '@')
        {
            $this->node->setAttribute(substr($offset, 1), $value);
        }
        else
        {
            $node = $this->child($offset) or runtime_error('Child node not found: ' . $offset);
            $node->nodeValue = $value;
        }
    }

    function offsetUnset($offset)
    {
        runtime_error('Node array unset is not supported');
    }

    function uri()
    {
        return $this->node->namespaceURI;
    }

    function ns()
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
        if($value)
        {
            $this->node->nodeValue = $value;
        }
        else
        {
            return $this->node->nodeValue;
        }
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

    function attributes()
    {
        $attributes = array();
        if($this->node->hasAttributes())
        {
            foreach($this->node->attributes as $name => $node)
            {
                $attributes[$name] = $node->nodeValue;
            }
        }
        return $attributes;
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
        return new nodeset($this->node->childNodes);
    }

    function append($node)
    {
        $this->node->appendChild($node->get());
    }

    function insert($new, $node)
    {
        $this->node->insertBefore($new->get(), $node->get());
    }

    function replace($old, $new)
    {
        $this->node->replaceChild($new->get(), $old->get());
    }

    function remove($child)
    {
        $this->node->removeChild($child->get());
    }

    function element()
    {
        return $this->node instanceof DOMElement;
    }

    function text()
    {
        return $this->node instanceof DOMText;
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

    private function xpath()
    {
        if(!$this->xpath)
        {
            $this->xpath = new DOMXPath($this->node->ownerDocument);
            $this->xpath->registerNamespace('www', 'https://github.com/nyan-cat/easyweb');
        }
        return $this->xpath;
    }

    private $node;
    private $xpath = null;
}

class nodeset implements Iterator
{
    function __construct($nodeset)
    {
        $this->nodeset = $nodeset;
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
            runtime_error('XML node set is empty');
        }
    }

    function last()
    {
        return count($this->array) ? $this->array[count($this->array) - 1] : null;
    }

    function checked_last()
    {
        $node = $this->last();
        if($node !== null)
        {
            return $node;
        }
        else
        {
            runtime_error('XML node set is empty');
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

    function size()
    {
        return count($this->array);
    }

    function get()
    {
        return $this->nodeset;
    }

    private $position = 0;
    private $nodeset;
    private $array = array();
}

class xml implements ArrayAccess
{
    function __construct($xml = null)
    {
        $this->xml = $xml ? $xml : new DOMDocument();
    }

    function offsetExists($offset)
    {
        $list = $this->xpath()->query($offset);
        return $list->length;
    }

    function offsetGet($offset)
    {
        return $this->evaluate($offset);
    }

    function offsetSet($offset, $value)
    {
        $list = $this->xpath()->query($offset);
        if($list->length == 1)
        {
            $list->item(0)->nodeValue = $value;
        }
        else
        {
            runtime_error('XML array set for node set is not supported');
        }
    }

    function offsetUnset($offset)
    {
        runtime_error('XML array unset is not supported');
    }

    static function load($filename)
    {
        fs::exists($filename) or runtime_error('XML document not found: ' . $filename);
        $xml = new DOMDocument();
        $xml->load(fs::normalize($filename));
        $xml->xinclude();
        return new xml($xml);
    }

    static function parse($string)
    {
        $xml = new DOMDocument();
        $xml->loadXML($string);
        return new xml($xml);
    }

    static function json($root, $json)
    {
        $xml = new xml();
        $xml->append(self::assoc_node($xml, $root, @json_decode($json, true)));
    }

    static function assoc_node($xml, $name, $assoc)
    {
        is_array($assoc) or runtime_error('Associative array expected');

        $node = $xml->element($name);

        foreach($assoc as $key => $value)
        {
            $key = is_numeric($key) ? 'element' : $key;
            if(is_array($value))
            {
                $node->append(self::assoc_node($xml, $key, $value));
            }
            else
            {
                $node->append($xml->element($key, $value));
            }
        }
        return $node;
    }

    function query($expression, $context = null)
    {
        return $context ? new nodeset($this->xpath()->query($expression, $context->get())) : new nodeset($this->xpath()->query($expression));
    }

    function evaluate($expression, $context = null)
    {
        $result = $this->xpath()->evaluate($expression, $context ? $context->get() : null);
        return $result instanceof DOMNodeList ? $result->item(0)->nodeValue : $result;
    }

    function query_assoc($expression, $context, $key, $value)
    {
        $result = array();
        foreach($this->query($expression, $context) as $node)
        {
            $result[$node[$key]] = $node[$value];
        }
        return $result;
    }

    function append($node)
    {
        $this->xml->appendChild($node->get());
    }

    function element($name, $value = null)
    {
        return new node($value !== null ? $this->xml->createElement($name, $value) : $this->xml->createElement($name));
    }

    function text($content)
    {
        return new node($this->xml->createTextNode($content));
    }

    function cdata($content)
    {
        return new node($this->xml->createCDATASection($content));
    }

    function fragment($content)
    {
        $fragment = $this->xml->createDocumentFragment();
        $fragment->appendXML($content);
        return new node($fragment);
    }

    function import($node)
    {
        return new node($this->xml->importNode($node->get(), true));
    }

    function root()
    {
        return new node($this->xml->documentElement);
    }

    function children()
    {
        return new nodeset($this->xml->childNodes);
    }

    function blank()
    {
        return !$this->xml->hasChildNodes();
    }

    function render($declaration = true)
    {
        if($declaration)
        {
            return $this->xml->saveXML();
        }
        else
        {
            $xml = '';
            foreach($this->children() as $child)
            {
                $xml .= $this->xml->saveXML($child->get());
            }
            return $xml;
        }
    }

    function register($uri, $namespace)
    {
        $this->xpath()->registerNamespace($namespace, $uri);
    }

    function get()
    {
        return $this->xml;
    }

    private function xpath()
    {
        if(!$this->xpath)
        {
            $this->xpath = new DOMXPath($this->xml);
            $this->xpath->registerNamespace('www', 'https://github.com/nyan-cat/easyweb');
        }
        return $this->xpath;
    }

    private $xml;
    private $xpath = null;
}

?>