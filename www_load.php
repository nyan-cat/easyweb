<?php

require_once('xml.php');
require_once('mysql_datasource.php');
require_once('mysql_procedure.php');
require_once('page.php');
require_once('template.php');

$config = new xml();
$config->load(config_location);

foreach($config->query('/config/vars//var') as $var)
{
    $this->vars->insert($var['@name'], $var['@value']);
}

foreach($config->query('/config/datasources//datasource[@type = "mysql"]') as $ds)
{
    $datasource = new mysql_datasource($ds['@server'], $ds['@username'], $ds['@password'], $ds['@database'], $ds['@charset']);

    foreach($config->query('/config/procedures//procedure[@datasource = "' . $ds['@name'] . '"]') as $procedure)
    {
        $this->dispatcher->insert(new mysql_procedure
        (
            $datasource,
            $procedure['@name'],
            $config->query_assoc('param', $procedure, '@name', '@type'),
            $procedure['@empty'] === 'true',
            $procedure['@root'],
            $procedure['@item'],
            $procedure->value(),
            $config->query_assoc('output', $procedure, '@name', '@transform')
        ));
    }
}

function load_template($config, $node)
{
    if($node)
    {
        $template = new template($node['@src'], $node->attribute('xml'));

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

foreach($config->query('/config/roles//role') as $role)
{
    $this->access->insert_role($role['@name'], $role['@value']);
}

foreach($config->query('/config/permissions//permission') as $permission)
{
    $this->access->insert_permission($permission['@name'], $permission['@value']);
}

?>