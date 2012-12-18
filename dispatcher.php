<?php

require_once('filesystem.php');
require_once('args.php');

class dispatcher
{
    function attach($access)
    {
        $this->access = $access;
    }

    function insert($procedure)
    {
        $mangled = $procedure->mangled();
        !isset($this->procedures[$mangled]) or runtime_error('Duplicate procedure: ' . $mangled);
        $this->procedures[$mangled] = $procedure;
    }

    function query($name, $args, $document)
    {
        $procedure = $this->get($name, $args);
        if($permission = $procedure->permission())
        {
            $this->access->query(vars::apply_assoc($permission, $args, true)) or runtime_error('Procedure ' . $name . ' doesn\'t meet permission ' . $permission);
        }
        $mangled = procedure::mangle_values($name, $args);

        if(!isset($this->cache[$mangled]))
        {
            $this->cache[$mangled] = $procedure->query($args, $document);
        }
        return $this->cache[$mangled];
    }

    function parse_query($expression, $document)
    {
        if(preg_match('/\A([\w:]+)\(([^\)]*)\)\Z/', $expression, $match))
        {
            return $this->query($match[1], args::decode($match[2]), $document);
        }
        else
        {
            if(!isset($this->cache[$expression]))
            {
                $this->cache[$expression] = xml::load($expression);
            }
            return $this->cache[$expression];
        }
    }

    private function get($name, $args)
    {
        $mangled = procedure::mangle($name, $args);
        isset($this->procedures[$mangled]) or runtime_error('Unknown procedure: ' . $mangled);
        return $this->procedures[$mangled];
    }

    private $access = null;
    private $cache = array();
    private $procedures = array();
}

?>