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

            $params = array_merge([$options->key => (object) []], $procedure->params);

            $collection->attach($procedure->name, $params, new sql_procedure
            (
                $params,
                isset($procedure->required) and $procedure->required !== 'false',
                isset($procedure->result) ? $procedure->result : 'array',
                $procedure->body,
                $datasources[$procedure->datasource]
            ));
        }
        else
        {
            switch($datasource->type)
            {
            case 'mysql':
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