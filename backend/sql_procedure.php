<?php

require_once('procedure.php');

class sql_procedure extends procedure
{
    function __construct($name, $params, $required, $body, $sql)
    {
        parent::__construct($params, self::make_id($name, $params), $required);
        $body = explode(';', trim(trim($body), ';'));
        $this->body = count($body) == 1 ? $body[0] : $body;
        $this->sql = $sql;
    }

    function query_direct($args)
    {
        if(!is_array($this->body))
        {
            $result = $this->sql->query($this->apply($this->body, $args));
            !($this->required and empty($result)) or backend_error('bad_query', 'SQL procedure returned empty result');
            return $result;
        }
        else
        {
            $set = [];

            $this->sql->begin();

            foreach($this->body as $query)
            {
                $result = $this->sql->query($this->apply($query, $args));
                if(!empty($result))
                {
                    $set[] = $result;
                }
            }

            if(empty($result) and $this->required)
            {
                $this->sql->rollback();
                backend_error('bad_input', 'Empty response from SQL procedure');
            }
            else
            {
                $this->sql->commit();
            }

            return count($set) == 1 ? $set[0] : $set;
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

    private $sql;
    private $body;
}

?>