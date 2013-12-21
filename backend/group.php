<?php

class group
{
    function __construct($name, $params)
    {
        $this->id = self::make_id($name, $params);
        $this->params = $params;
    }

    function id()
    {
        return $this->id;
    }

    static function make_id($name, $params)
    {
        return $name . '[' . implode(',', $params) . ']';
    }

    protected $id;
    protected $params;
}

class group_procedure extends group
{
    function __construct($name, $params, $procedure, $www)
    {
        parent::__construct($name, $params);
        $this->procedure = $procedure;
        $this->www = $www;
    }

    function query($args)
    {
        $matched = [];

        foreach($this->params as $name)
        {
            isset($args[$name]) or backend_error('bad_group', "Argument not found: $name");
            $matched[$name] = $args[$name];
        }

        return $this->www->query($this->procedure, $matched);
    }

    private $procedure;
    private $www;
}

class group_expression extends group
{
    function __construct($name, $params, $body, $access)
    {
        parent::__construct($name, $params);
        $this->body = trim($body);
        $this->access = $access;
    }

    function query($args)
    {
        if(!self::$xml)
        {
            self::$xml = new DOMDocument();
            self::$xpath = new DOMXPath(self::$xml);
        }

        $xpath = $this->substitute($args);

        return self::$xpath->evaluate($xpath);
    }

    private function substitute($args)
    {
        $procedures = function($matches) use($args)
        {
            $result = $this->access->parse_query($matches[1], $args);
            if($result === false)
            {
                $result = '0';
            }
            return $result;
        };

        $values = function($matches) use($args)
        {
            isset($args[$matches[1]]) or backend_error('bad_group', 'Unknown group variable: ' . $matches[1]);
            return $args[$matches[1]];
        };

        $result = preg_replace_callback('/(\w+\[[\w,]*\])/', $procedures->bindTo($this, $this), $this->body);

        return preg_replace_callback('/\$(\w+)/i', $values, $result);
    }

    private $body;
    private $access;

    private static $xml = null;
    private static $xpath = null;
}

?>