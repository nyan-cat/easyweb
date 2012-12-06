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
        $this->body = explode(';', trim(trim($body), ';'));
    }

    function query_document($args = array())
    {
        $this->validate($args);
        $pdo = $this->datasource->get();
        $doc = new DOMDocument('1.0', 'utf-8');
        $empty = true;

        if(count($this->body) > 1)
        {
            $autocommit = $pdo->getAttribute(PDO::ATTR_AUTOCOMMIT);
            $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
            $pdo->beginTransaction();
            $n = 0;

            foreach($this->body as $query)
            {
                $query = $this->apply($query, $args);
                if($result = $pdo->query($query))
                {
                    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
                    if(count($rows))
                    {
                        $empty = false;
                        $root = $doc->createElement($this->root[$n]);
                        $doc->appendChild($root);
                        
                        foreach($rows as $row)
                        {
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
                }
                else
                {
                    $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, $autocommit);
                    $pdo->rollBack();
                    runtime_error('PDO transaction failed: ' . $query . ' # ' . $this->error());
                }
            }

            if(!$this->empty && $empty)
            {
                $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, $autocommit);
                $pdo->rollBack();
                runtime_error('PDO procedure returned empty result: ' . $query);
            }

            $pdo->commit() or runtime_error('PDO transaction commit failed: ' . $query . ' # ' . $this->error());
        }
        else
        {
            $query = $this->apply($this->body[0], $args);
            if($result = $pdo->query($query))
            {
                $rows = $result->fetchAll(PDO::FETCH_ASSOC);
                if(count($rows))
                {
                    $empty = false;
                    $root = $doc->createElement($this->root[0]);
                    $doc->appendChild($root);
                    
                    foreach($rows as $row)
                    {
                        $item = $doc->createElement($this->item[0]);
                        $root->appendChild($item);

                        foreach($row as $name => $value)
                        {
                            $node = $doc->createElement($name, $this->transform($name, $value));
                            $item->appendChild($node);
                        }
                    }
                }
            }
            else
            {
                runtime_error('PDO query failed: ' . $query . ' # ' . $this->error());
            }

            if(!$this->empty && $empty)
            {
                runtime_error('PDO procedure returned empty result: ' . $query);
            }
        }

        return $doc;
    }

    private function error()
    {
        $info = $this->datasource->get()->errorInfo();
        return $info[2];
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