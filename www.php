<?php

require_once('vars.php');
require_once('locale.php');
require_once('dispatcher.php');
require_once('access.php');
require_once('page.php');
require_once('template.php');
require_once('router.php');
require_once('sql_datasource.php');
require_once('sql_procedure.php');
require_once('xslt.php');

class www
{
    static function create($language, $country)
    {
        if($www = fs::read(cache_location))
        {
            return unserialize($www);
        }
        else
        {
            $www = new www($language, $country);
            fs::write(cache_location, serialize($www));
            return $www;
        }
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

    function access($expression, $doc = null, $context = null)
    {
        return $this->access->query($expression, $doc, $context);
    }

    function request_document($url)
    {
        if($page = $this->router->match($url, $args))
        {
            foreach($args as $name => $value)
            {
                $this->insert_variable("url:$name", $value);
            }
            return $this->render($page, $args);
        }
        else
        {
            return null;
        }
    }

    function query_document($name, $args)
    {
        return $this->dispatcher->query_document($name, $args);
    }

    private function __construct($language, $country)
    {
        $this->vars = new vars();
        $this->locale = new locale($language, $country);
        $this->locale->load(locale_location);
        $this->dispatcher = new dispatcher();
        $this->access = new access($this->vars, $this->dispatcher);
        $this->router = new router($this->vars, $this->access);
        $this->xslt = new xslt();

        include('www_load.php');
    }

    private function render($page, $args)
    {
        if($action = $page->action())
        {
            include fs::normalize($action);
            action($this);
        }

        if($template = $page->template())
        {
            return $this->render_template($template, $args);
        }
        else
        {
            return null;
        }
    }

    private function render_template($template, $args)
    {
        return $this->render_xslt($template, $template->source(), $template->document(), $args);
    }

    private function render_xslt($template, $xsl, $xml, $args = array())
    {
        $this->xslt->import($xsl, $args);
        $document = $this->xslt->transform($xml ? $this->dispatcher->parse_query_document($xml) : new xml(), $this);
        $this->replace_www($template, $document, $args);
        return $document;
    }

    private function replace_www($template, $document, $args = array())
    {
        foreach($document->query('//www:*') as $node)
        {
            switch($node->name())
            {
            case 'www:template':
                $nested = $this->render_template($template->get($node['@name']), $args);
                break;
            case 'www:xslt':
                $params = $node->attribute('@args');
                $nested = $this->render_xslt($template, $node['@xsl'], $node['@xml'], $params ? args::decode($params) : array());
                break;
            case 'www:style':
                $src = $node['@src'];
                $nested = $document->element('link');
                $nested['@rel'] = 'stylesheet';
                $nested['@href'] = $src . '?' . fs::crc32($src);
                break;
            case 'www:script':
                $src = $node['@src'];
                $nested = $document->element('script', '');
                $nested['@type'] = 'text/javascript';
                $nested['@src'] = $src . '?' . fs::crc32($src);
                break;
            case 'www:bbcode':
                break;
            default:
                runtime_error('Unknown extension tag: ' . $node->name());
            }
            if($nested instanceof xml)
            {
                $parent = $node->parent();
                foreach($nested->children() as $child)
                {
                    $parent->insert($document->import($child), $node);
                }
                $parent->remove($node);
            }
            else
            {
                $node->parent()->replace($nested, $node);
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