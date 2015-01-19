<?php

$this->batch         = $config->batch;
$this->schema        = $config->schema;
$this->documentation = $config->documentation;

$datasources = [];

foreach($config->collections as $options)
{
    $collection = new collection($options->name, $options->key);

    foreach($options->procedures as $procedure)
    {
        $params = $procedure->params;

        if($procedure->datasource == 'php')
        {
            $collection->attach($procedure->name, new php_procedure
            (
                $procedure->name,
                $params,
                !isset($procedure->required) or $procedure->required !== 'false',
                isset($procedure->result) ? $procedure->result : 'array',
                new script($this, $procedure->body)
            ), $procedure->static);
        }
        else
        {
            $datasource = $config->datasources[$procedure->datasource];

            if(in_array($datasource->type, array_keys(sql::drivers())))
            {
                $drivers = sql::drivers();

                if(!isset($datasources[$procedure->datasource]))
                {
                    $datasources[$procedure->datasource] = new sql
                    (
                        $datasource->type,
                        $datasource->server,
                        $datasource->username,
                        $datasource->password,
                        $datasource->database,
                        $datasource->charset
                    );
                }

                $collection->attach($procedure->name, new sql_procedure
                (
                    $procedure->name,
                    $params,
                    !isset($procedure->required) or $procedure->required !== 'false',
                    isset($procedure->result) ? $procedure->result : 'array',
                    isset($procedure->output) ? $procedure->output : null,
                    $procedure->body,
                    $datasources[$procedure->datasource]
                ), $procedure->static);
            }
            else
            {
                switch($datasource->type)
                {
                case 'foursquare':
                    if(!isset($datasources[$procedure->datasource]))
                    {
                        $datasources[$procedure->datasource] = new foursquare($datasources->client_id, $datasources->client_secret);
                    }

                    $collection->attach($procedure->name, new foursquare_procedure
                    (
                        $procedure->name,
                        $procedure->method,
                        !isset($procedure->required) or $procedure->required !== 'false',
                        isset($procedure->result) ? $procedure->result : 'array',
                        $datasources[$procedure->datasource]
                    ), $procedure->static);
                    break;

                case 'solr':
                    if(!isset($datasources[$procedure->datasource]))
                    {
                        $datasources[$procedure->datasource] = new solr($datasource->server, $datasource->port, $datasource->url, $datasource->username, $datasource->password);
                    }

                    $order_by = [];

                    if(isset($procedure->order_by))
                    {
                        foreach($procedure->order_by as $order)
                        {
                            $mode = (object)
                            [
                                'type'  => isset($order['@type']) ? $order['@type'] : 'normal',
                                'order' => $order['@order']
                            ];
                    
                            if(isset($order->point))
                            {
                                $mode->point = $order->point;
                            }
                    
                            $order_by[$order->name] = $mode;
                        }
                    }

                    $collection->attach($procedure->name, new solr_procedure
                    (
                        $procedure->name,
                        $params,
                        !isset($procedure->required) or $procedure->required !== 'false',
                        isset($procedure->result) ? $procedure->result : 'array',
                        isset($procedure->output) ? $procedure->output : null,
                        $datasources[$procedure->datasource],
                        $procedure->core,
                        $procedure->method,
                        $procedure->body,
                        $order_by,
                        isset($procedure->offset) ? $procedure->offset : 0,
                        isset($procedure->count) ? $procedure->count : 10
                    ), $procedure->static);
                    break;
                }
            }
        }
    }

    $this->dispatcher->attach($options->name, $collection);
}

foreach($config->resources as $resource)
{
    foreach($resource->methods as $method)
    {
        $this->router->attach($method->type, new method($resource->uri, $method->access, new script($this, $method->script)));
    }
}

foreach($config->schemas as $name => $schema)
{
    $this->schemas[$name] = json\schema::load($schema->src);
    fs\write($schema->solr, $this->schemas[$name]->solr());
}

?>