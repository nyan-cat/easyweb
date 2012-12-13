<?php

require_once('filesystem.php');

function wwwaccess($expression, $node)
{
    $doc = new xml($node[0]->ownerDocument);
    $context = new node($node[0]);
    return xslt::top()->access($expression, $doc, $context);
}

function wwwbase64decode($string)
{
    return base64_decode($string);
}

function wwwbase64encode($string)
{
    return base64_encode($string);
}

function wwwcrc32($string)
{
    return crc32($string);
}

function wwwescapeuri($uri)
{
    return urlencode($uri);
}

function wwwlocal($alias)
{
    return xslt::top()->local($alias);
}

function wwwmd5($string)
{
    return md5($string);
}

function wwwquery($name, $args)
{
    return xslt::top()->query_document($name, args::decode($args))->get();
}

function wwwregexreplace($subject, $find, $replace)
{
    return preg_replace("/$find/", $replace, $subject);
}

function wwwreplace($subject, $find, $replace)
{
    return str_replace($find, $replace, $subject);
}

function wwwrfc822($datetime)
{
    return date(DATE_RFC822, strtotime($datetime));
}

function wwwrfc2822($datetime)
{
    return date(DATE_RFC2822, strtotime($datetime));
}

function wwwvar($name)
{
    return xslt::top()->variable($name);
}

class xslt
{
    function __construct()
    {
        $this->initialize();
    }

    function __wakeup()
    {
        $this->initialize();
    }

    static function push($www)
    {
        self::$stack[] = $www;
    }

    static function pop()
    {
        array_pop(self::$stack);
    }

    static function top()
    {
        return end(self::$stack);
    }

    function import($filename, $params = array())
    {
        $xsl = new DOMDocument();
        $xsl->load(fs::normalize($filename));
        $import = $xsl->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:import');
        $href = $xsl->createAttribute('href');
        $href->value = dirname(__FILE__) . '/xpath.xsl';
        $import->appendChild($href);
        $xsl->firstChild->insertBefore($import, $xsl->firstChild->firstChild);
        $xsl->xinclude();
        $this->xslt->importStylesheet($xsl);
        $this->xslt->setParameter('', $params);
    }

    function transform($xml, $www)
    {
        self::push($www);
        $result = new xml($this->xslt->transformToDoc($xml->get()));
        self::pop();
        return $result;
    }

    private function initialize()
    {
        $this->xslt = new XSLTProcessor();
        $this->xslt->registerPHPFunctions(array
        (
            'wwwaccess',
            'wwwbase64decode',
            'wwwbase64encode',
            'wwwcrc32',
            'wwwescapeuri',
            'wwwlocal',
            'wwwregexreplace',
            'wwwreplace',
            'wwwmd5',
            'wwwquery',
            'wwwrfc822',
            'wwwrfc2822',
            'wwwvar'
        ));
    }

    private static $stack = array();
    private $xslt;
}

?>