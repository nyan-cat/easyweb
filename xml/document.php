<?php

namespace xml;

require_once('nodelist.php');

class document implements \ArrayAccess
{
    function __construct($document = null)
    {
        $this->document = $document ? $document : new \DOMDocument();
        $this->xpath = new \DOMXPath($this->document);
    }

    function offsetExists($offset)
    {
        return $this->xpath->query($offset)->length != 0;
    }

    function offsetGet($offset)
    {
        return (string) $this->query($offset);
    }

    function offsetSet($offset, $value)
    {
        $result = $this->xpath->query($offset);
        if($result->length == 1)
        {
            $result->item(0)->nodeValue = $value;
        }
    }

    function offsetUnset($offset)
    {
        foreach($this->query($offset) as $child)
        {
            $child = $child->native();

            if($child instanceof \DOMAttr)
            {
                $child->parentNode->removeAttribute(substr($offset, 1));
            }
            else
            {
                $child->parentNode->removeChild($child);
            }
        }
    }

    function query($expression, $context = null)
    {
        return new nodelist($this->xpath->query($expression, $context ? $context->native() : $this->document->documentElement));
    }

    function append($node)
    {
        $this->document->appendChild($node->native());
    }

    function element($name, $value = null)
    {
        return new node($value !== null ? $this->document->createElement($name, $value) : $this->document->createElement($name));
    }

    function render()
    {
        $this->document->formatOutput = true;
        return $this->document->saveXML();
    }

    static function load($filename)
    {
        $document = new \DOMDocument();
        if($document->load($filename) !== false)
        {
            $document->xinclude();
            return new document($document);
        }
        else
        {
            return null;
        }
    }

    static function download($url)
    {
        $document = new \DOMDocument();
        $allow_url_fopen = ini_get('allow_url_fopen');
        ini_set('allow_url_fopen', 1);
        $loaded = $document->load($url);
        ini_set('allow_url_fopen', $allow_url_fopen);
        return $loaded ? new document($document) : null;
    }

    static function parse($string)
    {
        $document = new \DOMDocument();
        return $document->loadXML($string) ? new document($document) : null;
    }

    private $document;
    private $xpath;
}

?>