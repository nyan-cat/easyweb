<?php

class group_procedure
{
    function __construct($name, $params, $bindings, $www)
    {
        $this->name = $name;
        $this->params = $params;
        $this->bindings = $bindings;
        $this->www = $www;
    }

    function evaluate($args)
    {
        $bounded = [];

        foreach($this->bindings as $name => $value)
        {
            $bounded[$name] = self::substitute($value, $args);
        }

        return $www->evaluate($name, $bounded);
    }

    private static function substitute($body, $args)
    {
        preg_replace('/\$(\w+)/e', "self::replace('\\1', \$args)", $body);
    }

    private static function replace($name, $args)
    {
        isset($args[$name]) or backend_error('bad_group', "Unknown group parameter: $name");
        return $args[$name];
    }

    private $name;
    private $params;
    private $bindings;
    private $www;
}

class group_expression
{
    function __construct($body, $params)
    {
    }

    function evaluate($args)
    {
        if(!self::$xml)
        {
            self::$xml = new DOMDocument();
            self::$xpath = new DOMXPath(self::$xml);
        }

        return self::$xpath->evaluate($xpath);
    }

    private static $xml = null;
    private static $xpath = null;
}

?>