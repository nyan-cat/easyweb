<?php

class solr_procedure extends procedure
{
    function __construct($vars, $datasource, $name, $params, $empty, $root, $item, $core, $method, $body, $order_by, $offset = null, $count = null, $output = array(), $permission = null, $cache = true)
    {
        parent::__construct($vars, $name, $params, $empty, $root, $output, $permission, $cache);
        $this->datasource = $datasource;
        if($item)
        {
            $this->item = explode(',', $item);
            foreach($this->item as &$value)
            {
                $value = trim($value);
            }
        }
        $this->core = $core;
        $this->method = $method;
        $this->body = trim($body);
        $this->order_by = $order_by;
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
            !empty($args) or runtime_error('Solr add method should accept parameters');
            if(is_array(reset($args)))
            {
                $docs = array();
                foreach(reset($args) as $document)
                {
                    $doc = new SolrInputDocument();
                    foreach($document as $name => $value)
                    {
                        $doc->addField($name, $value);
                    }
                    $docs[] = $doc;
                }
                $solr->addDocuments($docs);
            }
            else
            {
                $doc = new SolrInputDocument();
                foreach($args as $name => $value)
                {
                    $doc->addField($name, $value);
                }
                $solr->addDocument($doc);
            }
            $solr->request("<commit/>");
            break;
        case 'delete':
            $solr->deleteByQuery(vars::apply_assoc($this->body, $args));
            $solr->request("<commit/>");
            break;
        case 'query':
            $root = $xml->element($this->root[0]);
            $xml->append($root);
            $query = new SolrQuery(vars::apply_assoc($this->body, $args));
            foreach($this->order_by as $name => $order)
            {
                $query->addSortField($name, $order == 'desc' ? SolrQuery::ORDER_DESC : SolrQuery::ORDER_ASC);
            }
            if(!is_null($this->offset))
            {
                $query->setStart(vars::apply_assoc($this->offset, $args));
            }
            is_null($this->count) or $query->setRows(vars::apply_assoc($this->count, $args));
            $response = $solr->query($query);
            $object = $response->getResponse();
            if(is_array($object['response']['docs']))
            {
                $root['@matched'] = $object['response']['numFound'];

                foreach($object['response']['docs'] as $doc)
                {
                    $item = $xml->element($this->item[0]);
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
                            $node = $this->transform($xml, $name, $value);
                            $item->append($node);
                        }
                    }
                }
            }
            else
            {
                $this->empty or runtime_error('Procedure returned an empty result: ' . $this->mangled());
            }
            break;
        default:
            runtime_error('Unknown Solr method: ' . $this->method);
        }
        return $xml;
    }

    private $datasource;
    private $item;
    private $core;
    private $method;
    private $body;
    private $offset;
    private $count;
}

?>