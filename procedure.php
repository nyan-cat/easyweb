<?php

require_once('exception.php');
require_once('validate.php');

class procedure
{
    function __construct($vars, $name, $params, $empty, $root, $output = array(), $permission = null, $cache = true)
    {
        $this->vars = $vars;
        $this->mangled = self::mangle($name, $params);
        $this->params = $params;
        $this->empty = $empty;
        if($root)
        {
            $this->root = explode(',', $root);
            foreach($this->root as &$value)
            {
                $value = trim($value);
            }
        }

        foreach($output as $name => &$transform)
        {
            $transform = explode(',', $transform);
            foreach($transform as &$value)
            {
                $value = trim($value);
            }
        }

        $this->output = $output;
        $this->permission = $permission;
        $this->cache = $cache;
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

    function permission()
    {
        return $this->permission;
    }

    function cache()
    {
        return $this->cache;
    }

    static function mangle($procedure, $args)
    {
        foreach($args as $name => $vt)
        {
            $procedure .= "[$name]";
        }
        return $procedure;
    }

    static function mangle_values($procedure, $args)
    {
        foreach($args as $name => $value)
        {
            $procedure .= is_array($value) ? "[$name]" : "[$name -> $value]"; //  TODO: mixed parameters cache issue
        }
        return $procedure;
    }

    protected function transform($xml, $name, $value)
    {
        if(isset($this->output[$name]) && !empty($this->output[$name]))
        {
            foreach($this->output[$name] as $transform)
            {
                switch($transform)
                {
                case 'none':
                    return $xml->element($name, htmlspecialchars($value));
                case 'xml':
                    return $this->xml($xml, $name, $value);
                case 'vars':
                    $value = $this->vars->apply($value);
                    break;
                case 'json2xml':
                    return xml::assoc_node($xml, $name, json_decode($value, true));
                case 'nl2p':
                    return $this->nl2p($xml, $name, $value);
                default:
                    runtime_error('Unknown transform: ' . $transform);
                }
            }
            return $xml->element($name, htmlspecialchars($value));
        }
        else
        {
            return $this->nl2br($xml, $name, $value);
        }
    }

    private function xml($xml, $name, $value)
    {
        return $xml->import(xml::parse("<?xml version=\"1.0\" encoding=\"utf-8\" ?><$name>$value</$name>")->root());
    }

    private function nl2br($xml, $name, $value)
    {
        $node = $xml->element($name);
        foreach(preg_split("/(\r\n|\n|\r)/", $value) as $n => $text)
        {
            !$n or $node->append($xml->element('br'));
            $node->append($xml->text($text));
        }
        return $node;
    }

    private function nl2p($xml, $name, $value)
    {
        $node = $xml->element($name);
        foreach(preg_split("/(\r\n|\n|\r)/", $value) as $p)
        {
            $node->append($xml->element('p', htmlspecialchars($p)));
        }
        return $node;
    }

    private $vars;
    private $mangled;
    private $params;
    protected $empty;
    protected $root;
    private $output;
    private $permission;
    private $cache;
}

?>