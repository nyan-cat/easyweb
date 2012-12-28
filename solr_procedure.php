<?php

class solr_procedure extends procedure
{
    function __construct($vars, $datasource, $name, $params, $empty, $root, $core, $method, $body, $offset = null, $count = null, $output = array(), $permission = null)
    {
        parent::__construct($vars, $name, $params, $empty, $root, $output, $permission);
        $this->datasource = $datasource;
        $this->core = $core;
        $this->method = $method;
        $this->body = trim($body);
        $this->offset = $offset;
        $this->count = $count;
    }

    function query($args, $document)
    {
        $this->validate($args);
        $xml = new xml();
        $solr = $this->datasource->get($this->core);
        switch($this->method)
        {
        case 'add':
            $doc = new SolrInputDocument();
            foreach($args as $name => $value)
            {
                $doc->addField($name, $value);
            }
            $solr->addDocument($doc);
            break;
        case 'delete':
            $solr->deleteByQuery(vars::apply_assoc($this->body, $args));
            break;
        case 'query':
            $root = $xml->element($this->root[0]);
            $xml->append($root);
            $query = new SolrQuery(vars::apply_assoc($this->body, $args));
            if(!is_null($this->offset))
            {
                $query->setStart($this->offset - 1);
            }
            is_null($this->count) or $query->setRows($this->count);
            $response = $solr->query($query);
            $object = $response->getResponse();
            foreach($object['response']['docs'] as $doc)
            {
                $item = $xml->element('doc');
                $root->append($item);
                foreach($doc as $name => $value)
                {
                    if(is_array($value))
                    {
                        $array = $xml->element($name);
                        $item->append($array);
                        foreach($value as $element)
                        {
                            $element = $xml->element('element', $element);
                            $array->append($element);
                        }
                    }
                    else
                    {
                        $node = $xml->element($name, $value);
                        $item->append($node);
                    }
                }
            }
            break;
        default:
            runtime_error('Unknown Solr method: ' . $this->method);
        }
        return $xml;
    }

    private $datasource;
    private $core;
    private $method;
    private $body;
    private $offset;
    private $count;
}

?>