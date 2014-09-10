<?php

$this->batch         = $config->batch;
$this->schema        = $config->schema;
$this->documentation = $config->documentation;

$datasources = [];

foreach($config->collections as $options)
{
    $collection = new collection($options->key);

    foreach($options->procedures as $procedure)
    {
        $datasource = $config->datasources[$procedure->datasource];
        $params = $procedure->params;

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
                $params,
                !isset($procedure->required) or $procedure->required !== 'false',
                isset($procedure->result) ? $procedure->result : 'array',
                isset($procedure->embed) ? $procedure->embed : null,
                $procedure->body,
                $datasources[$procedure->datasource]
            ));
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
                    $procedure->method,
                    !isset($procedure->required) or $procedure->required !== 'false',
                    isset($procedure->result) ? $procedure->result : 'array',
                    $datasources[$procedure->datasource]
                ));
                break;

            case 'php':
                $collection->attach($procedure->name, new php_procedure
                (
                    $params,
                    !isset($procedure->required) or $procedure->required !== 'false',
                    isset($procedure->result) ? $procedure->result : 'array',
                    new script($this, $procedure->script)
                ));
                break;

            case 'solr':
                if(!isset($datasources[$procedure->datasource]))
                {
                    $datasources[$procedure->datasource] = new solr($datasources->server, $datasources->port, $datasources->url, $datasources->username, $datasources->password);
                }

                $order_by = [];

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

                $collection->attach($procedure->name, new solr_procedure
                (
                    $params,
                    !isset($procedure->required) or $procedure->required !== 'false',
                    isset($procedure->result) ? $procedure->result : 'array',
                    isset($procedure->embed) ? $procedure->embed : null,
                    $datasources[$procedure->datasource],
                    $procedure->core,
                    $procedure->method,
                    $procedure->body,
                    $order_by,
                    $procedure->offset,
                    $procedure->count
                ));
                break;
            }
        }
    }

    $this->dispatcher->attach($options->name, $collection);
}

foreach($config->resources as $resource)
{
    foreach($resource->methods as $method)
    {
        $this->router->attach($method->type, new method($resource->uri, new script($this, $method->script)));
    }
}

?>