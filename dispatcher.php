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
        $procedure = $this->get($name, $args);
        $mangled = $procedure->mangled();

        if(!isset($this->cache[$mangled]))
        {
            $this->cache[$mangled] = $procedure->query_document($args);
        }
        return $this->cache[$mangled];
    }

    function parse_query_document($expression)
    {
        if(preg_match('/\A([\w:]+)\(([^\)]*)\)\Z/', $expression, $match))
        {
            return $this->query_document($match[1], args_decode($match[2]));
        }
        else
        {
            if(!isset($this->cache[$expression]))
            {
                $xml = new xml();
                $xml->load($expression);
                $this->cache[$expression] = $xml;
            }
            return $this->cache[$expression];
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

    private $cache = array();
    private $procedures = array();
}

?>