<?php

$config = xml::load(config_location);

if($schema = $config->root()->attribute('schema'))
{
    $this->schema = $schema;
}

if($documentation = $config->root()->attribute('documentation'))
{
    $this->documentation = $documentation;
}

foreach(sql::drivers() as $type => $internal)
{
    foreach($config->query('/config/datasources//datasource[@type = "' . $type . '"]') as $ds)
    {
        $sql = new sql($ds['@type'], $ds['@server'], $ds['@username'], $ds['@password'], $ds['@database'], $ds['@charset']);

        foreach($config->query('/config/procedures//procedure[@datasource = "' . $ds['@name'] . '"]') as $procedure)
        {
            $params = $config->query_assoc('param', $procedure, '@name', '@type');

            $this->dispatcher->insert(procedure::mangle($procedure['@name'], $params), new sql_procedure
            (
                $params,
                $procedure->attribute('required') !== 'false',
                $procedure->value(),
                $sql
            ));
        }
    }
}

foreach($config->query('/config/methods//method') as $method)
{
    $params = [];

    $url = $method['@url'];

    if($procedure = $method->attribute('procedure'))
    {
        $action = $this->dispatcher->get($procedure, $params);
    }
    else if($script = $method->attribute('script'))
    {
        $action = $script;
    }
    else
    {
        backend_error('bad_config', "Unknown method type: $url");
    }

    foreach($config->query('param', $method) as $param)
    {
        $params[$param['@name']] =
        [
            'type'   => $param['@type'],
            'secure' => $param->attribute('secure') === 'true'
        ];
    }

    $this->methods[$url] = new method($params, $action, $this);
}

/*foreach($config->query('/config/datasources//datasource[@type = "solr"]') as $ds)
{
    $datasource = new solr_datasource($ds['@server'], $ds['@port'], $ds['@url'], $ds['@username'], $ds['@password']);

    foreach($config->query('/config/procedures//procedure[@datasource = "' . $ds['@name'] . '"]') as $procedure)
    {
        $this->dispatcher->insert(new solr_procedure
        (
            $datasource,
            $config->query_assoc('param', $procedure, '@name', '@type'),
            $procedure->attribute('required') !== 'false',
            $procedure['@core'],
            $procedure['@method'],
            $procedure->value(),
            $config->query_assoc('order-by', $procedure, '@name', '@order'),
            $procedure->attribute('offset'),
            $procedure->attribute('count')
        ));
    }
}

foreach($config->query('/config/procedures//procedure[@datasource = "geoip"]') as $procedure)
{
    $this->dispatcher->insert(new geoip_procedure
    (
        $config->query_assoc('param', $procedure, '@name', '@type'),
        $procedure->attribute('required') !== 'false',
        $procedure['@method']
    ));
}

foreach($config->query('/config/datasources//datasource[@type = "http"]') as $ds)
{
    $datasource = new http_datasource($ds['@url'], $ds['@content-type'], $ds->attribute('username'), $ds->attribute('password'));

    foreach($config->query('/config/procedures//procedure[@datasource = "' . $ds['@name'] . '"]') as $procedure)
    {
        $this->dispatcher->insert(new http_procedure
        (
            $datasource,
            $procedure['@method'],
            $procedure['@url'],
            $config->query_assoc('param', $procedure, '@name', '@type'),
            $config->query_assoc('get', $procedure, '@name', '@value'),
            $config->query_assoc('post', $procedure, '@name', '@value'),
            $procedure->attribute('content-type'),
            $procedure->attribute('required') !== 'false'
        ));
    }
}

foreach($config->query('/config/datasources//datasource[@type = "foursquare"]') as $ds)
{
    $datasource = new foursquare_datasource($ds['@client-id'], $ds['@client-secret']);

    foreach($config->query('/config/procedures//procedure[@datasource = "' . $ds['@name'] . '"]') as $procedure)
    {
        $this->dispatcher->insert(new foursquare_procedure
        (
            $datasource,
            $procedure['@method'],
            $config->query_assoc('param', $procedure, '@name', '@type'),
            $procedure->attribute('required') !== 'false'
        ));
    }
}

*/

?>