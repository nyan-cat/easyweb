<?php

require_once('exception.php');
require_once('vars.php');

class access
{
    function __construct($vars, $dispatcher)
    {
        $this->vars = $vars;
        $this->dispatcher = $dispatcher;
    }

    function insert_role($name, $expression)
    {
        !isset($this->roles[$name]) or runtime_error('Duplicate role: ' . $name);
        $this->roles[$name] = $expression;
    }

    function insert_permission($name, $expression)
    {
        !isset($this->permissions[$name]) or runtime_error('Duplicate permission: ' . $name);
        $this->permissions[$name] = $expression;
    }

    function query($expression, $args = array())
    {
        if(!isset($this->expression_cache[$expression]))
        {
            $this->expression_cache[$expression] = $this->evaluate(preg_replace('/([\w:]+)/e', "\$this->replace_permission('\1', \$args)", $expression));
        }
        return $this->expression_cache[$expression];
    }

    private function replace_permission($name, $args = array())
    {
        if(!isset($this->permission_cache[$name]))
        {
            isset($this->permissions[$name]) or runtime_error('Permission not found: ' . $name);
            $this->permission_cache[$name] = $this->evaluate(preg_replace('/([\w:]+)/e', "\$this->replace_role('\1', \$args);", $this->permissions[$name]));
        }
        return $this->permission_cache[$name];
    }

    private function replace_role($name, $args = array())
    {
        if(!isset($this->role_cache[$name]))
        {
            isset($this->roles[$name]) or runtime_error('Role not found: ' . $name);
            $expression = $this->vars->apply(vars::apply_assoc($this->roles[$name], $args));
            $expression = preg_replace("/([\w:]+\([^\)]*\))/e", "\$this->dispatcher->parse_query_value('\\1');");
            $this->role_cache[$name] = $this->evaluate($expression);
        }
        return $this->role_cache[$name];
    }

    private function evaluate($expression)
    {
        preg_match('/\A[\w\s\$\(\)]+\Z/', $expression) or runtime_error('Illegal characters in access expression: ' . $expression);
        return eval("return ($expression) ? 1 : 0;");
    }

    private $vars;
    private $dispatcher;
    private $expression_cache = array();
    private $permission_cache = array();
    private $role_cache = array();
    private $roles = array();
    private $permissions = array();
}

?>