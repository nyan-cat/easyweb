<?php

require_once(www_root . 'xml/document.php');

$config = xml\document::load($filename);

$result = (object)
[
    'engine'    => $config['@engine'],
    'templates' => $config['@templates'],
    'schema'    => $config['@schema'],
    'pages'     => []
];

foreach($config->query('/config/pages//page') as $page)
{
    $params = [];

    foreach($page->query('param[@name and @value]') as $param)
    {
        $params[$param['@name']] = (object) ['value' => $param['@value']];
    }

    foreach($page->query('param[@name and @query]') as $param)
    {
        $params[$param['@name']] = (object) ['query' => $param['@query']];
    }

    $options = (object)
    [
        'uri'    => $page['@uri'],
        'params' => $params
    ];

    if(isset($page['@method']))
    {
        $options->method = $page['@method'];
    }

    if(isset($page['@template']))
    {
        $options->template = $page['@template'];
    }

    if(isset($page['@engine']))
    {
        $options->engine = $page['@engine'];
    }

    $script = trim($page->value());

    if(!empty($script))
    {
        $options->script = $script;
    }

    $result->pages[] = $options;
}

return $result;

?>