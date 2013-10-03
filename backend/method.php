<?php

require_once('datatype.php');
require_once('security.php');

class method
{
    function __construct($type, $get, $post, $action, $www)
    {
        $this->type = $type;
        $this->get = $get;
        $this->post = $post;
        $this->params = array_merge($get, $post);

        if(is_string($action))
        {
            $this->action = function($args) use($action, $www)
            {
                include(fs::normalize($action));
                return call_user_func_array(Closure::bind($action, $www), $args);
            };
        }
        else
        {
            $this->action = function($args) use($action)
            {
                return $action->query($args);
            };
        }
    }

    function call($args)
    {
        foreach($this->params as $name => $param)
        {
            isset($args[$name]) or backend_error('bad_input', "Missing parameter: $name");
            datatype::assert($param['type'], $args[$name]);

            if($param['secure'])
            {
                $args[$name] = security::unwrap($args[$name]);
            }
        }

        return $this->action->__invoke($args);
    }

    function match($type, $get, $post)
    {
        return $this->type == $type and sort(array_keys($this->get)) == sort(array_keys($get)) and sort(array_keys($this->post)) == sort(array_keys($post));
    }

    function assert($type, $get, $post)
    {
        $this->match($type, $get, $post) or backend_error('bad_request', 'Request parameters doesn\'t match to schema');
    }

    function schema()
    {
        return [$this->type, $this->get, $this->post];
    }

    private $type;
    private $get;
    private $post;
    private $params;
    private $action;
}

?>