<?php

class solr_procedure extends procedure
{
    function __construct($params, $required, $solr, $core, $method, $body, $order_by, $offset = null, $count = null)
    {
        in_array($method, ['add', 'delete', 'query']) or backend_error('bad_config', "Unknown Solr method: $method");

        parent::__construct($params, $required);

        $this->solr = $solr;
        $this->core = $core;
        $this->method = $method;
        $this->body = trim($body);
        $this->order_by = $order_by;
        $this->offset = $offset;
        $this->count = $count;
    }

    function query_direct($args)
    {
        $solr = $this->solr->get($this->core);  

        switch($this->method)
        {
        case 'add':
            foreach($args as $name => $value)
            {
                if(is_array($value))
                {
                    if($value === array_values($value))
                    {
                        foreach($value as $document)
                        {
                            self::add($solr, $document);
                        }
                    }
                    else
                    {
                        self::add($solr, $value);
                    }
                }
                else if(is_object($value))
                {
                    self::add($solr, get_object_vars($value));
                }
                else
                {
                    backend_error('bad_args', 'Bad Solr document: ' . $name);
                }
            }
            $solr->request("<commit/>");
            break;

        case 'delete':
            $solr->deleteByQuery(self::substitute($this->body, $args));
            $solr->request("<commit/>");
            break;

        case 'query':

            $query = new SolrQuery(self::substitute($this->body, $args));

            foreach($this->order_by as $name => $order)
            {
                $query->addSortField($name, $order == 'desc' ? SolrQuery::ORDER_DESC : SolrQuery::ORDER_ASC);
            }

            is_null($this->offset) or $query->setStart(self::substitute($this->offset, $args));
            is_null($this->count) or $query->setRows(self::substitute($this->count, $args));

            $result = [];

            $response = $solr->query($query);
            $object = $response->getResponse();

            if(is_array($object['response']['docs']))
            {
                $result['matched'] = $object['response']['numFound'];

                $documents = [];

                foreach($object['response']['docs'] as $doc)
                {
                    $document = [];

                    foreach($doc as $name => $value)
                    {
                        if(is_array($value))
                        {
                            $items = [];

                            foreach($value as $item)
                            {
                                $items[] = $item;
                            }

                            $document[$name] = $items;
                        }
                        else
                        {
                            $document[$name] = $value;
                        }
                    }

                    $documents[] = $document;
                }

                $result['documents'] = $documents;
            }
            else
            {
                !$this->required or backend_error('bad_input', 'Empty response from Solr procedure');
            }

            return $result;
        }
    }

    private static function substitute($body, $args)
    {
        preg_replace('/\$(\w+)/e', "self::replace('\\1', \$args)", $body);
    }

    private static function replace($name, $args)
    {
        isset($args[$name]) or backend_error('bad_config', "Unknown Solr procedure parameter: $name");
        return $args[$name];
    }

    private static function add($solr, $document)
    {
        $doc = new SolrInputDocument();
        foreach($document as $name => $value)
        {
            $doc->addField($name, $value);
        }
        $solr->addDocument($doc);
    }

    private $solr;
    private $core;
    private $method;
    private $body;
    private $order_by;
    private $offset;
    private $count;
}

?>