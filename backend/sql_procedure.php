<?php

require_once('procedure.php');

class sql_procedure extends procedure
{
    function __construct($params, $required, $body, $sql)
    {
        parent::__construct($params, $required);
        $body = explode(';', trim(trim($body), ';'));
        $this->body = count($body) == 1 ? $body[0] : $body;
        $this->sql = $sql;
    }

    function query_direct($args)
    {
        $this->validate($args);

        if(is_array($this->body))
        {
            $result = $sql->query($this->apply($this->body, $args));
            !($this->required and empty($result)) or backend_error('bad_query', 'SQL procedure returned empty result');
        }
        else
        {
            $result = [];

            $this->sql->begin();

            foreach($this->body as $query)
            {
                $result[] = $sql->query($this->apply($query, $args));
            }

            $sql->commit();
        }

        return $result;
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