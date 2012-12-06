<?php

require_once('vars.php');
require_once('locale.php');
require_once('dispatcher.php');
require_once('access.php');
require_once('router.php');
require_once('xslt.php');

class www
{
    static function create($language, $country)
    {
        $www = new www($language, $country);
        return $www;
    }

    function variable($name)
    {
        return $this->vars->get($name);
    }

    function insert_variable($name, $value)
    {
        return $this->vars->insert($name, $value);
    }

    function local($alias)
    {
        return $this->locale->get($alias);
    }

    function access($expression)
    {
        return $this->access->query($expression);
    }

    function request_document($url)
    {
        if($page = $this->router->match($url, $args))
        {
            return new xml($this->render($page, $args));
        }
        else
        {
            return null;
        }
    }

    function query_document($name, $args)
    {
        return new xml($this->dispatcher->query_document($name, $args));
    }

    private function __construct($language, $country)
    {
        $this->vars = new vars();
        $this->locale = new locale($language, $country);
        $this->locale->load(locale_location);
        $this->dispatcher = new dispatcher();
        $this->access = new access($this->vars, $this->dispatcher);
        $this->router = new router($this->access);
        $this->xslt = new xslt();

        include('www_load.php');
    }

    private function render($page, $args)
    {
        return $this->render_template($page->template(), $args);
    }

    private function render_template($template, $args)
    {
        return $this->render_xslt($template, $template->source(), $template->document(), $args);
    }

    private function render_xslt($template, $xsl, $xml, $args = array())
    {
        $this->xslt->import($xsl, $args);
        $document = $this->xslt->transform($xml ? $this->dispatcher->parse_query_document($xml) : new DOMDocument(), $this);
        $this->replace_www($template, $document, $args);
        return $document;
    }

    private function replace_www($template, $document, $args = array())
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('www', 'https://github.com/nyan-cat/easyweb');
        foreach($xpath->query('//www:*') as $node)
        {
            switch($node->nodeName)
            {
            case 'www:template':
                $nested = $this->render_template($template->get($node->attributes->getNamedItem('name')->nodeValue), $args);
                break;
            case 'www:xslt':
                $params = $node->attributes->getNamedItem('args');
                $nested = $this->render_xslt($template, $node->attributes->getNamedItem('xsl')->nodeValue, $node->attributes->getNamedItem('xml')->nodeValue, $params ? args_decode($params->nodeValue) : array());
                break;
            case 'www:style':
                $src = $node->attributes->getNamedItem('src')->nodeValue;
                $nested = $document->createElement('link');
                $nested->setAttribute('rel', 'stylesheet');
                $nested->setAttribute('href', $src . '?' . fs::crc32($src));
                break;
            case 'www:script':
                $src = $node->attributes->getNamedItem('src')->nodeValue;
                $nested = $document->createElement('script', '');
                $nested->setAttribute('type', 'text/javascript');
                $nested->setAttribute('src', $src . '?' . fs::crc32($src));
                break;
            case 'www:bbcode':
                break;
            default:
                runtime_error('Unknown extension tag: ' . $node->nodeName);
            }
            if($nested instanceof DOMDocument)
            {
                foreach($nested->childNodes as $child)
                {
                    $node->parentNode->insertBefore($document->importNode($child, true), $node);
                }
                $node->parentNode->removeChild($node);
            }
            else
            {
                $node->parentNode->replaceChild($nested, $node);
            }
        }
    }

    private $vars;
    private $locale;
    private $dispatcher;
    private $access;
    private $router;
    private $xslt;
}

?>