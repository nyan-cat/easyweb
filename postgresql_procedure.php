<?php

require_once('procedure.php');

class postgre_procedure extends procedure
{
    function __construct($datasource, $name, $params, $empty, $root, $item, $body, $output = array())
    {
        parent::__construct($name, $params, $empty, $root, $output);
        $this->datasource = $datasource;
        $this->item = explode(',', $item);
        foreach($this->item as &$value)
        {
            $value = trim($value);
        }
        $this->body = $body;
    }

    function query_document($args = array())
    {
        $this->validate($args);
        
        $pg = $this->datasource->get();

        if(pg_send_query($pg, $this->apply($this->body, $args)) === true)
        {
            $doc = new DOMDocument('1.0', 'utf-8');

            $n = 0;
            $empty = true;

            while($result = pg_get_result($pg))
            {
                if(pg_num_rows($pg))
                {
                    $root = $doc->createElement($this->root[$n]);
                    $doc->appendChild($root);

                    while($row = pg_fetch_assoc($result))
                    {
                        $empty = false;
                        $item = $doc->createElement($this->item[$n]);
                        $root->appendChild($item);

                        foreach($row as $name => $value)
                        {
                            $node = $doc->createElement($name, $this->transform($name, $value));
                            $item->appendChild($node);
                        }
                    }
                    ++$n;
                }
                pg_free_result($result);
            }

            if(!$this->empty && $empty)
            {
                runtime_error('PostgreSQL procedure returned empty result: ' . $this->apply($this->body, $args));
            }

            return $doc;
        }
        else
        {
            runtime_error('PostgreSQL query failed: ' . $this->apply($this->body, $args) . ' # ' . pg_last_eror($pg));
        }
    }

    private function apply($query, $args)
    {
        return preg_replace(array('/\[(\w+)]\]/e', '/\$(\w+)/e'), array("\$this->replace('\\1', $args)", "\$this->replace_escape('\\1', $args)"), $query);
    }

    private function replace($name, $args)
    {
        isset($args[$name]) or runtime_error('Unknown procedure parameter: ' . $name);
        return $args[$name];
    }

    private function replace_escape($name, $args)
    {
        isset($args[$name]) or runtime_error('Unknown procedure parameter: ' . $name);
        return '\'' . pg_escape_string($this->datasource->get(), $args[$name]) . '\'';
    }

    private $datasource;

    private $item;
    private $body;
}

?>