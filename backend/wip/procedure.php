<?php

require_once(www_root . 'backend/wip/datatype.php');

class procedure
{
    function __construct($params, $required, $result)
    {
        $this->params = $params;
        $this->required = $required;
        $this->result = $result;
    }

    function query($params)
    {
        $this->validate($params);
        return $this->query_direct($params);
    }

    function params()
    {
        return $this->params;
    }

    private function validate($params)
    {
        foreach($params as $name => $value)
        {
            if(isset($this->params[$name]->type) and $name[0] != '_')
            {
                datatype::assert($this->params[$name], $value);
            }
        }
    }

    private $params;
    protected $required;
    protected $result;
}

?>