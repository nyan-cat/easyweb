<?php

require_once(www_root . 'xml/document.php');

$config = xml\document::load($filename);

$result = (object)
[
    'batch'         => $config['@batch'],
    'schema'        => $config['@schema'],
    'documentation' => $config['@documentation'],
    'datasources'   => [],
    'collections'   => [],
    'resources'     => []
];

foreach($config->query('/config/datasources//datasource[@name and @type]') as $datasource)
{
    $name = $datasource['@name'];
    unset($datasource['@name']);
    $result->datasources[$name] = (object) iterator_to_array($datasource->attributes());
}

foreach($config->query('/config/collections//collection') as $collection)
{
    $procedures = [];

    foreach($collection->query('procedure[@name and @datasource]') as $procedure)
    {
        $params = [];

        if(isset($procedure['@params']))
        {
            foreach(explode(',', $procedure['@params']) as $param)
            {
                $param = trim($param);

                $params[$param] = (object) [];
            }
        }

        $procedures[] = (object) array_merge(iterator_to_array($procedure->attributes()),
        [
            'params'     => $params,
            'body'       => trim($procedure->value())
        ]);
    }

    $result->collections[] = (object)
    [
        'name'       => $collection['@name'],
        'key'        => $collection['@key'],
        'procedures' => $procedures
    ];
}

foreach($config->query('/config/resources//resource[@uri]') as $resource)
{
    $methods = [];

    foreach($resource->query('method[@type]') as $method)
    {
        $methods[] = (object)
        [
            'type'   => $method['@type'],
            'script' => trim($method->value())
        ];
    }

    $result->resources[] = (object)
    [
        'uri'     => $resource['@uri'],
        'methods' => $methods
    ];
}

return $result;

?>