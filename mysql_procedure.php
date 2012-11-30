<?php

require_once('procedure.php');

class mysql_procedure extends procedure
{
    function __construct($datasource, $name, $params, $empty, $root, $item, $body, $output = array())
    {
        parent::__construct($name, $params, $empty, $root, $output);
        $this->datasource = $datasource;
        $this->item = $item;
        $this->body = preg_match('/[^;]*;\s*\S+/', $body) ? 'begin;' . trim(trim($body), ';') . ';commit;' : $body;
    }

    function query_document($args = array())
    {
        $this->validate($args);
        
        $mysqli = $this->datasource->get();

        if($this->query($args) === false)
        {
            runtime_error('First MySQL query statement failed: ' . $this->substitute($this->body, $args) . ' # ' . $mysqli->error);
        }
        else
        {
            $doc = new DOMDocument('1.0', 'utf-8');

            for($n = 0, $empty = true; true;)
            {
                if($result = $mysqli->store_result())
                {
                    $root = $doc->createElement($this->root[$n]);
                    $doc->appendChild($root);

                    while($row = $result->fetch_assoc())
                    {
                        $empty = false;
                        $item = $doc->createElement($this->item);
                        $root->appendChild($item);

                        foreach($row as $name => $value)
                        {
                            $node = $doc->createElement($name, $this->transform($name, $value));
                            $item->appendChild($node);
                        }
                    }
                    $result->free();
                    ++$n;
                }

                if(!$mysqli->more_results())
                {
                    if(!$this->empty && $empty)
                    {
                        runtime_error('MySQL procedure returned empty result: ' . $this->substitute($this->body, $args));
                    }
                    break;
                }

                if(!$mysqli->next_result())
                {
                    runtime_error('MySQL query failed: ' . $this->substitute($this->body, $args) . ' # ' . $mysqli->error);
                    break;
                }
            }

            return $doc;
        }
    }

    private function query($args)
    {
        return $this->datasource->get()->multi_query($this->substitute($this->body, $args));
    }

    private function substitute($query, $args)
    {
        foreach($args as $name => $value)
        {
            $query = str_replace('$' . $name . '$', $this->datasource->get()->real_escape_string($value), $query);
            $query = str_replace('@' . $name . '@', preg_replace('/[a-zA-Z]+/', '', $value), $query);
        }
        return $query;
    }

    private $datasource;

    private $item;
    private $body;
}

?>