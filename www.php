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
require_once('bbcode.php');
require_once('response.php');
require_once('session.php');

class www
{
    static function create($language, $country)
    {
        $cache = cache_location . 'cache.tmp';

        if($www = fs::read($cache))
        {
            $www = unserialize($www);
            $www->locale->setup($language, $country);
            return $www;
        }
        else
        {
            $www = new www($language, $country);
            fs::write($cache, serialize($www));
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

    function request($url, &$response = null)
    {
        $page = $this->router->match($url, $args);
        if(!$page)
        {
            $page = $this->router->get('404');
        }
        foreach($args as $name => $value)
        {
            $this->insert_variable("url:$name", $value);
        }
        $response = new response($page->code(), $page->message());
        return $this->render($page, $response);
    }

    function query($name, $args = array())
    {
        return $this->dispatcher->query($name, $args, true);
    }

    function evaluate($name, $args = array())
    {
        return $this->dispatcher->query($name, $args, false);
    }

    private function __construct($language, $country)
    {
        $this->vars = new vars();
        $this->locale = new locale($language, $country);
        $this->locale->load(locale_location);
        $this->dispatcher = new dispatcher();
        $this->access = new access($this->vars, $this->dispatcher);
        $this->dispatcher->attach($this->access);
        $this->router = new router($this->vars, $this->access);
        $this->xslt = new xslt();

        include('www_load.php');
    }

    private function render($page, $response)
    {
        if($action = $page->action())
        {
            include fs::normalize($action);
            action($this, $response);
        }

        if($template = $page->template())
        {
            return $this->render_template($template);
        }
        else
        {
            return null;
        }
    }

    private function render_template($template)
    {
        return $this->render_xslt($template, $template->source(), $template->document(), $template->args());
    }

    private function render_xslt($template, $xsl, $xml, $args = array())
    {
        $this->xslt->import($xsl, $args);
        $document = $this->xslt->transform($xml ? $this->dispatcher->parse_query($xml, true) : new xml(), $this);
        $this->replace_www($template, $document, $args);
        return $document;
    }

    private function replace_www($template, $document, $args = array())
    {
        foreach($document->query('//www:*') as $node)
        {
            $nested = null;
            switch($node->name())
            {
            case 'www:template':
                $nested = $this->render_template($template->get($node['@name']));
                break;
            case 'www:xslt':
                $args = $node->attribute('args');
                if(($cache = $node->attribute('cache')) === null)
                {
                    $nested = $this->render_xslt($template, $node['@xsl'], $node['@xml'], $args ? args::decode($args) : array());
                }
                else
                {
                    $cache = args::decode($cache);
                    $filename = cache_location . md5($node['@xsl'] . $node['@xml'] . $args ? $args : '') . '.xml';

                    if(!fs::exists($filename))
                    {
                        fs::write($filename, $this->render_xslt($template, $node['@xsl'], $node['@xml'], $args ? args::decode($args) : array())->render());
                    }

                    $fragment = fs::checked_read($filename);

                    if(!empty($cache))
                    {
                        $fragment = var::apply_assoc($fragment, $cache);
                    }

                    $nested = $document->fragment($fragment);
                }
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
                $allow = $node->attribute('allow');
                $deny = $node->attribute('deny');
                $nested = bbcode::parse($node, $allow ? preg_split('/, */', $allow) : null, $deny ? preg_split('/, */', $deny) : null);
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
                $node->parent()->replace($node, $nested);
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