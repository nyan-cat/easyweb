<?php

require_once('procedure.php');

class php_procedure extends procedure
{
    function __construct($name, $params, $required, $result, $requires, $body, $www, $self)
    {
        parent::__construct($params, self::make_id($name, $params), $requires, $result);

        $imploded = '$' . implode(',$', array_keys($params));
        $this->body = '';
        foreach($requires as $require)
        {
            $this->body .= 'require_once(\'' . $www->folder('_require') . $require . '\');';
        }
        $this->body .= 'return function(' . (empty($params) ? '' : $imploded) . ") { {$body} };";
        $this->self = $self;
    }

    function query_direct($args)
    {
        $closure = eval($this->body);
        $result = call_user_func_array($closure->bindTo($this->self), array_values($args));

        switch($this->result)
        {
        case 'value':
            (!is_array($result) and !is_object($result)) or backend_error('bad_query', 'PHP result is not a value');
            return $result;

        case 'object':
            if(is_object($result))
            {
                return $result;
            }
            elseif(is_array($result) and $result !== array_values($result))
            {
                return (object) $result;
            }
            else
            {
                backend_error('bad_query', 'PHP result is not an object');
            }

        case 'array':
            (is_array($result) and $result === array_values($result)) or backend_error('bad_query', 'PHP result is not an array');
            return $result;

        case 'mixed':
            return $result;

        default:
            backend_error('bad_query', 'Unsupported PHP query result type: ' . $this->result);
        }
    }

    private $body;
    private $self;
}

?>