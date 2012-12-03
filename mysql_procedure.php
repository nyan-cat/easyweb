<?php

require_once('procedure.php');

class mysql_procedure extends procedure
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
        $this->body = preg_match('/[^;]*;\s*\S+/', $body) ? 'begin;' . trim(trim($body), ';') . ';commit;' : $body;
    }

    function query_document($args = array())
    {
        $this->validate($args);
        
        $mysqli = $this->datasource->get();

        if($this->query($args) === false)
        {
            runtime_error('First MySQL query statement failed: ' . $this->apply($this->body, $args) . ' # ' . $mysqli->error);
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
                        $item = $doc->createElement($this->item[$n]);
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
                        runtime_error('MySQL procedure returned empty result: ' . $this->apply($this->body, $args));
                    }
                    break;
                }

                if(!$mysqli->next_result())
                {
                    $mysqli->rollback();
                    runtime_error('MySQL query failed: ' . $this->apply($this->body, $args) . ' # ' . $mysqli->error);
                    break;
                }
            }

            return $doc;
        }
    }

    private function query($args)
    {
        return $this->datasource->get()->multi_query($this->apply($this->body, $args));
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
        return '\'' . $this->datasource->get()->real_escape_string($args[$name]) . '\'';
    }

    private $datasource;

    private $item;
    private $body;
}

?>