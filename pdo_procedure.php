<?php

require_once('procedure.php');

class pdo_procedure extends procedure
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
        
        $pdo = $this->datasource->get();
        $query = $this->apply($this->body, $args);

        $result = $pdo->query($query);

        if($result !== false)
        {
            $doc = new DOMDocument('1.0', 'utf-8');
            $empty = true;
            $n = 0;

            do
            {
                $root = $doc->createElement($this->root[$n]);
                $doc->appendChild($root);

                foreach($result->fetchAll(PDO::FETCH_ASSOC) as $row)
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
            while($result->nextRowset());

            if(!$this->empty && $empty)
            {
                runtime_error('PDO procedure returned empty result: ' . $query);
            }

            return $doc;
        }
        else
        {
            runtime_error('PDO query failed: ' . $query . ' # ' . $this->error());
        }
    }

    private function error()
    {
        $info = $this->pdo->errorInfo();
        return $info[2];
    }

    private function query($args)
    {
        return $this->datasource->get()->multi_query($this->apply($this->body, $args));
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
        return '\'' . $this->datasource->get()->quote($args[$name]) . '\'';
    }

    private $datasource;
    private $item;
    private $body;
}

?>