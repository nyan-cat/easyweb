<?php

require_once('exception.php');
require_once('vars.php');
require_once('xpression.php');

class access
{
    function __construct($vars, $dispatcher)
    {
        $this->vars = $vars;
        $this->dispatcher = $dispatcher;
        $this->xpression = new xpression();
    }

    function insert_group($name, $xpression)
    {
        $mangled = $xpression->mangled();
        !isset($this->groups[$mangled]) or runtime_error('Duplicate group: ' . $mangled);
        $this->groups[$mangled] = $xpression;
    }

    function insert_permission($name, $xpression)
    {
        $mangled = $xpression->mangled();
        !isset($this->permissions[$mangled]) or runtime_error('Duplicate permission: ' . $mangled);
        $this->permissions[$mangled] = $xpression;
    }

    function query($expression, $doc = null, $context = null)
    {
        $expression = $this->vars->apply($expression, true);
        if($doc)
        {
            $expression = preg_replace('/([\w:]+\w)\(([^\)]+)\)/e', "\$this->replace_xpath('\\1', '\\2', \$doc, \$context)", $expression);
        }
        $expression = preg_replace('/([\w:]+\w)\(([^\)]+)\)/e', "\$this->replace_permission('\\1', '\\2')", $expression);
        return $doc ? $doc->evaluate($expression, $context) : xpression::evaluate($expression);
    }

    private function permission($name, $args)
    {
        $mangled = xpression::mangle($name, $args);
        isset($this->permissions[$mangled]) or runtime_error('Permission not found: ' . $mangled);
        return $this->permissions[$mangled];
    }

    private function group($name, $args)
    {
        $mangled = xpression::mangle($name, $args);
        isset($this->groups[$mangled]) or runtime_error('Group not found: ' . $mangled);
        return $this->groups[$mangled];
    }

    private function replace_xpath($permission, $args, $doc, $context)
    {
        $args = stripslashes($args);
        $args = args::decode($args);
        foreach($args as $name => &$value)
        {
            if(preg_match('/\A\w+\Z/', $value))
            {
                $value = $doc->evaluate($value, $context);
            }
        }
        return $permission . '(' . args::encode($args) . ')';
    }

    private function replace_permission($name, $args)
    {
        $args = stripslashes($args);
        $args = args::decode($args);
        $expression = $this->permission($name, $args)->get($args);
        return preg_replace('/([\w:]+\w)\(([^\)]+)\)/e', "\$this->replace_group('\\1', '\\2');", $expression);
    }

    private function replace_group($name, $args)
    {
        $args = stripslashes($args);
        $args = args::decode($args);
        $expression = $this->group($name, $args)->get($args);
        $expression = $this->vars->apply($expression, true);
        return preg_replace('/(([\w:]+\w)\(([^\)]+)\))/e', "\$this->replace_query('\\1', '\\2')", $expression);
    }

    private function replace_query($expression)
    {
        $expression = stripslashes($expression);
        $result = $this->dispatcher->parse_query_value($this->vars->apply($expression));
        return args::quote($result);
    }

    private $vars;
    private $dispatcher;
    private $xpression;
    private $groups = array();
    private $permissions = array();
}

?>