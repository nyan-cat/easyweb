<?php

require_once(www_root . 'backend/wip/procedure.php');

class php_procedure extends procedure
{
    function __construct($name, $params, $required, $result, $script)
    {
        parent::__construct($name, $params, $required, $result);

        $this->script = $script;
    }

    function query_direct($args)
    {
        $result = $this->script->evaluate($args);

        if($this->required)
        {
            switch($this->result)
            {
            case 'value':
                (!is_array($result) and !is_object($result)) or error('bad_query_result', 'PHP result is not a value');
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
                    error('bad_query_result', 'PHP result is not an object');
                }

            case 'array':
                (is_array($result) and $result === array_values($result)) or error('bad_query_result', 'PHP result is not an array');
                return $result;

            case 'mixed':
                return $result;

            default:
                error('bad_query_result', 'Unsupported PHP query result type: ' . $this->result);
            }
        }
        else
        {
            return $result;
        }
    }

    private $script;
}

?>