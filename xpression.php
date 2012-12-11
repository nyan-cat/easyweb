<?php

class xpression
{
    function __construct($expression = '', $name = '', $params = array())
    {
        if(!self::$xml)
        {
            self::$xml = new DOMDocument();
            self::$xpath = new DOMXPath(self::$xml);
        }

        $this->mangled = self::mangle($name, $params);
        $this->params = $params;
        $this->expression = $expression;
    }

    function validate($args)
    {
        foreach($args as $name => $value)
        {
            validate::assert($this->params[$name], $value);
        }
    }

    function mangled()
    {
        return $this->mangled;
    }

    function get($args)
    {
        $this->validate($args);
        return vars::apply_assoc($this->expression, $args, true);
    }

    static function evaluate($xpath)
    {
        return self::$xpath->evaluate($xpath);
    }

    static function mangle($xpression, $args)
    {
        foreach($args as $name => $vt)
        {
            $xpression .= "[$name]";
        }
        return $xpression;
    }

    static function mangle_values($xpression, $args)
    {
        foreach($args as $name => $value)
        {
            $xpression .= "[$name -> $value]";
        }
        return $xpression;
    }

    static private $xml = null;
    static private $xpath = null;
    private $mangled;
    private $params;
    private $expression;
}

?>