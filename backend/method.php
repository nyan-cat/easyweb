<?php

class method
{
    function __construct($params, $action, $www)
    {
        $this->params = $params;

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
        foreach($this->params as $name => $type)
        {
            isset($params[$name]) or backend_error('bad_input', "Missing parameter: $name");
            preg_match($type['pattern'], $params[$name]) or backend_error('bad_input', "Parameter type mismatch: $name: " . $type['pattern'] . ' -> ' . $params[$name]);
            if($type['secured'])
            {
                security::assert($params[$name]['value'], $params[$name]['digest']);
            }
        }

        return $this->action->__invoke($args);
    }

    private $params;
    private $action;
}

?>