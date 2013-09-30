<?php

require_once('datatype.php');
require_once('security.php');

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

    function schema()
    {
        return empty($this->params) ? (object) null : $this->params;
    }

    private $params;
    private $action;
}

?>