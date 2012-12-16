<?php

$config = xml::load(config_location);

foreach($config->query('/config/vars//var') as $var)
{
    $this->vars->insert($var['@name'], $var['@value']);
}

function load_procedures($config, $vars, $types, $drivers)
{
    $procedures = array();
    foreach($types as $type)
    {
        $driver = $drivers[$type];

        foreach($config->query('/config/datasources//datasource[@type = "' . $driver . '"]') as $ds)
        {
            $datasource = new sql_datasource($driver, $ds['@server'], $ds['@username'], $ds['@password'], $ds['@database'], $ds['@charset']);

            foreach($config->query('/config/procedures//procedure[@datasource = "' . $ds['@name'] . '"]') as $procedure)
            {
                $procedures[] = new sql_procedure
                (
                    $vars,
                    $datasource,
                    $procedure['@name'],
                    $config->query_assoc('param', $procedure, '@name', '@type'),
                    $procedure->attribute('empty') !== 'false',
                    $procedure->attribute('root'),
                    $procedure->attribute('item'),
                    $procedure->value(),
                    $config->query_assoc('output', $procedure, '@name', '@transform'),
                    $procedure->attribute('permission')
                );
            }
        }
    }
    return $procedures;
}

function load_template($config, $node)
{
    if($node)
    {
        $template = new template($node['@src'], args::decode($node->attribute('args', '')), $node->attribute('xml'));

        foreach($config->query('template', $node) as $child)
        {
            $template->insert($child['@name'], load_template($config, $child));
        }

        return $template;
    }
    else
    {
        return null;
    }
}

foreach(load_procedures($config, $this->vars, array
(
    'cubrid',
    'dblib',
    'firebird',
    'ibm',
    'informix',
    'mysql',
    'oracle',
    'odbc',
    'postgresql',
    'sqlite',
    'mssql',
    '4d'
), array
(
    'cubrid'     => 'cubrid',
    'dblib'      => 'dblib',
    'firebird'   => 'firebird',
    'ibm'        => 'ibm',
    'informix'   => 'informix',
    'mysql'      => 'mysql',
    'oracle'     => 'oci',
    'odbc'       => 'odbc',
    'postgresql' => 'pgsql',
    'sqlite'     => 'sqlite',
    'mssql'      => 'sqlsrv',
    '4d'         => '4d'
)) as $procedure)
{
    $this->dispatcher->insert($procedure);
};

foreach($config->query('/config/pages//page') as $page)
{
    $this->router->insert($page['@name'], new page
    (
        $page->attribute('url'),
        load_template($config, $config->query('template', $page)->first()),
        $page->attribute('action'),
        $page->attribute('permission'),
        $page->attribute('code', '200'),
        $page->attribute('message', 'OK')
    ));
}

foreach($config->query('/config/groups//group') as $group)
{
    $this->access->insert_group($group['@name'], new xpression
    (
        $group->value(),
        $group['@name'],
        $config->query_assoc('param', $group, '@name', '@type')
    ));
}

foreach($config->query('/config/permissions//permission') as $permission)
{
    $this->access->insert_permission($permission['@name'], new xpression
    (
        $permission->value(),
        $permission['@name'],
        $config->query_assoc('param', $permission, '@name', '@type')
    ));
}

?>