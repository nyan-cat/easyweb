<?php

require_once('procedure.php');

class sql_procedure extends procedure
{
    function __construct($name, $params, $required, $result, $body, $sql)
    {
        parent::__construct($params, self::make_id($name, $params), $required, $result);
        $body = explode(';', trim(trim($body), ';'));
        $this->body = count($body) == 1 ? $body[0] : $body;
        $this->sql = $sql;
    }

    function query_direct($args)
    {
        if(!is_array($this->body))
        {
            $array = $this->sql->query($this->apply($this->body, $args));
            !($this->required and empty($array)) or backend_error('bad_query', 'SQL procedure returned empty result');
            return self::result([$array]);
        }
        else
        {
            $multiarray = [];

            $this->sql->begin();

            foreach($this->body as $query)
            {
                $array = $this->sql->query($this->apply($query, $args));
                if(!empty($array))
                {
                    $multiarray[] = $array;
                }
            }

            if(empty($multiarray) and $this->required)
            {
                $this->sql->rollback();
                backend_error('bad_input', 'Empty response from SQL procedure');
            }
            else
            {
                $this->sql->commit();
            }

            return self::result($multiarray);
        }
    }

    function evaluate_direct($args)
    {
        $result = $this->query_direct($args);
        count($result) == 1 or backend_error('bad_query', 'SQL query is not evaluateable');
        return $result[0];
    }

    private function apply($query, $args)
    {
        return preg_replace(array('/\[(\w+)\]/e', '/\$(\w+)/e'), array("\$this->replace('\\1', \$args)", "\$this->replace_escape('\\1', \$args)"), $query);
    }

    private function replace($name, $args)
    {
        isset($args[$name]) or backend_error('bad_input', 'Unknown procedure parameter: ' . $name);
        return $args[$name];
    }

    private function replace_escape($name, $args)
    {
        isset($args[$name]) or backend_error('bad_input', 'Unknown procedure parameter: ' . $name);
        return $this->sql->quote($args[$name]);
    }

    private function result($multiarray)
    {
        if(!empty($multiarray))
        {
            switch($this->result)
            {
            case 'value':
                count(get_object_vars($multiarray[0][0])) == 1 or backend_error('bad_query', 'SQL result is not a value');
                return reset($multiarray[0][0]);

            case 'object':
                return $multiarray[0][0];

            case 'array':
                return $multiarray[0];

            case 'multiarray':
                return $multiarray;
            }
        }
        else
        {
            switch($this->result)
            {
            case 'value':
                return null;

            case 'object':
                return (object) null;

            case 'array':
                return [];

            case 'multiarray':
                return [];
            }
        }

        backend_error('bad_query', 'Unsupported SQL query result type: ' . $this->result);
    }

    private $sql;
    private $body;
}

?>