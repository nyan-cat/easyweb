<?php

require_once('datatype.php');
require_once('security.php');

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
    function __construct($type, $get, $post, $accept, $content_type, $action, $access, $www)
    {
        $this->type = $type;
        $this->get = $get;
        $this->post = $post;
        $this->accept = $accept;
        $this->content_type = $content_type;

        if(is_string($action))
        {
            $this->action = function($get, $post) use($action, $www)
            {
                include(fs::normalize($action));
                $reflection = new ReflectionFunction($action);
                $arguments = $reflection->getParameters();
                if($arguments and $arguments[0]->isArray())
                {
                    return call_user_func(Closure::bind($action, $www), array_merge($get, $post));
                }
                else
                {
                    $args = [];
                    foreach($this->get as $name => $param)
                    {
                        $args[] = isset($get[$name]) ? $get[$name] : (isset($param['default']) ? $param['default'] : null);
                    }
                    foreach($this->post as $name => $param)
                    {
                        $args[] = isset($post[$name]) ? $post[$name] : (isset($param['default']) ? $param['default'] : null);
                    }
                    return call_user_func_array(Closure::bind($action, $www), $args);
                }
            };
        }
        else
        {
            $this->action = function($args) use($action)
            {
                return $action->query($args);
            };
        }

        $this->action = $this->action->bindTo($this, $this);
        $this->access = $access;
    }

    function call($get, $post)
    {
        foreach($this->get as $name => $param)
        {
            if(isset($get[$name]))
            {
                $min = isset($param['min']) ? $param['min'] : null;
                $max = isset($param['max']) ? $param['max'] : null;
                datatype::assert($param['type'], $get[$name], $min, $max);
            }
            else
            {
                !$param['required'] or isset($param['default']) or backend_error('bad_input', "Missing GET parameter: $name");
            }

            if($param['secure'])
            {
                $get[$name] = security::unwrap($get[$name]);
            }
        }

        foreach($this->post as $name => $param)
        {
            if(isset($post[$name]))
            {
                $min = isset($param['min']) ? $param['min'] : null;
                $max = isset($param['max']) ? $param['max'] : null;
                datatype::assert($param['type'], $post[$name], $min, $max);
            }
            else
            {
                !$param['required'] or isset($param['default']) or backend_error('bad_input', "Missing POST parameter: $name");
            }

            if($param['secure'])
            {
                $post[$name] = security::unwrap($post[$name]);
            }
        }

        return $this->action->__invoke($get, $post);
    }

    function match($type, $get, $post)
    {
        if(strtolower($type) != strtolower($this->type))
        {
            return false;
        }

        foreach($this->get as $name => $param)
        {
            if($param['required'] and !isset($get[$name]))
            {
                return false;
            }
        }

        foreach($this->post as $name => $param)
        {
            if($param['required'] and !isset($post[$name]))
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

    function access()
    {
        return $this->access;
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
    private $action;
    private $access = null;
}

?>