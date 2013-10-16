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

            $this->dispatcher->insert(new sql_procedure
            (
                $procedure['@name'],
                $params,
                $procedure->attribute('required') !== 'false',
                $procedure->value(),
                $sql
            ));
        }
    }
}

foreach($config->query('/config/datasources//datasource[@type = "solr"]') as $ds)
{
    $solr = new solr($ds['@server'], $ds['@port'], $ds['@url'], $ds['@username'], $ds['@password']);

    foreach($config->query('/config/procedures//procedure[@datasource = "' . $ds['@name'] . '"]') as $procedure)
    {
        $params = $config->query_assoc('param', $procedure, '@name', '@type');

        $this->dispatcher->insert(new solr_procedure
        (
            $procedure['@name'],
            $params,
            $procedure->attribute('required') !== 'false',
            $solr,
            $procedure['@core'],
            $procedure['@method'],
            $procedure->value(),
            $config->query_assoc('order-by', $procedure, '@name', '@order'),
            $procedure->attribute('offset'),
            $procedure->attribute('count')
        ));
    }
}

foreach($config->query('/config/datasources//datasource[@type = "foursquare"]') as $ds)
{
    $foursquare = new foursquare($ds['@client-id'], $ds['@client-secret']);

    foreach($config->query('/config/procedures//procedure[@datasource = "' . $ds['@name'] . '"]') as $procedure)
    {
        $this->dispatcher->insert(new foursquare_procedure
        (
            $procedure['@name'],
            $procedure['@method'],
            $procedure->attribute('required') !== 'false',
            $foursquare
        ));
    }
}

foreach($config->query('/config/procedures//procedure[@datasource = "geoip"]') as $procedure)
{
    $this->dispatcher->insert(new geoip_procedure
    (
        $procedure['@name'],
        $procedure['@method'],
        $procedure->attribute('required') !== 'false'
    ));
}

/*foreach($config->query('/config/datasources//datasource[@type = "http"]') as $ds)
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
}*/

foreach($config->query('/config/methods//method') as $method)
{
    $get = [];
    $post = [];

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

    foreach($config->query('get', $method) as $param)
    {
        $p =
        [
            'type'   => $param['@type'],
            'secure' => $param->attribute('secure') === 'true'
        ];

        if($min = $param->attribute('min'))
        {
            $p['min'] = $min;
        }

        if($max = $param->attribute('max'))
        {
            $p['max'] = $max;
        }

        if($default = $param->attribute('default'))
        {
            $p['default'] = $default;
            is_null($param->attribute('required')) or backend_error('bad_config', 'Default and required attributes should not be specified both for the same parameter: ' . $url . ' -> ' . $param['@name']);
            $p['required'] = false;
        }
        else
        {
            $p['required'] = $param->attribute('required') !== 'false';
        }

        if($p['secure'] and (!$p['required'] or isset($p['default'])))
        {
            backend_error('bad_config', 'Secure parameter cannot be required either have default value');
        }

        $get[$param['@name']] = $p;
    }

    foreach($config->query('post', $method) as $param)
    {
        !isset($get[$param['@name']]) or backend_error('bad_config', 'Duplicate parameter name for method ' . $url . ': ' . $param['@name']);

        $p =
        [
            'type'   => $param['@type'],
            'secure' => $param->attribute('secure') === 'true'
        ];

        if($min = $param->attribute('min'))
        {
            $p['min'] = $min;
        }

        if($max = $param->attribute('max'))
        {
            $p['max'] = $max;
        }

        if($default = $param->attribute('default'))
        {
            $p['default'] = $default;
            is_null($param->attribute('required')) or backend_error('bad_config', 'Default and required attributes should not be specified both for the same parameter: ' . $url . ' -> ' . $param['@name']);
            $p['required'] = false;
        }
        else
        {
            $p['required'] = $param->attribute('required') !== 'false';
        }

        if($p['secure'] and (!$p['required'] or isset($p['default'])))
        {
            backend_error('bad_config', 'Secure parameter cannot be required either have default value');
        }

        $post[$param['@name']] = $p;
    }

    $this->insert_method($url, new method($method['@type'], $get, $post, $action, $this));
}

?>