<?php

require_once('exception.php');

class vars
{
    function get($name)
    {
        isset($this->vars[$name]) or runtime_error('Variable not found: ' . $name);
        return $this->vars[$name];
    }

    function set($name, $value)
    {
        $this->vars[$name] = $this->apply($value);
    }

    function insert($name, $value)
    {
        !isset($this->vars[$name]) or runtime_error('Duplicate variable name: ' . $name);
        $this->vars[$name] = $this->apply($value);
    }

    function apply($string)
    {
        return preg_replace('/{{([^}]+)}}/e', "\$this->replace('\\1')", $string);
    }

    static function apply_assoc($string, $vars)
    {
        return preg_replace('/{{([^}]+)}}/e', "self::replace_assoc('\\1', $vars)", $string);
    }

    private function replace($name)
    {
        return isset($this->vars[$name]) ? $this->vars[$name] : '{{' . $name . '}}';
    }

    private static function replace_assoc($name, $vars)
    {
        return isset($vars[$name]) ? $vars[$name] : '{{' . $name . '}}';
    }

    private $vars = array();
}

?>