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

function wwwlocale($name)
{
    $locale = xslt::top()->locale();
    switch($name)
    {
    case 'country':
        return $locale->country();
    case 'language':
        return $locale->language();
    default:
        runtime_error('Unknown locale parameter name: ' . $name);
    }
}

function wwwpaginate($current, $count, $size)
{
    $begin = $current - (int)($size / 2);
    $end = $begin + $size;
    if($begin < 1)
    {
        $begin = 1;
        $end = min($count, $size);
    }
    if($end > $count)
    {
        $begin = max(1, $count - $size);
        $end = $count;
    }
    $previous = max(1, $current - 1);
    $next = min($count, $current + 1);

    $xml = new xml();

    $pages = $xml->element('pages');
    $xml->append($pages);
    
    if($previous != $current)
    {
        $pages->append($xml->element('previous', $previous));
    }

    for($n = $begin; $n < $end; ++$n)
    {
        $page = $xml->element('page', $n);
        if($n == $current)
        {
            $page['@current'] = 'current';
        }
        $pages->append($page);
    }

    if($next != $current)
    {
        $pages->append($xml->element('next', $next));
    }

    return $xml->get();
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

function wwwsequence($count)
{
    $xml = new xml();
    $sequence = $xml->element('sequence');
    $xml->append($sequence);
    for($n = 1; $n <= $count; ++$n)
    {
        $number = $xml->element('number', $n);
        $sequence->append($number);
    }
    return $xml->get();
}

function wwwsession($type, $name)
{
    $xml = new xml();
    switch($type)
    {
    case 'value':
        $xml->append($xml->element($name, session::value($name)));
        break;

    case 'vector':
        foreach(session::vector($name) as $value)
        {
            $xml->append($xml->element('value', $value));
        }
        break;

    case 'map':
        foreach(session::map($name) as $key => $value)
        {
            $xml->append($xml->element($key, $value));
        }
        break;

    case 'xml':
        $xml = session::xml($name);
        break;

    case 'object':
        $xml = xml::json($name, session::value($name));
        break;

    default:
        runtime_error('Unknown session variable type: ' . $type);
    }
    return $xml->get();
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
        fs::exists($filename) or runtime_error('XSL stylesheet not found: ' . $filename);
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
            'wwwlocale',
            'wwwpaginate',
            'wwwregexreplace',
            'wwwreplace',
            'wwwmd5',
            'wwwquery',
            'wwwrfc822',
            'wwwrfc2822',
            'wwwsequence',
            'wwwsession',
            'wwwvar'
        ));
    }

    private static $stack = array();
    private $xslt;
}

?>