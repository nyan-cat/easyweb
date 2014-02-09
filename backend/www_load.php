<?php

$config = xml::load($options->config);

if(($batch = $config->root()->attribute('batch')) !== null)
{
    $this->batch = $batch;
}

if(($schema = $config->root()->attribute('schema')) !== null)
{
    $this->schema = $schema;
}

if(($documentation = $config->root()->attribute('documentation')) !== null)
{
    $this->documentation = $documentation;
}

foreach($config->query('/config/vars//var') as $var)
{
    $this->vars[$var['@name']] = $var->value();
}

foreach($config->query('/config/folders//folder') as $folder)
{
    $this->folders[$folder['@name']] = $folder['@path'];
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
                $procedure->attribute('result', 'array'),
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

        $order_by = [];

        foreach($config->query('order-by', $procedure) as $order)
        {
            $mode =
            [
                'type'  => isset($order['@type']) ? $order['@type'] : 'normal',
                'order' => $order['@order']
            ];

            if(isset($order['@point']))
            {
                $mode['point'] = $order['@point'];
            }

            $order_by[$order['@name']] = (object) $mode;
        }

        $this->dispatcher->insert(new solr_procedure
        (
            $procedure['@name'],
            $params,
            $procedure->attribute('required') !== 'false',
            $procedure->attribute('result', 'array'),
            $solr,
            $procedure['@core'],
            $procedure['@method'],
            $procedure->value(),
            $order_by,
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
            $procedure->attribute('result', 'array'),
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
        $procedure->attribute('required') !== 'false',
        $procedure->attribute('result', 'array')
    ));
}

foreach($config->query('/config/procedures//procedure[@datasource = "php"]') as $procedure)
{
    $params = $config->query_assoc('param', $procedure, '@name', '@type');

    $require = [];

    foreach($config->query('require/@src', $procedure) as $src)
    {
        $require[] = $src->value();
    }

    $this->dispatcher->insert(new php_procedure
    (
        $procedure['@name'],
        $params,
        $procedure->attribute('required') !== 'false',
        $procedure->attribute('result', 'array'),
        $require,
        $procedure->value(),
        $this,
        $this->dispatcher
    ));
}

foreach($config->query('/config/domains//domain') as $domain)
{
    $name = $domain['@name'];

    if($extends = $domain->attribute('extends'))
    {
        isset($this->domains[$extends]) or backend_error('bad_config', 'Unknown base domain: ' . $extends);
        $this->domains[$name] = $name . '.' . $this->domains[$extends];
    }
    else
    {
        $this->domains[$name] = $name;
    }
}

$services = [];

foreach($config->query('/config/services//service') as $service)
{
    $services[$service['@name']] =
    [
        'accept'       => $service['@accept'],
        'content-type' => $service['@content-type'],
        'host'         => $service['@host']
    ];
}

foreach($config->query('/config/methods//method') as $method)
{
    $get = [];
    $post = [];

    $service = $method['@service'];
    isset($services[$service]) or backend_error('bad_config', "Unknown service $service");
    $service = $services[$service];
    $url = $method['@url'];

    if($accept = $method->attribute('accept'))
    {
        $service['accept'] = $accept;
    }
    if($content_type = $method->attribute('content-type'))
    {
        $service['content-type'] = $content_type;
    }
    if($host = $method->attribute('host'))
    {
        $service['host'] = $host;
    }
    
    foreach($config->query('get', $method) as $param)
    {
        $p = ['type' => $param['@type']];

        if(($domains = $param->attribute('domain')) !== null)
        {
            $domains = explode(',', $param['@domain']);
            foreach($domains as &$domain)
            {
                $domain = trim($domain);
                isset($this->domains[$domain]) or backend_error('bad_config', 'Unknown security domain: ' . $domain);
                $domain = $this->domains[$domain];
            }
            $p['domains'] = $domains;
        }

        if(($min = $param->attribute('min')) !== null)
        {
            $p['min'] = $min;
        }

        if(($max = $param->attribute('max')) !== null)
        {
            $p['max'] = $max;
        }

        if(($default = $param->attribute('default')) !== null)
        {
            $p['default'] = $default;
            is_null($param->attribute('required')) or backend_error('bad_config', 'Default and required attributes shall not be specified both for the same parameter: ' . $url . ' -> ' . $param['@name']);
            $p['required'] = false;
        }
        else
        {
            $p['required'] = $param->attribute('required') !== 'false';
        }

        $get[$param['@name']] = (object) $p;
    }

    foreach($config->query('post', $method) as $param)
    {
        !isset($get[$param['@name']]) or backend_error('bad_config', 'Duplicate parameter name for method ' . $url . ': ' . $param['@name']);

        $p = ['type' => $param['@type']];

        if(($domains = $param->attribute('domain')) !== null)
        {
            $domains = explode(',', $param['@domain']);
            foreach($domains as &$domain)
            {
                $domain = trim($domain);
                isset($this->domains[$domain]) or backend_error('bad_config', 'Unknown security domain: ' . $domain);
                $domain = $this->domains[$domain];
            }
            $p['domains'] = $domains;
        }

        if(($min = $param->attribute('min')) !== null)
        {
            $p['min'] = $min;
        }

        if(($max = $param->attribute('max')) !== null)
        {
            $p['max'] = $max;
        }

        if(($default = $param->attribute('default')) !== null)
        {
            $p['default'] = $default;
            is_null($param->attribute('required')) or backend_error('bad_config', 'Default and required attributes should not be specified both for the same parameter: ' . $url . ' -> ' . $param['@name']);
            $p['required'] = false;
        }
        else
        {
            $p['required'] = $param->attribute('required') !== 'false';
        }

        $post[$param['@name']] = (object) $p;
    }

    $require = [];

    foreach($config->query('require/@src', $method) as $src)
    {
        $require[] = $src->value();
    }

    $body = trim($method->value());
    if(empty($body))
    {
        $body = null;
    }

    $this->insert_method($url, new method
    (
        $method['@type'],
        $get,
        $post,
        $service['accept'],
        $service['content-type'],
        $service['host'],
        $method->attribute('access'),
        $method->attribute('procedure'),
        $require,
        $body,
        $this
    ));
}

?>