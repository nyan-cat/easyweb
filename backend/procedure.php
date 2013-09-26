<?php

require_once('exception.php');

class procedure
{
    function __construct($params, $required = true)
    {
        $this->params = $params;
        $this->required = $required;
    }

    function validate($params)
    {
        foreach($params as $name => $value)
        {
            validate::assert($this->params[$name], $value);
        }
    }

    function query($args)
    {
        $result = $this->query_direct($args);
        if(empty($result) and $this->required)
        {
            backend_error('bad_input', 'Empty response from procedure');
        }
    }

    static function mangle($name, $params)
    {
        return $name . '[' . implode(',', array_keys($params)) . ']';
    }

    private $params;
    private $required;
}

?>