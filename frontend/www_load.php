<?php

$config = xml::load($options['config']);

$templates = $config->root()->attribute('templates');
$engine = $config->root()->attribute('engine');
$cache = $config->root()->attribute('cache');
$schema = $config->root()->attribute('schema');
$api = new api($schema);

foreach($config->query('/config/pages//page') as $page)
{
    $params = [];

    foreach($config->query_assoc('param[@name and @value]', $page, '@name', '@value') as $name => $value)
    {
        $params[$name] = ['type' => 'value', 'value' => $value];
    }

    foreach($config->query_assoc('param[@name and @get]', $page, '@name', '@get') as $name => $get)
    {
        $params[$name] = ['type' => 'get', 'value' => $get];
    }

    $this->router->insert(new page
    (
        $page['@url'],
        $params,
        trim($page->value()),
        $templates,
        $page['@template'],
        $page->attribute('engine') ? $page['@engine'] : $engine,
        $cache,
        $api
    ));
}

?>