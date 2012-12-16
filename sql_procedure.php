<?php

require_once('procedure.php');

class sql_procedure extends procedure
{
    function __construct($vars, $datasource, $name, $params, $empty, $root, $item, $body, $output = array(), $permission = null)
    {
        parent::__construct($vars, $name, $params, $empty, $root, $output, $permission);
        $this->datasource = $datasource;
        if($item)
        {
            $this->item = explode(',', $item);
            foreach($this->item as &$value)
            {
                $value = trim($value);
            }
        }
        $this->body = explode(';', trim(trim($body), ';'));
    }

    function query_document($args = array())
    {
        $this->validate($args);
        $sql = $this->datasource->get();
        $xml = new xml();

        $this->single() or $sql->begin();

        foreach($this->body as $n => $query)
        {
            if($rows = $sql->query($this->apply($query, $args)))
            {
                $root = $xml->element($this->root[$n]);
                $xml->append($root);

                foreach($rows as $row)
                {
                    $item = $xml->element($this->item[$n]);
                    $root->append($item);

                    foreach($row as $name => $value)
                    {
                        $node = $this->transform($xml, $name, $value);
                        $item->append($node);
                    }
                }
            }
        }

        if(!$this->empty && $xml->blank())
        {
            $this->single() or $sql->rollback();
            runtime_error('SQL procedure returned empty result: ' . $this->mangled());
        }
        else
        {
            $this->single() or $sql->commit();
        }

        return $xml;
    }

    private function single()
    {
        return count($this->body) <= 1;
    }

    private function apply($query, $args)
    {
        return preg_replace(array('/\[(\w+)\]/e', '/\$(\w+)/e'), array("\$this->replace('\\1', \$args)", "\$this->replace_escape('\\1', \$args)"), $query);
    }

    private function replace($name, $args)
    {
        isset($args[$name]) or runtime_error('Unknown procedure parameter: ' . $name);
        return $args[$name];
    }

    private function replace_escape($name, $args)
    {
        isset($args[$name]) or runtime_error('Unknown procedure parameter: ' . $name);
        return $this->datasource->get()->quote($args[$name]);
    }

    private $datasource;
    private $item;
    private $body;
}

?>