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

            if(get_class($child) == 'DOMAttr')
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

    static function load($filename)
    {
        $document = new \DOMDocument();
        $document->load($filename) or replace_me();
        $document->xinclude();
        return new document($document);
    }

    static function download($url)
    {
        $document = new \DOMDocument();
        $allow_url_fopen = ini_get('allow_url_fopen');
        ini_set('allow_url_fopen', 1);
        $document->load($url);
        ini_set('allow_url_fopen', $allow_url_fopen);
        return new document($document);
    }

    static function parse($string)
    {
        $document = new \DOMDocument();
        $document->loadXML($string);
        return new document($document);
    }

    private $document;
    private $xpath;
}

?>