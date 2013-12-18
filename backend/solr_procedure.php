<?php

class solr_procedure extends procedure
{
    function __construct($name, $params, $required, $result, $solr, $core, $method, $body, $order_by, $offset, $count)
    {
        in_array($method, ['add', 'delete', 'query']) or backend_error('bad_config', "Unknown Solr method: $method");

        parent::__construct($params, self::make_id($name, $params), $required, $result);

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
            isset($args['_documents']) or backend_error('bad_query', 'Solr _documents argument missing');

            foreach($args['_documents'] as $name => $value)
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

            $offset = isset($args['_offset']) ? $args['_offset'] : $this->offset;
            $count = isset($args['_count']) ? $args['_count'] : $this->count;

            is_null($offset) or $query->setStart($offset);
            is_null($count) or $query->setRows($count);

            if(isset($args['_fields']) or isset($args['_queries']))
            {
                $query->setFacet(true);

                if(isset($args['_fields']))
                {
                    foreach($args['_fields'] as $field)
                    {
                        $query->addFacetField($field);
                    }
                }

                if(isset($args['_queries']))
                {
                    foreach($args['_queries'] as $fq)
                    {
                        $query->addFacetQuery($fq);
                    }
                }
            }

            $result = new stdClass();
            $result->matched = 0;
            $result->documents = [];
            $result->fields = new stdClass();
            $result->queries = new stdClass();

            $response = $solr->query($query);
            $object = $response->getResponse();

            if(is_array($object['response']['docs']))
            {
                $result->matched = $object['response']['numFound'];
                
                foreach($object['response']['docs'] as $doc)
                {
                    $document = new stdClass();

                    foreach($doc as $name => $value)
                    {
                        if($name != '_version_')
                        {
                            if(is_array($value))
                            {
                                $items = [];

                                foreach($value as $item)
                                {
                                    $items[] = $item;
                                }

                                $document->$name = $items;
                            }
                            else
                            {
                                $document->$name = $value;
                            }
                        }
                    }

                    $result->documents[] = $document;
                }
            }
            else
            {
                !$this->required or backend_error('bad_input', 'Empty response from Solr procedure');
            }

            if(isset($args['_fields']))
            {
                foreach($object['facet_counts']['facet_fields'] as $name => $counts)
                {
                    $array = [];

                    foreach($counts as $value => $count)
                    {
                        $array[$value] = $count;
                    }

                    $result->fields->$name = (object) $array;
                }
            }

            if(isset($args['_queries']))
            {
                foreach($object['facet_counts']['facet_queries'] as $fq => $count)
                {
                    $result->queries->$fq = $count;
                }
            }

            switch($this->result)
            {
            case 'array':
                return empty($result) ? (object) ['matched' => 0, 'documents' => [], 'fields' => (object) null, 'queries' => (object) null] : $result;

            case 'object':
                if($result->matched == 1 and count($result->documents) == 1)
                {
                    return $result->documents[0];
                }
                elseif($result->matched == 0 and count($result->documents) == 0)
                {
                    return null;
                }
                else
                {
                    backend_error('bad_query', 'Solr query result is not an object');
                }

            default:
                backend_error('bad_query', 'Unsupported Solr query result type: ' . $this->result);
            }
        }
    }

    private static function substitute($body, $args)
    {
        return preg_replace('/\$(\w+)/e', "self::replace('\\1', \$args)", $body);
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
    private $facets;
    private $order_by;
    private $offset;
    private $count;
}

?>