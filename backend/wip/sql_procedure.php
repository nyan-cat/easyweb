<?php

require_once(www_root . 'facilities/string.php');
require_once(www_root . 'backend/wip/procedure.php');
require_once(www_root . 'backend/wip/sql.php');

class sql_procedure extends procedure
{
    function __construct($name, $params, $required, $result, $embed, $body, $sql)
    {
        parent::__construct($name, $params, $required, $result, $embed);
        $body = explode(';', trim(trim($body), ';'));
        $this->body = count($body) == 1 ? $body[0] : $body;
        $this->sql = $sql;
    }

    function query_direct($args)
    {
        if(!is_array($this->body))
        {
            $array = $this->sql->query($this->apply($this->body, $args));
            !($this->required and empty($array)) or error('empty_query_result', 'SQL procedure returned empty result');
            return self::result(empty($array) ? [] : [$array]);
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
                error('empty_query_result', 'SQL procedure returned empty result');
            }
            else
            {
                $this->sql->commit();
            }

            return self::result($multiarray);
        }
    }

    private function apply($query, $args)
    {
        $sql = $this->sql;

        return replace(['/\[(\w+)\]/', '/\$(\w+)/'],
        [
            function($matches) use($args)
            {
                $name = $matches[1];
                return isset($args[$name]) ? $args[$name] : $matches[0];
            },
            function($matches) use($sql, $args)
            {
                $name = $matches[1];
                return isset($args[$name]) ? $sql->quote($args[$name]) : $matches[0];
            }
        ], $query);
    }

    private function result($multiarray)
    {
        if(!empty($multiarray))
        {
            switch($this->result)
            {
            case 'value':
                count(get_object_vars($multiarray[0][0])) == 1 or error('bad_query_result', 'SQL result is not a value');
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
                return null;

            case 'array':
                return [];

            case 'multiarray':
                return [];
            }
        }

        error('bad_query_result', 'Unsupported SQL query result type: ' . $this->result);
    }

    private $sql;
    private $body;
}

?>