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
    'resources'     => [],
    'schemas'       => []
];

foreach($config->query('/config/datasources//datasource[@name and @type]') as $datasource)
{
    $name = $datasource['@name'];
    unset($datasource['@name']);
    $result->datasources[$name] = (object) iterator_to_array($datasource->attributes());
}

foreach($config->query('/config//collection[@name]') as $collection)
{
    $procedures = [];

    foreach($collection->query('.//procedure[@name and @datasource]') as $procedure)
    {
        $params = [];
        $output = [];

        if(isset($procedure['@params']))
        {
            foreach(explode(',', $procedure['@params']) as $param)
            {
                $options = (object) [];
                $name = trim($param);
                $filter = explode('|', $name);
                if(count($filter) == 2)
                {
                    $name = trim($filter[0]);
                    $options->filter = trim($filter[1]);
                }
                $params[$name] = $options;
            }
        }

        if($attrib = isset($procedure['@output']) ? $procedure['@output'] : (isset($collection['@output']) ? $collection['@output'] : null))
        {
            if(in_array(isset($procedure['@result']) ? $procedure['@result'] : 'array', ['array', 'object', 'multiarray']))
            {
                foreach(explode(',', $attrib) as $field)
                {
                    $options = (object) [];
                    $filter = explode('|', $field);
                    if(count($filter) == 2)
                    {
                        $name = trim($filter[0]);
                        $options->filter = trim($filter[1]);
                        $output[$name] = $options;
                    }
                }
            }
        }

        $procedures[] = (object) array_merge(iterator_to_array($procedure->attributes()),
        [
            'static' => $procedure->parent()->name() == 'static',
            'params' => $params,
            'body'   => trim($procedure->value()),
            'output' => empty($output) ? null : $output
        ]);
    }

    $result->collections[] = (object)
    [
        'name'       => $collection['@name'],
        'key'        => isset($collection['@key']) ? $collection['@key'] : null,
        'procedures' => $procedures
    ];
}

foreach($config->query('/config/resources//resource[@uri]') as $resource)
{
    $methods = [];

    foreach($resource->query('method[@type]') as $method)
    {
        $access = [];
        if(isset($method['@access']))
        {
            foreach(explode(',', $method['@access']) as $expression)
            {
                $access[] = trim($expression);
            }
        }

        $methods[] = (object)
        [
            'type'   => $method['@type'],
            'access' => $access,
            'script' => trim($method->value())
        ];
    }

    $result->resources[] = (object)
    [
        'uri'     => $resource['@uri'],
        'methods' => $methods
    ];
}

foreach($config->query('/config/schemas//schema[@name and @src]') as $schema)
{
    $result->schemas[$schema['@name']] = $schema['@src'];
}

return $result;

?>