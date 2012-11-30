<?php

require_once('filesystem.php');
require_once('args.php');

class dispatcher
{
    function insert($procedure)
    {
        $mangled = $procedure->mangled();
        !isset($this->procedures[$mangled]) or runtime_error('Duplicate procedure: ' . $mangled);
        $this->procedures[$mangled] = $procedure;
    }

    function query_document($name, $args)
    {
        return $this->get($name, $args)->query_document($args);
    }

    function parse_query_document($expression)
    {
        if(preg_match('/\A([\w:]+)\(([^\)]*)\)\Z/', $expression, $match))
        {
            return $this->query_document($match[1], args_decode($match[2]));
        }
        else
        {
            $xml = new xml();
            $xml->load($expression);
            return $xml;
        }
    }

    function parse_query_value($expression)
    {
        return $this->parse_query_document($expression)->query('/*[position() = 1]/*[position() = 1]/*[position() = 1]')->checked_first()->checked_value();
    }

    private function get($name, $args)
    {
        $mangled = procedure::mangle($name, $args);
        isset($this->procedures[$mangled]) or runtime_error('Unknown procedure: ' . $mangled);
        return $this->procedures[$mangled];
    }

    private $procedures = array();
}

?>