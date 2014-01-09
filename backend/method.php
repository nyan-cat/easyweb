<?php

require_once('datatype.php');
require_once('security.php');
require_once('php_procedure.php');

class headers implements ArrayAccess, Iterator, Countable
{
    function __construct()
    {
        $this->iterator = new ArrayIterator($this->headers);
    }

    function offsetExists($offset)
    {
        return isset($this->headers[strtolower($offset)]);
    }

    function offsetGet($offset)
    {
        return $this->headers[strtolower($offset)]['value'];
    }

    function offsetSet($offset, $value)
    {
        return $this->headers[strtolower($offset)] = ['name' => $offset, 'value' => $value];
    }

    function offsetUnset($offset)
    {
        unset($this->headers[strtolower($offset)]);
    }

    function current()
    {
        return current($this->headers)['value'];
    }

    function key()
    {
        return current($this->headers)['name'];
    }
    
    function next()
    {
        next($this->headers);
    }
    
    function rewind()
    {
        reset($this->headers);
    }
    
    function valid()
    {
        return current($this->headers) !== false;
    }

    function count()
    {
        return count($this->headers);
    }
    
    private $headers = [];
}

class method
{
    function __construct($type, $get, $post, $accept, $content_type, $host, $access, $procedure, $require, $body, $www)
    {
        $this->type = $type;
        $this->get = $get;
        $this->post = $post;
        $this->accept = $accept;
        $this->content_type = $content_type;
        $this->host = $host;
        $this->access = $access;
        $this->procedure = $procedure;

        if(strlen(trim($body)))
        {
            $params = [];

            foreach($get as $name => $param)
            {
                $params[$name] = $param->type;
            }

            foreach($post as $name => $param)
            {
                $params[$name] = $param->type;
            }

            $this->body = new php_procedure('', $params, false, 'mixed', $require, $body, $www, $www);
        }
        $this->www = $www;

        (!$this->procedure or !$this->body) or backend_error('bad_config', 'Either method procedure or method body shall be defined');
    }

    function call($get, $post)
    {
        $args = [];

        foreach($this->get as $name => $param)
        {
            if(isset($param->domains) and isset($get[$name]))
            {
                if(($value = security::unwrap($get[$name], $param->domains)) !== null)
                {
                    $get[$name] = $value;
                }
                else
                {
                    unset($get[$name]);
                }
            }

            $value = isset($get[$name]) ? $get[$name] : (isset($param->default) ? $param->default : ($param->required ? backend_error('bad_input', "Missing GET parameter: $name") : null));

            if(!is_null($value))
            {
                $min = isset($param->min) ? $param->min : null;
                $max = isset($param->max) ? $param->max : null;
                !isset($param->type) or datatype::assert($param->type, $value, $min, $max);
                $args[$name] = $value;
            }
        }

        foreach($this->post as $name => $param)
        {
            if(isset($param->domains) and isset($post[$name]))
            {
                if(($value = security::unwrap($post[$name], $param->domains)) !== null)
                {
                    $post[$name] = $value;
                }
                else
                {
                    unset($post[$name]);
                }
            }

            $value = isset($post[$name]) ? $post[$name] : (isset($param->default) ? $param->default : ($param->required ? backend_error('bad_input', "Missing POST parameter: $name") : null));

            if(!is_null($value))
            {
                $min = isset($param->min) ? $param->min : null;
                $max = isset($param->max) ? $param->max : null;
                !isset($param->type) or datatype::assert($param->type, $value, $min, $max);
                $args[$name] = $value;
            }
        }

        if($this->access and !$this->www->parse_query($this->access, $args))
        {
            return [false, null];
        }

        if($this->body)
        {
            return [true, $this->body->query_direct($args)];
        }
        else
        {
            $procedure = $this->procedure;
            return [true, $this->www->$procedure($args)];
        }
    }

    function match($type, $get, $post)
    {
        if(strtolower($type) != strtolower($this->type))
        {
            return false;
        }

        foreach($this->get as $name => $param)
        {
            if($param->required and !isset($get[$name]))
            {
                return false;
            }
        }

        foreach($this->post as $name => $param)
        {
            if($param->required and !isset($post[$name]))
            {
                return false;
            }
        }

        return true;
    }

    function accept()
    {
        return $this->accept;
    }

    function content_type()
    {
        return $this->content_type;
    }

    function host()
    {
        return $this->host;
    }

    function schema()
    {
        return [$this->type, $this->get, $this->post];
    }

    private $type;
    private $get;
    private $post;
    private $accept;
    private $content_type;
    private $host;
    private $access;
    private $procedure;
    private $body;
    private $www;
}

?>